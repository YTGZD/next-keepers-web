<div id="market-overview" class="relative isolate overflow-hidden rounded-3xl bg-forest-deep p-6 text-ivory shadow-lg lg:p-8">
    {{-- Ambient backdrop light --}}
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-emerald/25 blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 h-64 w-64 rounded-full bg-gold/15 blur-3xl"></div>
    </div>

    <div class="grid items-center gap-8 lg:grid-cols-12">
        {{-- Left Content --}}
        <div class="lg:col-span-5 z-10">
            {{-- Eyebrow --}}
            <div class="flex items-center gap-2">
                <span class="h-3.5 w-1 rounded-full bg-gold"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-gold-soft">Market Overview</span>
            </div>

            <h2 class="mt-4 font-display text-3xl font-medium leading-tight tracking-tight sm:text-4xl text-ivory">
                Explore Acquisition Opportunities Across the U.S.
            </h2>

            <p class="mt-3 text-sm leading-relaxed text-ivory/80">
                Real-time market insights to find the best opportunities.
            </p>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-gold px-6 py-3 text-xs font-bold text-forest-deep shadow-md shadow-gold/20 transition hover:bg-gold-soft"
                >
                    <span>View Market Insights</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>

            {{-- Activity Legend --}}
            <div class="mt-8 flex flex-wrap items-center gap-5 text-xs text-ivory/80">
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-gold shadow-[0_0_8px_rgba(201,155,67,0.8)]"></span>
                    <span>High Activity</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full bg-emerald shadow-[0_0_8px_rgba(27,96,74,0.8)]"></span>
                    <span>Moderate Activity</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-2.5 rounded-full border-2 border-ivory/60 bg-transparent"></span>
                    <span>Growing Market</span>
                </div>
            </div>
        </div>

        {{-- Right: Illuminated US Map & Nationwide pill --}}
        <div class="relative lg:col-span-7">
            {{-- Top-right Nationwide Pill --}}
            <div class="mb-2 flex justify-end">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-full border border-ivory/20 bg-forest/80 px-3.5 py-1.5 text-xs font-semibold text-ivory transition hover:border-gold hover:text-gold"
                >
                    <span>Nationwide</span>
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>

            {{-- Render US Map SVG --}}
            @if ($mapData)
                <div class="relative w-full">
                    <svg
                        viewBox="{{ $mapData['viewBox'] ?? '0 0 960 600' }}"
                        class="h-auto w-full drop-shadow-[0_10px_25px_rgba(0,0,0,0.5)]"
                        role="img"
                        aria-label="Interactive map of U.S. acquisition opportunities"
                    >
                        <defs>
                            <filter id="dash-glow" x="-200%" y="-200%" width="500%" height="500%">
                                <feGaussianBlur stdDeviation="6"/>
                            </filter>
                            <linearGradient id="dash-land" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#1b604a" stop-opacity="0.65"/>
                                <stop offset="1" stop-color="#0e231b" stop-opacity="0.45"/>
                            </linearGradient>
                        </defs>

                        {{-- State Boundaries --}}
                        <path d="{{ $mapData['statesPath'] ?? '' }}" fill="url(#dash-land)"/>
                        <path d="{{ $mapData['bordersPath'] ?? '' }}" fill="none" stroke="#c99b43" stroke-opacity="0.3" stroke-width="0.75" stroke-linejoin="round"/>

                        {{-- Opportunity Glowing Points --}}
                        @foreach (($mapData['points'] ?? []) as $p)
                            @php
                                $r = match ($p['intensity'] ?? 2) {
                                    3 => 3.8,
                                    2 => 2.8,
                                    default => 2.0,
                                };
                            @endphp
                            <g class="cursor-pointer transition-opacity duration-200 hover:opacity-100 opacity-90">
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $r * 2.8 }}" fill="#e3c27d" opacity="0.45" filter="url(#dash-glow)"/>
                                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $r }}" fill="#f8e7ba"/>
                            </g>
                        @endforeach
                    </svg>
                </div>
            @endif
        </div>
    </div>
</div>
