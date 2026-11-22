<?php

namespace App\Filament\Resources\Imports\Pages;

use App\Filament\Resources\Imports\ListingImportResource;
use Filament\Resources\Pages\ListRecords;

class ListListingImports extends ListRecords
{
    protected static string $resource = ListingImportResource::class;

    protected function getHeaderActions(): array
    {
        return [ListingImportResource::importAction()];
    }
}
