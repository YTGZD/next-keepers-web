<x-layouts.dashboard title="My Watchlist | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        activeTab: '{{ $filter ?? 'all' }}',
        sortBy: '{{ $sortBy ?? 'recently_added' }}',
        selectedItems: [1, 2, 3, 4, 5],
        removedItems: [],
        items: {{ json_encode(array_map(fn($it) => array_merge($it, ['image' => asset($it['image'])]), $items)) }},
        toggleSelect(id) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
            } else {
                this.selectedItems.push(id);
            }
        },
        removeItem(id) {
            this.removedItems.push(id);
            fetch('/watchlist/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                }
            });
        },
        filterMatch(item) {
            if (this.removedItems.includes(item.id)) return false;
            if (this.activeTab === 'all') return true;
            if (this.activeTab === 'high_fit') return item.category === 'high_fit' || item.fit_score >= 90;
            if (this.activeTab === 'recently_added') return item.category === 'recently_added' || item.added_time.includes('days');
            if (this.activeTab === 'alerts') return item.category === 'alerts' || item.tag === 'Alert';
            return true;
        }
    }">
        {{-- Hero Header Banner with Mountain Panorama & KPI Row --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-gradient-to-r from-forest-deep/95 via-forest/80 to-forest-deep/90 text-ivory shadow-md min-h-[9.5rem] p-6 sm:p-8 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
            {{-- Background Mountain Panorama --}}
            <img
                src="{{ asset('images/dashboard/mountain-clean.jpg') }}"
                alt="Snow-capped mountains"
                class="absolute inset-0 size-full object-cover object-center opacity-30 mix-blend-luminosity -z-10"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-forest-deep/90 via-forest-deep/70 to-forest-deep/85 -z-10"></div>

            {{-- Title & Subtitle --}}
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-xl bg-gold/20 text-gold shadow-sm">
                        <svg class="size-5 fill-current" viewBox="0 0 24 24">
                            <path d="M5 3a2 2 0 00-2 2v16l9-4 9 4V5a2 2 0 00-2-2H5z"/>
                        </svg>
                    </span>
                    <h1 class="font-display text-3xl font-medium tracking-tight text-ivory sm:text-4xl">
                        My Watchlist
                    </h1>
                </div>
                <p class="mt-2 text-sm text-ivory/85 max-w-xl">
                    Your saved acquisition opportunities, ready for review and analysis.
                </p>
            </div>

            {{-- 4 KPI Metric Cards in Header --}}
            <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-3">
                {{-- Saved Businesses --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-forest/10 text-forest shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">
                            {{ $metrics['saved_businesses'] ?? 7 }}
                        </div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Saved Businesses</div>
                    </div>
                </div>

                {{-- Avg Fit Score --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-gold/15 text-gold shrink-0">
                        <svg class="size-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">
                            {{ $metrics['avg_fit_score'] ?? '89%' }}
                        </div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Avg. Fit Score</div>
                    </div>
                </div>

                {{-- Total Value --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-emerald/15 text-emerald shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-forest-deep leading-none">
                            {{ $metrics['total_value'] ?? '$8.7M' }}
                        </div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">Total Value</div>
                    </div>
                </div>

                {{-- vs last month --}}
                <div class="flex items-center gap-3 rounded-2xl border border-ivory/20 bg-white/95 backdrop-blur-md p-3.5 text-ink shadow-lg min-w-[130px]">
                    <div class="grid size-10 place-items-center rounded-xl bg-emerald/15 text-emerald shrink-0">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display text-2xl font-bold tracking-tight text-emerald leading-none">
                            {{ $metrics['growth_rate'] ?? '+12%' }}
                        </div>
                        <div class="text-[11px] font-medium text-forest-deep/60 mt-0.5">vs. last month</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Filter Tabs and Sort Bar --}}
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                >
                    All (7)
                </button>
                <button
                    type="button"
                    @click="activeTab = 'high_fit'"
                    :class="activeTab === 'high_fit' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                >
                    High Fit (4)
                </button>
                <button
                    type="button"
                    @click="activeTab = 'recently_added'"
                    :class="activeTab === 'recently_added' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                >
                    Recently Added (3)
                </button>
                <button
                    type="button"
                    @click="activeTab = 'alerts'"
                    :class="activeTab === 'alerts' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                    class="rounded-full px-4 py-2 text-xs font-semibold transition"
                >
                    Alerts (2)
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
                        <option value="recently_added">Recently Added</option>
                        <option value="fit_score">Highest Fit Score</option>
                        <option value="revenue">Highest Revenue</option>
                        <option value="price_low">Asking Price: Low to High</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </section>

        {{-- Watchlist Items List --}}
        <section class="space-y-4">
            <template x-for="item in items" :key="item.id">
                <div
                    x-show="filterMatch(item)"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="group relative overflow-hidden rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-forest/20 transition-all flex flex-col xl:flex-row xl:items-center justify-between gap-5"
                >
                    {{-- Left Block: Checkbox, Thumbnail & Details --}}
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        {{-- Select Checkbox --}}
                        <div class="pt-1">
                            <input
                                type="checkbox"
                                :checked="selectedItems.includes(item.id)"
                                @change="toggleSelect(item.id)"
                                class="size-4 rounded border-forest/30 text-forest-deep focus:ring-forest focus:ring-offset-0 cursor-pointer"
                            >
                        </div>

                        {{-- Business Image Thumbnail with Star Overlay --}}
                        <div class="relative h-20 w-36 sm:h-24 sm:w-44 shrink-0 overflow-hidden rounded-xl border border-forest/10 bg-forest/5">
                            <img
                                :src="item.image"
                                :alt="item.name"
                                class="size-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                            >
                            {{-- Star Badge Overlay --}}
                            <span class="absolute top-1.5 right-1.5 grid size-5 place-items-center rounded-full bg-forest-deep/60 backdrop-blur-sm text-gold text-xs shadow">
                                ★
                            </span>
                        </div>

                        {{-- Metadata & Descriptions --}}
                        <div class="min-w-0 flex-1">
                            {{-- Tag Pill --}}
                            <div class="flex items-center gap-2">
                                <template x-if="item.tag_type === 'green'">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200">
                                        <span>★</span>
                                        <span x-text="item.tag"></span>
                                    </span>
                                </template>
                                <template x-if="item.tag_type === 'amber'">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 border border-amber-200">
                                        <span>⚡</span>
                                        <span x-text="item.tag"></span>
                                    </span>
                                </template>
                                <template x-if="item.tag_type === 'teal'">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-semibold text-teal-700 border border-teal-200">
                                        <span>🔖</span>
                                        <span x-text="item.tag"></span>
                                    </span>
                                </template>
                                <template x-if="item.tag_type === 'orange'">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-[11px] font-semibold text-orange-700 border border-orange-200">
                                        <span>🔔</span>
                                        <span x-text="item.tag"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <a
                                :href="'/business/' + item.id"
                                class="mt-1 block font-display text-base sm:text-lg font-bold text-forest-deep hover:text-emerald transition truncate"
                                x-text="item.name"
                            ></a>

                            {{-- Meta Line (Location & Industry) --}}
                            <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-forest/70">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span x-text="item.location"></span>
                                </span>
                                <span class="text-forest/30">|</span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="size-3.5 text-forest/50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span x-text="item.industry"></span>
                                </span>
                            </div>

                            {{-- Description --}}
                            <p class="mt-1 text-xs text-sage line-clamp-1 max-w-xl" x-text="item.description"></p>
                        </div>
                    </div>

                    {{-- Middle Block: Financial Metrics --}}
                    <div class="flex items-center justify-between sm:justify-start sm:gap-6 border-y sm:border-y-0 sm:border-x border-forest/10 py-3 sm:py-0 sm:px-6 shrink-0">
                        <div class="text-left sm:text-center min-w-[75px]">
                            <div class="font-display text-base sm:text-lg font-bold text-forest-deep" x-text="item.revenue"></div>
                            <div class="text-[11px] font-medium text-forest/60">Revenue</div>
                        </div>
                        <div class="text-left sm:text-center min-w-[75px]">
                            <div class="font-display text-base sm:text-lg font-bold text-forest-deep" x-text="item.sde"></div>
                            <div class="text-[11px] font-medium text-forest/60">SDE</div>
                        </div>
                        <div class="text-left sm:text-center min-w-[85px]">
                            <div class="font-display text-base sm:text-lg font-bold text-forest-deep" x-text="item.asking_price"></div>
                            <div class="text-[11px] font-medium text-forest/60">Asking Price</div>
                        </div>
                    </div>

                    {{-- Circular Fit Score Progress Ring --}}
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
                                    :stroke-dasharray="163.3"
                                    :stroke-dashoffset="163.3 * (1 - (item.fit_score / 100))"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="font-display text-lg font-bold leading-none text-forest-deep" x-text="item.fit_score"></span>
                                <span class="text-[8px] font-semibold text-forest/60 leading-tight">Fit Score</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Block: Time Ago & Action Buttons --}}
                    <div class="flex flex-row xl:flex-col items-center xl:items-end justify-between xl:justify-center gap-2 shrink-0">
                        <span class="text-[11px] text-forest/60" x-text="item.added_time"></span>

                        <div class="flex items-center gap-2">
                            <a
                                :href="'/business/' + item.id"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-forest-deep px-4 py-2 text-xs font-semibold text-ivory hover:bg-forest transition shadow-sm"
                            >
                                <span>View Details</span>
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                            <button
                                type="button"
                                @click="removeItem(item.id)"
                                class="inline-flex items-center justify-center gap-1 rounded-xl border border-forest/20 px-3 py-2 text-xs font-medium text-forest/75 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition"
                                title="Remove from Watchlist"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Remove</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </section>

        {{-- Pagination & Results Footer --}}
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 text-xs text-forest/70">
            <div>
                Showing <span class="font-semibold text-forest-deep">1–5</span> of <span class="font-semibold text-forest-deep">7</span> results
            </div>

            <div class="flex items-center gap-1.5 self-center sm:self-auto">
                <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/15 bg-white text-forest/60 hover:bg-forest/5 hover:text-forest transition">
                    &lt;
                </button>
                <button type="button" class="grid size-8 place-items-center rounded-lg bg-forest-deep font-semibold text-ivory shadow-sm">
                    1
                </button>
                <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/15 bg-white font-medium text-forest hover:bg-forest/5 transition">
                    2
                </button>
                <button type="button" class="grid size-8 place-items-center rounded-lg border border-forest/15 bg-white text-forest/60 hover:bg-forest/5 hover:text-forest transition">
                    &gt;
                </button>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <span>Show:</span>
                <select class="rounded-lg border border-forest/15 bg-white px-2.5 py-1 text-xs font-semibold text-forest focus:outline-none cursor-pointer">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                </select>
            </div>
        </section>
    </div>
</x-layouts.dashboard>
