<?php

namespace App\Services\Listings;

use App\Models\Business;
use App\Models\ListingImport;
use Illuminate\Database\Eloquent\Builder;

/**
 * Publishes many draft listings at once: everything in the current view of
 * the review queue, everything a selection covers (including "select all"
 * across pages), or everything one import brought in.
 *
 * Only real drafts are touched. Archived, already published and sample rows
 * are left alone, and listings flagged "Needs review" are skipped unless the
 * admin says to include them. Rows are updated by id in chunks, so 10,000
 * listings are a few dozen queries, not 10,000 model saves.
 */
class ListingPublisher
{
    public const CHUNK = 500;

    /** Narrow any listings query to the drafts a bulk publish may touch. */
    public static function drafts(Builder $query, bool $includeFlagged = true): Builder
    {
        $query = (clone $query)->where('businesses.is_sample', false)->where('businesses.status', 'draft');

        return $includeFlagged ? $query : $query->where(fn (Builder $q) => $q->where('businesses.needs_review', false)->orWhereNull('businesses.needs_review'));
    }

    /**
     * How many drafts the query covers, and how many of them are flagged.
     *
     * @return array{drafts: int, flagged: int}
     */
    public static function counts(Builder $query): array
    {
        $drafts = self::drafts($query);
        $base = (clone $drafts)->reorder()->toBase();

        return [
            'drafts' => (clone $base)->count(),
            'flagged' => (clone $base)->where('businesses.needs_review', true)->count(),
        ];
    }

    /** Publish the drafts the query covers. Returns how many were published. */
    public static function publish(Builder $query, bool $includeFlagged = false): int
    {
        $ids = self::drafts($query, $includeFlagged)->reorder()->toBase()
            ->select('businesses.id')->orderBy('businesses.id')->pluck('id');

        $published = 0;
        foreach ($ids->chunk(self::CHUNK) as $chunk) {
            $now = now();
            $rows = fn () => Business::whereIn('id', $chunk->all())->where('status', 'draft');
            // A listing that was published before keeps its first publish date.
            $published += $rows()->whereNotNull('published_at')
                ->update(['status' => 'active', 'needs_review' => false, 'updated_at' => $now]);
            $published += $rows()
                ->update(['status' => 'active', 'needs_review' => false, 'published_at' => $now, 'updated_at' => $now]);
        }

        return $published;
    }

    /** The drafts an import created or last refreshed. */
    public static function forImport(ListingImport $import): Builder
    {
        return Business::query()->where('listing_import_id', $import->id);
    }

    /** One sentence for a confirmation dialog. */
    public static function describe(array $counts, string $where): string
    {
        if ($counts['drafts'] === 0) {
            return "There are no drafts {$where}.";
        }

        $sentence = number_format($counts['drafts']).' '.($counts['drafts'] === 1 ? 'draft' : 'drafts')." {$where} will be published and become visible to buyers.";
        if ($counts['flagged'] > 0) {
            $sentence .= ' '.number_format($counts['flagged']).' of them '.($counts['flagged'] === 1 ? 'is' : 'are').' flagged "Needs review" and '.($counts['flagged'] === 1 ? 'is' : 'are').' skipped unless you include flagged listings below.';
        }

        return $sentence;
    }
}
