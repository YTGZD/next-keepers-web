<?php

namespace App\Services\Listings;

use App\Models\BuyBox;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The deal box fields: validation, normalising buyer input into buy box
 * attributes, and the plain array the deal box panel (and saved boxes) use.
 */
class DealBoxCriteria
{
    /** Real estate choices on the panel => the listing values each one keeps. */
    public const REAL_ESTATE = [
        'leased' => ['Leased', 'Lease only', 'Not Included'],
        'included' => ['Included', 'Owned', 'Real estate only'],
    ];

    /** Sources the cleaned workbook draws on (more appear as they are published). */
    public const SOURCES = ['BizBuySell', 'BizScout', 'Acquisition Network', 'ListBiz'];

    /** What "Reset to default" goes back to: no limits, hygiene switches on. */
    public const DEFAULTS = [
        'industries' => [],
        'states' => [],
        'counties' => [],
        'sources' => [],
        'max_price_ex_re' => null,
        'min_earnings' => null,
        'max_multiple' => null,
        'min_years_in_business' => null,
        'real_estate_pref' => null,
        'keyword' => null,
        'require_earnings' => false,
        'seller_financing_only' => false,
        'operating_only' => true,
        'hide_delisted' => true,
        'hide_out_of_county' => true,
    ];

    /** Older buy box fields the panel no longer shows; saving the panel clears them. */
    public const LEGACY = ['min_asking_price', 'max_asking_price', 'min_revenue', 'profitable_only'];

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        $money = ['nullable', 'integer', 'min:0', 'max:10000000000'];

        return [
            'industries' => ['nullable', 'array'],
            'industries.*' => ['string', Rule::in(BuyBoxMatcher::INDUSTRIES)],
            'states' => ['nullable', 'array', 'max:60'],
            'states.*' => ['string', 'size:2', 'alpha'],
            'counties' => ['nullable'],
            'sources' => ['nullable', 'array', 'max:20'],
            'sources.*' => ['string', 'max:40'],
            'max_price_ex_re' => $money,
            'min_earnings' => $money,
            'max_multiple' => ['nullable', 'numeric', 'min:0.1', 'max:99'],
            'min_years_in_business' => ['nullable', 'integer', 'min:0', 'max:200'],
            'real_estate_pref' => ['nullable', Rule::in(array_keys(self::REAL_ESTATE))],
            'keyword' => ['nullable', 'string', 'max:120'],
            'require_earnings' => ['nullable', 'boolean'],
            'seller_financing_only' => ['nullable', 'boolean'],
            'operating_only' => ['nullable', 'boolean'],
            'hide_delisted' => ['nullable', 'boolean'],
            'hide_out_of_county' => ['nullable', 'boolean'],
            // Older fields, still accepted from scripts and the settings page.
            'min_asking_price' => $money,
            'max_asking_price' => $money,
            'min_revenue' => $money,
            'profitable_only' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Buy box attributes from validated input. Fields left out take their
     * default, so a saved box always says exactly what it filters on.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function attributes(array $input): array
    {
        $counties = $input['counties'] ?? [];
        $counties = collect(is_array($counties) ? $counties : explode(',', (string) $counties))
            ->map(fn ($c) => Str::title(trim((string) preg_replace('/\s+county$/i', '', trim((string) $c)))))
            ->filter()->unique()->take(60)->values()->all();
        $int = fn (string $key) => isset($input[$key]) && $input[$key] !== '' ? (int) $input[$key] : null;
        $bool = fn (string $key) => array_key_exists($key, $input) && $input[$key] !== null
            ? filter_var($input[$key], FILTER_VALIDATE_BOOLEAN)
            : self::DEFAULTS[$key] ?? false;
        $keyword = trim((string) ($input['keyword'] ?? ''));

        return [
            'industries' => array_values(array_unique($input['industries'] ?? [])),
            'states' => array_values(array_unique(array_map('strtoupper', $input['states'] ?? []))),
            'counties' => $counties,
            'sources' => array_values(array_unique($input['sources'] ?? [])),
            'max_price_ex_re' => $int('max_price_ex_re') ?: null,
            'min_earnings' => $int('min_earnings') ?: null,
            'max_multiple' => isset($input['max_multiple']) && $input['max_multiple'] !== '' ? round((float) $input['max_multiple'], 2) : null,
            'min_years_in_business' => $int('min_years_in_business') ?: null,
            'real_estate_pref' => $input['real_estate_pref'] ?? null ?: null,
            'keyword' => $keyword === '' ? null : $keyword,
            'require_earnings' => $bool('require_earnings'),
            'seller_financing_only' => $bool('seller_financing_only'),
            'operating_only' => $bool('operating_only'),
            'hide_delisted' => $bool('hide_delisted'),
            'hide_out_of_county' => $bool('hide_out_of_county'),
            'min_asking_price' => $int('min_asking_price') ?: null,
            'max_asking_price' => $int('max_asking_price') ?: null,
            'min_revenue' => $int('min_revenue') ?: null,
            'profitable_only' => $bool('profitable_only'),
        ];
    }

    /** The panel's fields for a buy box (or a saved box's stored criteria). @return array<string, mixed> */
    public static function toArray(BuyBox $box): array
    {
        $out = [];
        foreach (self::DEFAULTS as $key => $default) {
            $value = $box->{$key};
            $out[$key] = $value ?? $default;
        }

        return $out;
    }

    /** An unsaved buy box carrying these criteria, for previews and matching. */
    public static function box(array $attributes): BuyBox
    {
        return (new BuyBox)->forceFill($attributes);
    }
}
