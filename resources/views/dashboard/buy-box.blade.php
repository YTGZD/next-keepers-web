<x-layouts.dashboard title="My Buy Box | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        activeTab: 'all',
        sortBy: 'best_match',
        industries: ['HVAC', 'Restoration', 'Healthcare'],
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
        },
        saveBuyBoxCriteria() {
            fetch('{{ route('buybox.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({ industries: this.industries })
            });
        }
    }">
        {{-- Hero Header Banner with Mountain Panorama --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-forest-deep text-ivory shadow-sm min-h-[11rem]">
            {{-- Background Mountain Panorama --}}
            <img
                src="{{ asset('images/dashboard/mountain-clean.jpg') }}"
                alt="Snow-capped mountains"
                class="absolute inset-0 size-full object-cover object-center opacity-40 mix-blend-luminosity -z-10"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-forest-deep via-forest-deep/80 to-transparent -z-10"></div>

            <div class="p-6 sm:p-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-gold-soft hover:text-ivory transition"
                    >
                        <span aria-hidden="true">&larr;</span>
                        <span>Back to Dashboard</span>
                    </a>

                    <h1 class="mt-2 font-display text-3xl font-medium tracking-tight text-ivory sm:text-4xl">
                        My Buy Box
                    </h1>
                    <p class="mt-1.5 text-sm text-ivory/80 max-w-xl">
                        Your personalized acquisition criteria to find the perfect opportunity.
                    </p>
                </div>

                {{-- Floating Matching Businesses KPI Card --}}
                <div class="flex items-center gap-4 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-4 text-ink shadow-xl shrink-0">
                    <div class="grid size-12 place-items-center rounded-2xl bg-emerald/15 text-emerald">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8" stroke-width="2"/>
                            <circle cx="12" cy="12" r="3" stroke-width="2"/>
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-display text-2xl font-bold text-forest tabular">{{ $analytics['total_matches'] }}</span>
                        </div>
                        <p class="text-xs text-sage mt-0.5 font-medium">Matching Businesses</p>
                    </div>
                    <svg class="h-6 w-16 shrink-0" viewBox="0 0 64 24" fill="none">
                        <path d="M2 18 C 14 16, 20 8, 32 14 C 44 20, 52 4, 62 6" stroke="#1B604A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- Main 2-Column Layout (Left Criteria Filter Sidebar + Right Matches Content) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column: Your Criteria (4 cols) --}}
            <aside class="lg:col-span-4 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm space-y-5">
                {{-- Criteria Header --}}
                <div class="flex items-center justify-between border-b border-forest/5 pb-4">
                    <h2 class="font-display text-base font-bold text-forest">Your Criteria</h2>
                    <button type="button" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald hover:text-forest transition">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit</span>
                    </button>
                </div>

                {{-- Criteria Selectors List --}}
                <div class="space-y-4 text-xs">
                    {{-- Industries Tags --}}
                    <div>
                        <label class="block font-semibold text-forest mb-2">Industries</label>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <template x-for="item in industries" :key="item">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-forest/5 border border-forest/10 px-2.5 py-1 text-xs font-medium text-forest">
                                    <span x-text="item"></span>
                                    <button type="button" @click="removeIndustry(item)" class="text-sage hover:text-forest">&times;</button>
                                </span>
                            </template>
                            <button type="button" class="grid size-6 place-items-center rounded-lg border border-dashed border-forest/20 text-forest/50 hover:border-gold hover:text-gold transition">
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Location --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>Location</span>
                            </span>
                            <span class="text-sage font-medium">{{ $criteria['location'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- Revenue Range --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Revenue Range</span>
                            </span>
                            <span class="text-sage font-medium tabular">{{ $criteria['revenue_range'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- SDE Range --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <span>SDE Range</span>
                            </span>
                            <span class="text-sage font-medium tabular">{{ $criteria['sde_range'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- Asking Price --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <span>Asking Price</span>
                            </span>
                            <span class="text-sage font-medium tabular">{{ $criteria['asking_price'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- Profit Margin --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Profit Margin</span>
                            </span>
                            <span class="text-sage font-medium tabular">{{ $criteria['profit_margin'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- Employees --}}
                    <div class="border-t border-forest/5 pt-3">
                        <div class="flex items-center justify-between py-1 cursor-pointer">
                            <span class="flex items-center gap-2 font-medium text-forest">
                                <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Employees</span>
                            </span>
                            <span class="text-sage font-medium tabular">{{ $criteria['employees'] }} &rsaquo;</span>
                        </div>
                    </div>

                    {{-- Business Type Checkboxes --}}
                    <div class="border-t border-forest/5 pt-3 space-y-2">
                        <label class="block font-semibold text-forest">Business Type</label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="rounded border-forest/20 accent-[#1B604A]">
                            <span class="text-forest">Profitable Only</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="rounded border-forest/20 accent-[#1B604A]">
                            <span class="text-forest">Owner Operated</span>
                        </label>
                    </div>

                    {{-- Additional Preferences Checkboxes --}}
                    <div class="border-t border-forest/5 pt-3 space-y-2">
                        <label class="block font-semibold text-forest">Additional Preferences</label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="rounded border-forest/20 accent-[#1B604A]">
                            <span class="text-forest">Growing Market</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="rounded border-forest/20 accent-[#1B604A]">
                            <span class="text-forest">Absentee Owner OK</span>
                        </label>
                    </div>
                </div>

                {{-- Update Results Button --}}
                <div class="pt-2">
                    <button
                        type="button"
                        @click="saveBuyBoxCriteria()"
                        class="w-full flex items-center justify-center gap-2 rounded-2xl bg-gold py-3 text-xs font-bold text-forest-deep shadow transition hover:bg-gold-soft cursor-pointer"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <span>Update Results</span>
                    </button>
                </div>
            </aside>

            {{-- Right Column: Matches & Analytics (8 cols) --}}
            <div class="lg:col-span-8 space-y-5">
                {{-- Top Match Tabs & Sort --}}
                <div class="flex flex-wrap items-center justify-between gap-4">
                    {{-- Filter Tabs --}}
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <button
                            type="button"
                            @click="activeTab = 'all'"
                            :class="activeTab === 'all' ? 'bg-forest text-ivory shadow-sm' : 'bg-white text-forest/80 border border-forest/10 hover:border-gold'"
                            class="rounded-full px-4 py-2 transition flex items-center gap-1.5"
                        >
                            <span class="text-gold">&starf;</span>
                            <span>All Matches ({{ $analytics['total_matches'] }})</span>
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'high_fit'"
                            :class="activeTab === 'high_fit' ? 'bg-forest text-ivory shadow-sm' : 'bg-white text-forest/80 border border-forest/10 hover:border-gold'"
                            class="rounded-full px-4 py-2 transition flex items-center gap-1.5"
                        >
                            <span>&#127942;</span>
                            <span>High Fit ({{ $analytics['high_fit'] }})</span>
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'new'"
                            :class="activeTab === 'new' ? 'bg-forest text-ivory shadow-sm' : 'bg-white text-forest/80 border border-forest/10 hover:border-gold'"
                            class="rounded-full px-4 py-2 transition flex items-center gap-1.5"
                        >
                            <span class="text-gold">&#9889;</span>
                            <span>New Listings</span>
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'saved'"
                            :class="activeTab === 'saved' ? 'bg-forest text-ivory shadow-sm' : 'bg-white text-forest/80 border border-forest/10 hover:border-gold'"
                            class="rounded-full px-4 py-2 transition flex items-center gap-1.5"
                        >
                            <span>&#128278;</span>
                            <span>Saved Search</span>
                        </button>
                    </div>

                    {{-- Sort Dropdown --}}
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-sage">Sort by:</span>
                        <select x-model="sortBy" class="rounded-xl border border-forest/15 bg-white px-3 py-1.5 font-semibold text-forest shadow-sm focus:border-gold focus:outline-none">
                            <option value="best_match">Best Match</option>
                            <option value="highest_fit">Highest Fit Score</option>
                            <option value="lowest_price">Asking Price: Low to High</option>
                            <option value="highest_revenue">Revenue: High to Low</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>

                {{-- 4 Match Analytics Summary Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
                    {{-- Metric 1: Avg Fit Score --}}
                    <div class="flex items-center gap-3 rounded-2xl border border-forest/10 bg-white p-4 shadow-sm">
                        <div class="relative grid size-12 shrink-0 place-items-center">
                            <svg class="size-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#ebe6d8" stroke-width="10"/>
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#1B604A" stroke-width="10" stroke-dasharray="251.32" stroke-dashoffset="{{ round(251.32 * (1 - $analytics['avg_fit_score'] / 100), 1) }}" stroke-linecap="round"/>
                            </svg>
                            <span class="absolute font-display text-sm font-bold text-forest">{{ $analytics['avg_fit_score'] }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-forest text-xs">Avg. Fit Score</p>
                            
                        </div>
                    </div>

                    {{-- Metric 2: Avg Asking Price --}}
                    <div class="flex items-center gap-3 rounded-2xl border border-forest/10 bg-white p-4 shadow-sm">
                        <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-display text-base font-bold text-forest tabular">{{ $analytics['avg_asking_price'] }}</span>
                            </div>
                            <p class="text-[11px] text-sage">Avg. Asking Price</p>
                        </div>
                    </div>

                    {{-- Metric 3: New This Month --}}
                    <div class="flex items-center gap-3 rounded-2xl border border-forest/10 bg-white p-4 shadow-sm">
                        <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-display text-base font-bold text-forest tabular">{{ $analytics['new_this_month'] }}</span>
                            </div>
                            <p class="text-[11px] text-sage">New This Month</p>
                        </div>
                    </div>

                    {{-- Metric 4: Top Region --}}
                    <div class="flex items-center justify-between rounded-2xl border border-forest/10 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-display text-base font-bold text-forest">{{ $analytics['top_region'] }}</span>
                                <p class="text-[11px] text-sage">Top Region</p>
                            </div>
                        </div>
                        {{-- Small NY State icon outline --}}
                        <svg class="size-7 text-forest/20 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                </div>

                {{-- Matching Business Listings List --}}
                <div class="space-y-4">
                    @foreach ($matches as $opp)
                        <div class="group relative flex flex-col gap-5 rounded-3xl border border-forest/10 bg-white p-5 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:border-forest/30 transition sm:flex-row sm:items-center sm:justify-between">
                            {{-- Left Thumbnail & Info --}}
                            <div class="flex gap-4 items-start sm:items-center flex-1">
                                {{-- Thumbnail with Top Match Badge if applicable --}}
                                <div class="relative shrink-0 overflow-hidden rounded-2xl border border-forest/10 size-24 bg-forest-deep">
                                    <img
                                        src="{{ asset($opp['image']) }}"
                                        alt="{{ $opp['name'] }}"
                                        class="size-full object-cover transition duration-300 group-hover:scale-105"
                                    >
                                    @if ($opp['top_match'])
                                        <span class="absolute top-1.5 left-1.5 rounded-md bg-[#1B604A] px-1.5 py-0.5 text-[9px] font-bold text-ivory flex items-center gap-1 shadow">
                                            <span>Top Match</span>
                                            <span class="text-gold">&starf;</span>
                                        </span>
                                    @endif
                                </div>

                                {{-- Details --}}
                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('business.show', ['id' => $opp['id']]) }}" class="font-display text-base font-bold text-forest hover:text-emerald transition">
                                            {{ $opp['name'] }}
                                        </a>
                                    </div>
                                    <p class="text-xs text-sage flex items-center gap-2">
                                        <span class="flex items-center gap-1">
                                            <svg class="size-3.5 text-sage shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                            <span>{{ $opp['location'] }}</span>
                                        </span>
                                        <span class="text-forest/20">|</span>
                                        <span>Established {{ $opp['established'] }}</span>
                                    </p>
                                    <p class="text-xs text-ink/75 leading-relaxed line-clamp-1">
                                        {{ $opp['description'] }}
                                    </p>

                                    {{-- Financials Strip --}}
                                    <div class="flex items-center gap-5 pt-1 text-xs tabular font-medium">
                                        <div><span class="font-bold text-forest text-sm">{{ $opp['revenue'] }}</span> <span class="text-sage text-[11px] block sm:inline">Revenue</span></div>
                                        <div><span class="font-bold text-forest text-sm">{{ $opp['sde'] }}</span> <span class="text-sage text-[11px] block sm:inline">SDE</span></div>
                                        <div><span class="font-bold text-forest text-sm">{{ $opp['asking_price'] }}</span> <span class="text-sage text-[11px] block sm:inline">Asking Price</span></div>
                                        <div><span class="font-bold text-forest text-sm">{{ $opp['margin'] }}</span> <span class="text-sage text-[11px] block sm:inline">Margin</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Evaluation & Action Block --}}
                            <div class="flex flex-col sm:items-end justify-between gap-3 shrink-0 border-t sm:border-t-0 sm:border-l border-forest/5 pt-3 sm:pt-0 sm:pl-6">
                                <span class="text-[11px] text-sage flex items-center gap-1">
                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Sample listing</span>
                                </span>

                                <div class="flex items-center gap-4">
                                    {{-- Donut Gauge --}}
                                    <div class="relative grid size-16 shrink-0 place-items-center">
                                        <svg class="size-full -rotate-90" viewBox="0 0 100 100">
                                            <circle cx="50" cy="50" r="40" fill="none" stroke="#ebe6d8" stroke-width="9"/>
                                            <circle cx="50" cy="50" r="40" fill="none" stroke="#1B604A" stroke-width="9" stroke-dasharray="251.32" stroke-dashoffset="{{ 251.32 - (251.32 * $opp['fit_score'] / 100) }}" stroke-linecap="round"/>
                                        </svg>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                            <span class="font-display text-base font-bold text-forest tabular">{{ $opp['fit_score'] }}</span>
                                            <span class="text-[8px] font-semibold text-sage -mt-1">Fit Score</span>
                                        </div>
                                    </div>

                                    {{-- Met criteria checklist --}}
                                    <ul class="space-y-1 text-[11px] font-medium text-forest/90">
                                        @foreach ($opp['criteria_met'] as $crit)
                                            <li class="flex items-center gap-1.5">
                                                <svg class="size-3 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span>{{ $crit }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                {{-- Buttons --}}
                                <div class="flex items-center gap-2 pt-1 w-full sm:w-auto">
                                    <a
                                        href="{{ route('business.show', ['id' => $opp['id']]) }}"
                                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-full bg-forest px-4 py-2 text-xs font-semibold text-ivory hover:bg-emerald transition"
                                    >
                                        <span>View Details</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                    <button
                                        type="button"
                                        @click="toggleSave({{ $opp['id'] }})"
                                        :class="savedCards[{{ $opp['id'] }}] ? 'bg-emerald/10 text-emerald border-emerald' : 'bg-white text-forest/80 border-forest/15 hover:border-gold'"
                                        class="inline-flex items-center gap-1 rounded-full border px-3 py-2 text-xs font-semibold transition"
                                    >
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                        <span x-text="savedCards[{{ $opp['id'] }}] ? 'Saved' : 'Save'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Footer --}}
                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-forest/10 pt-4 text-xs">
                    <span class="text-sage">Showing {{ count($matches) }} of {{ $analytics['total_matches'] }} sample listings</span>

                    <div class="flex items-center gap-1">
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">&lsaquo;</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg bg-forest text-ivory font-bold">1</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">2</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">3</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">4</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">5</button>
                        <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/10 bg-white text-forest hover:border-gold">&rsaquo;</button>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sage">Show 10 per page</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
