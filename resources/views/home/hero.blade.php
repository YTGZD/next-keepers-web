<section class="relative isolate overflow-hidden bg-forest-deep text-ivory">
    {{-- ambient light behind the map --}}
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -right-40 top-0 h-[42rem] w-[60rem] rounded-full bg-emerald/30 blur-3xl"></div>
        <div class="absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-gold/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_30%,transparent_30%,#0a1a13_85%)]"></div>
    </div>

    <div class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-20 pt-32 sm:px-8 lg:grid-cols-12 lg:gap-6 lg:pb-28 lg:pt-36">
        <div class="lg:col-span-5">
            <x-eyebrow data-reveal>The future of business acquisition</x-eyebrow>

            <h1 data-reveal style="--reveal-delay: 80ms" class="mt-5 font-display text-5xl font-medium leading-[1.05] tracking-tight sm:text-6xl xl:text-[4rem]">
                Good businesses deserve better <span class="text-gold">discovery.</span>
            </h1>

            <p data-reveal style="--reveal-delay: 160ms" class="mt-6 max-w-lg text-lg leading-relaxed text-ivory/80">
                Next Keepers brings business discovery, financial intelligence, and market insights together to help you find and evaluate your next acquisition.
            </p>

            <div data-reveal style="--reveal-delay: 240ms" class="mt-9 flex flex-wrap items-center gap-4">
                <x-button href="#early-access">Get Early Access <span aria-hidden="true">&rarr;</span></x-button>
                <x-button variant="ghost" href="#platform">Explore the Platform</x-button>
            </div>

            <p data-reveal style="--reveal-delay: 300ms" class="mt-5 text-xs text-ivory/55">Early access is open. We're building in public and will invite founding members first.</p>
        </div>

        <div class="relative lg:col-span-7 lg:-mr-10 xl:-mr-20">
            <x-us-map data-reveal style="--reveal-delay: 120ms" />

            {{-- floating sample opportunity card --}}
            <aside
                data-reveal style="--reveal-delay: 380ms"
                aria-label="Sample opportunity card"
                class="mt-6 w-full max-w-xs rounded-2xl border border-ivory/20 bg-ivory p-4 text-ink shadow-2xl shadow-black/40 lg:absolute lg:-bottom-14 lg:right-6 lg:mt-0"
            >
                <div class="flex items-center justify-between text-[11px] font-semibold text-emerald">
                    <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-emerald"></span> Sample opportunity</span>
                    <x-sample-badge class="text-sage" />
                </div>
                <div class="mt-3 flex gap-3">
                    <div class="grid size-16 shrink-0 place-items-center rounded-lg bg-forest text-gold" aria-hidden="true">
                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold">HVAC &amp; Restoration Co.</p>
                        <p class="text-sm text-sage">Buffalo, NY</p>
                        <p class="mt-1 text-xs"><span class="rounded bg-emerald px-1.5 py-0.5 font-semibold text-ivory tabular">88 / 100</span> <span class="text-sage">Fit score</span></p>
                    </div>
                </div>
                <dl class="mt-3 grid grid-cols-3 divide-x divide-forest/10 rounded-lg bg-ivory-deep/70 text-center tabular">
                    <div class="px-2 py-2"><dd class="text-sm font-semibold">$1.8M</dd><dt class="text-[11px] text-sage">Revenue</dt></div>
                    <div class="px-2 py-2"><dd class="text-sm font-semibold">$420K</dd><dt class="text-[11px] text-sage">SDE</dt></div>
                    <div class="px-2 py-2"><dd class="text-sm font-semibold">3.2x</dd><dt class="text-[11px] text-sage">Multiple</dt></div>
                </dl>
            </aside>
        </div>
    </div>

    {{-- value strip --}}
    <div class="border-t border-ivory/10 bg-forest-deep/60">
        <ul class="mx-auto grid max-w-7xl gap-px px-5 py-6 text-sm text-ivory/85 sm:grid-cols-2 sm:px-8 lg:grid-cols-4 lg:divide-x lg:divide-ivory/10">
            @foreach ([
                ['Discover quality businesses', 'M21 21l-4.3-4.3M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z'],
                ['Leverage market intelligence', 'M4 20V10m6 10V4m6 16v-7m4 7H2'],
                ['Evaluate with confidence', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Connect with the right owners', 'M17 20h5v-2a4 4 0 00-5-3.9M9 11a4 4 0 100-8 4 4 0 000 8zm-7 9v-1a5 5 0 0110 0v1M16 3.1a4 4 0 010 7.8'],
            ] as [$label, $icon])
                <li class="flex items-center gap-3 py-3 lg:justify-center lg:px-4">
                    <svg class="size-6 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    <span>{{ $label }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
