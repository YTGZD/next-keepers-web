<?php

namespace App\Services\Listings;

use App\Models\Business;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Photos admins upload for listings.
 *
 * Each upload is re-encoded with GD into two files on the public disk:
 *   listings/<token>.webp        at most 1600 px on the long side (detail page)
 *   listings/<token>-thumb.webp  640 x 480, cropped to fill (cards, admin table)
 * Re-encoding drops EXIF data (camera, GPS) and anything that isn't pixels.
 * Without GD the original file is kept as both sizes.
 *
 * Files are served by the listing-photo route, not the /storage symlink, so
 * they work whether or not `storage:link` ran in the container. They live in
 * storage/, which the server keeps on a volume across rebuilds.
 */
class ListingPhotos
{
    public const DISK = 'public';

    public const DIRECTORY = 'listings';

    public const MAX_KB = 10240;

    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const LARGE = 1600;

    private const THUMB_WIDTH = 640;

    private const THUMB_HEIGHT = 480;

    /** File names the photo route will serve. */
    public const FILE_PATTERN = '[A-Za-z0-9]{40}(-thumb)?\.(webp|jpg|png)';

    /** Process an upload and return its path on the public disk (the large size). */
    public static function store(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        if (! in_array($mime, self::MIME_TYPES, true)) {
            throw new RuntimeException('Upload a JPG, PNG or WebP image.');
        }
        if ($file->getSize() > self::MAX_KB * 1024) {
            throw new RuntimeException('Images can be at most 10 MB.');
        }

        $token = Str::random(40);
        $disk = Storage::disk(self::DISK);

        $image = self::canProcess() ? self::load($file->getRealPath(), $mime) : null;

        if ($image === null) {
            if (self::canProcess() || @getimagesize($file->getRealPath()) === false) {
                throw new RuntimeException('That file could not be read as an image.');
            }
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
            $path = self::DIRECTORY."/{$token}.{$ext}";
            $disk->put($path, file_get_contents($file->getRealPath()));
            $disk->put(self::thumbPath($path), file_get_contents($file->getRealPath()));

            return $path;
        }

        $image = self::orient($image, $file->getRealPath(), $mime);
        [$encode, $ext] = self::encoder();
        $path = self::DIRECTORY."/{$token}.{$ext}";

        $disk->put($path, $encode(self::fit($image, self::LARGE)));
        $disk->put(self::thumbPath($path), $encode(self::cover($image, self::THUMB_WIDTH, self::THUMB_HEIGHT)));

        return $path;
    }

    /** Remove a photo and its thumbnail. */
    public static function delete(?string $path): void
    {
        if (blank($path) || ! self::isOwnPath($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete([$path, self::thumbPath($path)]);
    }

    public static function thumbPath(string $path): string
    {
        return preg_replace('/\.(\w+)$/', '-thumb.$1', $path);
    }

    public static function url(?string $path, bool $thumb = false): ?string
    {
        if (blank($path) || ! self::isOwnPath($path)) {
            return null;
        }

        return route('listing-photo', ['file' => basename($thumb ? self::thumbPath($path) : $path)]);
    }

    public static function isOwnPath(string $path): bool
    {
        return (bool) preg_match('#^'.self::DIRECTORY.'/'.self::FILE_PATTERN.'$#', $path);
    }

    public static function canProcess(): bool
    {
        return function_exists('imagecreatetruecolor');
    }

    /** Set or clear a listing's photo. The model deletes the old files. */
    public static function attach(Business $business, ?string $path): void
    {
        $business->forceFill(['photo_path' => $path])->save();
    }

    /** @return \GdImage|null */
    private static function load(string $file, string $mime)
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png' => @imagecreatefrompng($file),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
            default => false,
        };

        return $image ?: null;
    }

    /** Phone photos store rotation in EXIF; apply it, since re-encoding drops the tag. */
    private static function orient($image, string $file, string $mime)
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($file)['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        return $rotated ?: $image;
    }

    /** @return array{0: callable, 1: string} */
    private static function encoder(): array
    {
        $capture = function (callable $write): string {
            ob_start();
            $write();

            return (string) ob_get_clean();
        };

        if (function_exists('imagewebp')) {
            return [fn ($image) => $capture(fn () => imagewebp($image, null, 82)), 'webp'];
        }

        return [fn ($image) => $capture(fn () => imagejpeg($image, null, 84)), 'jpg'];
    }

    /** Scale down so the long side is at most $max (never up). */
    private static function fit($image, int $max)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $scale = min(1, $max / max($w, $h));

        return self::resample($image, 0, 0, $w, $h, max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
    }

    /** Crop to the target aspect ratio around the centre, then scale. */
    private static function cover($image, int $width, int $height)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $ratio = $width / $height;

        if ($w / $h > $ratio) {
            $cropW = (int) round($h * $ratio);
            $cropH = $h;
        } else {
            $cropW = $w;
            $cropH = (int) round($w / $ratio);
        }

        $outW = min($width, $cropW);
        $outH = (int) round($outW / $ratio);

        return self::resample($image, (int) (($w - $cropW) / 2), (int) (($h - $cropH) / 2), $cropW, $cropH, $outW, max(1, $outH));
    }

    private static function resample($image, int $x, int $y, int $w, int $h, int $outW, int $outH)
    {
        $out = imagecreatetruecolor($outW, $outH);
        // Flatten transparency onto ivory, the site background.
        imagefill($out, 0, 0, imagecolorallocate($out, 0xF5, 0xF2, 0xEA));
        imagecopyresampled($out, $image, 0, 0, $x, $y, $outW, $outH, $w, $h);

        return $out;
    }
}
