<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessFinancial extends Model
{
    protected $fillable = [
        'business_id',
        'fiscal_year',
        'revenue',
        'revenue_label',
        'sde',
        'sde_label',
        'revenue_bar_percent',
        'sde_bar_percent',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'revenue' => 'integer',
            'sde' => 'integer',
            'revenue_bar_percent' => 'integer',
            'sde_bar_percent' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
