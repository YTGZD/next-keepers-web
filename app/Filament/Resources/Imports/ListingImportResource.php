<?php

namespace App\Filament\Resources\Imports;

use App\Filament\Resources\Imports\Pages\ListListingImports;
use App\Filament\Resources\Imports\Pages\ViewListingImport;
use App\Jobs\RunListingImport;
use App\Models\ListingImport;
use App\Services\Listings\ListingImporter;
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
            ->modalDescription('Upload the cleaned listings workbook (.xlsx, "Clean Listings" sheet) or a CSV export of it. Rows are matched by Listing Key: new ones arrive as drafts in the review queue, known ones get their figures refreshed. Nothing is published automatically.')
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
                TextEntry::make('dry_run')->label('Mode')->formatStateUsing(fn (bool $state) => $state ? 'Dry run (nothing written)' : 'Import'),
                TextEntry::make('filters')->label('Rows')->state(fn (ListingImport $record) => self::presetLabel($record->filters)),
                TextEntry::make('user.name')->label('By')->placeholder('Server command'),
                TextEntry::make('created_at')->label('Started')->dateTime(),
                TextEntry::make('summary')->label('Result')->columnSpanFull()->state(fn (ListingImport $record) => $record->summary()),
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
                IconColumn::make('dry_run')->label('Dry run')->boolean(),
                TextColumn::make('status')->badge()->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('rows_matched')->label('Matched')->numeric(),
                TextColumn::make('created_count')->label('New')->numeric(),
                TextColumn::make('updated_count')->label('Updated')->numeric(),
                TextColumn::make('failed_count')->label('Failed')->numeric(),
                TextColumn::make('user.name')->label('By')->placeholder('Server command'),
            ])
            ->recordActions([ViewAction::make()]);
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
