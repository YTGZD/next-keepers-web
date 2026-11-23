<x-layouts.dashboard title="Market Intelligence | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        timeRange: '3M',
        selectedIndustry: 'Healthcare',
        regionalTab: 'population'
    }">
        {{-- Header with Time Range Filters --}}
        <section class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="grid size-5 place-items-center rounded-full bg-gold/20 text-gold text-xs">
                        <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-widest text-gold-soft">Market Intelligence</span>
                </div>
                <h1 class="mt-2 font-display text-3xl font-medium tracking-tight text-forest sm:text-4xl">
                    Market Intelligence
                </h1>
                <p class="mt-1 text-sm text-sage max-w-2xl">
                    Discover market trends, industry insights, and regional opportunities to make informed acquisition decisions.
                </p>
            </div>

            {{-- Time Range Filter Pills --}}
            <div class="flex flex-wrap items-center gap-1.5 rounded-full border border-forest/15 bg-white p-1 text-xs font-semibold shadow-sm shrink-0">
                @foreach (['1M', '3M', '6M', '1Y'] as $period)
                    <button
                        type="button"
                        @click="timeRange = '{{ $period }}'"
                        :class="timeRange === '{{ $period }}' ? 'bg-forest text-ivory shadow' : 'text-forest/75 hover:text-forest'"
                        class="rounded-full px-3.5 py-1.5 transition"
                    >
                        {{ $period }}
                    </button>
                @endforeach
                <div class="relative pl-1 border-l border-forest/10">
                    <select class="rounded-full bg-transparent px-2.5 py-1 text-xs font-semibold text-forest focus:outline-none cursor-pointer">
                        <option value="all">All Time</option>
                        <option value="5y">5 Years</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- Top 4 KPI Metrics Row --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                <div class="flex items-center justify-between rounded-2xl border border-forest/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md transition">
                    <div class="flex items-center gap-4">
                        <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald/10 text-emerald">
                            @if ($kpi['icon'] === 'store')
                                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            @elseif ($kpi['icon'] === 'chart')
                                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @elseif ($kpi['icon'] === 'briefcase')
                                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @elseif ($kpi['icon'] === 'handshake')
                                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.9M9 11a4 4 0 100-8 4 4 0 000 8zm-7 9v-1a5 5 0 0110 0v1M16 3.1a4 4 0 010 7.8"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="font-display text-2xl font-bold tracking-tight text-forest tabular">{{ $kpi['value'] }}</p>
                            <p class="text-xs text-sage mt-0.5">{{ $kpi['label'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald/10 px-2 py-0.5 text-xs font-semibold text-emerald tabular">
                            &uarr; {{ $kpi['change'] }}
                        </span>
                        <svg class="h-6 w-16" viewBox="0 0 64 24" fill="none">
                            <path d="M2 18 C 14 16, 20 8, 32 14 C 44 20, 52 4, 62 6" stroke="#1B604A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Main Middle Grid: Activity Map (6 cols) + Industry Trends (3 cols) + Top Industries (3 cols) --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            {{-- Left (6 cols): U.S. Business Activity Map --}}
            <div class="lg:col-span-6 rounded-3xl border border-forest/10 bg-forest-deep p-6 text-ivory shadow-md flex flex-col justify-between relative overflow-hidden">
                {{-- Map Header Controls --}}
                <div class="flex flex-wrap items-center justify-between gap-3 z-10 border-b border-ivory/10 pb-4">
                    <h2 class="font-display text-base font-bold text-ivory">U.S. Business Activity Map</h2>
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-3 text-ivory/80">
                            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-emerald"></span> Low</span>
                            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-full bg-gold"></span> High</span>
                        </div>
                        <select class="rounded-xl border border-ivory/20 bg-forest px-3 py-1.5 text-xs font-semibold text-ivory focus:outline-none">
                            <option>Business Activity</option>
                            <option>Asking Multiples</option>
                            <option>Deal Volume</option>
                        </select>
                    </div>
                </div>

                {{-- Interactive Map Display --}}
                <div class="relative w-full my-6 flex-1 flex items-center justify-center">
                    {{-- Zoom controls on the left --}}
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 z-20 flex flex-col gap-1.5 bg-forest/80 rounded-xl p-1 border border-ivory/15 shadow">
                        <button type="button" class="grid size-7 place-items-center rounded-lg text-ivory hover:bg-emerald transition">&plus;</button>
                        <button type="button" class="grid size-7 place-items-center rounded-lg text-ivory hover:bg-emerald transition">&minus;</button>
                        <button type="button" class="grid size-7 place-items-center rounded-lg text-ivory hover:bg-emerald transition">&#9678;</button>
                    </div>

                    {{-- Tooltip card for New York --}}
                    <div class="absolute right-4 top-2 z-20 rounded-2xl border border-gold/40 bg-white/95 p-3 text-ink shadow-2xl backdrop-blur-md min-w-[10rem]">
                        <p class="font-display text-sm font-bold text-forest">New York</p>
                        <p class="text-xs font-bold text-emerald tabular mt-0.5">+ 12.4%</p>
                        <p class="text-[11px] text-sage tabular">12,890 Businesses</p>
                    </div>

                    {{-- SVG Map --}}
                    @if ($mapData)
                        <svg viewBox="{{ $mapData['viewBox'] ?? '0 0 960 600' }}" class="h-auto w-full drop-shadow-[0_10px_30px_rgba(0,0,0,0.6)]">
                            <defs>
                                <filter id="mkt-glow" x="-200%" y="-200%" width="500%" height="500%">
                                    <feGaussianBlur stdDeviation="6"/>
                                </filter>
                                <linearGradient id="mkt-land" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#1b604a" stop-opacity="0.75"/>
                                    <stop offset="100%" stop-color="#0a1a13" stop-opacity="0.5"/>
                                </linearGradient>
                            </defs>
                            <path d="{{ $mapData['statesPath'] ?? '' }}" fill="url(#mkt-land)"/>
                            <path d="{{ $mapData['bordersPath'] ?? '' }}" fill="none" stroke="#c99b43" stroke-opacity="0.35" stroke-width="0.8"/>
                            @foreach (($mapData['points'] ?? []) as $p)
                                @php
                                    $r = match ($p['intensity'] ?? 2) { 3 => 4.0, 2 => 2.8, default => 2.0 };
                                @endphp
                                <g class="cursor-pointer transition-opacity duration-200 hover:opacity-100 opacity-85">
                                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $r * 2.8 }}" fill="#e3c27d" opacity="0.5" filter="url(#mkt-glow)"/>
                                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $r }}" fill="#f8e7ba"/>
                                </g>
                            @endforeach
                        </svg>
                    @endif
                </div>
            </div>

            {{-- Center (3 cols): Industry Trends --}}
            <div class="lg:col-span-3 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-forest/5 pb-3">
                        <h2 class="font-display text-base font-bold text-forest">Industry Trends</h2>
                        <a href="#industry-benchmarks" class="text-xs font-semibold text-emerald hover:text-forest transition">View Details &rarr;</a>
                    </div>

                    {{-- Industry dropdown --}}
                    <div class="mt-4">
                        <select x-model="selectedIndustry" class="w-full rounded-xl border border-forest/15 bg-white px-3.5 py-2 text-xs font-semibold text-forest shadow-sm focus:border-gold focus:outline-none">
                            <option value="Healthcare">Healthcare</option>
                            <option value="HVAC">HVAC &amp; Restoration</option>
                            <option value="Construction">Construction</option>
                            <option value="Manufacturing">Manufacturing</option>
                        </select>
                    </div>

                    {{-- Big Metric & Ascending Mini Bars --}}
                    <div class="mt-5 flex items-end justify-between">
                        <div>
                            <p class="font-display text-3xl font-bold text-forest tabular">+8.9%</p>
                            <p class="text-xs text-sage mt-0.5 font-medium">Market Growth (Last 12 Months)</p>
                        </div>
                        {{-- Mini ascending bar chart --}}
                        <div class="flex items-end gap-1 h-12 pb-1">
                            <div class="w-1.5 bg-emerald/30 rounded-t h-4"></div>
                            <div class="w-1.5 bg-emerald/40 rounded-t h-6"></div>
                            <div class="w-1.5 bg-emerald/60 rounded-t h-8"></div>
                            <div class="w-1.5 bg-emerald/80 rounded-t h-10"></div>
                            <div class="w-1.5 bg-emerald rounded-t h-12"></div>
                        </div>
                    </div>

                    {{-- Line chart Q1-Q4 '25 --}}
                    <div class="mt-6 pt-4 border-t border-forest/5">
                        <div class="h-28 w-full">
                            <svg class="h-full w-full" viewBox="0 0 200 80" fill="none">
                                <path d="M5 65 Q 40 55, 70 48 T 130 35 T 195 20" stroke="#1B604A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="5" cy="65" r="3" fill="#1B604A"/>
                                <circle cx="70" cy="48" r="3" fill="#1B604A"/>
                                <circle cx="130" cy="35" r="3" fill="#1B604A"/>
                                <circle cx="195" cy="20" r="3" fill="#1B604A"/>
                            </svg>
                        </div>
                        <div class="flex justify-between text-[11px] font-semibold text-sage mt-2 tabular">
                            <span>Q1 '25</span>
                            <span>Q2 '25</span>
                            <span>Q3 '25</span>
                            <span>Q4 '25</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right (3 cols): Top Industries --}}
            <div class="lg:col-span-3 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-forest/5 pb-3">
                        <h2 class="font-display text-base font-bold text-forest">Top Industries</h2>
                        <a href="#industry-benchmarks" class="text-xs font-semibold text-emerald hover:text-forest transition">View All &rarr;</a>
                    </div>

                    <div class="mt-4 space-y-3.5">
                        @foreach ($topIndustries as $ind)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="grid size-8 place-items-center rounded-xl bg-forest/5 text-forest shrink-0">
                                        @if ($ind['icon'] === 'plus')
                                            <svg class="size-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        @elseif ($ind['icon'] === 'hardhat')
                                            <svg class="size-4 text-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        @elseif ($ind['icon'] === 'briefcase')
                                            <svg class="size-4 text-forest" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        @elseif ($ind['icon'] === 'factory')
                                            <svg class="size-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        @elseif ($ind['icon'] === 'cart')
                                            <svg class="size-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        @endif
                                    </div>
                                    <span class="font-medium text-forest">{{ $ind['name'] }}</span>
                                </div>
                                <span class="font-bold text-forest tabular">{{ $ind['share'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Bottom 3 Modules Row: Regional Insights + Market Overview + Key Insights --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            {{-- Module 1 (Left, 5 cols): Regional Insights --}}
            <div class="lg:col-span-5 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="font-display text-base font-bold text-forest">Regional Insights</h2>

                    {{-- Tabs --}}
                    <div class="mt-3 flex flex-wrap gap-1.5 text-xs font-semibold">
                        <button type="button" class="rounded-full bg-forest text-ivory px-3 py-1.5 shadow-sm">Population Growth</button>
                        <button type="button" class="rounded-full bg-white text-forest/70 border border-forest/10 px-3 py-1.5 hover:border-gold transition">Housing Permits</button>
                        <button type="button" class="rounded-full bg-white text-forest/70 border border-forest/10 px-3 py-1.5 hover:border-gold transition">Business Density</button>
                        <button type="button" class="rounded-full bg-white text-forest/70 border border-forest/10 px-3 py-1.5 hover:border-gold transition">SBA Lending</button>
                    </div>

                    {{-- Map & Highlight Block --}}
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div class="rounded-2xl overflow-hidden bg-emerald/5 p-2 border border-forest/5">
                            <img
                                src="{{ asset('images/market/regional-map.png') }}"
                                alt="Regional map shaded by growth"
                                class="h-36 w-full object-contain"
                            >
                        </div>
                        <div class="space-y-2 text-xs">
                            <p class="text-sage font-medium">U.S. Population Growth</p>
                            <p class="font-display text-2xl font-bold text-forest tabular">+0.8%</p>
                            <p class="text-sage text-[11px]">(Last 12 Months)</p>
                            <div class="rounded-xl bg-forest/5 p-2.5 text-[11px] text-forest font-medium">
                                Strong growth in Sun Belt and Mountain West regions.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Module 2 (Center, 4 cols): Market Overview --}}
            <div class="lg:col-span-4 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-forest/5 pb-3">
                        <h2 class="font-display text-base font-bold text-forest">Market Overview</h2>
                        <a href="#full-report" class="text-xs font-semibold text-emerald hover:text-forest transition">View Full Report &rarr;</a>
                    </div>

                    <div class="mt-4 flex gap-4 items-center">
                        <div class="size-24 rounded-2xl overflow-hidden shrink-0 border border-forest/10 bg-forest-deep">
                            <img
                                src="{{ asset('images/market/city-skyline.png') }}"
                                alt="Modern city skyline"
                                class="size-full object-cover"
                            >
                        </div>
                        <div class="space-y-1.5">
                            <h3 class="font-display text-sm font-bold text-forest leading-snug">
                                A Healthy Market for Business Acquisitions
                            </h3>
                            <p class="text-xs text-ink/75 leading-relaxed line-clamp-3">
                                Business acquisition activity remains strong, with increasing opportunities across key industries and regions.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button
                        type="button"
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-full border border-forest/20 py-2.5 text-xs font-bold text-forest hover:bg-forest hover:text-ivory transition"
                    >
                        <span>Read Market Report</span>
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </div>

            {{-- Module 3 (Right, 3 cols): Key Insights --}}
            <div class="lg:col-span-3 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 border-b border-forest/5 pb-3">
                        <span class="text-gold font-bold">&starf;</span>
                        <h2 class="font-display text-base font-bold text-forest">Key Insights</h2>
                    </div>

                    <ul class="mt-4 space-y-3.5 text-xs text-ink/85">
                        @foreach ($keyInsights as $idx => $insight)
                            <li class="flex items-start gap-3">
                                <div class="grid size-7 place-items-center rounded-lg bg-emerald/10 text-emerald shrink-0 mt-0.5">
                                    @if ($idx === 0)
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    @elseif ($idx === 1)
                                        <svg class="size-4 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    @else
                                        <svg class="size-4 text-emerald" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                                    @endif
                                </div>
                                <span class="leading-relaxed">{{ $insight }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        @include('dashboard.partials.industry-benchmarks', ['benchmarks' => $benchmarks])
    </div>
</x-layouts.dashboard>
