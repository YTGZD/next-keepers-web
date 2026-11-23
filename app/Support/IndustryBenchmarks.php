<?php

namespace App\Support;

/**
 * National industry benchmarks for the market intelligence page.
 *
 * Source: BizBuySell Insight reports (businesses reported sold, national).
 * - resources/data/benchmarks/industry_multiples.json: full year 2025 by sector and subsector.
 * - resources/data/benchmarks/sector_annual.json: the same metrics per full year, FY2017 to FY2025.
 *
 * Both files come from the owner's market dashboard handoff (workbook v13). They are
 * published figures, not sample data, but a subsector with fewer than 10 sales in a
 * year is a thin sample and is left out of the highlights and medians.
 */
class IndustryBenchmarks
{
    public const YEARS = [2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025];

    public const THIN_SAMPLE = 10;

    public const SOURCE_URL = 'https://www.bizbuysell.com/insight-report/';

    /** @var array<int, array<string, mixed>> */
    private array $latest;

    /** @var array<int, array<string, mixed>> */
    private array $annual;

    public function __construct(?string $dir = null)
    {
        $dir ??= resource_path('data/benchmarks');
        $this->latest = self::read($dir.'/industry_multiples.json');
        $this->annual = self::read($dir.'/sector_annual.json');
    }

    /** Everything the page needs, ready for the view and the Alpine component. */
    public function toArray(): array
    {
        $rows = $this->rows();

        return [
            'years' => self::YEARS,
            'rows' => $rows,
            'median' => $this->medianByYear(),
            'sectors' => $this->sectors($rows),
            'highlights' => $this->highlights($rows),
            'defaultIndustry' => $this->mostTraded($rows)['subsector'] ?? null,
            'thin' => self::THIN_SAMPLE,
            'source' => self::SOURCE_URL,
        ];
    }

    /**
     * One row per 2025 subsector, with its cash-flow multiple and sales for every year.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rows(): array
    {
        $history = [];
        foreach ($this->annual as $r) {
            $key = self::key($r['subsector']);
            $history[$key]['cfx'][$r['fy']] = $r['cfx'];
            $history[$key]['sold'][$r['fy']] = $r['sold'];
        }

        $rows = [];
        foreach ($this->latest as $r) {
            $h = $history[self::key($r['subsector'])] ?? ['cfx' => [], 'sold' => []];
            $cfx = array_map(fn ($y) => $h['cfx'][$y] ?? null, self::YEARS);
            $sold = array_map(fn ($y) => $h['sold'][$y] ?? null, self::YEARS);
            $c2019 = $h['cfx'][2019] ?? null;

            $rows[] = [
                'subsector' => $r['subsector'],
                'sector' => $r['sector'],
                'sales' => $r['sales'],
                'sale' => $r['sale'],
                'ask' => $r['ask'],
                's2a' => $r['s2a'],
                'rev' => $r['rev'],
                'revx' => $r['revx'],
                'cf' => $r['cf'],
                'cfx' => $r['cfx'],
                'dom' => $r['dom'],
                'thin' => ($r['sales'] ?? 0) < self::THIN_SAMPLE,
                'cfx2019' => $c2019,
                'chg' => $c2019 && $r['cfx'] !== null ? round(($r['cfx'] / $c2019 - 1) * 100, 1) : null,
                'trend' => $cfx,
                'sold' => $sold,
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($a['subsector'], $b['subsector']));

        return $rows;
    }

    /**
     * Median cash-flow multiple across subsectors with 10+ sales, per year.
     *
     * @return array<int, float|null>
     */
    public function medianByYear(): array
    {
        return array_map(function ($year) {
            $values = array_column(array_filter(
                $this->annual,
                fn ($r) => $r['fy'] === $year && $r['cfx'] !== null && ($r['sold'] ?? 0) >= self::THIN_SAMPLE,
            ), 'cfx');

            return self::median($values);
        }, self::YEARS);
    }

    /**
     * Per sector, 2025: total sales and the median multiples of its subsectors with 10+ sales.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sectors(array $rows): array
    {
        $by = [];
        foreach ($rows as $r) {
            $by[$r['sector']][] = $r;
        }

        $out = [];
        foreach ($by as $sector => $list) {
            $solid = array_filter($list, fn ($r) => ! $r['thin'] && $r['cfx'] !== null);
            $out[] = [
                'sector' => $sector,
                'sales' => array_sum(array_column($list, 'sales')),
                'subsectors' => count($list),
                'cfx' => self::median(array_column($solid, 'cfx')),
                'revx' => self::median(array_filter(array_column($solid, 'revx'), fn ($v) => $v !== null)),
                'counted' => count($solid),
            ];
        }

        $out = array_values(array_filter($out, fn ($s) => $s['cfx'] !== null));
        usort($out, fn ($a, $b) => $b['cfx'] <=> $a['cfx']);

        return $out;
    }

    /** Four headline cards, computed from the data (subsectors with 10+ sales only). */
    public function highlights(array $rows): array
    {
        $solid = array_values(array_filter($rows, fn ($r) => ! $r['thin'] && $r['cfx'] !== null));
        usort($solid, fn ($a, $b) => $a['cfx'] <=> $b['cfx']);

        $most = $this->mostTraded($rows);
        $repriced = array_values(array_filter($solid, fn ($r) => $r['chg'] !== null));
        usort($repriced, fn ($a, $b) => $b['chg'] <=> $a['chg']);
        $up = $repriced[0] ?? null;

        $cards = [];

        $median = self::median(array_column($solid, 'cfx'));
        $s2a = self::median(array_filter(array_column($solid, 's2a'), fn ($v) => $v !== null));
        if ($median !== null) {
            $cards[] = [
                'kicker' => 'Typical multiple',
                'value' => self::x($median),
                'text' => 'median price to cash flow across '.count($solid).' industries with 10+ sales in 2025'
                    .($s2a !== null ? ', closing at '.round($s2a * 100).'% of asking.' : '.'),
                'industry' => null,
            ];
        }

        if (count($solid) >= 3) {
            [$a, $b, $c] = $solid;
            $cards[] = [
                'kicker' => 'Lowest multiples',
                'value' => self::x($a['cfx']),
                'text' => $a['subsector'].' sold for the lowest multiple of cash flow in 2025, then '
                    .$b['subsector'].' ('.self::x($b['cfx']).') and '.$c['subsector'].' ('.self::x($c['cfx']).').',
                'industry' => $a['subsector'],
            ];
        }

        if ($most) {
            $cards[] = [
                'kicker' => 'Most traded',
                'value' => number_format($most['sales']),
                'text' => $most['subsector'].' businesses sold in 2025, more than any other industry, at '
                    .self::x($most['cfx']).' cash flow.',
                'industry' => $most['subsector'],
            ];
        }

        if ($up) {
            $cards[] = [
                'kicker' => 'Biggest repricing',
                'value' => ($up['chg'] >= 0 ? '+' : '').number_format($up['chg'], 0).'%',
                'text' => $up['subsector'].': multiple from '.self::x($up['cfx2019']).' in 2019 to '
                    .self::x($up['cfx']).' in 2025.',
                'industry' => $up['subsector'],
            ];
        }

        return $cards;
    }

    private function mostTraded(array $rows): ?array
    {
        $best = null;
        foreach ($rows as $r) {
            if ($r['sales'] !== null && ($best === null || $r['sales'] > $best['sales'])) {
                $best = $r;
            }
        }

        return $best;
    }

    private static function x(?float $v): string
    {
        return $v === null ? 'n/a' : number_format($v, 2).'×';
    }

    /** BizBuySell spells a few subsectors with and without commas across years. */
    private static function key(string $subsector): string
    {
        return strtolower(str_replace(',', '', $subsector));
    }

    /** @param array<int|string, float|int> $values */
    public static function median(array $values): ?float
    {
        $values = array_values($values);
        $n = count($values);
        if ($n === 0) {
            return null;
        }
        sort($values);
        $mid = intdiv($n, 2);

        return round($n % 2 ? $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2, 2);
    }

    /** @return array<int, array<string, mixed>> */
    private static function read(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }
}
