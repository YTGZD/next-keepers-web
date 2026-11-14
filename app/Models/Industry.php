<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category_key',
        'icon',
        'color',
        'market_share_percent',
        'annual_growth_percent',
        'avg_multiple',
        'is_top_industry',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'market_share_percent' => 'float',
            'annual_growth_percent' => 'float',
            'avg_multiple' => 'float',
            'is_top_industry' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
