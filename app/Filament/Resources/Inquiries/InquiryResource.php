<?php

namespace App\Filament\Resources\Inquiries;

use App\Filament\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Models\Inquiry;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Buyer inquiries about a listing ("request dossier", "contact advisor"). Admins answer them by email for now. */
class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?int $navigationSort = 4;

    public const STATUSES = ['received' => 'Received', 'nda_pending' => 'NDA pending', 'cim_shared' => 'CIM shared', 'closed' => 'Closed'];

    public static function getNavigationBadge(): ?string
    {
        $count = Inquiry::where('status', 'received')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('business.name')->label('Listing'),
            TextEntry::make('type'),
            TextEntry::make('name'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('buyer_entity')->placeholder('None given'),
            TextEntry::make('created_at')->label('Received')->dateTime(),
            TextEntry::make('message')->columnSpanFull()->placeholder('No message'),
            Select::make('status')->options(self::STATUSES)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('business'))
            ->columns([
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('business.name')->label('Listing')->limit(40),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state),
            ])
            ->filters([SelectFilter::make('status')->options(self::STATUSES)])
            ->recordActions([EditAction::make()->label('Open')]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'edit' => EditInquiry::route('/{record}/edit'),
        ];
    }
}
