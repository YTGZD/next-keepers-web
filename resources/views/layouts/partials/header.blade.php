@php
    // Marketing pages beyond the homepage are not built yet, so each item scrolls to its homepage section.
    $links = [
        ['Platform', route('home').'#platform'],
        ['Market Intelligence', route('home').'#intelligence'],
        ['For Buyers', route('home').'#audiences'],
        ['For Owners', route('home').'#audiences'],
        ['About', route('about')],
    ];
@endphp
<header
    x-data="{ open: false, scrolled: false }"
    x-init="scrolled = window.scrollY > 12"
    @scroll.window.passive="scrolled = window.scrollY > 12"
    @keydown.escape.window="open = false"
    class="fixed inset-x-0 top-0 z-50 transition-colors duration-300"
    :class="scrolled || open ? 'bg-forest-deep/90 shadow-lg shadow-black/20 backdrop-blur-md' : 'bg-transparent'"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 sm:px-8">
        <a href="{{ route('home') }}" aria-label="Next Keepers home" class="rounded-lg">
            <x-logo class="-ml-2" />
        </a>

        <nav aria-label="Primary" class="hidden items-center gap-8 text-sm text-ivory/85 lg:flex">
            @foreach ($links as [$label, $href])
                <a href="{{ $href }}" class="rounded transition hover:text-gold-soft">{{ $label }}</a>
            @endforeach
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 rounded font-medium text-gold transition hover:text-gold-soft">
                <span>Dashboard</span>
                <span class="rounded bg-gold/20 px-1.5 py-0.5 text-[10px] font-bold uppercase text-gold">Preview</span>
            </a>
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm font-semibold text-ivory transition hover:text-gold">
                    <span class="grid size-8 place-items-center rounded-full border border-gold/40 bg-emerald text-xs font-bold text-ivory">{{ strtoupper(mb_substr(Auth::user()->name ?: 'U', 0, 1)) }}</span>
                    <span>Dashboard</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded text-xs text-ivory/70 transition hover:text-ivory">Log out</button>
                </form>
            @else
                <button type="button" @click="$dispatch('open-auth', { mode: 'login' })" class="rounded text-sm font-medium text-ivory/85 transition hover:text-gold">Log In</button>
                <button type="button" @click="$dispatch('open-auth', { mode: 'register' })" class="inline-flex items-center justify-center gap-2 rounded-full bg-gold px-5 py-2.5 text-sm font-semibold text-forest-deep shadow-md shadow-gold/20 transition hover:-translate-y-0.5 hover:bg-gold-soft active:translate-y-0">Get Started <span aria-hidden="true">&rarr;</span></button>
            @endauth
        </div>

        <button
            type="button"
            class="grid size-10 place-items-center rounded-lg text-ivory lg:hidden"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-controls="mobile-menu"
            aria-label="Toggle menu"
        >
            <svg x-show="!open" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            <svg x-show="open" x-cloak class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div id="mobile-menu" x-show="open" x-cloak x-transition.opacity class="border-t border-ivory/10 px-5 pb-6 pt-2 lg:hidden">
        <nav aria-label="Mobile" class="flex flex-col text-ivory">
            @foreach ($links as [$label, $href])
                <a href="{{ $href }}" @click="open = false" class="border-b border-ivory/10 py-3.5 text-base">{{ $label }}</a>
            @endforeach
            <a href="{{ route('dashboard') }}" @click="open = false" class="flex items-center justify-between border-b border-ivory/10 py-3.5 text-base font-medium text-gold">
                <span>Dashboard</span>
                <span class="rounded bg-gold/20 px-2 py-0.5 text-xs font-bold uppercase text-gold">Preview</span>
            </a>
        </nav>
        <div class="mt-5 flex flex-col gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full rounded-full bg-gold py-3 text-center text-sm font-semibold text-forest-deep">Go to Dashboard &rarr;</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-full border border-ivory/25 py-2.5 text-sm font-semibold text-ivory">Log out</button>
                </form>
            @else
                <button type="button" @click="open = false; $dispatch('open-auth', { mode: 'register' })" class="w-full rounded-full bg-gold py-3 text-sm font-semibold text-forest-deep">Get Started &rarr;</button>
                <button type="button" @click="open = false; $dispatch('open-auth', { mode: 'login' })" class="w-full rounded-full border border-ivory/25 py-2.5 text-sm font-semibold text-ivory">Log In</button>
            @endauth
        </div>
    </div>
</header>
