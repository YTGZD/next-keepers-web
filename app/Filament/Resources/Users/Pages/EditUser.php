<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** role is not mass-assignable on User, so it is set explicitly here. */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $data['role'] ?? null;
        unset($data['role'], $data['email'], $data['membership_tier'], $data['nda_status']);
        $record->update($data);

        if ($role !== null && ! $record->is(auth()->user()) && array_key_exists($role, UserResource::ROLES)) {
            $record->forceFill(['role' => $role])->save();
        }

        return $record;
    }
}
