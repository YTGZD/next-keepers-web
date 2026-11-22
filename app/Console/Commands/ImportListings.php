<?php

namespace App\Console\Commands;

use App\Models\ListingImport;
use App\Services\Listings\ListingImporter;
use Illuminate\Console\Command;

/**
 * Server-side import for large files: copy the workbook (or a CSV export of
 * it) to the server, then run this. Same rules as the upload in the admin
 * panel: matched by Listing Key, new rows arrive as drafts.
 */
class ImportListings extends Command
{
    protected $signature = 'nk:import-listings
        {file : Path to the cleaned listings .xlsx or .csv}
        {--preset=all : western-ny, healthcare or all}
        {--state=* : Only these states (e.g. --state=NY)}
        {--county=* : Only these counties (e.g. --county=Erie)}
        {--industry=* : Only these industries (e.g. --industry=Healthcare)}
        {--source=* : Only these sources (e.g. --source=BizBuySell)}
        {--limit= : Stop after this many matching rows}
        {--dry-run : Count what would change without writing anything}';

    protected $description = 'Import listings from the cleaned listings workbook (new rows arrive as drafts for review)';

    public function handle(ListingImporter $importer): int
    {
        $path = realpath((string) $this->argument('file'));
        if ($path === false || ! is_file($path)) {
            $this->error('File not found: '.$this->argument('file'));

            return self::FAILURE;
        }

        $preset = (string) $this->option('preset');
        if (! array_key_exists($preset, ListingImporter::PRESETS)) {
            $this->error('Unknown preset. Use one of: '.implode(', ', array_keys(ListingImporter::PRESETS)));

            return self::FAILURE;
        }

        @ini_set('memory_limit', '512M');

        $import = ListingImport::create([
            'via' => 'command',
            'file_name' => basename($path),
            'stored_path' => $path,
            'filters' => array_filter([
                'preset' => $preset,
                'state' => $this->option('state'),
                'county' => $this->option('county'),
                'industry' => $this->option('industry'),
                'source' => $this->option('source'),
            ]),
            'dry_run' => (bool) $this->option('dry-run'),
        ]);

        $this->info(($import->dry_run ? 'Dry run: ' : '').'reading '.basename($path).' ('.ListingImporter::PRESETS[$preset]['label'].')');

        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
        $importer->run($import, function (ListingImport $import): void {
            $this->output->write("\r  ".number_format($import->rows_read).' rows read, '.number_format($import->rows_matched).' matched   ');
        }, $limit);
        $this->newLine();

        if ($import->status === 'failed') {
            $this->error('Import failed: '.$import->message);

            return self::FAILURE;
        }

        $this->info($import->summary());
        if ($import->message) {
            $this->warn($import->message);
        }
        foreach (array_slice($import->problems ?? [], 0, 10) as $problem) {
            $this->line("  line {$problem['line']}: {$problem['message']}");
        }
        if (! $import->dry_run && $import->created_count > 0) {
            $this->line('New listings are drafts: review and publish them in the admin panel (Listings, Review queue).');
        }

        return self::SUCCESS;
    }
}
