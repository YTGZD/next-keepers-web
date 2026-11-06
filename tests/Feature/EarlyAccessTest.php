<?php

namespace Tests\Feature;

use App\Models\EarlyAccessSignup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarlyAccessTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'Buyer@Example.com ',
            'name' => 'Pat Buyer',
            'interest' => 'buyer',
            'consent' => '1',
        ], $overrides);
    }

    public function test_json_signup_is_stored_with_normalized_email_and_consent(): void
    {
        $this->postJson('/early-access', $this->payload())->assertCreated();

        $signup = EarlyAccessSignup::sole();
        $this->assertSame('buyer@example.com', $signup->email);
        $this->assertSame('buyer', $signup->interest);
        $this->assertNotNull($signup->consented_at);
        $this->assertSame(64, strlen($signup->ip_hash));
        $this->assertNotSame('127.0.0.1', $signup->ip_hash);
    }

    public function test_form_post_without_javascript_redirects_to_the_form_with_success(): void
    {
        $this->post('/early-access', $this->payload())
            ->assertRedirect(route('home').'#early-access')
            ->assertSessionHas('signup', 'success');
    }

    public function test_same_email_updates_instead_of_duplicating_and_response_is_identical(): void
    {
        $this->postJson('/early-access', $this->payload())->assertCreated();
        $this->postJson('/early-access', $this->payload(['interest' => 'investor']))->assertCreated();

        $this->assertSame(1, EarlyAccessSignup::count());
        $this->assertSame('investor', EarlyAccessSignup::sole()->interest);
    }

    public function test_consent_email_and_interest_are_required(): void
    {
        $this->postJson('/early-access', $this->payload(['consent' => null]))->assertUnprocessable()->assertJsonValidationErrors('consent');
        $this->postJson('/early-access', $this->payload(['email' => 'not-an-email']))->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/early-access', $this->payload(['interest' => 'hacker']))->assertUnprocessable()->assertJsonValidationErrors('interest');
        $this->assertSame(0, EarlyAccessSignup::count());
    }

    public function test_honeypot_submissions_look_successful_but_store_nothing(): void
    {
        $this->postJson('/early-access', $this->payload(['website' => 'http://spam.example']))->assertCreated();
        $this->assertSame(0, EarlyAccessSignup::count());
    }

    public function test_requests_are_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/early-access', $this->payload(['email' => "p{$i}@example.com"]))->assertCreated();
        }
        $this->postJson('/early-access', $this->payload(['email' => 'p6@example.com']))->assertStatus(429);
    }

    public function test_utm_source_is_sanitised_and_stored(): void
    {
        $this->postJson('/early-access?utm_source=linkedin<script>', $this->payload())->assertCreated();
        $this->assertSame('linkedinscript', EarlyAccessSignup::sole()->source);
    }

    public function test_export_command_writes_csv_and_neutralises_formulas(): void
    {
        EarlyAccessSignup::create(['email' => 'a@example.com', 'name' => '=HYPERLINK("x")', 'interest' => 'owner', 'consented_at' => now()]);
        $this->artisan('signups:export', ['path' => 'storage/framework/testing/signups.csv'])->assertSuccessful();

        $csv = file_get_contents(base_path('storage/framework/testing/signups.csv'));
        $this->assertStringContainsString('a@example.com', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        @unlink(base_path('storage/framework/testing/signups.csv'));
    }
}
