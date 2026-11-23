<?php

namespace Tests\Feature;

use App\Support\RegionalInsights;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionalInsightsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_state_on_the_map_has_all_four_measures(): void
    {
        $data = (new RegionalInsights)->toArray();

        $this->assertCount(51, $data['shapes']);
        $this->assertSame(['population', 'permits', 'density', 'sba'], array_keys($data['metrics']));
        foreach ($data['states'] as $abbr => $state) {
            foreach (array_keys($data['metrics']) as $key) {
                $this->assertNotNull($state[$key]['value'], "$abbr $key");
            }
        }
    }

    public function test_us_figures_come_from_the_data(): void
    {
        $metrics = (new RegionalInsights)->toArray()['metrics'];

        $this->assertSame(3.1, $metrics['population']['us']['value']);
        $this->assertSame(4.19, $metrics['permits']['us']['value']);
        $this->assertStringStartsWith('Fastest growing since 2020: Idaho', $metrics['population']['insight']);
    }

    public function test_sba_leaders_skip_states_with_few_loans(): void
    {
        $data = (new RegionalInsights)->toArray();

        $this->assertStringContainsString('Washington', $data['metrics']['sba']['insight']);
        $this->assertSame(206.0, $data['states']['NY']['sba']['loans']);
    }

    public function test_market_intelligence_renders_the_working_card_without_the_static_image(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);

        $this->get('/market-intelligence')
            ->assertOk()
            ->assertSee('id="regional-insights"', false)
            ->assertSee('Housing Permits')
            ->assertSee('County Business Patterns')
            ->assertDontSee('regional-map.png')
            ->assertDontSee('Strong growth in Sun Belt');
    }
}
