<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegionalMarket extends Model
{
    protected $fillable = [
        'city',
        'state',
        'state_code',
        'location_label',
        'region_name',
        'service_radius_miles',
        'total_businesses',
        'opportunities_count',
        'population_growth_percent',
        'housing_permits_growth_percent',
        'industry_growth_percent',
        'business_activity_growth_percent',
        'sba_lending_growth_percent',
        'avg_asking_price',
    ];

    protected function casts(): array
    {
        return [
            'service_radius_miles' => 'integer',
            'total_businesses' => 'integer',
            'opportunities_count' => 'integer',
            'population_growth_percent' => 'float',
            'housing_permits_growth_percent' => 'float',
            'industry_growth_percent' => 'float',
            'business_activity_growth_percent' => 'float',
            'sba_lending_growth_percent' => 'float',
            'avg_asking_price' => 'integer',
        ];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function toMarketInsightsArray(): array
    {
        return [
            'city' => $this->location_label,
            'population_growth' => ($this->population_growth_percent >= 0 ? '+' : '') . number_format($this->population_growth_percent, 1) . '%',
            'housing_permits' => ($this->housing_permits_growth_percent >= 0 ? '+' : '') . number_format($this->housing_permits_growth_percent, 1) . '%',
            'industry_growth' => ($this->industry_growth_percent >= 0 ? '+' : '') . number_format($this->industry_growth_percent, 1) . '%',
            'business_activity' => ($this->business_activity_growth_percent >= 0 ? '+' : '') . number_format($this->business_activity_growth_percent, 1) . '%',
        ];
    }
}
