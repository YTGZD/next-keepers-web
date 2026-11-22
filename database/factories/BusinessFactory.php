<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * A real (non-sample) listing shaped like a row of the cleaned listings workbook.
 *
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Dental Practice', 'Home Care Agency', 'HVAC Contractor', 'Med Spa', 'Veterinary Clinic']).' in '.fake()->city();
        $revenue = fake()->numberBetween(4, 60) * 50_000;
        $earnings = (int) round($revenue * fake()->randomFloat(2, 0.1, 0.3), -3);
        $asking = (int) round($earnings * fake()->randomFloat(1, 2, 4.5), -3);

        return [
            'is_sample' => false,
            'listing_key' => 'test:'.Str::uuid(),
            'source' => 'BizBuySell',
            'source_url' => fake()->url(),
            'scraped_on' => now()->subDays(10)->toDateString(),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'name' => $name,
            'industry' => 'Healthcare',
            'sub_industry' => 'Chiropractic / Dental / Medical',
            'category' => 'healthcare',
            'county' => 'Erie',
            'state' => 'NY',
            'city' => 'Buffalo',
            'location' => 'Buffalo, NY',
            'location_precision' => 'City',
            'listing_type' => 'Business for Sale',
            'operating_status' => 'Yes',
            'asking_price' => $asking,
            'annual_revenue' => $revenue,
            'cash_flow' => $earnings,
            'earnings' => $earnings,
            'earnings_basis' => 'Cash Flow (SDE-equivalent, as listed)',
            'real_estate' => 'Lease only',
            'real_estate_in_price' => 'No',
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'active', 'published_at' => now()]);
    }

    public function sample(): static
    {
        return $this->state(fn () => ['is_sample' => true, 'listing_key' => null, 'source' => null, 'status' => 'active']);
    }
}
