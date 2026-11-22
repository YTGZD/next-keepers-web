<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Initials avatar rendered as an inline SVG. Replaces Filament's default,
 * which loads images from ui-avatars.com (the site uses no external CDNs).
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $name = (string) Filament::getNameForDefaultAvatar($record);
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: '?';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#1B604A"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Inter, sans-serif" font-size="26" font-weight="600" fill="#F5F2EA">'
            .htmlspecialchars($initials, ENT_XML1).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
