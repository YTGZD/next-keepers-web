<?php

namespace App\Filament\Resources\Listings;

use App\Filament\Resources\Listings\Pages\CreateListing;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Models\Business;
use App\Models\ListingImport;
use App\Services\Listings\ListingPublisher;
use App\Support\ListingBenchmark;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Listings (the businesses table). Real listings arrive as drafts, get
 * reviewed here, and are published to buyers. Sample rows (the fictional
 * preview) are hidden by default and can't be edited here.
 */
class ListingResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $modelLabel = 'listing';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = 1;

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'active' => 'Published',
        'under_loi' => 'Under LOI',
        'sold' => 'Sold',
        'archived' => 'Archived',
    ];

    private const YES_NO = ['Yes' => 'Yes', 'No' => 'No', 'Unknown' => 'Unknown'];

    public static function getNavigationBadge(): ?string
    {
        $count = Business::real()->where('status', 'draft')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Drafts waiting for review';
    }

    public static function form(Schema $schema): Schema
    {
        $money = fn (string $name, string $label) => TextInput::make($name)->label($label)->numeric()->minValue(0)->prefix('$')->helperText('Leave empty if not disclosed');

        return $schema->columns(1)->components([
            Section::make('Listing')->columns(2)->components([
                TextInput::make('name')->label('Title')->required()->maxLength(140)->columnSpanFull(),
                Select::make('status')->options(self::STATUS_LABELS)->required()->default('draft')
                    ->helperText('Only Published, Under LOI and Sold listings are visible to buyers.'),
                Toggle::make('is_verified')->label('Verified by Next Keepers')
                    ->helperText('Only turn on after you have checked the financials yourself.'),
                TextInput::make('listing_key')->label('Listing key')->maxLength(120)->unique(ignoreRecord: true)
                    ->helperText('Source and ID, e.g. bizbuysell:2535967. Imports match on this.'),
                Select::make('source')->options(array_combine(self::sources(), self::sources()))->searchable(),
                TextInput::make('source_url')->label('Source URL')->url()->maxLength(500),
                TextInput::make('original_url')->label('Original listing URL')->url()->maxLength(500),
                DatePicker::make('scraped_on')->label('Scraped on'),
                TextInput::make('listing_type')->maxLength(40)->default('Business for Sale'),
                Select::make('operating_status')->label('Operating business?')->options(['Yes' => 'Yes', 'No' => 'No', 'Check' => 'Check']),
                Textarea::make('description')->rows(5)->columnSpanFull()
                    ->helperText('Kept admin-only until you decide how source text may be shown.'),
            ]),
            Section::make('Location')->columns(3)->components([
                TextInput::make('state')->maxLength(10),
                TextInput::make('county')->maxLength(80),
                TextInput::make('city')->maxLength(80),
                TextInput::make('msa')->label('MSA')->maxLength(120),
                Select::make('location_precision')->options(array_combine(self::PRECISIONS, self::PRECISIONS)),
                TextInput::make('zip')->label('ZIP')->maxLength(10),
            ]),
            Section::make('Industry')->columns(3)->components([
                TextInput::make('industry')->required()->maxLength(100)->datalist(fn () => self::distinct('industry')),
                TextInput::make('sub_industry')->maxLength(100)->datalist(fn () => self::distinct('sub_industry')),
                Select::make('industry_confidence')->options(['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']),
                TextInput::make('brand')->label('Brand / franchise')->maxLength(120),
                Select::make('benchmark_subsector')->label('Benchmark industry (override)')
                    ->options(fn () => array_combine(ListingBenchmark::subsectors(), ListingBenchmark::subsectors()))
                    ->searchable()->columnSpan(2)
                    ->helperText(fn (?Business $record) => self::benchmarkHelp($record)),
            ]),
            Section::make('Financials (as listed)')->columns(3)->components([
                $money('asking_price', 'Asking price'),
                $money('annual_revenue', 'Revenue'),
                $money('cash_flow', 'Cash flow (as listed)'),
                $money('sde', 'SDE'),
                $money('ebitda', 'EBITDA'),
                $money('earnings', 'Earnings used for multiples'),
                Select::make('earnings_basis')->options(array_combine(self::EARNINGS_BASES, self::EARNINGS_BASES)),
                Toggle::make('price_disclosed')->default(true)->inline(false),
            ]),
            Section::make('Derived multiples')->description('Calculated from the figures above, the same way as the listings workbook.')
                ->columns(4)->visibleOn('edit')->components([
                    TextEntry::make('m_pe')->label('Price / earnings')->state(fn (Business $record) => self::multiple($record->metrics()->priceToEarnings())),
                    TextEntry::make('m_pe_ex')->label('Price / earnings (ex-RE)')->state(fn (Business $record) => self::multiple($record->metrics()->priceExRealEstateToEarnings())),
                    TextEntry::make('m_pr')->label('Price / revenue')->state(fn (Business $record) => self::multiple($record->metrics()->priceToRevenue(), 2)),
                    TextEntry::make('m_margin')->label('Earnings margin')->state(fn (Business $record) => self::percent($record->metrics()->earningsMargin())),
                    TextEntry::make('m_cash')->label('Est. cash needed')->state(fn (Business $record) => self::dollars($record->metrics()->cashNeeded())),
                    TextEntry::make('m_goodwill')->label('Implied goodwill')->state(fn (Business $record) => self::dollars($record->metrics()->impliedGoodwill())),
                    TextEntry::make('m_rent')->label('Rent % of revenue')->state(fn (Business $record) => self::percent($record->metrics()->rentShareOfRevenue())),
                ]),
            Section::make('Industry benchmark')->description('What buyers see on the listing: its price / earnings (ex-real estate) next to the BizBuySell 2025 multiple for its industry.')
                ->columns(4)->visibleOn('edit')->components([
                    TextEntry::make('b_industry')->label('Compared with')
                        ->state(fn (Business $record) => ListingBenchmark::for($record)['subsector'] ?? 'No benchmark'),
                    TextEntry::make('b_match')->label('Match')->state(fn (Business $record) => ListingBenchmark::for($record)['match']),
                    TextEntry::make('b_multiple')->label('Listing vs industry')
                        ->state(fn (Business $record) => ListingBenchmark::for($record)['headline'] ?? 'n/a'),
                    TextEntry::make('b_signal')->label('Signal')->badge()
                        ->state(fn (Business $record) => ListingBenchmark::for($record)['signal_label'] ?? 'n/a')
                        ->color(fn (Business $record) => self::signalColor(ListingBenchmark::for($record)['signal'] ?? null)),
                    TextEntry::make('b_message')->label('Note shown to buyers')->columnSpanFull()
                        ->state(fn (Business $record) => implode(' ', array_merge([ListingBenchmark::for($record)['message']], ListingBenchmark::for($record)['caveats']))),
                ]),
            Section::make('Real estate, assets and lease')->columns(3)->collapsed()->components([
                TextInput::make('real_estate')->maxLength(40),
                Select::make('real_estate_in_price')->label('Real estate in price?')->options(self::YES_NO),
                $money('real_estate_value', 'Real estate value'),
                $money('ffe_value', 'FF&E / equipment'),
                Select::make('ffe_in_price')->label('FF&E in price?')->options(self::YES_NO),
                $money('inventory_value', 'Inventory'),
                Select::make('inventory_in_price')->label('Inventory in price?')->options(self::YES_NO),
                $money('monthly_rent', 'Monthly rent'),
                Textarea::make('lease_notes')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Operations')->columns(3)->collapsed()->components([
                TextInput::make('established_year')->numeric()->minValue(1800)->maxValue((int) date('Y')),
                TextInput::make('employees')->label('Employees (total)')->numeric()->minValue(0),
                TextInput::make('employees_full_time')->numeric()->minValue(0),
                TextInput::make('employees_part_time')->numeric()->minValue(0),
                TextInput::make('contractors')->numeric()->minValue(0),
                TextInput::make('square_feet')->numeric()->minValue(0),
            ]),
            Section::make('Deal terms')->columns(3)->collapsed()->components([
                TextInput::make('broker_name')->maxLength(160),
                TextInput::make('broker_listing_ref')->label('Broker ref / listing #')->maxLength(80),
                TextInput::make('reason_for_sale')->maxLength(160),
                TextInput::make('seller_financing')->maxLength(20),
                TextInput::make('seller_financing_percent')->label('Seller financing %')->numeric()->minValue(0)->maxValue(100),
                Toggle::make('sba_mentioned')->label('SBA mentioned')->inline(false),
                Textarea::make('finance_signals')->rows(2)->columnSpanFull(),
            ]),
            Section::make('Data quality and notes')->columns(2)->components([
                Toggle::make('needs_review')->label('Needs review')->inline(false),
                Select::make('qa_severity')->label('QA severity')->options(['info' => 'info', 'warn' => 'warn']),
                TagsInput::make('qa_flags')->label('QA flags')->columnSpanFull(),
                Textarea::make('qa_notes')->label('QA notes')->rows(2)->columnSpanFull(),
                Textarea::make('number_sources')->label('Where each number came from')->rows(2)->columnSpanFull(),
                Textarea::make('admin_notes')->label('Your notes (never shown to buyers)')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Title')->searchable()->sortable()->limit(60)->grow()
                    ->description(fn (Business $record) => trim(($record->sub_industry ?: $record->industry).($record->is_sample ? ' · sample' : ''))),
                TextColumn::make('status')->badge()->sortable()
                    ->formatStateUsing(fn (string $state) => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success', 'draft' => 'gray', 'archived' => 'danger', default => 'warning',
                    }),
                IconColumn::make('needs_review')->label('Review')->boolean()
                    ->trueIcon(Heroicon::OutlinedExclamationTriangle)->trueColor('warning')
                    ->falseIcon(Heroicon::OutlinedCheck)->falseColor('gray'),
                TextColumn::make('location')->searchable(['city', 'county', 'state'])->toggleable(),
                TextColumn::make('source')->sortable()->toggleable(),
                TextColumn::make('asking_price')->label('Asking')->money('USD', decimalPlaces: 0)->sortable()->placeholder('n/d'),
                TextColumn::make('annual_revenue')->label('Revenue')->money('USD', decimalPlaces: 0)->sortable()->placeholder('n/d'),
                TextColumn::make('earnings')->money('USD', decimalPlaces: 0)->sortable()->placeholder('n/d'),
                TextColumn::make('pe')->label('P/E')->state(fn (Business $record) => self::multiple($record->metrics()->priceToEarnings())),
                TextColumn::make('vs_industry')->label('vs industry')->badge()->toggleable()
                    ->state(fn (Business $record) => ListingBenchmark::for($record)['signal_label'] ?? null)
                    ->color(fn (Business $record) => self::signalColor(ListingBenchmark::for($record)['signal'] ?? null))
                    ->tooltip(fn (Business $record) => ListingBenchmark::for($record)['message'])
                    ->placeholder('n/a'),
                TextColumn::make('qa_flags')->label('QA flags')->badge()->separator(',')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::STATUS_LABELS)->multiple(),
                TernaryFilter::make('is_sample')->label('Sample preview rows')
                    ->placeholder('Real listings only')->trueLabel('Sample rows only')->falseLabel('Real listings only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_sample', true),
                        false: fn (Builder $query) => $query->where('is_sample', false),
                        blank: fn (Builder $query) => $query->where('is_sample', false),
                    ),
                TernaryFilter::make('needs_review'),
                SelectFilter::make('source')->options(fn () => array_combine(self::distinct('source'), self::distinct('source'))),
                SelectFilter::make('state')->options(fn () => array_combine(self::distinct('state'), self::distinct('state')))->multiple()->searchable(),
                Filter::make('county')
                    ->schema([TextInput::make('county')])
                    ->query(fn (Builder $query, array $data) => $query->when($data['county'] ?? null, fn (Builder $query, string $c) => $query->where('county', $c))),
                SelectFilter::make('listing_import_id')->label('Import')
                    ->options(fn () => ListingImport::where('dry_run', false)->latest('id')->limit(50)->get()
                        ->mapWithKeys(fn (ListingImport $import) => [$import->id => $import->file_name.' · '.$import->created_at->format('M j, H:i')])->all()),
                SelectFilter::make('industry')->options(fn () => array_combine(self::distinct('industry'), self::distinct('industry'))),
                SelectFilter::make('sub_industry')->options(fn () => array_combine(self::distinct('sub_industry'), self::distinct('sub_industry'))),
            ])
            ->recordActions([
                EditAction::make()->hidden(fn (Business $record) => $record->is_sample),
                Action::make('publish')->label('Publish')->icon(Heroicon::OutlinedEye)->color('success')
                    ->visible(fn (Business $record) => ! $record->is_sample && ! in_array($record->status, Business::VISIBLE_STATUSES, true))
                    ->requiresConfirmation()
                    ->modalDescription(fn (Business $record) => $record->needs_review
                        ? 'This listing is flagged for review: '.Str::limit((string) $record->qa_notes, 200).' Publish anyway?'
                        : 'Buyers will be able to see this listing.')
                    ->action(fn (Business $record) => self::publish($record)),
                Action::make('archive')->label('Archive')->icon(Heroicon::OutlinedArchiveBox)->color('danger')
                    ->visible(fn (Business $record) => ! $record->is_sample && $record->status !== 'archived')
                    ->requiresConfirmation()
                    ->action(fn (Business $record) => $record->update(['status' => 'archived'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')->label('Publish selected')->icon(Heroicon::OutlinedEye)->color('success')
                        ->requiresConfirmation()
                        ->modalDescription(fn (Builder $selectedRecordsQuery) => ListingPublisher::describe(ListingPublisher::counts($selectedRecordsQuery), 'in your selection')
                            .' Selected listings that are already published or archived are left as they are.')
                        ->schema([
                            Toggle::make('include_flagged')->label('Include listings flagged "Needs review"')->default(false),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Builder $selectedRecordsQuery, array $data): void {
                            $count = ListingPublisher::publish($selectedRecordsQuery, (bool) ($data['include_flagged'] ?? false));
                            Notification::make()->success()->title('Published '.number_format($count).' '.($count === 1 ? 'listing' : 'listings'))->send();
                        }),
                    BulkAction::make('archive')->label('Archive selected')->icon(Heroicon::OutlinedArchiveBox)->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Builder $selectedRecordsQuery): void {
                            $count = (clone $selectedRecordsQuery)->reorder()->where('is_sample', false)->where('status', '!=', 'archived')->update(['status' => 'archived', 'updated_at' => now()]);
                            Notification::make()->success()->title('Archived '.number_format($count).' '.($count === 1 ? 'listing' : 'listings'))->send();
                        }),
                ]),
            ]);
    }

    public static function publish(Business $record, bool $notify = true): void
    {
        $record->update(['status' => 'active', 'published_at' => $record->published_at ?? now(), 'needs_review' => false]);

        if ($notify) {
            Notification::make()->success()->title('Listing published')->send();
        }
    }

    /** Sample preview rows are managed by the seeder, not here. */
    public static function canEdit($record): bool
    {
        return ! $record->is_sample;
    }

    public static function canDelete($record): bool
    {
        return false; // archive instead, so watchlists and history keep working
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
            'create' => CreateListing::route('/create'),
            'edit' => EditListing::route('/{record}/edit'),
        ];
    }

    private const PRECISIONS = ['City', 'City (source listing)', 'City (inferred)', 'County only', 'County (source listing)', 'Region', 'Statewide', 'Nationwide', 'Not disclosed', 'Outside US'];

    private const EARNINGS_BASES = ['SDE', 'EBITDA', 'Cash Flow (SDE-equivalent, as listed)', 'Cash Flow (SDE, as listed)', 'None'];

    /** @return list<string> */
    private static function sources(): array
    {
        return array_values(array_unique(array_merge(['BizBuySell', 'BizScout', 'Acquisition Network', 'ListBizForSale', 'Direct'], self::distinct('source'))));
    }

    /** @return list<string> */
    private static function distinct(string $column): array
    {
        return Business::real()->whereNotNull($column)->where($column, '!=', '')->distinct()->orderBy($column)->pluck($column)->all();
    }

    private static function benchmarkHelp(?Business $record): string
    {
        $default = ListingBenchmark::defaultFor($record?->sub_industry);
        $base = $default && $default['subsector']
            ? 'Leave empty to use the default for this sub-industry: '.$default['subsector'].' ('.$default['match'].').'
            : 'Leave empty to use the default for the sub-industry.';

        return $base.' Pick one when a listing fits a more specific industry, e.g. a gas station filed under grocery.';
    }

    private static function signalColor(?string $signal): string
    {
        return match ($signal) {
            'below' => 'success', 'above' => 'warning', default => 'gray',
        };
    }

    private static function multiple(?float $value, int $decimals = 1): string
    {
        return $value === null ? 'n/a' : number_format($value, $decimals).'x';
    }

    private static function percent(?float $value): string
    {
        return $value === null ? 'n/a' : number_format($value * 100, 1).'%';
    }

    private static function dollars(?float $value): string
    {
        return $value === null ? 'n/a' : '$'.number_format($value);
    }
}
