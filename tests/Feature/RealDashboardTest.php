<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\User;
use App\Services\Listings\BuyBoxMatcher;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NextKeepersPlatformSeeder::class);
    }

    private function listings(): array
    {
        return [
            'erie' => Business::factory()->published()->create(['name' => 'Erie Dental', 'county' => 'Erie', 'state' => 'NY', 'asking_price' => 900000, 'earnings' => 300000]),
            'hidden_price' => Business::factory()->published()->create(['name' => 'Monroe Home Care', 'county' => 'Monroe', 'state' => 'NY', 'asking_price' => null, 'price_disclosed' => false]),
            'too_big' => Business::factory()->published()->create(['name' => 'Big Erie Clinic', 'county' => 'Erie', 'state' => 'NY', 'asking_price' => 9000000]),
            'texas' => Business::factory()->published()->create(['name' => 'Austin Med Spa', 'county' => 'Travis', 'state' => 'TX', 'city' => 'Austin']),
            'retail' => Business::factory()->published()->create(['name' => 'Buffalo Shop', 'industry' => 'Retail', 'sub_industry' => 'Specialty Retail', 'county' => 'Erie', 'state' => 'NY']),
            'draft' => Business::factory()->create(['name' => 'Unreviewed Erie Practice', 'county' => 'Erie', 'state' => 'NY']),
        ];
    }

    public function test_a_new_account_starts_with_an_empty_buy_box(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Set up your buy box')
            ->assertSee('Listed figures, not verified')
            ->assertDontSee('Illustrative sample data')
            ->assertDontSee('HVAC, Restoration, Healthcare');

        $this->assertSame([], BuyBox::where('user_id', $user->id)->first()->industries);
    }

    public function test_buy_box_matches_on_what_listings_disclose(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $this->actingAs($user)->post('/buy-box', [
            'industries' => ['Healthcare'], 'states' => ['NY'], 'counties' => 'Erie, Monroe', 'max_asking_price' => 2000000,
        ])->assertRedirect(route('buybox'));

        $this->actingAs($user)->get('/buy-box')->assertOk()
            ->assertSee('2 listings fit this box')
            ->assertSee('Erie Dental')
            ->assertSee('Monroe Home Care')
            ->assertSee('Asking price not disclosed')
            ->assertDontSee('Big Erie Clinic')
            ->assertDontSee('Austin Med Spa')
            ->assertDontSee('Buffalo Shop')
            ->assertDontSee('Unreviewed Erie Practice');

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Erie Dental')->assertSee('2 matches');
    }

    public function test_fit_score_counts_only_criteria_known_to_be_met(): void
    {
        $box = new BuyBox(['industries' => ['Healthcare'], 'states' => ['NY'], 'max_asking_price' => 1000000, 'min_earnings' => 100000]);
        $matcher = new BuyBoxMatcher;

        $known = Business::factory()->make(['state' => 'NY', 'asking_price' => 900000, 'earnings' => 300000]);
        $this->assertSame(100, $matcher->evaluate($known, $box)['score']);

        $partial = Business::factory()->make(['state' => 'NY', 'asking_price' => null, 'earnings' => null]);
        $result = $matcher->evaluate($partial, $box);
        $this->assertSame(50, $result['score']);
        $this->assertSame(['Asking price', 'Earnings'], $result['unknown']);
        $this->assertSame('Asking price and earnings not disclosed', BuyBoxMatcher::unknownNote($result['unknown']));
    }

    public function test_discover_filters_and_never_mixes_sample_and_real_rows(): void
    {
        $user = User::factory()->create();
        $this->listings();
        $sampleName = Business::sample()->value('name');

        $this->actingAs($user)->get('/discover?state=TX')->assertOk()
            ->assertSee('Austin Med Spa')->assertDontSee('Erie Dental')->assertDontSee($sampleName);
        $this->actingAs($user)->get('/discover?q=Dental')->assertOk()->assertSee('Erie Dental')->assertDontSee('Buffalo Shop');

        auth()->logout();
        $this->get('/discover')->assertOk()->assertSee($sampleName)->assertDontSee('Erie Dental');
    }

    public function test_listing_page_shows_listed_figures_and_not_disclosed(): void
    {
        $user = User::factory()->create();
        $listings = $this->listings();

        $this->actingAs($user)->get('/business/'.$listings['hidden_price']->id)->assertOk()
            ->assertSee('Monroe Home Care')
            ->assertSee('Not disclosed')
            ->assertSee('View original listing')
            ->assertDontSee('Owner-Operated')
            ->assertDontSee('50-mile radius');

        $this->actingAs($user)->get('/business/'.$listings['draft']->id)->assertNotFound();
    }

    public function test_watchlist_lists_saved_real_listings_with_an_honest_total(): void
    {
        $user = User::factory()->create();
        $listings = $this->listings();

        $this->actingAs($user)->get('/watchlist')->assertOk()->assertSee('Nothing saved yet')->assertDontSee('$8.7M');

        $this->actingAs($user)->postJson('/watchlist/toggle/'.$listings['erie']->id)->assertOk();
        $this->actingAs($user)->postJson('/watchlist/toggle/'.$listings['hidden_price']->id)->assertOk();

        $this->actingAs($user)->get('/watchlist')->assertOk()
            ->assertSee('Erie Dental')->assertSee('Monroe Home Care')
            ->assertSee('$900K')
            ->assertSee("1 saved listing doesn't disclose a price");
    }
}
