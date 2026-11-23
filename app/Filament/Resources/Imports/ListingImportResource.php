<?php

namespace App\Filament\Resources\Imports;

use App\Filament\Resources\Imports\Pages\ListListingImports;
use App\Filament\Resources\Imports\Pages\ViewListingImport;
use App\Jobs\RunListingImport;
use App\Models\ListingImport;
use App\Services\Listings\ListingImporter;
use App\Services\Listings\ListingPublisher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Listing imports: upload the cleaned listings workbook (or a CSV export of it)
 * and see what each import did. Large files can be imported on the server
 * with `php artisan nk:import-listings` instead.
 */
class ListingImportResource extends Resource
{
    protected static ?string $model = ListingImport::class;

    protected static ?string $modelLabel = 'import';

    protected static ?string $slug = 'imports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?int $navigationSort = 2;

    public const MAX_UPLOAD_KB = 51200;

    public const STATUS_COLORS = ['queued' => 'gray', 'running' => 'warning', 'done' => 'success', 'failed' => 'danger'];

    /** The "Import listings" button, used on this resource and on the Listings page. */
    public static function importAction(): Action
    {
        return Action::make('importListings')
            ->label('Import listings')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->modalHeading('Import listings')
            ->modalDescription('Upload the cleaned listings workbook (.xlsx, "Clean Listings" sheet) or a CSV export of it. Rows are matched by Listing Key: new ones arrive as drafts in the review queue, known ones get their figures refreshed. Turn on "Publish right away" to skip the review queue for this file.')
            ->modalSubmitActionLabel('Start import')
            ->schema([
                FileUpload::make('file')
                    ->label('File')
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private')
                    ->storeFileNamesIn('file_name')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel',
                    ])
                    ->maxSize(self::MAX_UPLOAD_KB)
                    ->helperText('Up to 50 MB. For the full workbook, filter it first or use the server command.')
                    ->required(),
                Select::make('preset')
                    ->label('Which rows')
                    ->options(collect(ListingImporter::PRESETS)->map(fn (array $preset) => $preset['label'])->all())
                    ->default('western-ny')
                    ->required(),
                Section::make('More filters (optional)')->collapsed()->columns(2)->components([
                    TagsInput::make('state')->placeholder('e.g. NY'),
                    TagsInput::make('county')->placeholder('e.g. Erie'),
                    TagsInput::make('industry')->placeholder('e.g. Healthcare'),
                    TagsInput::make('source')->placeholder('e.g. BizBuySell'),
                ]),
                Toggle::make('publish')
                    ->label('Publish right away')
                    ->helperText('New listings go live as soon as the import finishes, instead of waiting in the review queue. You can still archive any of them later.')
                    ->live()
                    ->default(false),
                Toggle::make('publish_flagged')
                    ->label('Also publish listings flagged "Needs review"')
                    ->helperText('Off: flagged rows stay drafts so you can check them first.')
                    ->visible(fn (Get $get) => (bool) $get('publish'))
                    ->default(false),
                Toggle::make('dry_run')
                    ->label('Dry run: only count what would change')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $import = self::start($data);

                Notification::make()
                    ->title($import->dry_run ? 'Dry run started' : 'Import started')
                    ->body('It runs in the background. The Imports page shows the result when it finishes.')
                    ->success()
                    ->send();
            });
    }

    /**
     * "Publish drafts": publish every draft an import created or last refreshed,
     * in one go. Used as a row action on the Imports table and on an import's page.
     */
    public static function publishDraftsAction(): Action
    {
        return Action::make('publishDrafts')
            ->label('Publish drafts')
            ->icon(Heroicon::OutlinedEye)
            ->color('success')
            ->visible(fn (ListingImport $record) => $record->status === 'done' && ! $record->dry_run
                && ListingPublisher::drafts(ListingPublisher::forImport($record))->exists())
            ->modalHeading(fn (ListingImport $record) => 'Publish drafts from '.$record->file_name)
            ->modalDescription(fn (ListingImport $record) => ListingPublisher::describe(ListingPublisher::counts(ListingPublisher::forImport($record)), 'from this import'))
            ->modalSubmitActionLabel('Publish')
            ->schema([
                Toggle::make('include_flagged')->label('Include listings flagged "Needs review"')->default(false),
            ])
            ->action(function (ListingImport $record, array $data): void {
                $count = ListingPublisher::publish(ListingPublisher::forImport($record), (bool) ($data['include_flagged'] ?? false));
                $record->increment('published_count', $count);

                Notification::make()->success()->title('Published '.number_format($count).' '.($count === 1 ? 'listing' : 'listings'))->send();
            });
    }

    /** Drafts still waiting from an import, for the table and the import page. */
    public static function draftsLeft(ListingImport $record): int
    {
        return $record->dry_run ? 0 : ListingPublisher::drafts(ListingPublisher::forImport($record))->count();
    }

    /** Record the upload and run it once the response has been sent. */
    public static function start(array $data): ListingImport
    {
        $import = ListingImport::create([
            'user_id' => Auth::id(),
            'via' => 'panel',
            'file_name' => $data['file_name'] ?? basename($data['file']),
            'stored_path' => $data['file'],
            'filters' => array_filter([
                'preset' => $data['preset'] ?? 'all',
                'state' => $data['state'] ?? [],
                'county' => $data['county'] ?? [],
                'industry' => $data['industry'] ?? [],
                'source' => $data['source'] ?? [],
            ]),
            'dry_run' => (bool) ($data['dry_run'] ?? false),
            'publish' => (bool) ($data['publish'] ?? false),
            'publish_flagged' => (bool) ($data['publish'] ?? false) && (bool) ($data['publish_flagged'] ?? false),
        ]);

        RunListingImport::dispatchAfterResponse($import->id);

        return $import;
    }

    public static function presetLabel(?array $filters): string
    {
        $label = ListingImporter::PRESETS[$filters['preset'] ?? 'all']['label'] ?? 'Every row in the file';
        $extra = collect(['state', 'county', 'industry', 'source'])
            ->filter(fn (string $key) => ! empty($filters[$key]))
            ->map(fn (string $key) => $key.': '.implode(', ', (array) $filters[$key]));

        return $extra->isEmpty() ? $label : $label.' ('.$extra->implode('; ').')';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Import')->columns(3)->components([
                TextEntry::make('file_name')->label('File'),
                TextEntry::make('status')->badge()->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                TextEntry::make('dry_run')->label('Mode')->state(fn (ListingImport $record) => match (true) {
                    $record->dry_run => 'Dry run (nothing written)',
                    $record->publish && $record->publish_flagged => 'Import and publish everything',
                    $record->publish => 'Import and publish (flagged rows stay drafts)',
                    default => 'Import (new rows wait in the review queue)',
                }),
                TextEntry::make('filters')->label('Rows')->state(fn (ListingImport $record) => self::presetLabel($record->filters)),
                TextEntry::make('user.name')->label('By')->placeholder('Server command'),
                TextEntry::make('created_at')->label('Started')->dateTime(),
                TextEntry::make('summary')->label('Result')->columnSpanFull()->state(fn (ListingImport $record) => $record->summary()),
                TextEntry::make('drafts_left')->label('Drafts still in the review queue')
                    ->state(fn (ListingImport $record) => number_format(self::draftsLeft($record)))
                    ->visible(fn (ListingImport $record) => $record->status === 'done' && ! $record->dry_run),
                TextEntry::make('message')->columnSpanFull()->placeholder('None')->visible(fn (ListingImport $record) => filled($record->message)),
            ]),
            Section::make('Rows that need a look')->components([
                TextEntry::make('problem_lines')->hiddenLabel()
                    ->state(fn (ListingImport $record) => collect($record->problems ?? [])->map(fn (array $p) => "Line {$p['line']}: {$p['message']}")->all())
                    ->listWithLineBreaks()
                    ->placeholder('No problems'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll(fn () => ListingImport::whereIn('status', ['queued', 'running'])->exists() ? '5s' : null)
            ->columns([
                TextColumn::make('created_at')->label('Started')->since()->sortable(),
                TextColumn::make('file_name')->label('File')->limit(40),
                TextColumn::make('filters')->label('Rows')->state(fn (ListingImport $record) => self::presetLabel($record->filters))->limit(40),
                IconColumn::make('dry_run')->label('Dry run')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('rows_matched')->label('Matched')->numeric(),
                TextColumn::make('created_count')->label('New')->numeric(),
                TextColumn::make('updated_count')->label('Updated')->numeric(),
                TextColumn::make('failed_count')->label('Failed')->numeric()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_count')->label('Published')->numeric(),
                TextColumn::make('drafts_left')->label('Drafts left')->state(fn (ListingImport $record) => self::draftsLeft($record))->numeric(),
                TextColumn::make('user.name')->label('By')->placeholder('Server command')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([self::publishDraftsAction(), ViewAction::make()]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingImports::route('/'),
            'view' => ViewListingImport::route('/{record}'),
        ];
    }
}
