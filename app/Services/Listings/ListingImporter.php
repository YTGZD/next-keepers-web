<?php

namespace App\Services\Listings;

use App\Models\Business;
use App\Models\ListingImport;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use RuntimeException;
use Throwable;

/**
 * Loads listings from the cleaned listings workbook ("Clean Listings" sheet)
 * or a CSV export of it, into the businesses table.
 *
 * - Rows are matched by "Listing Key": a new key creates a draft, a known key
 *   refreshes the listed data. Review state (status, verification, admin
 *   notes, publish date) is never touched by an import.
 * - Missing figures stay null ("not disclosed"), never 0.
 * - Ratio columns of the workbook are ignored: ListingMetrics recomputes them.
 * - Sample (preview) rows are never overwritten.
 */
class ListingImporter
{
    /** Counties counted as Western New York (Buffalo and Rochester areas). */
    public const WESTERN_NY_COUNTIES = [
        'Erie', 'Niagara', 'Chautauqua', 'Cattaraugus', 'Allegany', 'Genesee', 'Orleans', 'Wyoming',
        'Monroe', 'Livingston', 'Ontario', 'Wayne',
    ];

    /** Ready-made scopes for the two studies, plus "everything". */
    public const PRESETS = [
        'western-ny' => ['label' => 'Western New York (Buffalo and Rochester areas)', 'state' => ['NY'], 'county' => self::WESTERN_NY_COUNTIES],
        'healthcare' => ['label' => 'Healthcare, nationwide', 'industry' => ['Healthcare']],
        'all' => ['label' => 'Every row in the file'],
    ];

    private const CHUNK = 250;

    private const MAX_PROBLEMS = 50;

    /** Fields an import may write. Review fields (status, is_verified, published_at, admin_notes) are not here. */
    private const DATA_FIELDS = [
        'name', 'title_original', 'description', 'industry', 'sub_industry', 'industry_confidence', 'category',
        'listing_type', 'operating_status', 'brand',
        'source', 'source_url', 'scraped_on',
        'city', 'county', 'state', 'zip', 'street_address', 'location_precision', 'location_raw',
        'asking_price', 'price_disclosed', 'annual_revenue', 'cash_flow', 'sde', 'ebitda', 'earnings', 'earnings_basis',
        'real_estate', 'real_estate_in_price', 'real_estate_value', 'ffe_value', 'ffe_in_price',
        'inventory_value', 'inventory_in_price', 'monthly_rent', 'lease_notes',
        'established_year', 'employees', 'employees_full_time', 'employees_part_time', 'contractors',
        'square_feet', 'lot_acres',
        'broker_name', 'broker_listing_ref', 'reason_for_sale', 'seller_financing', 'seller_financing_percent',
        'sba_mentioned', 'finance_signals', 'signals',
        'number_sources', 'qa_severity', 'qa_flags', 'qa_notes', 'needs_review',
    ];

    /**
     * Run an import and record the outcome on the ListingImport row.
     *
     * @param  callable(ListingImport): void|null  $progress  called every few hundred rows
     */
    public function run(ListingImport $import, ?callable $progress = null, ?int $limit = null): ListingImport
    {
        $import->forceFill(['status' => 'running', 'started_at' => now(), 'message' => null])->save();
        $filters = $this->normalizeFilters($import->filters ?? []);
        $problems = [];
        $counts = ['rows_read' => 0, 'rows_matched' => 0, 'created_count' => 0, 'updated_count' => 0, 'unchanged_count' => 0, 'skipped_count' => 0, 'failed_count' => 0];
        $seen = [];

        try {
            $batch = [];
            foreach ($this->rows($this->absolutePath($import->stored_path), $import->file_name) as $line => $row) {
                $counts['rows_read']++;
                if (! $this->matches($row, $filters)) {
                    continue;
                }
                $counts['rows_matched']++;
                $batch[$line] = $row;

                if (count($batch) >= self::CHUNK) {
                    $this->processBatch($import, $batch, $counts, $problems, $seen);
                    $batch = [];
                    $import->forceFill($counts)->save();
                    $progress && $progress($import);
                }
                if ($limit !== null && $counts['rows_matched'] >= $limit) {
                    break;
                }
            }
            $this->processBatch($import, $batch, $counts, $problems, $seen);

            $import->forceFill($counts + [
                'status' => 'done',
                'problems' => $problems ?: null,
                'finished_at' => now(),
                'message' => $counts['rows_read'] === 0 ? 'No listing rows found. Is there a "Listing Key" column?' : null,
            ])->save();
        } catch (Throwable $e) {
            report($e);
            $import->forceFill($counts + [
                'status' => 'failed',
                'problems' => $problems ?: null,
                'finished_at' => now(),
                'message' => Str::limit($e->getMessage(), 500),
            ])->save();
        }

        $progress && $progress($import);

        return $import;
    }

    /** @param array<int, array<string, mixed>> $batch */
    private function processBatch(ListingImport $import, array $batch, array &$counts, array &$problems, array &$seen): void
    {
        if ($batch === []) {
            return;
        }

        $write = function () use ($import, $batch, &$counts, &$problems, &$seen): void {
            foreach ($batch as $line => $row) {
                try {
                    $outcome = $this->importRow($import, $row, $seen);
                    $counts[$outcome.'_count']++;
                    if ($outcome === 'skipped') {
                        $this->problem($problems, $line, $this->skipReason($row));
                    }
                } catch (Throwable $e) {
                    $counts['failed_count']++;
                    $this->problem($problems, $line, Str::limit($e->getMessage(), 200));
                }
            }
        };

        $import->dry_run ? $write() : DB::transaction($write);
    }

    /** @return 'created'|'updated'|'unchanged'|'skipped' */
    private function importRow(ListingImport $import, array $row, array &$seen): string
    {
        $key = self::text($row['listing key'] ?? null, 120);
        if ($key === null) {
            return 'skipped';
        }

        $attributes = self::map($row);
        $existing = Business::where('listing_key', $key)->first();
        $alreadyThisRun = isset($seen[$key]);
        $seen[$key] = true;

        if ($existing?->is_sample) {
            return 'skipped';
        }

        if ($import->dry_run) {
            return $existing || $alreadyThisRun ? 'updated' : 'created';
        }

        if ($existing) {
            $existing->fill($attributes);
            if (! $existing->isDirty()) {
                $existing->forceFill(['last_seen_at' => now()])->saveQuietly();

                return 'unchanged';
            }
            $existing->forceFill(['last_seen_at' => now(), 'listing_import_id' => $import->id])->save();

            return 'updated';
        }

        $listing = new Business;
        $listing->forceFill($attributes + [
            'listing_key' => $key,
            'listing_import_id' => $import->id,
            'slug' => Str::limit(Str::slug($attributes['name']), 110, '').'-'.substr(md5($key), 0, 8),
            'is_sample' => false,
            'is_verified' => false,
            'status' => 'draft',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ])->save();

        return 'created';
    }

    /**
     * Map one workbook row (headers lower-cased) to business attributes.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function map(array $row): array
    {
        $get = fn (string $header) => $row[$header] ?? null;
        $yes = fn (string $header) => self::text($get($header)) === 'Yes';

        $industry = self::text($get('industry'), 100) ?? 'Unclassified';
        $subIndustry = self::text($get('sub-industry'), 100);
        $title = self::text($get('title (clean)')) ?? self::text($get('title (original)'))
            ?? trim(($subIndustry ?? $industry).' business');
        $county = self::text($get('county'), 80);
        if ($county !== null && Str::startsWith($county, 'Unknown')) {
            $county = null;
        }
        $established = self::integer($get('year established'));
        $sellerFinancingShare = self::number($get('seller fin. %'));
        $qaFlags = self::text($get('qa flags'));

        $attributes = [
            'name' => Str::limit($title, 137),
            'title_original' => self::text($get('title (original)'), 255),
            'description' => self::text($get('description')),
            'industry' => $industry,
            'sub_industry' => $subIndustry,
            'industry_confidence' => self::text($get('industry confidence'), 10),
            'category' => self::category($industry, $subIndustry),
            'listing_type' => self::text($get('listing type'), 40),
            'operating_status' => self::text($get('operating business?'), 10),
            'brand' => self::text($get('brand / franchise'), 120),

            'source' => self::text($get('source'), 40),
            'source_url' => self::text($get('url'), 500),
            'scraped_on' => self::date($get('scrape date')),

            'city' => self::text($get('city / place'), 80),
            'county' => $county,
            'state' => self::text($get('state'), 10),
            'zip' => self::text($get('zip'), 10),
            'street_address' => self::text($get('street address'), 160),
            'location_precision' => self::text($get('location precision'), 40),
            'location_raw' => self::text($get('location (raw)'), 160),

            'asking_price' => self::money($get('asking price')),
            'price_disclosed' => self::text($get('price status')) !== 'Not Disclosed',
            'annual_revenue' => self::money($get('revenue')),
            'cash_flow' => self::money($get('cash flow (as listed)')),
            'sde' => self::money($get('sde')),
            'ebitda' => self::money($get('ebitda')),
            'earnings' => self::money($get('earnings used')),
            'earnings_basis' => self::text($get('earnings basis'), 60),

            'real_estate' => self::text($get('real estate'), 40),
            'real_estate_in_price' => self::text($get('re in asking price?'), 10),
            'real_estate_value' => self::money($get('real estate value')),
            'ffe_value' => self::money($get('ff&e / equipment')),
            'ffe_in_price' => self::text($get('ff&e in price?'), 10),
            'inventory_value' => self::money($get('inventory')),
            'inventory_in_price' => self::text($get('inventory in price?'), 10),
            'monthly_rent' => self::money($get('monthly lease payment')),
            'lease_notes' => self::text($get('lease notes')),

            'established_year' => $established !== null && $established >= 1800 && $established <= (int) date('Y') ? $established : null,
            'employees' => self::count($get('employees total')),
            'employees_full_time' => self::count($get('employees full-time')),
            'employees_part_time' => self::count($get('employees part-time')),
            'contractors' => self::count($get('contractors')),
            'square_feet' => self::count($get('sq ft')),
            'lot_acres' => self::number($get('lot acres')),

            'broker_name' => self::text($get('broker'), 160),
            'broker_listing_ref' => self::text($get('broker ref / listing #'), 80),
            'reason_for_sale' => self::text($get('reason for sale'), 160),
            'seller_financing' => self::text($get('seller financing'), 20),
            // The workbook stores a share (0.25); the column holds a percent (25.00).
            'seller_financing_percent' => $sellerFinancingShare === null ? null
                : round($sellerFinancingShare <= 1 ? $sellerFinancingShare * 100 : $sellerFinancingShare, 2),
            'sba_mentioned' => $yes('sba mentioned'),
            'finance_signals' => self::joinText([$get('finance signals'), $get('other financing')]),
            'signals' => self::text($get('signals')),

            'number_sources' => self::text($get('where each number came from')),
            'qa_severity' => self::text($get('qa severity'), 10),
            'qa_flags' => $qaFlags === null ? null : array_values(array_filter(array_map('trim', explode(',', $qaFlags)))),
            'qa_notes' => self::text($get('qa notes')),
            'needs_review' => $yes('needs review'),
        ];

        return array_intersect_key($attributes, array_flip(self::DATA_FIELDS));
    }

    /**
     * Rows of the file as header => value arrays (headers lower-cased), keyed by line number.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function rows(string $path, ?string $originalName = null): Generator
    {
        $extension = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));
        $reader = match ($extension) {
            'xlsx' => new XlsxReader,
            'csv', 'txt' => new CsvReader,
            default => throw new RuntimeException("Unsupported file type .{$extension}: upload an .xlsx or .csv file."),
        };
        $reader->open($path);

        try {
            // In the workbook only "Clean Listings" holds listings; otherwise use the first sheet.
            $target = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getName() === 'Clean Listings') {
                    $target = $sheet->getIndex();
                }
            }
            foreach ($reader->getSheetIterator() as $sheet) {
                if ($sheet->getIndex() !== $target) {
                    continue;
                }
                $headers = null;
                $line = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    $values = array_map(fn ($cell) => $cell->getValue(), $row->getCells());
                    if ($headers === null) {
                        $normalized = array_map(fn ($v) => is_string($v) ? strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $v))) : '', $values);
                        if (in_array('listing key', $normalized, true)) {
                            $headers = $normalized;
                        }

                        continue;
                    }
                    $assoc = [];
                    foreach ($headers as $i => $header) {
                        if ($header !== '') {
                            $assoc[$header] = $values[$i] ?? null;
                        }
                    }
                    if (array_filter($assoc, fn ($v) => $v !== null && $v !== '') !== []) {
                        yield $line => $assoc;
                    }
                }

                return;
            }
        } finally {
            $reader->close();
        }
    }

    /** @return array{state: list<string>, county: list<string>, industry: list<string>, source: list<string>} */
    public function normalizeFilters(array $filters): array
    {
        $preset = self::PRESETS[$filters['preset'] ?? 'all'] ?? [];
        $list = fn (string $key) => array_values(array_unique(array_filter(array_map(
            fn ($v) => strtolower(trim(preg_replace('/\s+county$/i', '', (string) $v))),
            array_merge($preset[$key] ?? [], (array) ($filters[$key] ?? []))
        ))));

        return ['state' => $list('state'), 'county' => $list('county'), 'industry' => $list('industry'), 'source' => $list('source')];
    }

    private function matches(array $row, array $filters): bool
    {
        $value = fn (string $header) => strtolower(trim(preg_replace('/\s+county$/i', '', (string) ($row[$header] ?? ''))));

        foreach (['state' => 'state', 'county' => 'county', 'industry' => 'industry', 'source' => 'source'] as $filter => $header) {
            if ($filters[$filter] !== [] && ! in_array($value($header), $filters[$filter], true)) {
                return false;
            }
        }

        return true;
    }

    private function skipReason(array $row): string
    {
        $key = self::text($row['listing key'] ?? null);
        if ($key === null) {
            return 'No Listing Key';
        }

        return "{$key}: matches a sample row, left alone";
    }

    private function problem(array &$problems, int $line, string $message): void
    {
        if (count($problems) < self::MAX_PROBLEMS) {
            $problems[] = ['line' => $line, 'message' => $message];
        }
    }

    public function absolutePath(string $storedPath): string
    {
        return str_starts_with($storedPath, '/') ? $storedPath : Storage::disk('local')->path($storedPath);
    }

    private static function category(string $industry, ?string $subIndustry): string
    {
        return match (true) {
            $industry === 'Healthcare' => 'healthcare',
            $subIndustry !== null && str_contains($subIndustry, 'HVAC') => 'hvac',
            $industry === 'Retail' => 'retail',
            str_starts_with($industry, 'Manufacturing') => 'manufacturing',
            default => 'services',
        };
    }

    // Value parsing. Formula cells ("=IF(...)") and blanks are treated as missing.

    public static function text(mixed $value, ?int $max = null): ?string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        if ($value === null || is_bool($value)) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '' || str_starts_with($value, '=')) {
            return null;
        }

        return $max === null ? $value : Str::limit($value, $max - 3);
    }

    private static function joinText(array $values): ?string
    {
        $parts = array_values(array_filter(array_map(fn ($v) => self::text($v), $values)));

        return $parts === [] ? null : implode('; ', array_unique($parts));
    }

    public static function number(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $text = self::text($value);
        if ($text === null) {
            return null;
        }
        $clean = str_replace(['$', ',', ' '], '', $text);

        return is_numeric($clean) ? (float) $clean : null;
    }

    /** Whole dollars; negative or missing amounts are stored as "not disclosed". */
    public static function money(mixed $value): ?int
    {
        $number = self::number($value);

        return $number === null || $number < 0 ? null : (int) round($number);
    }

    private static function integer(mixed $value): ?int
    {
        $number = self::number($value);

        return $number === null ? null : (int) round($number);
    }

    private static function count(mixed $value): ?int
    {
        $number = self::integer($value);

        return $number === null || $number < 0 ? null : $number;
    }

    private static function date(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_int($value) || is_float($value)) {
            // Excel serial date
            return CarbonImmutable::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }
        $text = self::text($value);
        if ($text === null) {
            return null;
        }
        try {
            return CarbonImmutable::parse($text)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
