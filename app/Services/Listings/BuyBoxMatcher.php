<?php

namespace App\Services\Listings;

use App\Models\Business;
use App\Models\BuyBox;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Matches real listings against a buyer's buy box.
 *
 * A listing is a match when nothing it discloses contradicts the buy box.
 * Figures a listing doesn't disclose are not counted against it, but they
 * don't count as met either: the fit score is the share of the buyer's
 * criteria the listing is known to meet, and the card says what is unknown.
 */
class BuyBoxMatcher
{
    /** Industries as they appear in the cleaned listings workbook. */
    public const INDUSTRIES = [
        'Healthcare', 'Home & Property Services', 'Food & Beverage', 'Retail', 'Professional Services',
        'Personal Services', 'Automotive', 'Transportation & Logistics', 'Manufacturing & Distribution',
        'Recreation & Entertainment', 'Education & Childcare',
    ];

    public function hasCriteria(BuyBox $box): bool
    {
        return $this->criteria($box) !== [];
    }

    /** The criteria a buyer has set, as label => description. @return array<string, string> */
    public function criteria(BuyBox $box): array
    {
        $money = fn (?int $v) => Business::formatCompactCurrency((int) $v);
        $c = [];
        if (! empty($box->industries)) {
            $c['industry'] = implode(', ', $box->industries);
        }
        if (! empty($box->counties)) {
            $c['location'] = implode(', ', $box->counties).' ('.implode(', ', $box->states ?? []).')';
        } elseif (! empty($box->states)) {
            $c['location'] = implode(', ', $box->states);
        }
        if ($box->min_asking_price || $box->max_asking_price) {
            $c['price'] = match (true) {
                $box->min_asking_price && $box->max_asking_price => $money($box->min_asking_price).' to '.$money($box->max_asking_price),
                (bool) $box->max_asking_price => 'Up to '.$money($box->max_asking_price),
                default => 'From '.$money($box->min_asking_price),
            };
        }
        if ($box->min_revenue) {
            $c['revenue'] = 'Revenue from '.$money($box->min_revenue);
        }
        if ($box->min_earnings) {
            $c['earnings'] = 'Earnings from '.$money($box->min_earnings);
        }
        if ($box->profitable_only) {
            $c['profitable'] = 'Profitable only';
        }

        return $c;
    }

    /** Narrow a listings query to those the buy box doesn't rule out. */
    public function apply(Builder $query, BuyBox $box): Builder
    {
        if (! empty($box->industries)) {
            $query->whereIn('industry', $box->industries);
        }
        if (! empty($box->states)) {
            $query->whereIn('state', $box->states);
        }
        if (! empty($box->counties)) {
            $query->whereIn('county', $box->counties);
        }
        if ($box->min_asking_price) {
            $query->where(fn (Builder $q) => $q->whereNull('asking_price')->orWhere('asking_price', '>=', $box->min_asking_price));
        }
        if ($box->max_asking_price) {
            $query->where(fn (Builder $q) => $q->whereNull('asking_price')->orWhere('asking_price', '<=', $box->max_asking_price));
        }
        if ($box->min_revenue) {
            $query->where(fn (Builder $q) => $q->whereNull('annual_revenue')->orWhere('annual_revenue', '>=', $box->min_revenue));
        }
        if ($box->min_earnings) {
            $query->where(fn (Builder $q) => $q->whereNull('earnings')->orWhere('earnings', '>=', $box->min_earnings));
        }
        if ($box->profitable_only) {
            $query->where('earnings', '>', 0);
        }

        return $query;
    }

    /**
     * @return array{score: int|null, met: list<string>, unknown: list<string>, total: int}
     */
    public function evaluate(Business $listing, BuyBox $box): array
    {
        $criteria = $this->criteria($box);
        $met = [];
        $unknown = [];

        $check = function (string $key, string $label, ?bool $result) use ($criteria, &$met, &$unknown): void {
            if (! isset($criteria[$key])) {
                return;
            }
            if ($result === null) {
                $unknown[] = $label;
            } elseif ($result) {
                $met[] = $label;
            }
        };

        $check('industry', 'Industry', in_array($listing->industry, $box->industries ?? [], true));
        $check('location', 'Location', match (true) {
            ! empty($box->counties) => $listing->county === null ? null : in_array($listing->county, $box->counties, true),
            default => $listing->state === null ? null : in_array($listing->state, $box->states ?? [], true),
        });
        $check('price', 'Asking price', $listing->asking_price === null ? null
            : ($listing->asking_price >= (int) $box->min_asking_price
                && (! $box->max_asking_price || $listing->asking_price <= $box->max_asking_price)));
        $check('revenue', 'Revenue', $listing->annual_revenue === null ? null : $listing->annual_revenue >= $box->min_revenue);
        $check('earnings', 'Earnings', $listing->earnings === null ? null : $listing->earnings >= $box->min_earnings);
        $check('profitable', 'Profitable', $listing->earnings === null ? null : $listing->earnings > 0);

        $total = count($criteria);

        return [
            'score' => $total === 0 ? null : (int) round(100 * count($met) / $total),
            'met' => $met,
            'unknown' => $unknown,
            'total' => $total,
        ];
    }

    /** Short "not disclosed" note for a card, e.g. "Price and earnings not disclosed". */
    public static function unknownNote(array $unknown): ?string
    {
        if ($unknown === []) {
            return null;
        }

        return Str::ucfirst(Str::lower(collect($unknown)->join(', ', ' and '))).' not disclosed';
    }
}
