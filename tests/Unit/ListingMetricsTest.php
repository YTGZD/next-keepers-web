<?php

namespace Tests\Unit;

use App\Models\Business;
use PHPUnit\Framework\TestCase;

/** The derived multiples must match the formula columns of the cleaned listings workbook. */
class ListingMetricsTest extends TestCase
{
    private function listing(array $attributes): Business
    {
        return (new Business)->forceFill($attributes + ['operating_status' => 'Yes']);
    }

    public function test_multiples_match_the_workbook_for_a_simple_listing(): void
    {
        // Workbook row 2: asking 475,000, revenue 2,500,000, earnings 1,200,000, lease only.
        $m = $this->listing(['asking_price' => 475_000, 'annual_revenue' => 2_500_000, 'earnings' => 1_200_000, 'real_estate_in_price' => 'No'])->metrics();

        $this->assertSame(475_000.0, $m->priceExRealEstate());
        $this->assertEqualsWithDelta(0.3958, $m->priceToEarnings(), 0.0001);
        $this->assertEqualsWithDelta(0.19, $m->priceToRevenue(), 0.0001);
        $this->assertEqualsWithDelta(0.48, $m->earningsMargin(), 0.0001);
        $this->assertNull($m->impliedGoodwill());
        $this->assertNull($m->rentShareOfRevenue());
    }

    public function test_real_estate_inventory_and_equipment_adjust_price_and_cash_needed(): void
    {
        $m = $this->listing([
            'asking_price' => 1_000_000, 'real_estate_in_price' => 'Yes', 'real_estate_value' => 400_000,
            'ffe_value' => 100_000, 'ffe_in_price' => 'Yes', 'inventory_value' => 50_000, 'inventory_in_price' => 'No',
            'earnings' => 200_000, 'annual_revenue' => 1_000_000, 'monthly_rent' => 5_000,
        ])->metrics();

        $this->assertSame(600_000.0, $m->priceExRealEstate());
        $this->assertSame(500_000.0, $m->impliedGoodwill());      // 600k - FF&E in price
        $this->assertSame(650_000.0, $m->cashNeeded());           // 600k + inventory sold on top
        $this->assertSame(3.0, $m->priceExRealEstateToEarnings());
        $this->assertSame(3.25, $m->cashNeededToEarnings());
        $this->assertEqualsWithDelta(0.06, $m->rentShareOfRevenue(), 0.0001);
    }

    public function test_missing_figures_give_null_not_zero(): void
    {
        $m = $this->listing(['asking_price' => 300_000, 'earnings' => null, 'annual_revenue' => null])->metrics();
        $this->assertNull($m->priceToEarnings());
        $this->assertNull($m->priceToRevenue());
        $this->assertNull($m->earningsMargin());

        $reNotSplit = $this->listing(['asking_price' => 300_000, 'real_estate_in_price' => 'Yes', 'real_estate_value' => null, 'earnings' => 100_000])->metrics();
        $this->assertNull($reNotSplit->priceExRealEstate());
        $this->assertSame(3.0, $reNotSplit->priceToEarnings());
    }

    public function test_multiples_only_apply_to_operating_businesses(): void
    {
        $m = $this->listing(['operating_status' => 'No', 'asking_price' => 300_000, 'earnings' => 100_000, 'annual_revenue' => 500_000])->metrics();
        $this->assertNull($m->priceToEarnings());
        $this->assertNull($m->priceToRevenue());
    }
}
