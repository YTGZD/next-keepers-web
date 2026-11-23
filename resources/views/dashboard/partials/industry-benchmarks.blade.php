{{--
    Industry benchmarks: what businesses sold for in 2025, nationally, by industry, plus each
    industry's cash-flow multiple from FY2017 to FY2025. Published BizBuySell Insight figures
    (App\Support\IndustryBenchmarks), not sample data. Fewer than 10 sales is flagged as thin.
--}}
@if (! empty($benchmarks['rows']))
<section id="industry-benchmarks" class="scroll-mt-24 space-y-6" x-data="industryBenchmarks(@js($benchmarks))" aria-labelledby="industry-benchmarks-title">
    {{-- Section header --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="grid size-5 place-items-center rounded-full bg-gold/20 text-gold text-xs">
                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M7 15l3-4 3 2 4-6"/></svg>
                </span>
                <span class="text-xs font-bold uppercase tracking-widest text-gold-soft">Industry benchmarks</span>
            </div>
            <h2 id="industry-benchmarks-title" class="mt-2 font-display text-2xl font-medium tracking-tight text-forest sm:text-3xl">What businesses actually sold for</h2>
            <p class="mt-1 max-w-2xl text-sm text-sage">
                Reported sales of small businesses in 2025, nationally, by industry: price paid, cash flow, multiples and time to sell, with each industry's multiple back to 2017.
            </p>
        </div>
        <span class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-full border border-emerald/25 bg-emerald/5 px-3 py-1 text-[11px] font-semibold text-emerald lg:self-auto">
            <span class="size-1.5 rounded-full bg-emerald" aria-hidden="true"></span>
            Published data · BizBuySell Insight
        </span>
    </div>

    {{-- Highlights --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($benchmarks['highlights'] as $card)
            @if ($card['industry'])
                <button type="button" @click="choose(@js($card['industry']), true)"
                    class="group rounded-2xl border border-forest/10 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            @else
                <div class="rounded-2xl border border-forest/10 bg-white p-5 shadow-sm">
            @endif
                <p class="text-[11px] font-bold uppercase tracking-widest text-sage">{{ $card['kicker'] }}</p>
                <p class="mt-2 font-display text-3xl font-bold tracking-tight text-forest tabular">{{ $card['value'] }}</p>
                <p class="mt-1.5 text-xs leading-relaxed text-ink/75">{{ $card['text'] }}</p>
                @if ($card['industry'])
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-emerald group-hover:text-forest">Chart it <span aria-hidden="true">&rarr;</span></span>
                @endif
            @if ($card['industry'])
                </button>
            @else
                </div>
            @endif
        @endforeach
    </div>

    {{-- Chart + sector bars --}}
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
        {{-- Multiples over time --}}
        <div id="benchmark-chart" class="scroll-mt-24 rounded-3xl border border-forest/10 bg-white p-5 shadow-sm sm:p-6 lg:col-span-7">
            <div class="flex flex-col gap-3 border-b border-forest/5 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-display text-base font-bold text-forest">Cash flow multiple, 2017 to 2025</h3>
                <div class="flex flex-col gap-1 sm:items-end">
                    <label for="bm-pick" class="sr-only">Industry to chart</label>
                    <input id="bm-pick" type="search" list="bm-industries" x-model="pick"
                        @change="choosePick()" @keydown.enter.prevent="choosePick()"
                        placeholder="Search an industry, e.g. dental, HVAC"
                        class="w-full rounded-xl border border-forest/15 bg-white px-3.5 py-2 text-xs font-semibold text-forest shadow-sm placeholder:font-normal placeholder:text-sage focus:border-gold focus:outline-none sm:w-72">
                    <datalist id="bm-industries">
                        @foreach ($benchmarks['rows'] as $row)
                            <option value="{{ $row['subsector'] }}">{{ $row['sector'] }}</option>
                        @endforeach
                    </datalist>
                    <p class="text-[11px] text-gold" x-show="pickMsg" x-text="pickMsg" role="status"></p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-sage" x-text="current ? current.sector : ''"></p>
                    <p class="font-display text-lg font-bold leading-snug text-forest" x-text="current ? current.subsector : '—'"></p>
                </div>
                <div class="text-right" aria-live="polite">
                    <p class="font-display text-2xl font-bold text-forest tabular" x-text="x2(readout.value)"></p>
                    <p class="text-[11px] text-sage tabular">
                        <span x-text="readout.year"></span>
                        <template x-if="readout.sold !== null && readout.sold !== undefined"><span> · <span x-text="int(readout.sold)"></span> sold</span></template>
                        <template x-if="readout.thin"><span class="font-semibold text-gold"> · thin sample</span></template>
                        <span> · all industries <span x-text="x2(readout.median)"></span></span>
                    </p>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] font-medium text-sage">
                <span class="flex items-center gap-1.5"><span class="h-0.5 w-5 rounded bg-emerald" aria-hidden="true"></span> Selected industry</span>
                <span class="flex items-center gap-1.5"><span class="h-0 w-5 border-t-2 border-dashed border-gold" aria-hidden="true"></span> Median of all industries (10+ sales)</span>
            </div>

            <svg viewBox="0 0 640 240" class="mt-2 h-auto w-full overflow-visible" role="img" :aria-label="chartLabel" @mouseleave="hover = null">
                {{-- grid + y labels --}}
                @for ($t = 0; $t < 5; $t++)
                    <line x1="44" x2="624" :y1="py(ticks()[{{ $t }}])" :y2="py(ticks()[{{ $t }}])" stroke="#10291f" stroke-opacity="0.07"/>
                    <text x="36" :y="py(ticks()[{{ $t }}]) + 3.5" text-anchor="end" class="fill-sage text-[10px] tabular" x-text="tickLabel(ticks()[{{ $t }}])"></text>
                @endfor
                {{-- x labels --}}
                @foreach ($benchmarks['years'] as $i => $year)
                    <text :x="px({{ $i }})" y="234" text-anchor="middle" class="fill-sage text-[10px] tabular">{{ $year }}</text>
                @endforeach
                {{-- hover guide --}}
                <line x-show="hover !== null" :x1="px(hover ?? 0)" :x2="px(hover ?? 0)" y1="14" y2="212" stroke="#c99b43" stroke-opacity="0.45" stroke-dasharray="3 3"/>
                {{-- lines --}}
                <path :d="medianLine" fill="none" stroke="#c99b43" stroke-width="2" stroke-dasharray="5 4" stroke-linecap="round"/>
                <path :d="line" fill="none" stroke="#1B604A" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                {{-- points: hover or focus to read a year --}}
                @foreach ($benchmarks['years'] as $i => $year)
                    <rect :x="px({{ $i }}) - 30" y="0" width="60" height="214" fill="transparent" @mouseenter="hover = {{ $i }}"/>
                    <circle x-show="point({{ $i }}) !== null" :cx="px({{ $i }})" :cy="py(point({{ $i }}) ?? 0)"
                        :r="hover === {{ $i }} ? 5.5 : 4" fill="#fff" stroke="#1B604A" stroke-width="2.25"
                        :class="current && current.sold[{{ $i }}] !== null && current.sold[{{ $i }}] < thin ? 'opacity-50' : ''"
                        tabindex="0" @focus="hover = {{ $i }}" @blur="hover = null" @mouseenter="hover = {{ $i }}"
                        :aria-label="'{{ $year }}: ' + x2(point({{ $i }}))" class="cursor-pointer focus:outline-none"/>
                @endforeach
            </svg>

            {{-- 2025 deal profile for the selected industry --}}
            <dl class="mt-4 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-forest/10 bg-forest/10 text-xs sm:grid-cols-4" x-show="current">
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Sold in 2025</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="int(current?.sales)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Median sale price</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="usd(current?.sale)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Median cash flow</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="usd(current?.cf)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Median revenue</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="usd(current?.rev)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Price / revenue</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="x2(current?.revx)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Sale / asking</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="pct(current?.s2a)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Days to sell</dt><dd class="mt-0.5 font-display text-base font-bold text-forest tabular" x-text="int(current?.dom)"></dd></div>
                <div class="bg-ivory/60 p-3"><dt class="text-sage">Multiple vs 2019</dt><dd class="mt-0.5 font-display text-base font-bold tabular" :class="(current?.chg ?? 0) >= 0 ? 'text-emerald' : 'text-gold'" x-text="chg(current?.chg)"></dd></div>
            </dl>
            <p class="mt-2 text-[11px] text-gold" x-show="current && current.thin">
                Fewer than 10 sales in 2025: treat these figures as a rough guide.
            </p>
        </div>

        {{-- Multiples by sector --}}
        <div class="flex flex-col rounded-3xl border border-forest/10 bg-forest-deep p-5 text-ivory shadow-md sm:p-6 lg:col-span-5">
            <div class="border-b border-ivory/10 pb-4">
                <h3 class="font-display text-base font-bold text-ivory">Multiples by sector, 2025</h3>
                <p class="mt-1 text-[11px] text-ivory/65">Median price to cash flow of each sector's industries with 10+ sales. Pick a sector to filter the table.</p>
            </div>
            <ul class="mt-4 -mr-2 max-h-[34rem] space-y-2 overflow-y-auto pr-2 [scrollbar-color:rgba(245,242,234,0.25)_transparent] [scrollbar-width:thin]">
                <template x-for="s in sectors" :key="s.sector">
                    <li>
                        <button type="button" @click="filterSector(s.sector)" :aria-pressed="sector === s.sector"
                            class="group w-full rounded-lg px-2 py-1 text-left transition hover:bg-ivory/5"
                            :class="sector === s.sector ? 'bg-ivory/10' : ''">
                            <span class="flex items-baseline justify-between gap-3 text-xs">
                                <span class="truncate font-medium text-ivory/90" x-text="s.sector"></span>
                                <span class="shrink-0 font-bold text-gold-soft tabular" x-text="x2(s.cfx)"></span>
                            </span>
                            <span class="mt-1 block h-1.5 overflow-hidden rounded-full bg-ivory/10">
                                <span class="block h-full rounded-full transition-all" :class="sector === s.sector ? 'bg-gold' : 'bg-emerald group-hover:bg-gold/70'" :style="'width:' + barWidth(s)"></span>
                            </span>
                            <span class="mt-0.5 block text-[10px] text-ivory/50 tabular" x-text="int(s.sales) + ' sold · ' + s.counted + ' of ' + s.subsectors + ' industries counted'"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- All industries table --}}
    <div class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 border-b border-forest/5 pb-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="font-display text-base font-bold text-forest">All industries, sold in 2025</h3>
                <p class="mt-0.5 text-[11px] text-sage"><span x-text="table.length"></span> of {{ count($benchmarks['rows']) }} industries. Click a row to chart it. Muted counts are thin samples (fewer than 10 sales).</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <label for="bm-q" class="sr-only">Search industries</label>
                <input id="bm-q" type="search" x-model="q" placeholder="Search, e.g. dental, car wash"
                    class="rounded-xl border border-forest/15 bg-white px-3.5 py-2 text-xs text-forest shadow-sm placeholder:text-sage focus:border-gold focus:outline-none sm:w-60">
                <label for="bm-sector" class="sr-only">Sector</label>
                <select id="bm-sector" x-model="sector"
                    class="rounded-xl border border-forest/15 bg-white px-3 py-2 text-xs font-semibold text-forest shadow-sm focus:border-gold focus:outline-none">
                    <option value="">All sectors</option>
                    @foreach (collect($benchmarks['rows'])->pluck('sector')->unique()->sort() as $sectorName)
                        <option value="{{ $sectorName }}">{{ $sectorName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $cols = [
                ['subsector', 'Industry', 'left'], ['sector', 'Sector', 'left'], ['sales', 'Sales', 'right'],
                ['sale', 'Median sale price', 'right'], ['cf', 'Median cash flow', 'right'], ['cfx', 'Price / cash flow', 'right'],
                ['revx', 'Price / revenue', 'right'], ['s2a', 'Sale / asking', 'right'], ['dom', 'Days to sell', 'right'],
                ['chg', 'Multiple vs 2019', 'right'],
            ];
        @endphp
        <div class="mt-2 max-h-[28rem] overflow-auto">
            <table class="w-full min-w-[54rem] text-xs">
                <thead class="sticky top-0 z-10 bg-white">
                    <tr class="border-b border-forest/10 text-[11px] uppercase tracking-wider text-sage">
                        @foreach ($cols as [$key, $label, $align])
                            <th scope="col" class="py-2.5 px-2 font-semibold {{ $align === 'right' ? 'text-right' : 'text-left' }}" :aria-sort="ariaSort('{{ $key }}')">
                                <button type="button" @click="sortBy('{{ $key }}')" class="inline-flex items-center gap-1 uppercase tracking-wider hover:text-forest">
                                    {{ $label }}
                                    <span class="text-gold" x-show="sortKey === '{{ $key }}'" x-text="sortDir > 0 ? '▲' : '▼'" aria-hidden="true"></span>
                                </button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="r in table" :key="r.subsector">
                        <tr @click="choose(r.subsector, true)" @keydown.enter="choose(r.subsector, true)" tabindex="0"
                            class="cursor-pointer border-b border-forest/5 transition hover:bg-ivory/70 focus:bg-ivory/70"
                            :class="r.subsector === industry ? 'bg-emerald/5' : ''">
                            <td class="py-2 px-2 font-semibold text-forest" x-text="r.subsector"></td>
                            <td class="py-2 px-2 text-sage" x-text="r.sector"></td>
                            <td class="py-2 px-2 text-right tabular" :class="r.thin ? 'text-sage/70' : 'text-forest'" x-text="int(r.sales)"></td>
                            <td class="py-2 px-2 text-right tabular" x-text="usd(r.sale)"></td>
                            <td class="py-2 px-2 text-right tabular" x-text="usd(r.cf)"></td>
                            <td class="py-2 px-2 text-right font-bold text-forest tabular" x-text="x2(r.cfx)"></td>
                            <td class="py-2 px-2 text-right tabular" x-text="x2(r.revx)"></td>
                            <td class="py-2 px-2 text-right text-sage tabular" x-text="pct(r.s2a)"></td>
                            <td class="py-2 px-2 text-right text-sage tabular" x-text="int(r.dom)"></td>
                            <td class="py-2 px-2 text-right font-semibold tabular" :class="r.chg === null ? 'text-sage' : (r.chg >= 0 ? 'text-emerald' : 'text-gold')" x-text="chg(r.chg)"></td>
                        </tr>
                    </template>
                    <tr x-show="table.length === 0" x-cloak><td colspan="10" class="py-6 text-center text-sage">No industry matches.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-[11px] leading-relaxed text-sage">
        Source: <a href="{{ $benchmarks['source'] }}" class="font-semibold text-emerald underline-offset-2 hover:underline" target="_blank" rel="noopener">BizBuySell Insight reports</a>, businesses reported sold nationally, full years 2017 to 2025 (2025 is the latest full year). Medians and multiples are as BizBuySell publishes them; multiples are averages per industry. BizBuySell regrouped its sectors from 2021, so some industries start part-way through. These are national benchmarks for context, not a valuation of any business.
    </p>
</section>
@endif
