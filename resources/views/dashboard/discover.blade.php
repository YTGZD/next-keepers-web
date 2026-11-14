<x-layouts.dashboard title="Discover Businesses | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        activeTab: 'all',
        sortBy: 'best_match',
        viewMode: 'list',
        selectedMapLayer: 'density',
        industries: ['Healthcare', 'HVAC', 'Restoration'],
        removeIndustry(name) {
            this.industries = this.industries.filter(i => i !== name);
        },
        savedCards: {{ \Illuminate\Support\Js::from(collect($savedIds ?? [])->mapWithKeys(fn($id) => [$id => true])->all()) }},
        toggleSave(id) {
            this.savedCards[id] = !this.savedCards[id];
            fetch('/watchlist/toggle/' + id, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                }
            });
        }
    }">
        {{-- Hero Header Banner with Mountain Panorama --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-forest-deep text-ivory shadow-md min-h-[10rem] p-6 sm:p-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
            {{-- Background Mountain Panorama --}}
            <img
                src="{{ asset('images/dashboard/mountain-clean.jpg') }}"
                alt="Snow-capped mountains"
                class="absolute inset-0 size-full object-cover object-center opacity-30 mix-blend-luminosity -z-10"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-forest-deep/95 via-forest-deep/80 to-forest-deep/85 -z-10"></div>

            {{-- Title & Subtitle --}}
            <div class="relative z-10">
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-gold-soft hover:text-ivory transition mb-2"
                >
                    <span aria-hidden="true">&larr;</span>
                    <span>Back to Dashboard</span>
                </a>
                <h1 class="font-display text-3xl font-medium tracking-tight text-ivory sm:text-4xl">
                    Discover Businesses
                </h1>
                <p class="mt-1.5 text-sm text-ivory/80 max-w-xl">
                    Find and evaluate acquisition opportunities powered by AI and real market data.
                </p>
            </div>

            {{-- 2 Floating Header KPI Cards --}}
            <div class="relative z-10 flex flex-wrap items-center gap-3">
                {{-- Total Opportunities --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-forest-deep/90 backdrop-blur-md px-5 py-3.5 text-ivory shadow-lg min-w-[150px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-forest text-emerald shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-ivory leading-none">
                            {{ number_format($totalOpportunities ?? 0) }}
                        </div>
                        <div class="text-[11px] font-medium text-ivory/70 mt-0.5">Total Opportunities</div>
                    </div>
                </div>

                {{-- Avg Fit Score --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-forest-deep/90 backdrop-blur-md px-5 py-3.5 text-ivory shadow-lg min-w-[150px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-gold/20 text-gold shrink-0">
                        <svg class="size-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-ivory leading-none">
                            {{ $avgFitScore ?? 0 }}
                        </div>
                        <div class="text-[11px] font-medium text-ivory/70 mt-0.5">Avg. Fit Score</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Filter Tabs and Sort Bar --}}
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-1.5"
                >
                    <span class="text-gold">★</span>
                    <span>All Opportunities ({{ $totalOpportunities }})</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'high_fit'"
                    :class="activeTab === 'high_fit' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-1.5"
                >
                    <span class="text-gold">★</span>
                    <span>High Fit ({{ $highFitCount }})</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'new_listings'"
                    :class="activeTab === 'new_listings' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-1.5"
                >
                    <span class="text-amber-500">⚡</span>
                    <span>New Listings</span>
                </button>
                <button
                    type="button"
                    @click="activeTab = 'saved_search'"
                    :class="activeTab === 'saved_search' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-1.5"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    <span>Saved Search</span>
                </button>
            </div>

            {{-- Sort Dropdown --}}
            <div class="flex items-center gap-2 text-xs text-forest/75 self-end sm:self-auto">
                <span class="font-medium text-forest/60">Sort by:</span>
                <div class="relative">
                    <select
                        x-model="sortBy"
                        class="appearance-none rounded-xl border border-forest/15 bg-white py-1.5 pl-3 pr-8 text-xs font-semibold text-forest shadow-sm focus:border-forest focus:outline-none cursor-pointer"
                    >
                        <option value="best_match">Best Match</option>
                        <option value="highest_fit">Highest Fit Score</option>
                        <option value="highest_revenue">Highest Revenue</option>
                        <option value="price_low">Asking Price: Low to High</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column: Refine Your Search Criteria Panel (3.5 cols on desktop) --}}
            <div class="lg:col-span-4 xl:col-span-3 rounded-2xl border border-forest/10 bg-white p-5 shadow-sm space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-forest/10">
                    <div class="flex items-center gap-2 font-display text-base font-bold text-forest-deep">
                        <svg class="size-4.5 text-forest" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Refine Your Search</span>
                    </div>
                    <button type="button" class="text-xs font-semibold text-forest/60 hover:text-emerald transition inline-flex items-center gap-1">
                        <span>Reset All</span>
                        <span>↺</span>
                    </button>
                </div>

                {{-- Industry Filter --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Industry</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>All Industries ✕</option>
                            <option>Healthcare</option>
                            <option>HVAC & Restoration</option>
                            <option>Automotive</option>
                            <option>Manufacturing</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <template x-for="ind in industries" :key="ind">
                            <span class="inline-flex items-center gap-1 rounded-full bg-forest/5 px-2.5 py-1 text-[11px] font-semibold text-forest-deep border border-forest/10">
                                <span x-text="ind"></span>
                                <button type="button" @click="removeIndustry(ind)" class="text-forest/50 hover:text-rose-600">✕</button>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Location Filter --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Location</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>Within 50 miles ✕</option>
                            <option>Within 25 miles</option>
                            <option>Within 100 miles</option>
                            <option>Statewide (NY)</option>
                            <option>Nationwide</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Revenue Range --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Revenue Range</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>$500K – $5M ✕</option>
                            <option>$1M – $3M</option>
                            <option>$3M – $10M</option>
                            <option>$10M+</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- SDE Range --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>SDE Range</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>$200K – $1.5M ✕</option>
                            <option>$300K – $800K</option>
                            <option>$800K – $2M</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Asking Price --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Asking Price</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>$500K – $5M ✕</option>
                            <option>$1M – $2.5M</option>
                            <option>$2.5M – $5M</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Profit Margin --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Profit Margin</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>15%+ ✕</option>
                            <option>20%+</option>
                            <option>25%+</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Employees --}}
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-forest-deep">
                        <svg class="size-3.5 text-forest/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Employees</span>
                    </label>
                    <div class="relative">
                        <select class="w-full appearance-none rounded-xl border border-forest/15 bg-white py-2 pl-3 pr-8 text-xs font-medium text-forest focus:border-forest focus:outline-none">
                            <option>5+ ✕</option>
                            <option>10+</option>
                            <option>25+</option>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Business Type Checkboxes --}}
                <div class="space-y-2 pt-2 border-t border-forest/10">
                    <span class="text-xs font-bold text-forest-deep">Business Type</span>
                    <label class="flex items-center gap-2.5 text-xs text-forest/80 cursor-pointer">
                        <input type="checkbox" checked class="size-4 rounded border-forest/30 text-forest focus:ring-forest cursor-pointer">
                        <span>Profitable Only</span>
                    </label>
                    <label class="flex items-center gap-2.5 text-xs text-forest/80 cursor-pointer">
                        <input type="checkbox" class="size-4 rounded border-forest/30 text-forest focus:ring-forest cursor-pointer">
                        <span>Owner Operated</span>
                    </label>
                </div>

                {{-- Update Results Button --}}
                <button
                    type="button"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gold py-2.5 text-xs font-bold text-forest-deep shadow-md hover:bg-gold-soft transition cursor-pointer"
                >
                    <svg class="size-4 text-forest-deep" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <span>Update Results</span>
                </button>
            </div>

            {{-- Right Column: Map, Market Insights & Opportunity Cards (8.5 cols on desktop) --}}
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                {{-- Top Visual Row: Activity Map + Market Insights Card --}}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
                    {{-- Map Card (7 cols on xl) --}}
                    <div class="xl:col-span-7 relative overflow-hidden rounded-2xl border border-forest/15 bg-forest-deep text-ivory p-4 shadow-sm min-h-[260px] flex flex-col justify-between">
                        {{-- Background Map Image --}}
                        <img
                            src="{{ asset('images/discovery/discovery-map.png') }}"
                            alt="U.S. Business Activity Map"
                            class="absolute inset-0 size-full object-cover object-center opacity-85"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-forest-deep/90 via-transparent to-forest-deep/40 pointer-events-none"></div>

                        {{-- Floating Tooltip on New York --}}
                        <div class="relative z-10 self-end mr-8 mt-2 rounded-xl border border-ivory/30 bg-white/95 backdrop-blur-md px-3.5 py-2 text-ink shadow-xl">
                            <div class="text-xs font-bold text-forest-deep leading-tight">New York</div>
                            <div class="text-[11px] font-medium text-emerald leading-tight">Sample region &middot; illustrative</div>
                        </div>

                        {{-- Right Layer Buttons on Map --}}
                        <div class="relative z-10 self-end flex flex-col gap-1.5 mt-auto">
                            <button
                                type="button"
                                @click="selectedMapLayer = 'density'"
                                :class="selectedMapLayer === 'density' ? 'bg-gold text-forest-deep font-bold shadow' : 'bg-forest-deep/80 text-ivory/80 hover:text-ivory border border-ivory/20'"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] backdrop-blur-md transition"
                            >
                                <span>📊</span>
                                <span>Business Density</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedMapLayer = 'population'"
                                :class="selectedMapLayer === 'population' ? 'bg-gold text-forest-deep font-bold shadow' : 'bg-forest-deep/80 text-ivory/80 hover:text-ivory border border-ivory/20'"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] backdrop-blur-md transition"
                            >
                                <span>👥</span>
                                <span>Population Growth</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedMapLayer = 'housing'"
                                :class="selectedMapLayer === 'housing' ? 'bg-gold text-forest-deep font-bold shadow' : 'bg-forest-deep/80 text-ivory/80 hover:text-ivory border border-ivory/20'"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] backdrop-blur-md transition"
                            >
                                <span>🏠</span>
                                <span>Housing Permits</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedMapLayer = 'sba'"
                                :class="selectedMapLayer === 'sba' ? 'bg-gold text-forest-deep font-bold shadow' : 'bg-forest-deep/80 text-ivory/80 hover:text-ivory border border-ivory/20'"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] backdrop-blur-md transition"
                            >
                                <span>🏛️</span>
                                <span>SBA Lending</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedMapLayer = 'industry'"
                                :class="selectedMapLayer === 'industry' ? 'bg-gold text-forest-deep font-bold shadow' : 'bg-forest-deep/80 text-ivory/80 hover:text-ivory border border-ivory/20'"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] backdrop-blur-md transition"
                            >
                                <span>🥧</span>
                                <span>Industry Concentration</span>
                            </button>
                        </div>
                    </div>

                    {{-- Market Insights Card (5 cols on xl) --}}
                    <div class="xl:col-span-5 rounded-2xl border border-forest/10 bg-white p-5 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-amber-500 text-lg">💡</span>
                                    <h3 class="font-display text-base font-bold text-forest-deep">Market Insights</h3>
                                </div>
                                <a href="{{ route('market.intelligence') }}" class="text-xs font-semibold text-forest/70 hover:text-emerald transition inline-flex items-center gap-1">
                                    <span>View Full Report</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                            {{-- Mini Bar Chart Graphic --}}
                            <div class="mt-4 flex items-end gap-1.5 h-16 pt-2 pb-1 border-b border-forest/10">
                                <div class="flex-1 bg-emerald/30 rounded-t h-[25%]"></div>
                                <div class="flex-1 bg-emerald/40 rounded-t h-[35%]"></div>
                                <div class="flex-1 bg-emerald/50 rounded-t h-[50%]"></div>
                                <div class="flex-1 bg-emerald/60 rounded-t h-[65%]"></div>
                                <div class="flex-1 bg-emerald/75 rounded-t h-[80%]"></div>
                                <div class="flex-1 bg-emerald rounded-t h-[95%]"></div>
                            </div>

                            {{-- Bullet Points --}}
                            <ul class="mt-4 space-y-2.5 text-xs text-forest/80">
                                <li class="flex items-start gap-2">
                                    <span class="grid size-4.5 place-items-center rounded-full bg-emerald/15 text-emerald shrink-0 mt-0.5 text-[11px] font-bold">✓</span>
                                    <span>Strong business growth in the Northeast</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="grid size-4.5 place-items-center rounded-full bg-emerald/15 text-emerald shrink-0 mt-0.5 text-[11px] font-bold">✓</span>
                                    <span>High demand for healthcare and home services</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="grid size-4.5 place-items-center rounded-full bg-emerald/15 text-emerald shrink-0 mt-0.5 text-[11px] font-bold">✓</span>
                                    <span>Attractive acquisition multiples: 3.5x – 5.0x</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Results Bar: Showing count, View Mode Toggle, Pagination --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1 text-xs text-forest/70">
                    <div class="font-medium">
                        Showing <span class="font-semibold text-forest-deep">{{ count($businesses) }}</span> of <span class="font-semibold text-forest-deep">{{ $totalOpportunities }}</span> sample listings
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- View Toggle (List vs Map) --}}
                        <div class="inline-flex rounded-xl border border-forest/15 bg-white p-1 shadow-sm">
                            <button
                                type="button"
                                @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-forest-deep text-ivory font-semibold shadow' : 'text-forest/75 hover:text-forest'"
                                class="rounded-lg px-3 py-1 transition inline-flex items-center gap-1.5"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span>List</span>
                            </button>
                            <button
                                type="button"
                                @click="viewMode = 'map'"
                                :class="viewMode === 'map' ? 'bg-forest-deep text-ivory font-semibold shadow' : 'text-forest/75 hover:text-forest'"
                                class="rounded-lg px-3 py-1 transition inline-flex items-center gap-1.5"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Map</span>
                            </button>
                        </div>

                    </div>
                </div>

                {{-- Opportunity Cards List --}}
                <div class="space-y-4">
                    @foreach ($businesses as $biz)
                        <div class="group relative overflow-hidden rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-forest/20 transition-all flex flex-col xl:flex-row xl:items-center justify-between gap-5">
                            {{-- Left Block: Thumbnail & Details --}}
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                {{-- Thumbnail --}}
                                <div class="relative h-20 w-36 sm:h-24 sm:w-44 shrink-0 overflow-hidden rounded-xl border border-forest/10 bg-forest/5">
                                    <img
                                        src="{{ asset($biz['image']) }}"
                                        alt="{{ $biz['name'] }}"
                                        class="size-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                                    >
                                </div>

                                {{-- Details --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <a
                                            href="{{ route('business.show', ['id' => $biz['id']]) }}"
                                            class="font-display text-base sm:text-lg font-bold text-forest-deep hover:text-emerald transition truncate"
                                        >
                                            {{ $biz['name'] }}
                                        </a>
                                        @if (!empty($biz['verified']))
                                            <span class="grid size-4 place-items-center rounded-full bg-teal-600 text-white text-[10px] font-bold" title="Verified Listing">
                                                ✓
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Meta line --}}
                                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-forest/70">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span>{{ $biz['industry'] }}</span>
                                        </span>
                                        <span class="text-forest/30">•</span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $biz['location'] }}</span>
                                        </span>
                                    </div>

                                    <p class="mt-1 text-xs text-sage line-clamp-1 max-w-xl">
                                        {{ $biz['description'] }}
                                    </p>

                                    {{-- Tag Pills --}}
                                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                                        @foreach ($biz['tags'] as $tag)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-800 border border-emerald-200/60">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Financials (4 Columns: Annual Revenue, SDE, Asking Price, Employees) --}}
                            <div class="flex items-center justify-between sm:justify-start sm:gap-5 border-y sm:border-y-0 sm:border-x border-forest/10 py-3 sm:py-0 sm:px-5 shrink-0">
                                <div class="text-left sm:text-center min-w-[75px]">
                                    <div class="font-display text-sm sm:text-base font-bold text-forest-deep">{{ $biz['revenue'] }}</div>
                                    <div class="text-[10px] font-medium text-forest/60">Annual Revenue</div>
                                </div>
                                <div class="text-left sm:text-center min-w-[65px]">
                                    <div class="font-display text-sm sm:text-base font-bold text-forest-deep">{{ $biz['sde'] }}</div>
                                    <div class="text-[10px] font-medium text-forest/60">SDE</div>
                                </div>
                                <div class="text-left sm:text-center min-w-[75px]">
                                    <div class="font-display text-sm sm:text-base font-bold text-forest-deep">{{ $biz['asking_price'] }}</div>
                                    <div class="text-[10px] font-medium text-forest/60">Asking Price</div>
                                </div>
                                <div class="text-left sm:text-center min-w-[55px]">
                                    <div class="font-display text-sm sm:text-base font-bold text-forest-deep">{{ $biz['employees'] }}</div>
                                    <div class="text-[10px] font-medium text-forest/60">Employees</div>
                                </div>
                            </div>

                            {{-- Circular Fit Score Ring --}}
                            <div class="flex items-center justify-center shrink-0">
                                <div class="relative size-16 grid place-items-center">
                                    <svg class="size-16 -rotate-90" viewBox="0 0 64 64">
                                        <circle cx="32" cy="32" r="26" stroke="currentColor" stroke-width="4.5" class="text-emerald/15" fill="none" />
                                        <circle
                                            cx="32"
                                            cy="32"
                                            r="26"
                                            stroke="currentColor"
                                            stroke-width="4.5"
                                            stroke-linecap="round"
                                            class="text-emerald"
                                            fill="none"
                                            stroke-dasharray="163.3"
                                            stroke-dashoffset="{{ 163.3 * (1 - ($biz['fit_score'] / 100)) }}"
                                        />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                        <span class="font-display text-lg font-bold leading-none text-forest-deep">{{ $biz['fit_score'] }}</span>
                                        <span class="text-[8px] font-semibold text-forest/60 leading-tight">Fit Score</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-row xl:flex-col items-center xl:items-end justify-between xl:justify-center gap-2 shrink-0">
                                <a
                                    href="{{ route('business.show', ['id' => $biz['id']]) }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-forest-deep px-4 py-2 text-xs font-semibold text-ivory hover:bg-forest transition shadow-sm"
                                >
                                    <span>View Details</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                                <button
                                    type="button"
                                    @click="toggleSave({{ $biz['id'] }})"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-forest/20 px-4 py-2 text-xs font-medium text-forest/75 hover:bg-forest/5 transition"
                                >
                                    <svg
                                        class="size-3.5"
                                        :class="savedCards[{{ $biz['id'] }}] ? 'fill-gold text-gold' : 'text-forest/60'"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span x-text="savedCards[{{ $biz['id'] }}] ? 'Saved' : 'Save'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
