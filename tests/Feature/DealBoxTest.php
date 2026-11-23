<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\DealBox;
use App\Models\User;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DealBoxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NextKeepersPlatformSeeder::class);
    }

    /** @return array<string, Business> */
    private function listings(): array
    {
        $year = (int) now()->year;
        $make = fn (array $a) => Business::factory()->published()->create($a);

        return [
            // $1.5M price including $600K of real estate: $900K without it, 3x on $300K.
            'with_re' => $make(['name' => 'Amherst Pizza', 'asking_price' => 1500000, 'real_estate_in_price' => 'Yes', 'real_estate_value' => 600000, 'real_estate' => 'Included', 'earnings' => 300000, 'established_year' => $year - 20, 'seller_financing' => 'Yes']),
            'pricey' => $make(['name' => 'Pricey Practice', 'asking_price' => 3000000, 'earnings' => 400000, 'established_year' => $year - 30]),
            'high_multiple' => $make(['name' => 'Rich Multiple Clinic', 'asking_price' => 1000000, 'earnings' => 100000, 'established_year' => $year - 15]),
            'young' => $make(['name' => 'New Med Spa', 'asking_price' => 600000, 'earnings' => 200000, 'established_year' => $year - 2]),
            'undisclosed' => $make(['name' => 'Quiet Home Care', 'asking_price' => null, 'earnings' => null, 'established_year' => null, 'real_estate' => 'Unknown']),
            'not_operating' => $make(['name' => 'Empty Storefront', 'operating_status' => 'No', 'asking_price' => 200000, 'earnings' => null]),
            'delisted' => $make(['name' => 'Gone Dental', 'qa_flags' => ['DELISTED'], 'asking_price' => 500000, 'earnings' => 200000]),
            'out_of_county' => $make(['name' => 'Elsewhere Vet', 'qa_flags' => ['OUT_OF_COUNTY', 'CARD_ONLY'], 'asking_price' => 500000, 'earnings' => 200000]),
            'bizscout' => $make(['name' => 'Scout Dental', 'source' => 'BizScout', 'asking_price' => 700000, 'earnings' => 250000, 'established_year' => $year - 12]),
        ];
    }

    private function results(User $user, array $criteria): array
    {
        return $this->actingAs($user)->getJson('/buy-box/results?'.http_build_query($criteria))->assertOk()->json();
    }

    public function test_the_page_shows_the_deal_box_panel(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $this->actingAs($user)->get('/buy-box')->assertOk()
            ->assertSee('Deal box')
            ->assertSee('Price, without real estate')
            ->assertSee('Hide listings flagged as outside the county')
            ->assertSee('Amherst Pizza')
            // Hygiene switches are on by default.
            ->assertDontSee('Empty Storefront')
            ->assertDontSee('Gone Dental')
            ->assertDontSee('Elsewhere Vet');
    }

    public function test_hygiene_switches_hide_and_show_flagged_listings(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $html = $this->results($user, [])['html'];
        $this->assertStringNotContainsString('Gone Dental', $html);
        $this->assertStringNotContainsString('Elsewhere Vet', $html);
        $this->assertStringNotContainsString('Empty Storefront', $html);

        $html = $this->results($user, ['hide_delisted' => 0, 'hide_out_of_county' => 0, 'operating_only' => 0])['html'];
        $this->assertStringContainsString('Gone Dental', $html);
        $this->assertStringContainsString('Elsewhere Vet', $html);
        $this->assertStringContainsString('Empty Storefront', $html);
    }

    public function test_price_without_real_estate_multiple_and_years(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $data = $this->results($user, ['max_price_ex_re' => 1000000, 'max_multiple' => 3.5, 'min_years_in_business' => 10]);
        $html = $data['html'];

        // $1.5M asking, but $900K without real estate at 3x: fits.
        $this->assertStringContainsString('Amherst Pizza', $html);
        $this->assertStringContainsString('Scout Dental', $html);
        // Undisclosed figures never count against a listing.
        $this->assertStringContainsString('Quiet Home Care', $html);
        $this->assertStringNotContainsString('Pricey Practice', $html);       // $3M
        $this->assertStringNotContainsString('Rich Multiple Clinic', $html);  // 10x
        $this->assertStringNotContainsString('New Med Spa', $html);           // 2 years
        $this->assertSame(3, $data['count']);
    }

    public function test_sde_source_keyword_real_estate_and_financing(): void
    {
        $user = User::factory()->create();
        $this->listings();

        $html = $this->results($user, ['min_earnings' => 250000, 'require_earnings' => 1])['html'];
        $this->assertStringContainsString('Pricey Practice', $html);
        $this->assertStringNotContainsString('Quiet Home Care', $html);
        $this->assertStringNotContainsString('New Med Spa', $html);

        $html = $this->results($user, ['sources' => ['BizScout']])['html'];
        $this->assertStringContainsString('Scout Dental', $html);
        $this->assertStringNotContainsString('Amherst Pizza', $html);

        $html = $this->results($user, ['keyword' => 'pizza'])['html'];
        $this->assertStringContainsString('Amherst Pizza', $html);
        $this->assertStringNotContainsString('Scout Dental', $html);

        $html = $this->results($user, ['real_estate_pref' => 'leased'])['html'];
        $this->assertStringNotContainsString('Amherst Pizza', $html);
        $this->assertStringContainsString('Scout Dental', $html);     // factory: lease only
        $this->assertStringContainsString('Quiet Home Care', $html);  // unknown is kept

        $html = $this->results($user, ['seller_financing_only' => 1])['html'];
        $this->assertStringContainsString('Amherst Pizza', $html);
        $this->assertStringNotContainsString('Scout Dental', $html);
    }

    public function test_saving_a_box_makes_it_the_active_buy_box_and_boxes_are_private(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->listings();

        $response = $this->actingAs($user)->postJson('/deal-boxes', [
            'name' => 'First SBA deal', 'max_price_ex_re' => 1500000, 'min_earnings' => 150000, 'max_multiple' => 3.5,
            'min_years_in_business' => 10, 'states' => ['NY'], 'counties' => ['Erie'], 'require_earnings' => true,
        ])->assertOk()->assertJsonPath('boxes.0.name', 'First SBA deal');

        $box = BuyBox::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1500000, $box->max_price_ex_re);
        $this->assertSame(3.5, $box->max_multiple);
        $this->assertSame(['Erie'], $box->counties);
        $this->assertTrue($box->require_earnings);
        $this->assertTrue($box->hide_delisted);

        // Saving again under the same name updates it rather than adding a second box.
        $this->actingAs($user)->postJson('/deal-boxes', ['name' => 'First SBA deal', 'max_multiple' => 4])->assertOk();
        $this->assertSame(1, DealBox::where('user_id', $user->id)->count());
        $this->assertEquals(4, DealBox::where('user_id', $user->id)->first()->criteria['max_multiple']);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Amherst Pizza');

        $id = $response->json('id');
        $this->actingAs($other)->deleteJson("/deal-boxes/{$id}")->assertNotFound();
        $this->actingAs($other)->get('/buy-box')->assertOk()->assertDontSee('First SBA deal');

        $this->actingAs($user)->deleteJson("/deal-boxes/{$id}")->assertOk()->assertJsonPath('boxes', []);
        $this->assertSame(0, DealBox::count());
    }

    public function test_guests_cannot_use_the_deal_box_endpoints(): void
    {
        $this->getJson('/buy-box/results')->assertUnauthorized();
        $this->postJson('/deal-boxes', ['name' => 'x'])->assertUnauthorized();
        $this->deleteJson('/deal-boxes/1')->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson('/deal-boxes', ['name' => ''])->assertUnprocessable();
    }
}
