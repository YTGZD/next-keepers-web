<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    /** Only the status is editable; the buyer's own words are kept as sent. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['status' => $data['status']];
    }
}
