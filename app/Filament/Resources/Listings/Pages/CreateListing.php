<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_sample'] = false;
        $data['slug'] = Str::limit(Str::slug($data['name']), 120, '').'-'.Str::lower(Str::random(6));
        $data['listing_key'] = $data['listing_key'] ?: null;
        $data['first_seen_at'] = now();

        return $data;
    }
}
