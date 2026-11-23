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

    /**
     * Asking price without real estate, as SQL (see ListingMetrics::priceExRealEstate):
     * null when the price includes real estate of unknown value.
     */
    private const PRICE_EX_RE_SQL = "case when real_estate_in_price = 'Yes' then "
        .'case when real_estate_value > 0 and asking_price > real_estate_value then asking_price - real_estate_value end '
        .'else asking_price end';

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
        if ($box->max_price_ex_re) {
            $c['price_ex_re'] = 'Up to '.$money($box->max_price_ex_re).' without real estate';
        }
        if ($box->min_revenue) {
            $c['revenue'] = 'Revenue from '.$money($box->min_revenue);
        }
        if ($box->min_earnings) {
            $c['earnings'] = 'SDE / cash flow from '.$money($box->min_earnings);
        }
        if ($box->max_multiple) {
            $c['multiple'] = 'Price / SDE up to '.rtrim(rtrim(number_format($box->max_multiple, 2), '0'), '.').'x';
        }
        if ($box->min_years_in_business) {
            $c['years'] = 'At least '.$box->min_years_in_business.' '.Str::plural('year', $box->min_years_in_business).' in business';
        }
        if (! empty($box->sources)) {
            $c['source'] = 'From '.implode(', ', $box->sources);
        }
        if ($box->real_estate_pref) {
            $c['real_estate'] = $box->real_estate_pref === 'leased' ? 'Leased, no real estate' : 'Real estate included or owned';
        }
        if ($box->keyword) {
            $c['keyword'] = '"'.$box->keyword.'"';
        }
        if ($box->require_earnings) {
            $c['earnings_disclosed'] = 'Discloses earnings';
        }
        if ($box->seller_financing_only) {
            $c['seller_financing'] = 'Seller financing offered';
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

        $priceExRe = self::PRICE_EX_RE_SQL;
        if ($box->max_price_ex_re) {
            $query->where(fn (Builder $q) => $q->whereRaw("({$priceExRe}) is null")->orWhereRaw("({$priceExRe}) <= ?", [$box->max_price_ex_re]));
        }
        if ($box->max_multiple) {
            // Mirrors ListingMetrics::priceExRealEstateToEarnings(): only operating
            // businesses with a positive price and earnings have a multiple.
            $query->whereRaw(
                "not (coalesce(earnings, 0) > 0 and coalesce(({$priceExRe}), 0) > 0 and coalesce(operating_status, 'Yes') = 'Yes' and ({$priceExRe}) > ? * earnings)",
                [$box->max_multiple]
            );
        }
        if ($box->min_years_in_business) {
            $latest = (int) now()->year - $box->min_years_in_business;
            $query->where(fn (Builder $q) => $q->whereNull('established_year')->orWhere('established_year', '<=', $latest));
        }
        if (! empty($box->sources)) {
            $query->whereIn('source', $box->sources);
        }
        if ($box->real_estate_pref && isset(DealBoxCriteria::REAL_ESTATE[$box->real_estate_pref])) {
            $query->where(fn (Builder $q) => $q->whereNull('real_estate')->orWhere('real_estate', 'Unknown')
                ->orWhereIn('real_estate', DealBoxCriteria::REAL_ESTATE[$box->real_estate_pref]));
        }
        if ($box->keyword) {
            $like = '%'.$box->keyword.'%';
            $query->where(fn (Builder $q) => $q->where('name', 'like', $like)
                ->orWhere('sub_industry', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('county', 'like', $like)
                ->orWhere('location', 'like', $like)
                ->orWhere('brand', 'like', $like)
                ->orWhere('description', 'like', $like));
        }
        if ($box->require_earnings) {
            $query->whereNotNull('earnings');
        }
        if ($box->seller_financing_only) {
            $query->whereIn('seller_financing', ['Yes', 'Possible']);
        }
        if ($box->operating_only) {
            $query->where(fn (Builder $q) => $q->whereNull('operating_status')->orWhere('operating_status', '!=', 'No'));
        }
        if ($box->hide_delisted) {
            $this->withoutFlag($query, 'DELISTED');
        }
        if ($box->hide_out_of_county) {
            $this->withoutFlag($query, 'OUT_OF_COUNTY');
        }

        return $query;
    }

    /** Leave out listings whose cleaning QA flags include $flag. */
    private function withoutFlag(Builder $query, string $flag): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('qa_flags')->orWhere('qa_flags', 'not like', '%"'.$flag.'"%'));
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

        $metrics = $listing->metrics();
        $priceExRe = $metrics->priceExRealEstate();
        $check('price_ex_re', 'Price without real estate', $priceExRe === null ? null : $priceExRe <= $box->max_price_ex_re);
        $multiple = $metrics->priceExRealEstateToEarnings();
        $check('multiple', 'Multiple', $multiple === null ? null : $multiple <= $box->max_multiple);
        $check('years', 'Years in business', $listing->established_year === null ? null
            : (int) now()->year - $listing->established_year >= $box->min_years_in_business);
        $check('source', 'Source', in_array($listing->source, $box->sources ?? [], true));
        $check('real_estate', 'Real estate', in_array($listing->real_estate, [null, 'Unknown'], true) ? null
            : in_array($listing->real_estate, DealBoxCriteria::REAL_ESTATE[$box->real_estate_pref] ?? [], true));
        // Hard filters: a listing shown already meets them.
        $check('keyword', 'Keyword', true);
        $check('earnings_disclosed', 'Earnings disclosed', $listing->earnings !== null);
        $check('seller_financing', 'Seller financing', in_array($listing->seller_financing, ['Yes', 'Possible'], true));

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
