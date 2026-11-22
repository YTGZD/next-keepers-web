<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingImport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'problems' => 'array',
            'dry_run' => 'boolean',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Listings this import created or last updated. */
    public function listings(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['done', 'failed'], true);
    }

    /** One line for the admin panel and the command output. */
    public function summary(): string
    {
        $verb = $this->dry_run ? 'would be ' : '';

        return sprintf(
            '%s rows read, %s matched the filters: %s %screated, %s %supdated, %s unchanged, %s skipped, %s failed.',
            number_format($this->rows_read),
            number_format($this->rows_matched),
            number_format($this->created_count), $verb,
            number_format($this->updated_count), $verb,
            number_format($this->unchanged_count),
            number_format($this->skipped_count),
            number_format($this->failed_count),
        );
    }
}
