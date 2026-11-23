<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\Listings\ListingImporter;
use App\Support\ListingBenchmark;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Each listing next to its industry's BizBuySell multiples, matching the workbook's benchmark columns. */
class ListingBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $attributes): Business
    {
        return (new Business)->forceFill($attributes + [
            'operating_status' => 'Yes',
            'industry' => 'Healthcare',
            'real_estate_in_price' => 'No',
            'earnings_basis' => 'SDE',
        ]);
    }

    public function test_matches_the_workbook_for_an_overridden_listing(): void
    {
        // Workbook row 2: override "Other Health Care and Fitness Businesses" (2.79x), CJ -0.858, CL "Below market".
        $b = ListingBenchmark::for($this->listing([
            'listing_key' => 'bizbuysell:2535967', 'sub_industry' => 'Med Spa / Wellness / Weight Loss',
            'asking_price' => 475_000, 'annual_revenue' => 2_500_000, 'earnings' => 1_200_000,
        ]));

        $this->assertSame('compared', $b['status']);
        $this->assertSame('Other Health Care and Fitness Businesses', $b['subsector']);
        $this->assertSame('Override', $b['match']);
        $this->assertSame(2.79, $b['industry_pe']);
        $this->assertEqualsWithDelta(-0.8581, $b['pct'], 0.0001);
        $this->assertSame('below', $b['signal']);
        $this->assertStringStartsWith('Priced below its industry: asking is 0.4× SDE', $b['message']);
    }

    public function test_uses_the_sub_industry_default_and_says_how_far_above_it_is(): void
    {
        $b = ListingBenchmark::for($this->listing([
            'sub_industry' => 'Electrical / HVAC / Plumbing', 'asking_price' => 1_800_000, 'earnings' => 500_000,
        ]));

        $this->assertSame('Electrical and Mechanical Contracting Businesses', $b['subsector']);
        $this->assertSame('Close', $b['match']);
        $this->assertSame('above', $b['signal']);
        $this->assertSame('3.6× vs 2.9× · 0.7× above industry', $b['headline']);
        $this->assertSame('Priced above its industry: asking is 3.6× SDE, 0.7× more than the 2.9× cash flow that Electrical and Mechanical Contracting Businesses sold for in 2025.', $b['message']);
    }

    public function test_within_twenty_percent_reads_in_line(): void
    {
        $b = ListingBenchmark::for($this->listing([
            'sub_industry' => 'Electrical / HVAC / Plumbing', 'asking_price' => 1_500_000, 'earnings' => 500_000,
        ]));

        $this->assertSame('in_line', $b['signal']);
        $this->assertStringContainsString('in line with its industry', $b['message']);
    }

    public function test_the_listings_own_pick_wins_and_ebitda_real_estate_and_thin_samples_are_flagged(): void
    {
        $b = ListingBenchmark::for($this->listing([
            'sub_industry' => 'Grocery / Convenience / Gas', 'benchmark_subsector' => 'Hotels', 'earnings_basis' => 'EBITDA',
            'asking_price' => 2_000_000, 'real_estate_in_price' => 'Yes', 'real_estate_value' => 1_000_000, 'earnings' => 250_000,
        ]));

        $this->assertSame('Hotels', $b['subsector']);
        $this->assertSame(4.0, round($b['listing_pe'], 2)); // (2.0M - 1.0M real estate) / 250k
        $this->assertTrue($b['thin']);
        $caveats = implode(' ', $b['caveats']);
        $this->assertStringContainsString('Only 8 sales', $caveats);
        $this->assertStringContainsString('reports EBITDA', $caveats);
        $this->assertStringContainsString('Real estate is taken out', $caveats);
    }

    public function test_proxy_categories_say_so(): void
    {
        $b = ListingBenchmark::for($this->listing(['sub_industry' => 'Veterinary', 'asking_price' => 900_000, 'earnings' => 300_000]));

        $this->assertSame('Other Pet Service Businesses', $b['subsector']);
        $this->assertStringContainsString('no exact category for Veterinary', implode(' ', $b['caveats']));
    }

    public function test_missing_earnings_still_shows_the_industry_but_never_compares(): void
    {
        $b = ListingBenchmark::for($this->listing(['sub_industry' => 'Home Care / Senior Care', 'asking_price' => 900_000, 'earnings' => null]));

        $this->assertSame('no_multiple', $b['status']);
        $this->assertNull($b['signal']);
        $this->assertSame('Home Health Care Businesses sold for 2.8× cash flow in 2025. The listing doesn\'t disclose earnings, so its multiple can\'t be compared.', $b['message']);

        $re = ListingBenchmark::for($this->listing(['sub_industry' => 'Home Care / Senior Care', 'asking_price' => 900_000, 'earnings' => 100_000, 'real_estate_in_price' => 'Yes']));
        $this->assertStringContainsString('Real estate is in the price with no separate value', $re['message']);
    }

    public function test_no_benchmark_for_property_unclassified_or_unknown_sub_industries(): void
    {
        foreach (['Commercial / Mixed-use Property' => 'property sale', 'Unclassified' => 'doesn\'t say clearly', 'Space Tourism' => 'no category that matches Space Tourism'] as $sub => $text) {
            $b = ListingBenchmark::for($this->listing(['sub_industry' => $sub, 'asking_price' => 900_000, 'earnings' => 300_000]));
            $this->assertSame('no_benchmark', $b['status']);
            $this->assertStringContainsString($text, $b['message']);
        }
    }

    public function test_listing_page_and_cards_show_the_comparison(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $listing = Business::factory()->published()->create([
            'name' => 'Rochester HVAC Co', 'industry' => 'Home & Property Services', 'sub_industry' => 'Electrical / HVAC / Plumbing',
            'asking_price' => 1_800_000, 'earnings' => 500_000, 'earnings_basis' => 'SDE', 'real_estate_in_price' => 'No',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('business.show', ['id' => $listing->id]))->assertOk()
            ->assertSee('Industry multiples')
            ->assertSee('Above industry')
            ->assertSee('0.7× more than the 2.9× cash flow', false)
            ->assertSee('not a valuation of this business')
            ->assertSee('industry=Electrical%20and%20Mechanical%20Contracting%20Businesses', false);

        $this->actingAs($user)->get('/businesses')->assertOk()
            ->assertSee('Price / earnings 3.6× vs 2.9× · 0.7× above industry', false);

        $this->actingAs($user)->get('/market-intelligence?industry=Electrical+and+Mechanical+Contracting+Businesses')->assertOk()
            ->assertViewHas('benchmarks', fn ($b) => $b['defaultIndustry'] === 'Electrical and Mechanical Contracting Businesses');
    }

    public function test_importer_reads_the_workbook_override_column_only_when_filled(): void
    {
        $this->assertSame('Gas Stations', ListingImporter::map(['sub-industry' => 'Grocery / Convenience / Gas', 'bbs subsector (override)' => 'Gas Stations'])['benchmark_subsector']);
        $this->assertArrayNotHasKey('benchmark_subsector', ListingImporter::map(['sub-industry' => 'Grocery / Convenience / Gas', 'bbs subsector (override)' => '']));
        $this->assertArrayNotHasKey('benchmark_subsector', ListingImporter::map(['sub-industry' => 'Grocery / Convenience / Gas']));
    }
}
