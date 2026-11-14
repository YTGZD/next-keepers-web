<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineDeal extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'stage',
        'nda_signed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'nda_signed' => 'boolean',
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
}
