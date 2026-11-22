<?php

namespace App\Filament\Resources\Signups\Pages;

use App\Filament\Resources\Signups\EarlyAccessSignupResource;
use Filament\Resources\Pages\ListRecords;

class ListEarlyAccessSignups extends ListRecords
{
    protected static string $resource = EarlyAccessSignupResource::class;
}
