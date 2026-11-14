<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyBox extends Model
{
    protected $fillable = [
        'user_id',
        'industries',
        'location',
        'radius_miles',
        'min_revenue',
        'max_revenue',
        'revenue_range',
        'min_sde',
        'max_sde',
        'sde_range',
        'min_asking_price',
        'max_asking_price',
        'asking_price',
        'min_profit_margin',
        'profit_margin',
        'min_employees',
        'max_employees',
        'employees',
        'profitable_only',
        'owner_operated',
        'growing_market',
        'absentee_owner_ok',
    ];

    protected function casts(): array
    {
        return [
            'industries' => 'array',
            'radius_miles' => 'integer',
            'min_revenue' => 'integer',
            'max_revenue' => 'integer',
            'min_sde' => 'integer',
            'max_sde' => 'integer',
            'min_asking_price' => 'integer',
            'max_asking_price' => 'integer',
            'min_profit_margin' => 'float',
            'min_employees' => 'integer',
            'max_employees' => 'integer',
            'profitable_only' => 'boolean',
            'owner_operated' => 'boolean',
            'growing_market' => 'boolean',
            'absentee_owner_ok' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toCriteriaArray(): array
    {
        return [
            'industries' => $this->industries ?? ['HVAC', 'Restoration', 'Healthcare'],
            'location' => $this->location ?? 'New York, NY + 100 miles',
            'revenue_range' => $this->revenue_range ?? '$500K – $3M',
            'sde_range' => $this->sde_range ?? '$200K – $1.5M',
            'asking_price' => $this->asking_price ?? '$500K – $3M',
            'profit_margin' => $this->profit_margin ?? '≥ 15%',
            'employees' => $this->employees ?? '5 – 50',
            'profitable_only' => (bool) $this->profitable_only,
            'owner_operated' => (bool) $this->owner_operated,
            'growing_market' => (bool) $this->growing_market,
            'absentee_owner_ok' => (bool) $this->absentee_owner_ok,
        ];
    }
}
