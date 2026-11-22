<?php

namespace Tests\Feature;

use App\Filament\Resources\Listings\Pages\CreateListing;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Business;
use App\Models\EarlyAccessSignup;
use App\Models\Inquiry;
use App\Models\User;
use Database\Seeders\NextKeepersPlatformSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/listings')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_buyers_cannot_open_the_admin_panel(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/listings')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_every_admin_page_renders_for_an_admin(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $listing = Business::factory()->create(['qa_flags' => ['NO_EARNINGS'], 'needs_review' => true]);
        EarlyAccessSignup::create(['email' => 's@example.com', 'consented_at' => now()]);
        $inquiry = Inquiry::create(['business_id' => $listing->id, 'name' => 'Ann', 'email' => 'ann@example.com']);
        $buyer = User::factory()->create();

        $this->actingAs($this->admin());

        foreach (['/admin', '/admin/listings', '/admin/listings/create', "/admin/listings/{$listing->id}/edit",
            '/admin/users', "/admin/users/{$buyer->id}/edit", '/admin/early-access-signups', '/admin/inquiries',
            "/admin/inquiries/{$inquiry->id}/edit", '/admin/newsletter-subscribers'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_review_queue_lists_real_drafts_and_hides_sample_rows(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $draft = Business::factory()->create();
        $published = Business::factory()->published()->create();
        $sample = Business::sample()->firstOrFail();
        $this->actingAs($this->admin());

        Livewire::test(ListListings::class)
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published, $sample]);

        Livewire::test(ListListings::class, ['activeTab' => 'all'])
            ->assertCanSeeTableRecords([$draft, $published])
            ->assertCanNotSeeTableRecords([$sample]);
    }

    public function test_publishing_makes_a_draft_visible_to_buyers(): void
    {
        $draft = Business::factory()->create(['needs_review' => true]);
        $this->actingAs($this->admin());

        Livewire::test(ListListings::class)
            ->callAction(TestAction::make('publish')->table($draft))
            ->assertHasNoErrors();

        $draft->refresh();
        $this->assertSame('active', $draft->status);
        $this->assertNotNull($draft->published_at);
        $this->assertFalse($draft->needs_review);
        $this->assertTrue(Business::visible()->whereKey($draft->id)->exists());

        Livewire::test(ListListings::class, ['activeTab' => 'all'])
            ->callAction(TestAction::make('archive')->table($draft));
        $this->assertSame('archived', $draft->fresh()->status);
    }

    public function test_sample_rows_cannot_be_edited_from_the_panel(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $this->actingAs($this->admin());

        $this->get('/admin/listings/'.Business::sample()->firstOrFail()->id.'/edit')->assertForbidden();
    }

    public function test_an_admin_can_add_a_listing_with_undisclosed_figures(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateListing::class)
            ->fillForm([
                'name' => 'Home Care Agency in Erie County',
                'industry' => 'Healthcare',
                'sub_industry' => 'Home Care / Senior Care',
                'status' => 'draft',
                'state' => 'NY',
                'county' => 'Erie',
                'asking_price' => 850000,
                'annual_revenue' => null,
                'earnings' => 250000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $listing = Business::where('name', 'Home Care Agency in Erie County')->firstOrFail();
        $this->assertFalse($listing->is_sample);
        $this->assertFalse($listing->is_verified);
        $this->assertSame('draft', $listing->status);
        $this->assertNull($listing->annual_revenue);
        $this->assertSame('Not disclosed', $listing->revenue_label);
        $this->assertSame('$850K', $listing->asking_price_label);
        $this->assertSame('Erie County, NY', $listing->location);
        $this->assertEqualsWithDelta(3.4, $listing->metrics()->priceToEarnings(), 0.001);
    }

    public function test_editing_and_publishing_via_the_form_sets_published_at(): void
    {
        $listing = Business::factory()->create();
        $this->actingAs($this->admin());

        Livewire::test(EditListing::class, ['record' => $listing->getRouteKey()])
            ->fillForm(['status' => 'active', 'asking_price' => 500000])
            ->call('save')
            ->assertHasNoFormErrors();

        $listing->refresh();
        $this->assertSame('active', $listing->status);
        $this->assertSame(500000, $listing->asking_price);
        $this->assertNotNull($listing->published_at);
    }

    public function test_admins_can_change_roles_but_not_their_own(): void
    {
        $admin = $this->admin();
        $buyer = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $buyer->getRouteKey()])
            ->fillForm(['role' => 'broker'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('broker', $buyer->fresh()->role);

        Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
            ->fillForm(['name' => 'Renamed', 'role' => 'buyer'])
            ->call('save');
        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertSame('Renamed', $admin->fresh()->name);
    }

    public function test_the_sample_preview_account_is_not_listed_as_a_user(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $this->actingAs($this->admin());

        $this->get('/admin/users')->assertOk()->assertDontSee(config('nextkeepers.demo_email'));
    }

    public function test_the_admin_footer_shows_which_version_is_live(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin')->assertOk()->assertSee('Local copy (not deployed)');

        file_put_contents(base_path('BUILD'), "abc1234 2026-11-22 01:30 UTC\n");
        try {
            $this->get('/admin')->assertOk()->assertSee('Live version abc1234 2026-11-22 01:30 UTC');
        } finally {
            @unlink(base_path('BUILD'));
        }
    }
}
