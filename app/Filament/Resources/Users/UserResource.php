<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Registered accounts. Admins can change a role; passwords are never shown or set here. */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 2;

    public const ROLES = ['buyer' => 'Buyer', 'owner' => 'Owner', 'broker' => 'Broker', 'investor' => 'Investor', 'admin' => 'Admin'];

    /** The shared sample account behind the public preview is not a real user. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('email', '!=', config('nextkeepers.demo_email'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->disabled(),
            Select::make('role')->options(self::ROLES)->required()
                ->disabled(fn (?User $record) => $record?->is(auth()->user()))
                ->helperText('You cannot change your own role, so you can never lock yourself out.'),
            TextInput::make('company')->maxLength(140),
            TextInput::make('membership_tier')->disabled(),
            TextInput::make('nda_status')->label('NDA status')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->badge()->formatStateUsing(fn (string $state) => self::ROLES[$state] ?? $state),
                TextColumn::make('buy_box_count')->label('Buy box')->counts('buyBox')->formatStateUsing(fn (int $state) => $state ? 'Set' : 'None'),
                TextColumn::make('watchlist_items_count')->label('Watchlist')->counts('watchlistItems'),
                TextColumn::make('created_at')->label('Joined')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(self::ROLES),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        return false; // buyers sign up on the site; admins come from `php artisan nk:admin`
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
