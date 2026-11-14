<x-layouts.dashboard title="Next Keepers: Acquisition Intelligence Dashboard">
    <div class="space-y-6 max-w-7xl mx-auto">
        {{-- Top 4 KPI Metric Cards --}}
        @include('dashboard.partials.kpi-cards')

        {{-- Main Dashboard Layout Grid --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Main Left Column (8 cols): Market Overview Map, Buy Box Banner & Top Opportunities --}}
            <div class="space-y-6 lg:col-span-8">
                @include('dashboard.partials.market-overview')

                {{-- Prominent Buy Box Callout Banner --}}
                <a
                    href="{{ route('buybox') }}"
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-3xl border border-gold/40 bg-gradient-to-r from-forest via-forest-deep to-emerald p-5 text-ivory shadow-lg cursor-pointer hover:border-gold hover:shadow-xl transition block"
                >
                    <div class="flex items-center gap-4">
                        <div class="grid size-12 place-items-center rounded-2xl bg-gold/20 text-gold shrink-0 border border-gold/30">
                            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8" stroke-width="2"/>
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                                <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-display text-base font-bold text-gold">My Buy Box is Active</span>
                                <span class="rounded-full bg-emerald px-2 py-0.5 text-[10px] font-bold text-ivory">{{ $matchCount }} {{ \Illuminate\Support\Str::plural('Match', $matchCount) }}</span>
                            </div>
                            <p class="text-xs text-ivory/80 mt-1">HVAC, Restoration, Healthcare in NY + 100mi &bull; Revenue $500K-$3M &bull; Margin &ge;15%</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-gold px-5 py-2.5 text-xs font-bold text-forest-deep shadow hover:bg-gold-soft transition shrink-0">
                        <span>Open Buy Box &amp; Filter Results</span>
                        <span aria-hidden="true">&rarr;</span>
                    </span>
                </a>

                @include('dashboard.partials.opportunities')
            </div>

            {{-- Right Column (4 cols): Key Insights, Philosophy, and Stay Informed --}}
            <div class="space-y-6 lg:col-span-4">
                @include('dashboard.partials.key-insights')
                @include('dashboard.partials.philosophy-card')
                @include('dashboard.partials.stay-informed')
            </div>
        </div>
    </div>
</x-layouts.dashboard>
