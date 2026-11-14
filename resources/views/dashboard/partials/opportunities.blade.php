<div id="opportunities-section" class="mt-8" x-data="{ selectedCategory: 'all' }">
    {{-- Header & View All --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="h-3.5 w-1 rounded-full bg-gold"></span>
            <h3 class="text-xs font-bold uppercase tracking-widest text-forest">Top Acquisition Opportunities</h3>
        </div>
        <a href="#all-results" class="inline-flex items-center gap-1.5 text-xs font-bold text-forest transition hover:text-emerald">
            <span>View All Results</span>
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    {{-- Filter Pills --}}
    <div class="mt-4 flex flex-wrap items-center gap-2" role="tablist" aria-label="Opportunity categories">
        @foreach ($categories as $catKey => $catLabel)
            <button
                type="button"
                @click="selectedCategory = '{{ $catKey }}'"
                :class="selectedCategory === '{{ $catKey }}'
                    ? 'bg-forest text-ivory font-semibold shadow-sm'
                    : 'bg-white/80 text-forest/80 border border-forest/10 hover:border-gold hover:text-forest'"
                class="rounded-full px-4 py-2 text-xs transition duration-150"
            >
                {{ $catLabel }}
            </button>
        @endforeach
    </div>

    {{-- Opportunity Cards List --}}
    <div class="mt-5 space-y-3">
        @foreach ($opportunities as $opp)
            <div
                x-show="selectedCategory === 'all' || selectedCategory === '{{ $opp['category'] }}'"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                @click="window.location.href = '{{ route('business.show', ['id' => $opp['id']]) }}'"
                class="group flex flex-col gap-4 rounded-2xl border border-forest/10 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md hover:border-forest/30 cursor-pointer sm:flex-row sm:items-center sm:justify-between"
            >
                {{-- Left: Thumbnail + Title + Location --}}
                <div class="flex items-center gap-4 min-w-[16rem]">
                    <div class="shrink-0 overflow-hidden rounded-xl border border-forest/10 bg-ivory-deep size-16">
                        <img
                            src="{{ asset($opp['image']) }}"
                            alt="{{ $opp['name'] }}"
                            class="size-full object-cover transition duration-300 group-hover:scale-110"
                            onerror="this.src='{{ asset('images/key.jpg') }}'"
                        >
                    </div>
                    <div>
                        <h4 class="font-display text-base font-semibold text-forest group-hover:text-emerald transition">
                            {{ $opp['name'] }}
                        </h4>
                        <p class="mt-1 flex items-center gap-1 text-xs text-sage">
                            <svg class="size-3.5 text-sage shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $opp['location'] }}</span>
                        </p>
                    </div>
                </div>

                {{-- Middle: Financial Metrics Grid --}}
                <div class="grid grid-cols-3 gap-6 sm:gap-8 text-left tabular">
                    <div>
                        <p class="text-[11px] font-medium text-sage">Revenue</p>
                        <p class="mt-0.5 font-semibold text-forest text-sm">{{ $opp['revenue'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-sage">SDE</p>
                        <p class="mt-0.5 font-semibold text-forest text-sm">{{ $opp['sde'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-sage">Asking Price</p>
                        <p class="mt-0.5 font-semibold text-forest text-sm">{{ $opp['asking'] }}</p>
                    </div>
                </div>

                {{-- Right: Fit Score Badge + Action CTA --}}
                <div class="flex items-center justify-between sm:justify-end gap-5">
                    {{-- Fit Score Pill --}}
                    <div class="flex flex-col items-center justify-center rounded-2xl bg-emerald/10 px-4 py-2 text-center min-w-[4.5rem]">
                        <span class="font-display text-xl font-bold text-emerald tabular">{{ $opp['fit_score'] }}</span>
                        <span class="text-[10px] font-semibold text-emerald">Fit Score</span>
                    </div>

                    {{-- View Details CTA --}}
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-forest px-4 py-2.5 text-xs font-semibold text-ivory shadow-sm transition group-hover:bg-emerald"
                    >
                        <span>View Details</span>
                        <span aria-hidden="true">&rarr;</span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
