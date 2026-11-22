<?php

namespace App\Jobs;

use App\Models\ListingImport;
use App\Services\Listings\ListingImporter;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Runs an import uploaded in the admin panel. It is dispatched "after response"
 * so the upload returns at once and the file is read in the same PHP process
 * once the page is sent (the server has no queue worker).
 */
class RunListingImport
{
    use Dispatchable;

    public function __construct(public int $importId) {}

    public function handle(ListingImporter $importer): void
    {
        $import = ListingImport::find($this->importId);
        if (! $import || $import->status !== 'queued') {
            return;
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        ignore_user_abort(true);

        $importer->run($import);
    }
}
