<div class="relative isolate overflow-hidden rounded-3xl bg-forest-deep p-6 text-ivory shadow-md">
    {{-- Background glowing key visual --}}
    <div class="pointer-events-none absolute -right-4 -bottom-6 h-48 w-40 opacity-70" aria-hidden="true">
        <img
            src="{{ asset('images/dashboard/philosophy-key.png') }}"
            alt="Golden Key Archway"
            class="h-full w-full object-contain"
            onerror="this.src='{{ asset('images/key.jpg') }}'"
        >
    </div>

    <div class="relative z-10 max-w-[15rem]">
        {{-- Eyebrow --}}
        <div class="flex items-center gap-2">
            <span class="h-3.5 w-1 rounded-full bg-gold"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-gold-soft">Our Philosophy</span>
        </div>

        <h3 class="mt-3 font-display text-2xl font-medium leading-snug tracking-tight text-ivory">
            Every great business deserves a next keeper.
        </h3>

        <p class="mt-2 text-xs text-ivory/70 leading-relaxed">
            Preserving value. Building what's next.
        </p>

        <a
            href="{{ route('home') }}#mission"
            class="mt-5 inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-2 text-xs font-semibold text-gold transition hover:border-gold hover:bg-gold hover:text-forest-deep"
        >
            <span>Learn Our Story</span>
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
</div>
