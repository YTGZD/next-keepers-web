<?php

namespace App\Support;

/**
 * State-level data for the Regional Insights card on the market intelligence page.
 *
 * Files in resources/data/regional/, from the owner's market dashboard handoff:
 * - local_states.json: Census population estimates (Vintage 2025) and Building Permits Survey, per state + US.
 * - sba_states.json: SBA 7(a) acquisition loans per state, last four quarters vs the four before,
 *   with business establishments from County Business Patterns.
 * - us_states.json: state outlines as SVG paths (Albers USA, 975 x 610).
 *
 * Published public figures, not sample data.
 */
class RegionalInsights
{
    /** States with fewer acquisition loans than this are left out of the SBA "leaders" line. */
    public const MIN_LOANS = 20;

    private array $local;

    private array $sba;

    private array $shapes;

    public function __construct(?string $dir = null)
    {
        $dir ??= resource_path('data/regional');
        $this->local = self::read($dir.'/local_states.json');
        $this->sba = self::read($dir.'/sba_states.json');
        $this->shapes = self::read($dir.'/us_states.json');
    }

    public function toArray(): array
    {
        $local = [];
        foreach ($this->local as $r) {
            $local[$r['st']] = $r;
        }
        $sba = [];
        foreach ($this->sba as $r) {
            $sba[$r['state']] = $r;
        }

        $states = [];
        foreach ($this->shapes as $s) {
            $l = $local[$s['abbr']] ?? null;
            $b = $sba[$s['abbr']] ?? null;
            $states[$s['abbr']] = [
                'name' => $s['name'],
            ] + self::metrics($l, $b);
        }

        $us = self::metrics($local['US'] ?? null, self::usSba($this->sba));

        $metrics = [
            'population' => [
                'tab' => 'Population Growth',
                'title' => 'Population change, 2020 to 2025',
                'unit' => 'pct',
                'source' => 'U.S. Census Bureau population estimates, Vintage 2025',
            ],
            'permits' => [
                'tab' => 'Housing Permits',
                'title' => 'New homes permitted per 1,000 residents, 2025',
                'unit' => 'dec',
                'source' => 'U.S. Census Bureau, Building Permits Survey',
            ],
            'density' => [
                'tab' => 'Business Density',
                'title' => 'Business establishments per 1,000 residents',
                'unit' => 'dec',
                'source' => 'U.S. Census Bureau, County Business Patterns',
            ],
            'sba' => [
                'tab' => 'SBA Lending',
                'title' => 'SBA acquisition loans per 10,000 businesses, '.($this->sba[0]['period'] ?? 'last four quarters'),
                'unit' => 'dec',
                'source' => 'SBA 7(a) loan data (FOIA), acquisition loans; business counts from County Business Patterns',
            ],
        ];

        foreach ($metrics as $key => &$m) {
            $m['us'] = $us[$key];
            $m['insight'] = $this->insight($key, $states);
        }

        return [
            'states' => $states,
            'shapes' => array_map(fn ($s) => ['abbr' => $s['abbr'], 'd' => $s['d']], $this->shapes),
            'metrics' => $metrics,
            'period' => $this->sba[0]['period'] ?? null,
        ];
    }

    /** Value + detail lines for each metric, for one state or the US. */
    private static function metrics(?array $l, ?array $b): array
    {
        $num = fn ($v) => is_numeric($v) ? (float) $v : null;
        $int = fn ($v) => is_numeric($v) ? number_format((float) $v) : '—';
        $pct = fn ($v) => is_numeric($v) ? (($v > 0 ? '+' : '').number_format((float) $v, 1).'%') : '—';

        $estab = $num($b['estab'] ?? null);
        $pop = $num($b['population'] ?? null);
        $density = $estab && $pop ? round($estab / $pop * 1000, 1) : null;
        $loans = $num($b['acq_loans'] ?? null);
        $prev = $num($b['acq_loans_prev'] ?? null);
        $per10k = $estab && $loans !== null ? round($loans / $estab * 10000, 2) : null;

        return [
            'population' => [
                'value' => $num($l['pop_chg'] ?? null),
                'rows' => [
                    ['Population, 2025', $int($l['pop2025'] ?? null)],
                    ['Population, 2020', $int($l['pop2020'] ?? null)],
                ],
            ],
            'permits' => [
                'value' => $num($l['per1k'] ?? null),
                'rows' => [
                    ['Homes permitted, 2025', $int($l['u2025'] ?? null)],
                    ['2025 vs 2024', $pct($l['chg25'] ?? null)],
                    ['Jan–Aug 2026 vs 2025', $pct($l['chg_ytd'] ?? null)],
                ],
            ],
            'density' => [
                'value' => $density,
                'rows' => [
                    ['Business establishments', $int($estab)],
                    ['Residents', $int($pop)],
                ],
            ],
            'sba' => [
                'value' => $per10k,
                'loans' => $loans,
                'rows' => [
                    ['Acquisition loans', $int($loans)],
                    ['Four quarters before', $int($prev)],
                    ['Median acquisition loan', $num($b['acq_median'] ?? null) ? '$'.number_format($b['acq_median'] / 1000).'K' : '—'],
                ],
            ],
        ];
    }

    /** US totals for the SBA file (it has no US row). Puerto Rico is outside the 50 states + DC map and is left out. */
    private static function usSba(array $rows): array
    {
        $rows = array_filter($rows, fn ($r) => $r['state'] !== 'PR');

        return [
            'estab' => array_sum(array_column($rows, 'estab')),
            'population' => array_sum(array_column($rows, 'population')),
            'acq_loans' => array_sum(array_column($rows, 'acq_loans')),
            'acq_loans_prev' => array_sum(array_column($rows, 'acq_loans_prev')),
            'acq_median' => null,
        ];
    }

    /** One computed sentence per metric: the top three states. */
    private function insight(string $key, array $states): string
    {
        $pool = array_filter($states, function ($s) use ($key) {
            if ($s[$key]['value'] === null) {
                return false;
            }
            if ($key === 'sba') {
                return ($s['sba']['loans'] ?? 0) >= self::MIN_LOANS;
            }

            return true;
        });
        uasort($pool, fn ($a, $b) => $b[$key]['value'] <=> $a[$key]['value']);
        $top = array_slice($pool, 0, 3, true);
        $fmt = fn ($v) => $key === 'population' ? (($v > 0 ? '+' : '').number_format($v, 1).'%') : number_format($v, 1);
        $names = implode(', ', array_map(fn ($s) => $s['name'].' ('.$fmt($s[$key]['value']).')', $top));

        return match ($key) {
            'population' => 'Fastest growing since 2020: '.$names.'.',
            'permits' => 'Most new homes for their size: '.$names.'.',
            'density' => 'Most businesses per resident: '.$names.'.',
            'sba' => 'Most SBA-financed purchases for their size: '.$names.' (states with '.self::MIN_LOANS.'+ loans).',
        };
    }

    private static function read(string $path): array
    {
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($data) ? $data : [];
    }
}
