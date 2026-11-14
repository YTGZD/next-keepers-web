<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistItem extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'tag',
        'tag_type',
        'tag_icon',
        'category',
        'added_time_label',
        'fit_score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fit_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function toWatchlistArray(): array
    {
        $biz = $this->business;

        return [
            'id' => $biz ? $biz->id : $this->business_id,
            'watchlist_id' => $this->id,
            'name' => $biz ? $biz->name : 'Saved Business',
            'location' => $biz ? $biz->location : 'New York, NY',
            'industry' => $biz ? $biz->industry : 'Services',
            'tag' => $this->tag,
            'tag_type' => $this->tag_type,
            'tag_icon' => $this->tag_icon,
            'description' => $biz ? $biz->description : '',
            'revenue' => $biz ? $biz->revenue_label : '$1.0M',
            'sde' => $biz ? $biz->sde_label : '$250K',
            'asking_price' => $biz ? $biz->asking_price_label : '$1.2M',
            'fit_score' => $this->fit_score ?: ($biz ? $biz->fit_score : 88),
            'added_time' => $this->added_time_label ?: 'Added recently',
            'image' => $biz ? $biz->watchlist_image : 'images/watchlist/thumb-hvac.png',
            'category' => $this->category,
        ];
    }
}
