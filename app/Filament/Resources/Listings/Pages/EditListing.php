<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['listing_key'] = $data['listing_key'] ?: null;
        if (in_array($data['status'] ?? null, ['active', 'under_loi', 'sold'], true) && ! $this->record->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
