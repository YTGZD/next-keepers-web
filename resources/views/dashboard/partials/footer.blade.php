<footer class="mt-12 border-t border-forest/10 bg-forest-deep px-6 py-6 text-ivory/80">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between text-xs">
        {{-- Logo --}}
        <div class="flex items-center gap-3">
            <span class="grid size-8 place-items-center rounded-lg border border-ivory/20 bg-emerald/30 font-display font-semibold text-ivory">NK</span>
            <span class="font-display text-base tracking-tight text-ivory">Next Keepers</span>
        </div>

        {{-- Nav Links --}}
        <nav class="flex flex-wrap items-center gap-x-6 gap-y-2 text-ivory/70">
            <a href="{{ route('home') }}#platform" class="hover:text-gold transition">Platform</a>
            <span class="text-ivory/20" aria-hidden="true">|</span>
            <a href="{{ route('dashboard') }}#market-overview" class="hover:text-gold transition">Market Intelligence</a>
            <span class="text-ivory/20" aria-hidden="true">|</span>
            <a href="{{ route('home') }}#audiences" class="hover:text-gold transition">For Buyers</a>
            <span class="text-ivory/20" aria-hidden="true">|</span>
            <a href="{{ route('home') }}#audiences" class="hover:text-gold transition">For Owners</a>
            <span class="text-ivory/20" aria-hidden="true">|</span>
            <a href="{{ route('home') }}#mission" class="hover:text-gold transition">About</a>
        </nav>

        {{-- Social Icons & Legal --}}
        <div class="flex flex-wrap items-center gap-4">
            {{-- Social Icons --}}
            <div class="flex items-center gap-2">
                <a href="https://linkedin.com" target="_blank" rel="noopener" class="grid size-7 place-items-center rounded-full border border-ivory/20 text-ivory/80 hover:border-gold hover:text-gold transition" aria-label="LinkedIn">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                </a>
                <a href="https://youtube.com" target="_blank" rel="noopener" class="grid size-7 place-items-center rounded-full border border-ivory/20 text-ivory/80 hover:border-gold hover:text-gold transition" aria-label="YouTube">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg>
                </a>
                <a href="https://x.com" target="_blank" rel="noopener" class="grid size-7 place-items-center rounded-full border border-ivory/20 text-ivory/80 hover:border-gold hover:text-gold transition" aria-label="X">
                    <svg class="size-3" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
            </div>

            <div class="flex items-center gap-3 text-ivory/60">
                <a href="{{ route('privacy') }}" class="hover:text-gold transition">Privacy Policy</a>
                <span class="text-ivory/20" aria-hidden="true">&bull;</span>
                <a href="{{ route('terms') }}" class="hover:text-gold transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
