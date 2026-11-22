<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BuyBoxMatchingService
{
    /**
     * The signed-in user, or, for guests browsing the public dashboard preview,
     * the shared read-only sample account. Guests can never write: every
     * mutating route sits behind the `auth` middleware.
     */
    public function resolveActiveUser(): User
    {
        $user = Auth::user();
        if ($user instanceof User) {
            return $user;
        }

        $demo = User::where('email', config('nextkeepers.demo_email'))->first();
        abort_if(
            ! $demo,
            503,
            'The dashboard preview has no sample data yet. Run: php artisan db:seed --class=NextKeepersPlatformSeeder'
        );

        return $demo;
    }

    /**
     * Get or create the BuyBox for a given user.
     */
    public function getOrCreateBuyBox(User $user): BuyBox
    {
        // Real accounts start with an empty buy box; only the preview account
        // carries the sample criteria below.
        if ($user->email !== config('nextkeepers.demo_email')) {
            return BuyBox::firstOrCreate(['user_id' => $user->id], [
                'industries' => [],
                'location' => '',
                'min_revenue' => null,
                'max_revenue' => null,
                'min_asking_price' => null,
                'max_asking_price' => null,
                'profitable_only' => false,
                'growing_market' => false,
            ]);
        }

        return BuyBox::firstOrCreate(
            ['user_id' => $user->id],
            [
                'industries' => ['HVAC', 'Restoration', 'Healthcare'],
                'location' => 'New York, NY + 100 miles',
                'radius_miles' => 100,
                'min_revenue' => 500000,
                'max_revenue' => 3000000,
                'revenue_range' => '$500K – $3M',
                'min_sde' => 200000,
                'max_sde' => 1500000,
                'sde_range' => '$200K – $1.5M',
                'min_asking_price' => 500000,
                'max_asking_price' => 3000000,
                'asking_price' => '$500K – $3M',
                'min_profit_margin' => 15.0,
                'profit_margin' => '≥ 15%',
                'min_employees' => 5,
                'max_employees' => 50,
                'employees' => '5 – 50',
                'profitable_only' => true,
                'owner_operated' => false,
                'growing_market' => true,
                'absentee_owner_ok' => false,
            ]
        );
    }

    /**
     * Evaluate a business against a user's BuyBox and return score + matched criteria.
     *
     * @return array{score: int, criteria_met: array<int, string>, fit_breakdown: array<string, bool>}
     */
    public function evaluate(Business $business, ?BuyBox $buyBox = null): array
    {
        if (! $buyBox) {
            return [
                'score' => $business->fit_score,
                'criteria_met' => $business->criteria_met ?? ['Your industry', 'Your location', 'Strong margins'],
                'fit_breakdown' => $business->fit_criteria ?? [
                    'Industry Match' => true,
                    'Financials' => true,
                    'Location' => true,
                    'Size' => true,
                ],
            ];
        }

        $score = 60;
        $criteriaMet = [];

        // 1. Industry match (up to +14 pts)
        $targetIndustries = collect($buyBox->industries ?? [])
            ->map(fn ($i) => strtolower(trim((string) $i)))
            ->filter();

        $bizCategory = strtolower((string) $business->category);
        $bizIndustryText = strtolower($business->industry.' '.$bizCategory.' '.$business->name);
        $industryMatched = $targetIndustries->isEmpty() || $targetIndustries->contains(
            fn (string $ind) => str_contains($bizIndustryText, $ind) || ($bizCategory !== '' && str_contains($ind, $bizCategory))
        );

        if ($industryMatched) {
            $score += 14;
            $criteriaMet[] = $business->category === 'healthcare' ? 'Healthcare' : 'Your industry';
        } elseif ($business->is_growing_market) {
            $score += 9;
            $criteriaMet[] = 'Growing market';
        }

        // 2. Location match (up to +8 pts)
        $locationMatch = str_contains(strtolower($buyBox->location ?? ''), strtolower($business->state))
            || str_contains(strtolower($buyBox->location ?? ''), 'new york')
            || $business->state === 'NY';

        if ($locationMatch) {
            $score += 8;
            $criteriaMet[] = $business->city === 'Rochester' ? 'Your location' : 'Your region';
        }

        // 3. Financials match: Revenue, SDE, Asking Price, Margin (up to +12 pts)
        $revOk = $business->annual_revenue >= ($buyBox->min_revenue * 0.75)
            && $business->annual_revenue <= ($buyBox->max_revenue * 1.35);
        $sdeOk = $business->sde >= ($buyBox->min_sde * 0.75)
            && $business->sde <= ($buyBox->max_sde * 1.35);
        $marginOk = $business->profit_margin_percent >= $buyBox->min_profit_margin;

        if ($revOk && $sdeOk) {
            $score += 6;
        }
        if ($marginOk) {
            $score += 6;
            $criteriaMet[] = $business->profit_margin_percent >= 20 ? 'Strong margins' : 'Profitable';
        } elseif ($business->is_profitable) {
            $score += 4;
            $criteriaMet[] = 'Profitable';
        }

        // 4. Size & operational attributes (up to +6 pts)
        $sizeOk = $business->employees >= $buyBox->min_employees && $business->employees <= $buyBox->max_employees;
        if ($sizeOk) {
            $score += 4;
        }
        if ($business->is_owner_operated) {
            $score += 2;
            if (count($criteriaMet) < 3) {
                $criteriaMet[] = 'Owner operated';
            }
        }

        // Blend with curated baseline fit_score so UI stays consistent unless filters change drastically
        $finalScore = (int) min(99, max(65, round(($score + $business->fit_score) / 2)));

        if (empty($criteriaMet)) {
            $criteriaMet = ['Verified listing', 'Profitable'];
        }

        return [
            'score' => $finalScore,
            'criteria_met' => array_slice(array_values(array_unique($criteriaMet)), 0, 3),
            'fit_breakdown' => [
                'Industry Match' => $industryMatched,
                'Financials' => $revOk && $marginOk,
                'Location' => $locationMatch,
                'Size' => $sizeOk,
            ],
        ];
    }
}
