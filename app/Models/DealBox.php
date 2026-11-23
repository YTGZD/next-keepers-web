<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A named, saved set of deal box criteria. Private to its account. */
class DealBox extends Model
{
    protected $fillable = ['name', 'criteria'];

    protected function casts(): array
    {
        return ['criteria' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
