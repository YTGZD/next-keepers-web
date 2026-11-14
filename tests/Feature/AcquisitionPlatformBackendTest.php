<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\Inquiry;
use App\Models\MessageThread;
use App\Models\NewsletterSubscription;
use App\Models\PipelineDeal;
use App\Models\User;
use App\Models\WatchlistItem;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcquisitionPlatformBackendTest extends TestCase
{
    use RefreshDatabase;

    /** Every read-only dashboard page. */
    private const PAGES = ['/dashboard', '/discover', '/business/2', '/buy-box', '/market-intelligence', '/watchlist', '/messages', '/settings'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(NextKeepersPlatformSeeder::class);
    }

    private function demo(): User
    {
        return User::where('email', config('nextkeepers.demo_email'))->firstOrFail();
    }

    public function test_guests_can_browse_the_read_only_sample_dashboard(): void
    {
        foreach (self::PAGES as $page) {
            $this->get($page)->assertOk()->assertSee('Illustrative sample data');
        }

        $this->get('/dashboard')->assertSee('HVAC Services Company')->assertSee('Medical Equipment Distributor');
        $this->get('/discover')->assertSee('Rochester HVAC Services Co.');
        $this->get('/business/2')->assertSee('Medical Equipment Distributor');
        $this->get('/market-intelligence')->assertSee('Healthcare Services');
        $this->get('/settings')->assertSee('Sample Buyer')->assertDontSee('Yousef');
    }

    public function test_dashboard_shows_counts_from_the_database_not_invented_totals(): void
    {
        $this->get('/dashboard')->assertOk()->assertDontSee('1,248')->assertDontSee('$2.4B');
    }

    public function test_guests_cannot_write_anything(): void
    {
        $business = Business::firstOrFail();
        $before = [WatchlistItem::count(), Inquiry::count(), PipelineDeal::count(), $this->demo()->only('name', 'password')];

        $this->postJson("/watchlist/toggle/{$business->id}")->assertUnauthorized();
        $this->deleteJson("/watchlist/{$business->id}")->assertUnauthorized();
        $this->postJson("/pipeline/toggle/{$business->id}")->assertUnauthorized();
        $this->postJson("/business/{$business->id}/inquire", ['name' => 'X', 'email' => 'x@example.com'])->assertUnauthorized();
        $this->postJson('/buy-box', ['location' => 'Anywhere'])->assertUnauthorized();
        $this->postJson('/settings/profile', ['name' => 'Hacked', 'email' => 'a@example.com'])->assertUnauthorized();
        $this->postJson('/settings/password', ['current_password' => 'x', 'new_password' => 'password123'])->assertUnauthorized();
        $this->postJson('/messages/1/send', ['text' => 'hi'])->assertUnauthorized();
        $this->post('/settings/profile', ['name' => 'Hacked'])->assertRedirect(route('login'));

        $this->assertSame($before, [WatchlistItem::count(), Inquiry::count(), PipelineDeal::count(), $this->demo()->only('name', 'password')]);
    }

    public function test_registering_creates_a_real_account_and_every_page_works_for_a_new_user(): void
    {
        $this->post('/register', ['name' => 'Ada Buyer', 'email' => 'Ada@Example.com', 'password' => 'a-long-password'])
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('a-long-password', $user->password));
        $this->assertNotSame('a-long-password', $user->password);
        $this->assertSame('Free account', $user->membership_tier);

        // Brand-new accounts have no watchlist, messages or NDAs: nothing may be invented for them.
        foreach (self::PAGES as $page) {
            $this->get($page)->assertOk();
        }
        $this->get('/settings')->assertSee('Ada Buyer')->assertDontSee('Verified Pro Buyer')->assertDontSee('Master NDA');
        $this->assertSame(0, WatchlistItem::where('user_id', $user->id)->count());
        $this->assertSame(0, MessageThread::where('user_id', $user->id)->count());
    }

    public function test_registration_is_validated_and_cannot_take_over_an_existing_account(): void
    {
        $victim = User::factory()->create(['email' => 'victim@example.com', 'password' => 'original-password']);
        $hash = $victim->password;

        $this->post('/register', ['name' => 'Mallory', 'email' => 'victim@example.com', 'password' => 'attacker-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame($hash, $victim->fresh()->password);
        $this->assertSame($victim->name, $victim->fresh()->name);

        $this->post('/register', ['name' => '', 'email' => 'not-an-email', 'password' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->post('/register', [])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_login_requires_the_correct_password_and_never_creates_accounts(): void
    {
        User::factory()->create(['email' => 'real@example.com', 'password' => 'right-password']);
        $users = User::count();

        $this->post('/login', ['email' => 'real@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'anything-at-all'])->assertSessionHasErrors('email');
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        $this->assertSame($users, User::count());

        // The shared sample account has a random password: nobody can sign in as it.
        $this->post('/login', ['email' => config('nextkeepers.demo_email'), 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'REAL@example.com', 'password' => 'right-password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => 'real@example.com', 'password' => 'guess-'.$i]);
        }
        $this->post('/login', ['email' => 'real@example.com', 'password' => 'guess-7'])->assertStatus(429);
    }

    public function test_changing_password_requires_the_current_one(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);

        $this->actingAs($user)->postJson('/settings/password', ['current_password' => 'nope', 'new_password' => 'new-password-1'])
            ->assertStatus(422)->assertJsonValidationErrors('current_password');
        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));

        $this->actingAs($user)->postJson('/settings/password', ['current_password' => 'old-password-1', 'new_password' => 'new-password-1'])
            ->assertOk();
        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
    }

    public function test_watchlist_toggle_and_remove_persist_in_database(): void
    {
        $user = User::factory()->create();
        $business = Business::firstOrFail();

        $this->actingAs($user)->postJson("/watchlist/toggle/{$business->id}")->assertOk()->assertJson(['in_watchlist' => true]);
        $this->assertDatabaseHas('watchlist_items', ['user_id' => $user->id, 'business_id' => $business->id]);

        $this->actingAs($user)->postJson("/watchlist/toggle/{$business->id}")->assertOk()->assertJson(['in_watchlist' => false]);
        $this->assertDatabaseMissing('watchlist_items', ['user_id' => $user->id, 'business_id' => $business->id]);

        $this->actingAs($user)->postJson("/watchlist/toggle/{$business->id}");
        $this->actingAs($user)->deleteJson("/watchlist/{$business->id}")->assertOk();
        $this->assertDatabaseMissing('watchlist_items', ['user_id' => $user->id, 'business_id' => $business->id]);
    }

    public function test_business_inquiry_creates_inquiry_and_pipeline_deal_without_claiming_an_nda(): void
    {
        $user = User::factory()->create();
        $business = Business::findOrFail(3);

        $this->actingAs($user)
            ->postJson("/business/{$business->id}/inquire", ['name' => 'Ada Buyer', 'email' => 'ada@example.com', 'type' => 'dossier'])
            ->assertCreated();

        $this->assertSame(1, Inquiry::where('business_id', $business->id)->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('pipeline_deals', ['user_id' => $user->id, 'business_id' => $business->id, 'stage' => 'inquiry_sent', 'nda_signed' => false]);
    }

    public function test_sending_message_appends_to_the_users_own_thread_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $thread = MessageThread::firstOrFail();
        $thread->update(['user_id' => $user->id]);
        $count = $thread->messages()->count();

        $this->actingAs($other)->postJson("/messages/{$thread->id}/send", ['text' => 'not mine'])->assertNotFound();
        $this->assertSame($count, $thread->messages()->count());

        $this->actingAs($user)->postJson("/messages/{$thread->id}/send", ['text' => 'We would like a management call Friday.'])->assertCreated();
        $this->assertSame($count + 1, $thread->messages()->count());
        $this->assertSame('We would like a management call Friday.', $thread->fresh()->last_message);
    }

    public function test_buy_box_profile_and_newsletter_updates_persist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/buy-box', ['industries' => ['HVAC', 'Manufacturing'], 'location' => 'Rochester, NY + 50 miles', 'profitable_only' => true])->assertOk();
        $buyBox = BuyBox::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(['HVAC', 'Manufacturing'], $buyBox->industries);

        $this->actingAs($user)->postJson('/settings/profile', ['name' => 'Ada Buyer', 'email' => $user->email, 'company' => 'Ada Holdings'])->assertOk();
        $this->assertSame('Ada Holdings', $user->fresh()->company);

        $this->actingAs($user)->postJson('/newsletter/subscribe', ['email' => 'alerts@example.com'])->assertCreated();
        $this->assertSame($user->id, NewsletterSubscription::where('email', 'alerts@example.com')->value('user_id'));
    }

    public function test_newsletter_signup_works_for_guests_without_attaching_the_sample_account(): void
    {
        $this->postJson('/newsletter/subscribe', ['email' => 'guest@example.com'])->assertCreated();
        $this->assertNull(NewsletterSubscription::where('email', 'guest@example.com')->value('user_id'));
    }
}
