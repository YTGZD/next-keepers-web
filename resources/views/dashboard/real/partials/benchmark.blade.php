{{-- The listing's multiples next to its industry's. Expects $bm (App\Support\ListingBenchmark::for). --}}
@php
    $x = fn ($v, $d = 1) => $v === null ? 'n/a' : number_format($v, $d).'×';
    $pct = fn ($v) => $v === null ? null : ($v >= 0 ? '+' : '−').number_format(abs($v) * 100).'%';
    $matchLabel = [
        'Exact' => 'same category',
        'Close' => 'closest category',
        'Proxy' => 'nearest available category',
        'Override' => 'category picked for this listing',
    ][$bm['match']] ?? null;
    $compared = $bm['status'] === 'compared';
    $scale = $compared ? max($bm['listing_pe'], $bm['industry_pe']) * 1.15 : 1;
@endphp
<section aria-labelledby="benchmark-heading" class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="benchmark-heading" class="font-display text-lg font-semibold text-forest">Industry multiples</h2>
            @if ($bm['subsector'])
                <p class="mt-0.5 text-[11px] text-sage">
                    Compared with <span class="font-semibold text-forest">{{ $bm['subsector'] }}</span>{{ $matchLabel ? ' ('.$matchLabel.')' : '' }}
                    &middot; businesses reported sold in {{ $bm['year'] }}, nationally
                </p>
            @endif
        </div>
        @if ($bm['signal'] ?? null)
            @include('dashboard.real.partials.benchmark-signal', ['signal' => $bm['signal'], 'label' => $bm['signal_label']])
        @endif
    </div>

    <p class="mt-3 text-sm leading-relaxed text-ink/85">{{ $bm['message'] }}</p>

    @if ($compared)
        <div class="mt-4 space-y-2 text-xs" role="img" aria-label="Price to earnings: this listing {{ $x($bm['listing_pe']) }}, industry {{ $x($bm['industry_pe']) }}">
            @foreach ([['This listing (asking)', $bm['listing_pe'], 'bg-forest'], ['Industry (sold)', $bm['industry_pe'], 'bg-gold']] as [$label, $value, $color])
                <div class="grid grid-cols-[8.5rem_1fr_3rem] items-center gap-3">
                    <span class="text-sage">{{ $label }}</span>
                    <span class="h-2.5 rounded-full bg-ivory-deep/70">
                        <span class="block h-2.5 rounded-full {{ $color }}" style="width: {{ min(100, round($value / $scale * 100, 1)) }}%"></span>
                    </span>
                    <span class="text-right font-semibold text-forest tabular">{{ $x($value) }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if ($bm['status'] !== 'no_benchmark')
        <dl class="mt-5 grid grid-cols-2 gap-3 text-xs sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Industry price / earnings</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $x($bm['industry_pe']) }}</dd>
                <dd class="text-[11px] text-sage">This listing: {{ $bm['listing_pe'] === null ? 'not disclosed' : $x($bm['listing_pe']) }}</dd>
            </div>
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Industry price / revenue</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $x($bm['industry_pr'], 2) }}</dd>
                <dd class="text-[11px] text-sage">This listing: {{ $bm['listing_pr'] === null ? 'not disclosed' : $x($bm['listing_pr'], 2) }}</dd>
            </div>
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Median sale price</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $bm['median_sale'] ? \App\Models\Business::formatCompactCurrency($bm['median_sale']) : 'n/a' }}</dd>
                @if ($bm['asking_vs_sale'] !== null)
                    <dd class="text-[11px] text-sage">This asking price: {{ $pct($bm['asking_vs_sale']) }}</dd>
                @endif
            </div>
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Sale price vs asking</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $bm['sale_to_ask'] ? round($bm['sale_to_ask'] * 100).'%' : 'n/a' }}</dd>
                <dd class="text-[11px] text-sage">Median, industry</dd>
            </div>
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Days to sell</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $bm['days_on_market'] ?? 'n/a' }}</dd>
                <dd class="text-[11px] text-sage">{{ number_format($bm['sales']) }} {{ $bm['sales'] === 1 ? 'sale' : 'sales' }} in {{ $bm['year'] }}</dd>
            </div>
            <div class="rounded-2xl bg-ivory/60 p-3">
                <dt class="text-[11px] text-sage">Multiple in 2019</dt>
                <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $x($bm['pe_2019']) }}</dd>
                @if ($bm['change_since_2019'] !== null)
                    <dd class="text-[11px] text-sage">{{ $pct($bm['change_since_2019'] / 100) }} since then</dd>
                @endif
            </div>
        </dl>
    @endif

    @if ($bm['caveats'])
        <ul class="mt-4 space-y-1 text-xs text-forest/70">
            @foreach ($bm['caveats'] as $caveat)
                <li class="flex gap-2"><span aria-hidden="true" class="text-gold">&bull;</span> {{ $caveat }}</li>
            @endforeach
        </ul>
    @endif

    <p class="mt-4 border-t border-forest/5 pt-3 text-[11px] leading-relaxed text-sage">
        Industry figures: <a href="{{ \App\Support\ListingBenchmark::SOURCE_URL }}" target="_blank" rel="noopener" class="font-semibold text-emerald underline-offset-2 hover:underline">BizBuySell Insight</a>, prices businesses actually sold for, so asking multiples usually sit a little above them.
        National benchmarks for context, not a valuation of this business.
        @if ($bm['subsector'])
            <a href="{{ route('market.intelligence', ['industry' => $bm['subsector']]) }}#industry-benchmarks" class="font-semibold text-emerald underline-offset-2 hover:underline">See this industry since 2017 &rarr;</a>
        @endif
    </p>
</section>
