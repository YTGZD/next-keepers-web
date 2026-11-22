<?php

namespace Tests\Feature;

use App\Filament\Resources\Imports\Pages\ListListingImports;
use App\Models\Business;
use App\Models\ListingImport;
use App\Models\User;
use App\Services\Listings\ListingImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class ListingImportTest extends TestCase
{
    use RefreshDatabase;

    /** A few columns of the "Clean Listings" sheet, in workbook order. */
    private const HEADERS = [
        'Source Row', 'Listing Key', 'Source', 'Scrape Date', 'County', 'State', 'City / Place', 'Location Precision',
        'Title (clean)', 'Title (original)', 'Listing Type', 'Operating Business?', 'Industry', 'Sub-Industry',
        'Asking Price', 'Price Status', 'Revenue', 'Cash Flow (as listed)', 'SDE', 'Earnings Used', 'Earnings Basis',
        'RE in Asking Price?', 'Price / Earnings (as listed)', 'Year Established', 'Seller Fin. %', 'SBA Mentioned',
        'QA Flags', 'Needs Review', 'Description', 'URL',
    ];

    private function row(array $overrides = []): array
    {
        return array_merge([
            'Source Row' => 2, 'Listing Key' => 'bizbuysell:1001', 'Source' => 'BizBuySell', 'Scrape Date' => '2026-10-11',
            'County' => 'Erie', 'State' => 'NY', 'City / Place' => 'Buffalo', 'Location Precision' => 'City',
            'Title (clean)' => 'Established Dental Practice', 'Title (original)' => 'Established Dental Practice!!',
            'Listing Type' => 'Business for Sale', 'Operating Business?' => 'Yes', 'Industry' => 'Healthcare',
            'Sub-Industry' => 'Chiropractic / Dental / Medical', 'Asking Price' => 900000, 'Price Status' => 'Disclosed',
            'Revenue' => 1500000, 'Cash Flow (as listed)' => 300000, 'SDE' => '', 'Earnings Used' => 300000,
            'Earnings Basis' => 'Cash Flow (SDE-equivalent, as listed)', 'RE in Asking Price?' => 'No',
            'Price / Earnings (as listed)' => '=IF(AND(Q2="Yes"),V2/AB2,"")', 'Year Established' => 2005,
            'Seller Fin. %' => 0.1, 'SBA Mentioned' => 'Yes', 'QA Flags' => 'LOC_COUNTY_ONLY, DESC_TRUNCATED',
            'Needs Review' => 'No', 'Description' => 'A dental practice.', 'URL' => 'https://www.bizbuysell.com/x/1001/',
        ], $overrides);
    }

    private function csv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, self::HEADERS);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($h) => $row[$h] ?? '', self::HEADERS));
        }
        rewind($handle);

        return stream_get_contents($handle);
    }

    private function csvFile(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'nk').'.csv';
        file_put_contents($path, $this->csv($rows));

        return $path;
    }

    private function sampleRows(): array
    {
        return [
            $this->row(),
            $this->row(['Listing Key' => 'bizbuysell:1002', 'County' => 'Monroe', 'City / Place' => 'Rochester', 'Title (clean)' => 'HVAC Contractor', 'Industry' => 'Home & Property Services', 'Sub-Industry' => 'Electrical / HVAC / Plumbing', 'Asking Price' => '', 'Price Status' => 'Not Disclosed', 'Earnings Used' => '', 'Cash Flow (as listed)' => '', 'Needs Review' => 'Yes']),
            $this->row(['Listing Key' => 'acqnet:abc', 'Source' => 'Acquisition Network', 'County' => 'Unknown (CA)', 'State' => 'CA', 'City / Place' => '']),
            $this->row(['Listing Key' => 'bizbuysell:1003', 'County' => 'Kings', 'City / Place' => 'Brooklyn', 'Industry' => 'Retail']),
        ];
    }

    public function test_mapping_keeps_missing_figures_null_and_ignores_formulas(): void
    {
        $row = array_change_key_case($this->row([
            'Asking Price' => '$1,250,000', 'Earnings Used' => '', 'County' => 'Unknown (NY)', 'Year Established' => 1198,
            'Title (clean)' => str_repeat('Long title ', 30),
        ]), CASE_LOWER);

        $mapped = ListingImporter::map($row);

        $this->assertSame(1250000, $mapped['asking_price']);
        $this->assertNull($mapped['earnings']);
        $this->assertNull($mapped['sde']);
        $this->assertNull($mapped['county']);
        $this->assertNull($mapped['established_year']);
        $this->assertSame(10.0, $mapped['seller_financing_percent']);
        $this->assertTrue($mapped['sba_mentioned']);
        $this->assertSame(['LOC_COUNTY_ONLY', 'DESC_TRUNCATED'], $mapped['qa_flags']);
        $this->assertSame('healthcare', $mapped['category']);
        $this->assertLessThanOrEqual(140, mb_strlen($mapped['name']));
        $this->assertArrayNotHasKey('status', $mapped);
        $this->assertArrayNotHasKey('price / earnings (as listed)', $mapped);
    }

    public function test_western_ny_import_creates_unpublished_drafts_for_matching_rows_only(): void
    {
        $this->artisan('nk:import-listings', ['file' => $this->csvFile($this->sampleRows()), '--preset' => 'western-ny'])
            ->assertSuccessful();

        $this->assertSame(['bizbuysell:1001', 'bizbuysell:1002'], Business::orderBy('listing_key')->pluck('listing_key')->all());
        $this->assertSame(0, Business::visible()->count());
        $this->assertSame(0, Business::where('is_verified', true)->count());

        $dental = Business::where('listing_key', 'bizbuysell:1001')->first();
        $this->assertSame('draft', $dental->status);
        $this->assertSame('Buffalo, NY', $dental->location);
        $this->assertSame('$900K', $dental->asking_price_label);
        $this->assertSame(3.0, $dental->metrics()->priceToEarnings());
        $this->assertSame('20%', $dental->margin_label);
        $this->assertSame(0, $dental->fit_score);
        $this->assertSame('Not disclosed', $dental->annual_growth_label);
        $this->assertFalse($dental->is_owner_operated);

        $hvac = Business::where('listing_key', 'bizbuysell:1002')->first();
        $this->assertNull($hvac->asking_price);
        $this->assertFalse($hvac->price_disclosed);
        $this->assertSame('Not disclosed', $hvac->asking_price_label);
        $this->assertTrue($hvac->needs_review);
        $this->assertSame('hvac', $hvac->category);

        $import = ListingImport::sole();
        $this->assertSame('done', $import->status);
        $this->assertSame([4, 2, 2], [$import->rows_read, $import->rows_matched, $import->created_count]);
        $this->assertSame($import->id, $dental->listing_import_id);
    }

    public function test_reimporting_refreshes_figures_but_keeps_review_state(): void
    {
        $file = $this->csvFile([$this->row()]);
        $this->artisan('nk:import-listings', ['file' => $file])->assertSuccessful();
        $listing = Business::sole();
        $listing->update(['status' => 'active', 'published_at' => now(), 'admin_notes' => 'Called the broker']);

        $this->artisan('nk:import-listings', ['file' => $file])->assertSuccessful();
        $this->assertSame(1, ListingImport::latest('id')->first()->unchanged_count);

        $this->artisan('nk:import-listings', ['file' => $this->csvFile([$this->row(['Asking Price' => 850000])])])->assertSuccessful();
        $this->assertSame(1, ListingImport::latest('id')->first()->updated_count);

        $listing->refresh();
        $this->assertSame(1, Business::count());
        $this->assertSame(850000, $listing->asking_price);
        $this->assertSame('$850K', $listing->asking_price_label);
        $this->assertSame('active', $listing->status);
        $this->assertNotNull($listing->published_at);
        $this->assertSame('Called the broker', $listing->admin_notes);
    }

    public function test_sample_rows_are_never_overwritten(): void
    {
        $sample = Business::factory()->sample()->create(['listing_key' => 'bizbuysell:1001', 'name' => 'Fictional HVAC']);

        $this->artisan('nk:import-listings', ['file' => $this->csvFile([$this->row()])])->assertSuccessful();

        $this->assertSame('Fictional HVAC', $sample->fresh()->name);
        $this->assertSame(1, ListingImport::sole()->skipped_count);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('nk:import-listings', ['file' => $this->csvFile($this->sampleRows()), '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame(0, Business::count());
        $import = ListingImport::sole();
        $this->assertTrue($import->dry_run);
        $this->assertSame(4, $import->created_count);
    }

    public function test_reads_the_clean_listings_sheet_of_the_workbook(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'nk').'.xlsx';
        $writer = new XlsxWriter;
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Read Me');
        $writer->addRow(Row::fromValues(['This sheet is notes, not listings']));
        $writer->addNewSheetAndMakeItCurrent()->setName('Clean Listings');
        $writer->addRow(Row::fromValues(self::HEADERS));
        foreach ($this->sampleRows() as $row) {
            $writer->addRow(Row::fromValues(array_map(fn ($h) => $row[$h] ?? '', self::HEADERS)));
        }
        $writer->close();

        $this->artisan('nk:import-listings', ['file' => $path, '--preset' => 'healthcare'])->assertSuccessful();

        $this->assertSame(2, Business::count()); // the HVAC and retail rows are not Healthcare
        $this->assertSame('2026-10-11', Business::where('listing_key', 'acqnet:abc')->first()->scraped_on->toDateString());
    }

    public function test_rejects_unknown_presets_and_missing_files(): void
    {
        $this->artisan('nk:import-listings', ['file' => '/nope.csv'])->assertFailed();
        $this->artisan('nk:import-listings', ['file' => $this->csvFile([$this->row()]), '--preset' => 'hvac'])->assertFailed();
        $this->assertSame(0, Business::count());
    }

    public function test_an_admin_can_upload_a_file_in_the_panel(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);

        Livewire::test(ListListingImports::class)
            ->callAction('importListings', data: [
                'file' => UploadedFile::fake()->createWithContent('western-ny.csv', $this->csv($this->sampleRows())),
                'preset' => 'western-ny',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        // In the panel the import runs after the response is sent; under test it runs at once.
        $import = ListingImport::sole();
        $this->assertSame('western-ny.csv', $import->file_name);
        $this->assertSame($admin->id, $import->user_id);
        Storage::disk('local')->assertExists($import->stored_path);
        $this->assertSame('done', $import->fresh()->status);
        $this->assertSame(2, Business::where('status', 'draft')->count());

        $this->get('/admin/imports')->assertOk()->assertSee('western-ny.csv');
        $this->get("/admin/imports/{$import->id}")->assertOk()->assertSee('2 created');
    }

    public function test_buyers_cannot_open_the_imports_page(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/imports')->assertForbidden();
    }
}
