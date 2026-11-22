<?php

namespace App\Filament\Resources\Signups;

use App\Filament\Resources\Signups\Pages\ListEarlyAccessSignups;
use App\Models\EarlyAccessSignup;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

/** Early-access signups from the homepage. Read-only apart from marking who was invited. */
class EarlyAccessSignupResource extends Resource
{
    protected static ?string $model = EarlyAccessSignup::class;

    protected static ?string $modelLabel = 'early-access signup';

    protected static ?string $slug = 'early-access-signups';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('consented_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('interest')->badge(),
                TextColumn::make('source')->toggleable(),
                TextColumn::make('consented_at')->label('Signed up')->dateTime()->sortable(),
                TextColumn::make('invited_at')->label('Invited')->dateTime()->placeholder('Not yet')->sortable(),
            ])
            ->filters([
                SelectFilter::make('interest')->options(['buyer' => 'Buyer', 'owner' => 'Owner', 'investor' => 'Investor']),
                TernaryFilter::make('invited')->nullable()->attribute('invited_at'),
            ])
            ->recordActions([
                Action::make('markInvited')->label('Mark invited')->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (EarlyAccessSignup $record) => $record->invited_at === null)
                    ->action(fn (EarlyAccessSignup $record) => $record->forceFill(['invited_at' => now()])->save()),
            ])
            ->toolbarActions([
                BulkAction::make('markInvited')->label('Mark selected as invited')
                    ->action(fn (Collection $records) => $records->each(fn (EarlyAccessSignup $s) => $s->forceFill(['invited_at' => $s->invited_at ?? now()])->save())),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ListEarlyAccessSignups::route('/')];
    }
}
