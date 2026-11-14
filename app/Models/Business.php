<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'tagline',
        'description',
        'overview_description',
        'industry_id',
        'industry',
        'category',
        'regional_market_id',
        'city',
        'state',
        'location',
        'service_area',
        'established_year',
        'employees',
        'annual_growth_label',
        'annual_revenue',
        'revenue_label',
        'revenue_growth_percent',
        'sde',
        'sde_label',
        'sde_growth_percent',
        'asking_price',
        'asking_price_label',
        'sde_multiple',
        'profit_margin_percent',
        'margin_label',
        'is_profitable',
        'is_owner_operated',
        'is_absentee_owner',
        'is_growing_market',
        'is_verified',
        'is_top_match',
        'status',
        'discover_tab',
        'fit_score',
        'hero_image',
        'dashboard_image',
        'watchlist_image',
        'buybox_image',
        'facility_image',
        'tags',
        'overview_checklist',
        'why_this_business',
        'criteria_met',
        'fit_criteria',
        'listed_time_ago',
        'listed_at',
    ];

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
        ];
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
            return '$' . rtrim(rtrim(number_format($amount / 1_000_000_000, 1), '0'), '.') . 'B';
        }
        if ($amount >= 1_000_000) {
            return '$' . rtrim(rtrim(number_format($amount / 1_000_000, 1), '0'), '.') . 'M';
        }
        if ($amount >= 1_000) {
            return '$' . round($amount / 1_000) . 'K';
        }

        return '$' . number_format($amount);
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
            'revenue_growth' => '+' . rtrim(rtrim(number_format($this->revenue_growth_percent, 1), '0'), '.') . '%',
            'sde' => $this->sde_label,
            'sde_growth' => '+' . rtrim(rtrim(number_format($this->sde_growth_percent, 1), '0'), '.') . '%',
            'asking_price' => $this->asking_price_label,
            'sde_multiple' => number_format($this->sde_multiple, 1) . 'x',
            'employees' => $this->employees,
            'founded' => (string) $this->established_year,
            'service_area' => $this->service_area,
            'annual_growth' => $this->annual_growth_label,
            'metrics' => [
                ['label' => 'Annual Revenue', 'value' => $this->revenue_label, 'change' => '+' . round($this->revenue_growth_percent) . '%', 'icon' => 'chart', 'color' => 'emerald'],
                ['label' => 'SDE', 'value' => $this->sde_label, 'change' => '+' . round($this->sde_growth_percent) . '%', 'icon' => 'coins', 'color' => 'emerald'],
                ['label' => 'Asking Price', 'value' => $this->asking_price_label, 'change' => null, 'icon' => 'tag', 'color' => 'teal'],
                ['label' => 'SDE Multiple', 'value' => number_format($this->sde_multiple, 1) . 'x', 'change' => null, 'icon' => 'calc', 'color' => 'blue'],
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
                ['label' => 'Service Area', 'value' => $this->location . ' (' . $this->service_area . ')', 'icon' => 'pin'],
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
                'Strategic location in ' . $this->location,
                'Excellent acquisition fit (' . $score . '/100)',
            ],
        ];
    }
}
