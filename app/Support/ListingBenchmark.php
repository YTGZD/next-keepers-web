<?php

namespace App\Support;

use App\Models\Business;

/**
 * A listing's multiples next to its industry's, with a short note comparing them.
 *
 * Mirrors the benchmark columns of the cleaned listings workbook (Clean Listings BX to CL):
 * - the listing's sub-industry picks a BizBuySell subsector from the "Subsector Map" sheet
 *   (resources/data/benchmarks/subsector_map.json),
 * - a per-listing override wins over that default: the row's own benchmark_subsector
 *   (set in the admin panel or by an import), else the workbook's "BBS Subsector (override)"
 *   column (resources/data/benchmarks/subsector_overrides.json, keyed by Listing Key),
 * - the listing's price / earnings (ex-real estate) is compared with the subsector's 2025
 *   cash-flow multiple, and 20% either side reads as below or above market ("Value Signal").
 *
 * BizBuySell multiples are on prices businesses actually sold for, nationally, so a listing's
 * asking multiple is expected to sit a little above them. Everything here is context, not a
 * valuation, and missing figures are never treated as zero.
 */
class ListingBenchmark
{
    public const YEAR = 2025;

    public const SOURCE_URL = IndustryBenchmarks::SOURCE_URL;

    /** Workbook "Benchmark Summary" C4: 20% either side of the industry multiple. */
    public const SIGNAL_THRESHOLD = 0.2;

    public const SIGNAL_LABELS = [
        'below' => 'Below industry',
        'in_line' => 'In line with industry',
        'above' => 'Above industry',
    ];

    /** @var array<string, array<string, mixed>>|null subsector => 2025 row (+ trend) */
    private static ?array $subsectors = null;

    /** @var array<string, array{subsector: ?string, match: ?string, note: ?string}>|null */
    private static ?array $map = null;

    /** @var array<string, string>|null listing key => subsector */
    private static ?array $overrides = null;

    public static function for(Business $business): array
    {
        self::load();

        [$subsector, $match, $note] = self::resolve($business);
        $row = $subsector !== null ? (self::$subsectors[$subsector] ?? null) : null;

        if ($row === null || $row['cfx'] === null) {
            return [
                'status' => 'no_benchmark',
                'subsector' => null,
                'match' => 'None',
                'signal' => null,
                'signal_label' => null,
                'headline' => null,
                'message' => self::noBenchmarkMessage($business),
                'caveats' => [],
            ];
        }

        $metrics = $business->metrics();
        $pe = $metrics->priceExRealEstateToEarnings();
        $pr = $metrics->priceExRealEstateToRevenue();
        $basis = self::basisWord($business);

        $result = [
            'status' => $pe === null ? 'no_multiple' : 'compared',
            'subsector' => $row['subsector'],
            'sector' => $row['sector'],
            'match' => $match,
            'match_note' => $note,
            'year' => self::YEAR,
            'sales' => $row['sales'],
            'thin' => $row['thin'],
            'industry_pe' => $row['cfx'],
            'industry_pr' => $row['revx'],
            'median_sale' => $row['sale'],
            'median_cash_flow' => $row['cf'],
            'sale_to_ask' => $row['s2a'],
            'days_on_market' => $row['dom'],
            'pe_2019' => $row['cfx2019'],
            'change_since_2019' => $row['chg'],
            'listing_pe' => $pe,
            'listing_pr' => $pr,
            'basis' => $basis,
            'diff' => null,
            'pct' => null,
            'signal' => null,
            'signal_label' => null,
            'pr_pct' => $pr !== null && $row['revx'] ? $pr / $row['revx'] - 1 : null,
            'asking_vs_sale' => self::askingVsSale($business, $row['sale']),
            'real_estate_excluded' => $business->real_estate_in_price === 'Yes' && $pe !== null,
        ];

        if ($pe !== null) {
            $pct = $pe / $row['cfx'] - 1;
            $signal = match (true) {
                $pct <= -self::SIGNAL_THRESHOLD => 'below',
                $pct >= self::SIGNAL_THRESHOLD => 'above',
                default => 'in_line',
            };
            $result['diff'] = round($pe, 1) - round($row['cfx'], 1);
            $result['pct'] = $pct;
            $result['signal'] = $signal;
            $result['signal_label'] = self::SIGNAL_LABELS[$signal];
        }

        $result['headline'] = self::headline($result);
        $result['message'] = self::message($business, $result);
        $result['caveats'] = self::caveats($business, $result);

        return $result;
    }

    /** Subsector names, for the admin override picker. @return list<string> */
    public static function subsectors(): array
    {
        self::load();

        return array_keys(self::$subsectors);
    }

    /** The subsector the sub-industry maps to by default (no override). */
    public static function defaultFor(?string $subIndustry): ?array
    {
        self::load();

        return $subIndustry !== null ? (self::$map[mb_strtolower($subIndustry)] ?? null) : null;
    }

    /** Forget the loaded tables (tests swap the data files). */
    public static function flush(): void
    {
        self::$subsectors = self::$map = self::$overrides = null;
    }

    /** @return array{0: ?string, 1: string, 2: ?string} subsector, match quality, note */
    private static function resolve(Business $business): array
    {
        $own = trim((string) $business->benchmark_subsector);
        if ($own !== '' && isset(self::$subsectors[$own])) {
            return [$own, 'Override', null];
        }

        $key = $business->listing_key;
        if ($key !== null && isset(self::$overrides[$key], self::$subsectors[self::$overrides[$key]])) {
            return [self::$overrides[$key], 'Override', null];
        }

        $default = self::defaultFor($business->sub_industry);
        if ($default === null) {
            return [null, 'None', null];
        }

        return [$default['subsector'], $default['match'] ?? 'Close', $default['note']];
    }

    private static function headline(array $r): string
    {
        $industry = self::x($r['industry_pe']);
        if ($r['listing_pe'] === null) {
            return 'Industry '.$industry;
        }

        $d = abs($r['diff']);
        $gap = $d < 0.05 ? 'same as industry' : self::x($d).' '.($r['diff'] > 0 ? 'above' : 'below').' industry';

        return self::x($r['listing_pe']).' vs '.$industry.' · '.$gap;
    }

    private static function message(Business $business, array $r): string
    {
        $industry = self::x($r['industry_pe']).' cash flow that '.$r['subsector'].' sold for in '.$r['year'];

        if ($r['listing_pe'] === null) {
            return $r['subsector'].' sold for '.self::x($r['industry_pe']).' cash flow in '.$r['year']
                .'. '.self::missingReason($business).', so its multiple can\'t be compared.';
        }

        $asking = 'asking is '.self::x($r['listing_pe']).' '.$r['basis'];
        $d = abs($r['diff']);

        return match ($r['signal']) {
            'above' => 'Priced above its industry: '.$asking.', '.self::x($d).' more than the '.$industry.'.',
            'below' => 'Priced below its industry: '.$asking.', '.self::x($d).' less than the '.$industry.'.',
            default => 'Priced in line with its industry: '.$asking.' against the '.$industry
                .($d < 0.05 ? '.' : ' ('.self::x($d).' '.($r['diff'] > 0 ? 'above' : 'below').').'),
        };
    }

    /** @return list<string> */
    private static function caveats(Business $business, array $r): array
    {
        $out = [];
        if ($r['thin']) {
            $out[] = 'Only '.$r['sales'].' '.($r['sales'] === 1 ? 'sale' : 'sales').' in this industry in '.$r['year'].', a thin sample: treat the benchmark as rough.';
        }
        if ($r['match'] === 'Proxy') {
            $out[] = 'BizBuySell has no exact category for '.($business->sub_industry ?? 'this business').', so this uses the nearest one, '.$r['subsector'].'.';
        }
        if ($r['listing_pe'] !== null && $r['basis'] === 'EBITDA') {
            $out[] = 'This listing reports EBITDA, which is usually lower than the cash flow (SDE) BizBuySell uses, so its multiple reads higher than a like-for-like one.';
        }
        if ($r['real_estate_excluded']) {
            $out[] = 'Real estate is taken out of the price before comparing, as the industry figures are for the business only.';
        }

        return $out;
    }

    private static function noBenchmarkMessage(Business $business): string
    {
        return match ($business->sub_industry) {
            null, '', 'Unclassified' => 'No industry benchmark: the listing doesn\'t say clearly enough what the business does.',
            'Commercial / Mixed-use Property' => 'No industry benchmark: this is a property sale, and the industry figures are for operating businesses.',
            'Liquor License Only' => 'No industry benchmark: this is a license sale without a business or premises.',
            default => 'No industry benchmark: BizBuySell has no category that matches '.$business->sub_industry.'.',
        };
    }

    private static function missingReason(Business $business): string
    {
        $positive = fn ($v) => is_numeric($v) && (float) $v > 0;

        return match (true) {
            $business->operating_status !== null && $business->operating_status !== 'Yes' => 'This isn\'t listed as an operating business',
            ! $positive($business->asking_price) => 'The listing doesn\'t disclose an asking price',
            $business->earnings === null => 'The listing doesn\'t disclose earnings',
            ! $positive($business->earnings) => 'The listing\'s earnings aren\'t positive',
            $business->real_estate_in_price === 'Yes' => 'Real estate is in the price with no separate value',
            default => 'The listing doesn\'t give the figures needed',
        };
    }

    private static function askingVsSale(Business $business, mixed $sale): ?float
    {
        $price = $business->metrics()->priceExRealEstate();

        return $price !== null && is_numeric($sale) && $sale > 0 && ($business->operating_status ?? 'Yes') === 'Yes'
            ? $price / $sale - 1
            : null;
    }

    private static function basisWord(Business $business): string
    {
        return match ($label = $business->earningsLabel()) {
            'Earnings' => 'earnings',
            'Cash flow' => 'cash flow',
            default => $label,
        };
    }

    public static function x(?float $value): string
    {
        return $value === null ? 'n/a' : number_format($value, 1).'×';
    }

    private static function load(): void
    {
        if (self::$subsectors !== null) {
            return;
        }

        $dir = resource_path('data/benchmarks');
        self::$subsectors = [];
        foreach ((new IndustryBenchmarks($dir))->rows() as $row) {
            self::$subsectors[$row['subsector']] = $row;
        }

        self::$map = [];
        foreach (self::read($dir.'/subsector_map.json') as $row) {
            self::$map[mb_strtolower($row['sub_industry'])] = [
                'subsector' => $row['subsector'] ?: null,
                'match' => $row['match'] ?? null,
                'note' => $row['note'] ?? null,
            ];
        }

        self::$overrides = self::read($dir.'/subsector_overrides.json');
    }

    private static function read(string $path): array
    {
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($data) ? $data : [];
    }
}
