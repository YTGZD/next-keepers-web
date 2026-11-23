<?php

namespace Tests\Feature;

use App\Support\IndustryBenchmarks;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustryBenchmarksTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmarks_load_every_2025_industry_with_its_history(): void
    {
        $data = (new IndustryBenchmarks)->toArray();

        $this->assertCount(137, $data['rows']);
        $this->assertSame(IndustryBenchmarks::YEARS, $data['years']);

        $auto = collect($data['rows'])->firstWhere('subsector', 'Auto Repair and Service Shops');
        $this->assertSame(247, $auto['sales']);
        $this->assertSame(2.7, $auto['cfx']);
        $this->assertSame(2.61, $auto['cfx2019']);
        $this->assertSame([null, 2.43, 2.61, 2.55, 2.86, 2.91, 2.8, 2.86, 2.7], $auto['trend']);
        $this->assertFalse($auto['thin']);
    }

    public function test_subsectors_spelled_with_and_without_commas_are_joined(): void
    {
        $rows = collect((new IndustryBenchmarks)->rows());

        foreach (['Bars, Pubs and Taverns', 'Dance, Pilates and Yoga Studios'] as $name) {
            $this->assertNotNull($rows->firstWhere('subsector', $name)['trend'][8], $name);
        }
    }

    public function test_thin_samples_stay_out_of_highlights_and_medians(): void
    {
        $data = (new IndustryBenchmarks)->toArray();
        $thin = collect($data['rows'])->where('thin', true)->pluck('subsector');

        $this->assertNotEmpty($thin);
        foreach ($data['highlights'] as $card) {
            if ($card['industry']) {
                $this->assertNotContains($card['industry'], $thin);
            }
        }
        $this->assertCount(4, $data['highlights']);
        $this->assertNotNull($data['median'][8]);
    }

    public function test_median_handles_odd_even_and_empty_lists(): void
    {
        $this->assertSame(2.0, IndustryBenchmarks::median([3, 1, 2]));
        $this->assertSame(2.5, IndustryBenchmarks::median([4, 1, 2, 3]));
        $this->assertNull(IndustryBenchmarks::median([]));
    }

    public function test_market_intelligence_shows_the_benchmarks_with_their_source(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);

        $this->get('/market-intelligence')
            ->assertOk()
            ->assertSee('id="industry-benchmarks"', false)
            ->assertSee('What businesses actually sold for')
            ->assertSee('Most traded')
            ->assertSee('Dental Practices')
            ->assertSee('BizBuySell Insight reports');
    }
}
