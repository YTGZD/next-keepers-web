<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    public function test_homepage_renders_every_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Good businesses deserve better', false)
            ->assertSee('id="platform"', false)
            ->assertSee('id="intelligence"', false)
            ->assertSee('next keeper.', false)
            ->assertSee('id="audiences"', false)
            ->assertSee('id="early-access"', false)
            ->assertSee('Sample data');
    }

    public function test_legal_pages_render(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
        $this->get('/terms')->assertOk()->assertSee('Terms of Service');
    }

    public function test_contact_and_social_links_only_show_when_configured(): void
    {
        config(['nextkeepers.contact_email' => null, 'nextkeepers.social' => ['linkedin' => null, 'youtube' => null, 'x' => null]]);
        $this->get('/')->assertDontSee('mailto:', false)->assertDontSee('aria-label="LinkedIn"', false);

        config(['nextkeepers.contact_email' => 'hello@example.com', 'nextkeepers.social' => ['linkedin' => 'https://linkedin.com/company/x']]);
        $this->get('/')->assertSee('mailto:hello@example.com', false)->assertSee('aria-label="LinkedIn"', false);
    }

    public function test_quote_section_shows_mission_until_real_testimonials_exist(): void
    {
        config(['testimonials.items' => []]);
        $this->get('/')->assertOk()->assertSee('Preserving value.', false)->assertSee('The Next Keepers mission');

        config(['testimonials.items' => [['quote' => 'A real quote.', 'name' => 'Jane Doe', 'role' => 'Owner']]]);
        $this->get('/')->assertOk()->assertSee('A real quote.', false)->assertSee('Jane Doe');
    }
}
