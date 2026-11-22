<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\NextKeepersPlatformSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ListingDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_preview_listings_are_marked_sample_and_never_verified(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);

        $this->assertGreaterThan(0, Business::count());
        $this->assertSame(Business::count(), Business::sample()->count());
        $this->assertSame(0, Business::where('is_verified', true)->count());
    }

    public function test_new_listings_default_to_unverified_drafts(): void
    {
        $listing = Business::create(['slug' => 'x', 'name' => 'X', 'industry' => 'Healthcare']);

        $listing->refresh();
        $this->assertSame('draft', $listing->status);
        $this->assertFalse($listing->is_verified);
        $this->assertFalse($listing->is_sample);
        $this->assertNull($listing->asking_price);
        $this->assertSame(0, Business::visible()->count());
    }

    public function test_draft_listings_cannot_be_opened_by_id(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);
        $draft = Business::factory()->create(['name' => 'Secret Draft Dental Practice']);

        $buyer = User::factory()->create();
        $this->get("/business/{$draft->id}")->assertOk()->assertDontSee('Secret Draft Dental Practice');
        $this->actingAs($buyer)->get("/business/{$draft->id}")->assertNotFound();

        $draft->update(['status' => 'active']);
        $this->actingAs($buyer)->get("/business/{$draft->id}")->assertOk()->assertSee('Secret Draft Dental Practice');

        // Guests browse the sample preview only, even for a published real listing.
        auth()->logout();
        $this->get("/business/{$draft->id}")->assertOk()->assertDontSee('Secret Draft Dental Practice');
    }

    public function test_listing_keys_are_unique(): void
    {
        Business::factory()->create(['listing_key' => 'bizbuysell:1']);

        $this->expectException(QueryException::class);
        Business::factory()->create(['listing_key' => 'bizbuysell:1']);
    }

    public function test_only_admins_pass_the_admin_gate_and_the_role_cannot_be_mass_assigned(): void
    {
        $buyer = User::create(['name' => 'B', 'email' => 'b@example.com', 'password' => 'a-long-password', 'role' => 'admin']); // role ignored: not fillable
        $this->assertSame('buyer', $buyer->fresh()->role);
        $this->assertFalse(Gate::forUser($buyer)->allows('admin'));

        $buyer->forceFill(['role' => 'admin'])->save();
        $this->assertTrue(Gate::forUser($buyer->fresh())->allows('admin'));
    }

    public function test_admin_command_creates_and_promotes_admins(): void
    {
        $this->artisan('nk:admin', ['email' => 'Owner@Example.com'])
            ->expectsQuestion('Password for the new admin (at least 12 characters)', 'a-strong-password')
            ->expectsQuestion('Type it again', 'a-strong-password')
            ->assertSuccessful();
        $this->assertTrue(User::where('email', 'owner@example.com')->firstOrFail()->isAdmin());

        $this->artisan('nk:admin', ['email' => 'short@example.com'])
            ->expectsQuestion('Password for the new admin (at least 12 characters)', 'short')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'short@example.com']);

        $buyer = User::factory()->create();
        $this->artisan('nk:admin', ['email' => $buyer->email])->assertSuccessful();
        $this->assertTrue($buyer->fresh()->isAdmin());

        $this->artisan('nk:admin', ['email' => $buyer->email, '--revoke' => true])->assertSuccessful();
        $this->assertFalse($buyer->fresh()->isAdmin());
    }

    public function test_the_sample_account_can_never_become_admin(): void
    {
        $this->seed(NextKeepersPlatformSeeder::class);

        $this->artisan('nk:admin', ['email' => config('nextkeepers.demo_email')])->assertFailed();
        $this->assertFalse(User::where('email', config('nextkeepers.demo_email'))->firstOrFail()->isAdmin());
    }
}
