<?php

namespace App\Models;

use App\Services\Listings\BuyBoxMatcher;
use App\Support\ListingBenchmark;
use App\Support\ListingMetrics;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    /** Statuses a listing can be shown to buyers in. Drafts and archived rows never are. */
    public const VISIBLE_STATUSES = ['active', 'under_loi', 'sold'];

    public const STATUSES = ['draft', 'active', 'under_loi', 'sold', 'archived'];

    /**
     * Neutral values for real listings. The original table was built for the
     * fictional preview, so its column defaults are sample claims ("8% growth",
     * "owner-operated", a fit score of 88); a real listing starts without them.
     */
    public const REAL_LISTING_DEFAULTS = [
        'service_area' => 'Not disclosed',
        'annual_growth_label' => 'Not disclosed',
        'revenue_growth_percent' => 0,
        'sde_growth_percent' => 0,
        'is_owner_operated' => false,
        'is_absentee_owner' => false,
        'is_growing_market' => false,
        'is_top_match' => false,
        'discover_tab' => 'new_listings',
        'fit_score' => 0,
    ];

    /** Written only by seeders, the importer and the admin panel, never from buyer input. */
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'established_year' => 'integer',
            'employees' => 'integer',
            'annual_revenue' => 'integer',
            'revenue_growth_percent' => 'float',
            'sde' => 'integer',
            'sde_growth_percent' => 'float',
            'asking_price' => 'integer',
            'sde_multiple' => 'float',
            'profit_margin_percent' => 'float',
            'is_profitable' => 'boolean',
            'is_owner_operated' => 'boolean',
            'is_absentee_owner' => 'boolean',
            'is_growing_market' => 'boolean',
            'is_verified' => 'boolean',
            'is_top_match' => 'boolean',
            'fit_score' => 'integer',
            'tags' => 'array',
            'overview_checklist' => 'array',
            'why_this_business' => 'array',
            'criteria_met' => 'array',
            'fit_criteria' => 'array',
            'listed_at' => 'datetime',
            'is_sample' => 'boolean',
            'scraped_on' => 'date',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'published_at' => 'datetime',
            'price_disclosed' => 'boolean',
            'cash_flow' => 'integer',
            'ebitda' => 'integer',
            'earnings' => 'integer',
            'real_estate_value' => 'integer',
            'ffe_value' => 'integer',
            'inventory_value' => 'integer',
            'monthly_rent' => 'integer',
            'employees_full_time' => 'integer',
            'employees_part_time' => 'integer',
            'contractors' => 'integer',
            'square_feet' => 'integer',
            'lot_acres' => 'float',
            'seller_financing_percent' => 'float',
            'sba_mentioned' => 'boolean',
            'qa_flags' => 'array',
            'needs_review' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Real listings never keep the sample defaults ("$1.8M", "2h ago", ...):
        // display labels always follow the stored numbers.
        static::creating(function (Business $business): void {
            if (! $business->is_sample) {
                foreach (self::REAL_LISTING_DEFAULTS as $attribute => $value) {
                    if (! array_key_exists($attribute, $business->getAttributes())) {
                        $business->setAttribute($attribute, $value);
                    }
                }
            }
        });

        static::saving(function (Business $business): void {
            if (! $business->is_sample) {
                $business->syncDisplayLabels();
            }
        });
    }

    public function syncDisplayLabels(): void
    {
        $money = fn (?int $amount): string => $amount === null ? 'Not disclosed' : self::formatCompactCurrency($amount);

        $this->asking_price_label = $money($this->asking_price);
        $this->revenue_label = $money($this->annual_revenue);
        $this->sde_label = $money($this->earnings ?? $this->sde);
        $margin = $this->metrics()->earningsMargin();
        $this->margin_label = $margin === null ? 'Not disclosed' : round($margin * 100).'%';
        // Stored copies of derived figures, for the columns the older views read.
        $multiple = $this->metrics()->priceToEarnings();
        $this->sde_multiple = $multiple === null ? 0 : min(round($multiple, 2), 99.99);
        $this->profit_margin_percent = $margin === null ? 0 : max(min(round($margin * 100, 2), 999.99), -999.99);
        $this->is_profitable = $this->earnings !== null && $this->earnings > 0;
        $this->location = match (true) {
            filled($this->city) && filled($this->state) => "{$this->city}, {$this->state}",
            filled($this->county) && filled($this->state) => "{$this->county} County, {$this->state}",
            filled($this->state) => $this->state,
            default => 'Location not disclosed',
        };
        $this->listed_time_ago = $this->scraped_on ? 'Listed '.$this->scraped_on->format('M j, Y') : 'Date not disclosed';
        $this->is_verified = (bool) $this->is_verified;
    }

    /** Listings a buyer may see (published, not draft or archived). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', self::VISIBLE_STATUSES);
    }

    /** Fictional listings used by the public preview. */
    public function scopeSample(Builder $query): Builder
    {
        return $query->where('is_sample', true);
    }

    /** Listings that came from a real source (imported or entered by an admin). */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_sample', false);
    }

    /** Multiples and ratios derived from the listed figures. */
    public function metrics(): ListingMetrics
    {
        return ListingMetrics::for($this);
    }

    public function industryRelation(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function regionalMarket(): BelongsTo
    {
        return $this->belongsTo(RegionalMarket::class, 'regional_market_id');
    }

    public function financials(): HasMany
    {
        return $this->hasMany(BusinessFinancial::class)->orderBy('fiscal_year');
    }

    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }

    public function pipelineDeals(): HasMany
    {
        return $this->hasMany(PipelineDeal::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public static function formatCompactCurrency(int|float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return '$'.rtrim(rtrim(number_format($amount / 1_000_000_000, 1), '0'), '.').'B';
        }
        if ($amount >= 1_000_000) {
            return '$'.rtrim(rtrim(number_format($amount / 1_000_000, 1), '0'), '.').'M';
        }
        if ($amount >= 1_000) {
            return '$'.round($amount / 1_000).'K';
        }

        return '$'.number_format($amount);
    }

    /**
     * A real listing as a card (dashboard, discover, buy box, watchlist).
     * Only figures the listing discloses; anything missing reads "Not disclosed".
     *
     * @param  array{score: int|null, met: list<string>, unknown: list<string>, total: int}|null  $match
     */
    public function toRealCardArray(?array $match = null): array
    {
        $multiple = $this->metrics()->priceToEarnings();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'location_note' => $this->locationNote(),
            'industry' => $this->industry,
            'sub_industry' => $this->sub_industry,
            'asking' => $this->asking_price_label,
            'revenue' => $this->revenue_label,
            'earnings' => $this->sde_label,
            'earnings_label' => $this->earningsLabel(),
            'multiple' => $multiple === null ? 'Not disclosed' : number_format($multiple, 1).'x',
            'margin' => $this->margin_label,
            'excerpt' => $this->description ? Str::limit(strip_tags($this->description), 180) : null,
            'source' => $this->source,
            'listed' => $this->scraped_on?->format('M j, Y'),
            'score' => $match['score'] ?? null,
            'met' => $match['met'] ?? [],
            'unknown_note' => $match ? BuyBoxMatcher::unknownNote($match['unknown']) : null,
            'benchmark' => ListingBenchmark::for($this),
        ];
    }

    /** "Earnings (SDE)" style label from the listing's earnings basis. */
    public function earningsLabel(): string
    {
        return match (true) {
            $this->earnings_basis === null, $this->earnings_basis === 'None' => 'Earnings',
            str_contains($this->earnings_basis, 'EBITDA') => 'EBITDA',
            str_contains($this->earnings_basis, 'Cash Flow') => 'Cash flow',
            default => $this->earnings_basis,
        };
    }

    /** How precise the location is, when it is less than a city. */
    public function locationNote(): ?string
    {
        // Describe what is shown, which is the most precise part the listing gives.
        return match (true) {
            filled($this->city) => null,
            filled($this->county) => 'County-level location',
            filled($this->state) => 'Only the state is disclosed',
            default => null,
        };
    }

    public function toDashboardOpportunityArray(?int $calculatedFitScore = null): array
    {
        return [
            'id' => $this->id,
            'name' => $this->short_name ?: $this->name,
            'location' => $this->location,
            'revenue' => $this->revenue_label,
            'sde' => $this->sde_label,
            'asking' => $this->asking_price_label,
            'fit_score' => $calculatedFitScore ?? $this->fit_score,
            'category' => $this->category,
            'image' => $this->dashboard_image,
        ];
    }

    public function toDiscoverArray(?int $calculatedFitScore = null): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'industry' => $this->industry,
            'location' => $this->location,
            'description' => $this->description,
            'tags' => $this->tags ?? ['Recurring Revenue', 'Established Brand', 'Strong Fit'],
            'revenue' => $this->revenue_label,
            'sde' => $this->sde_label,
            'asking_price' => $this->asking_price_label,
            'employees' => $this->employees,
            'fit_score' => $calculatedFitScore ?? $this->fit_score,
            'verified' => (bool) $this->is_verified,
            'image' => $this->watchlist_image,
            'category' => $this->discover_tab,
        ];
    }

    public function toBuyBoxMatchArray(?int $calculatedFitScore = null, ?array $matchedCriteria = null): array
    {
        $score = $calculatedFitScore ?? $this->fit_score;

        return [
            'id' => $this->id,
            'name' => $this->short_name ?: $this->name,
            'location' => $this->location,
            'established' => (string) $this->established_year,
            'description' => $this->description,
            'revenue' => $this->revenue_label,
            'sde' => $this->sde_label,
            'asking_price' => $this->asking_price_label,
            'margin' => $this->margin_label,
            'fit_score' => $score,
            'top_match' => $this->is_top_match || $score >= 92,
            'criteria_met' => $matchedCriteria ?? $this->criteria_met ?? ['Your industry', 'Your location', 'Strong margins'],
            'image' => $this->buybox_image,
            'time_ago' => $this->listed_time_ago ?? '2h ago',
        ];
    }

    public function toDetailArray(?int $calculatedFitScore = null): array
    {
        $score = $calculatedFitScore ?? $this->fit_score;
        $market = $this->regionalMarket;
        $financialRows = $this->financials->map(fn (BusinessFinancial $fin) => [
            'year' => (string) $fin->fiscal_year,
            'revenue' => round($fin->revenue / 1_000_000, 2),
            'revenue_label' => $fin->revenue_label,
            'sde' => round($fin->sde / 1_000_000, 2),
            'sde_label' => $fin->sde_label,
            'revenue_bar_percent' => $fin->revenue_bar_percent,
            'sde_bar_percent' => $fin->sde_bar_percent,
        ])->values()->all();

        if (empty($financialRows)) {
            $financialRows = [
                ['year' => '2022', 'revenue' => 1.5, 'revenue_label' => '$1.5M', 'sde' => 0.35, 'sde_label' => '$350K', 'revenue_bar_percent' => 75, 'sde_bar_percent' => 25],
                ['year' => '2023', 'revenue' => 1.6, 'revenue_label' => '$1.6M', 'sde' => 0.37, 'sde_label' => '$370K', 'revenue_bar_percent' => 80, 'sde_bar_percent' => 27],
                ['year' => '2024', 'revenue' => 1.8, 'revenue_label' => '$1.8M', 'sde' => 0.42, 'sde_label' => '$420K', 'revenue_bar_percent' => 95, 'sde_bar_percent' => 32],
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->short_name ?: $this->name,
            'full_name' => $this->name,
            'location' => $this->location,
            'industry' => $this->industry,
            'tagline' => $this->tagline ?? $this->description,
            'tags' => $this->tags ?? ['Profitable Business', 'Owner-Operated', 'Growth Opportunity'],
            'hero_image' => $this->hero_image,
            'revenue' => $this->revenue_label,
            'revenue_growth' => '+'.rtrim(rtrim(number_format($this->revenue_growth_percent, 1), '0'), '.').'%',
            'sde' => $this->sde_label,
            'sde_growth' => '+'.rtrim(rtrim(number_format($this->sde_growth_percent, 1), '0'), '.').'%',
            'asking_price' => $this->asking_price_label,
            'sde_multiple' => number_format($this->sde_multiple, 1).'x',
            'employees' => $this->employees,
            'founded' => (string) $this->established_year,
            'service_area' => $this->service_area,
            'annual_growth' => $this->annual_growth_label,
            'metrics' => [
                ['label' => 'Annual Revenue', 'value' => $this->revenue_label, 'change' => '+'.round($this->revenue_growth_percent).'%', 'icon' => 'chart', 'color' => 'emerald'],
                ['label' => 'SDE', 'value' => $this->sde_label, 'change' => '+'.round($this->sde_growth_percent).'%', 'icon' => 'coins', 'color' => 'emerald'],
                ['label' => 'Asking Price', 'value' => $this->asking_price_label, 'change' => null, 'icon' => 'tag', 'color' => 'teal'],
                ['label' => 'SDE Multiple', 'value' => number_format($this->sde_multiple, 1).'x', 'change' => null, 'icon' => 'calc', 'color' => 'blue'],
                ['label' => 'Employees', 'value' => (string) $this->employees, 'change' => null, 'icon' => 'users', 'color' => 'emerald'],
            ],
            'overview' => [
                'description' => $this->overview_description ?: $this->description,
                'checklist' => $this->overview_checklist ?? [
                    'Recurring revenue model',
                    'Strong local brand',
                    'Growth opportunities',
                ],
                'facility_image' => $this->facility_image ?: 'images/business/facility-video.png',
            ],
            'highlights' => [
                ['label' => 'Founded', 'value' => (string) $this->established_year, 'icon' => 'calendar'],
                ['label' => 'Employees', 'value' => (string) $this->employees, 'icon' => 'users'],
                ['label' => 'Service Area', 'value' => $this->location.' ('.$this->service_area.')', 'icon' => 'pin'],
                ['label' => 'Annual Growth', 'value' => $this->annual_growth_label, 'icon' => 'growth'],
            ],
            'financials' => $financialRows,
            'market_insights' => $market ? $market->toMarketInsightsArray() : [
                'city' => $this->location,
                'population_growth' => '+2.1%',
                'housing_permits' => '+12.3%',
                'industry_growth' => '+3.5%',
                'business_activity' => '+6.8%',
            ],
            'fit_score' => [
                'score' => $score,
                'total' => 100,
                'criteria' => $this->fit_criteria ?? [
                    'Industry Match' => true,
                    'Financials' => true,
                    'Location' => true,
                    'Size' => true,
                ],
            ],
            'why_this_business' => $this->why_this_business ?? [
                'Growing industry with stable demand',
                'Strong financial performance',
                'Strategic location in '.$this->location,
                'Excellent acquisition fit ('.$score.'/100)',
            ],
        ];
    }
}
