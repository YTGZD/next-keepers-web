<?php

namespace App\Http\Controllers;

use App\Services\Listings\ListingPhotos;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serves listing photos from the public disk. A route instead of the /storage
 * symlink, so photos show whether or not `storage:link` ran in the container.
 * File names are unguessable tokens and a new upload gets a new name, so the
 * response can be cached for a year.
 */
class ListingPhotoController extends Controller
{
    public function __invoke(string $file): BinaryFileResponse
    {
        $path = ListingPhotos::DIRECTORY.'/'.$file;
        abort_unless(ListingPhotos::isOwnPath($path) && Storage::disk(ListingPhotos::DISK)->exists($path), 404);

        return response()->file(Storage::disk(ListingPhotos::DISK)->path($path), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
