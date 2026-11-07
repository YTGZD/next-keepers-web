@php
    // Marketing pages beyond the homepage are not built yet, so each item scrolls to its homepage section.
    $links = [
        ['Platform', route('home').'#platform'],
        ['Market Intelligence', route('home').'#intelligence'],
        ['For Buyers', route('home').'#audiences'],
        ['For Owners', route('home').'#audiences'],
        ['About', route('home').'#mission'],
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
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            <x-button href="{{ route('home') }}#early-access" class="!px-5 !py-2.5">Get Early Access</x-button>
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
        </nav>
        <x-button href="{{ route('home') }}#early-access" @click="open = false" class="mt-5 w-full">Get Early Access</x-button>
    </div>
</header>
