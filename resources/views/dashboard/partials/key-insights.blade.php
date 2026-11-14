<div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="h-3.5 w-1 rounded-full bg-gold"></span>
            <h3 class="text-xs font-bold uppercase tracking-widest text-forest">Key Insights</h3>
        </div>
        <a href="#all-insights" class="inline-flex items-center gap-1 text-xs font-bold text-forest transition hover:text-emerald">
            <span>View All</span>
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    {{-- Insights List --}}
    <div class="mt-5 space-y-4">
        {{-- Item 1: Business Acquisition Activity --}}
        <div class="flex items-center justify-between border-b border-forest/5 pb-4">
            <div class="flex items-center gap-3.5">
                <div class="grid size-10 shrink-0 place-items-center rounded-2xl bg-emerald/10 text-emerald">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                </div>
                <div>
                    <p class="font-display text-lg font-bold text-forest tabular">+11.2%</p>
                    <p class="text-xs font-medium text-forest/90">Business Acquisition Activity</p>
                    <p class="text-[11px] text-sage">vs. last year</p>
                </div>
            </div>
            {{-- Green Sparkline --}}
            <svg class="h-8 w-20 shrink-0" viewBox="0 0 80 30" fill="none">
                <path d="M2 24 C 15 22, 22 14, 35 18 C 48 22, 58 6, 78 4" stroke="#1B604A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        {{-- Item 2: Housing Permits --}}
        <div class="flex items-center justify-between border-b border-forest/5 pb-4">
            <div class="flex items-center gap-3.5">
                <div class="grid size-10 shrink-0 place-items-center rounded-2xl bg-[#f7edd4] text-gold">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <div>
                    <p class="font-display text-lg font-bold text-forest tabular">+8.7%</p>
                    <p class="text-xs font-medium text-forest/90">Housing Permits</p>
                    <p class="text-[11px] text-sage">vs. last year</p>
                </div>
            </div>
            {{-- Gold Sparkline --}}
            <svg class="h-8 w-20 shrink-0" viewBox="0 0 80 30" fill="none">
                <path d="M2 26 C 18 20, 30 24, 45 14 C 55 8, 65 12, 78 6" stroke="#C99B43" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        {{-- Item 3: SBA Lending --}}
        <div class="flex items-center justify-between pt-1">
            <div class="flex items-center gap-3.5">
                <div class="grid size-10 shrink-0 place-items-center rounded-2xl bg-emerald/10 text-emerald">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="font-display text-lg font-bold text-forest tabular">+6.1%</p>
                    <p class="text-xs font-medium text-forest/90">SBA Lending</p>
                    <p class="text-[11px] text-sage">vs. last year</p>
                </div>
            </div>
            {{-- Green Sparkline --}}
            <svg class="h-8 w-20 shrink-0" viewBox="0 0 80 30" fill="none">
                <path d="M2 22 C 20 22, 35 15, 50 18 C 62 20, 68 8, 78 6" stroke="#1B604A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>
</div>
