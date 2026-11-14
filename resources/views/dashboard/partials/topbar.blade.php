<header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-forest/10 bg-[#f4f2eb]/90 px-4 backdrop-blur-md sm:px-6 lg:px-8">
    {{-- Left: Mobile Hamburger & Global Search --}}
    <div class="flex flex-1 items-center gap-3">
        <button
            type="button"
            @click="$dispatch('toggle-mobile-sidebar')"
            class="grid size-10 place-items-center rounded-xl border border-forest/15 bg-white text-forest shadow-sm lg:hidden"
            aria-label="Open sidebar"
        >
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Search Input --}}
        <form action="{{ route('discover') }}" method="GET" class="relative w-full max-w-xl">
            <label for="search-input" class="sr-only">Search</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-forest/40">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </div>
            <input
                id="search-input"
                name="q"
                type="text"
                value="{{ request('q') }}"
                placeholder="Search for businesses, industries, or locations..."
                class="w-full rounded-full border border-forest/10 bg-white/95 py-2.5 pl-11 pr-4 text-sm text-ink placeholder:text-sage shadow-sm focus:border-gold focus:bg-white focus:outline-none focus:ring-2 focus:ring-gold/30"
            >
        </form>
    </div>

    {{-- Right: Buy Box Button, Notifications & User Profile --}}
    <div class="flex items-center gap-3 sm:gap-4 pl-2 sm:pl-4">
        {{-- Prominent My Buy Box Header CTA Button --}}
        <a
            href="{{ route('buybox') }}"
            class="flex items-center gap-2 rounded-full bg-forest px-4 py-2 text-xs font-bold text-gold border border-gold/40 hover:bg-emerald transition shadow-md cursor-pointer shrink-0"
        >
            <svg class="size-4 text-gold shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="8" stroke-width="2"/>
                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
            </svg>
            <span>My Buy Box</span>
            <span class="rounded-full bg-gold px-2 py-0.5 text-[10px] font-black text-forest-deep shadow">{{ $nav['listings'] }}</span>
        </a>

        {{-- Notification Bell --}}
        <div class="relative" x-data="{ open: false }">
            <button
                type="button"
                @click="open = !open"
                aria-label="Notifications"
                class="relative grid size-11 place-items-center rounded-full border border-forest/10 bg-white text-forest shadow-sm transition hover:border-gold hover:text-emerald"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if ($nav['unreadNotifications'] > 0)
                    <span class="absolute -top-1 -right-1 grid size-5 place-items-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm">{{ $nav['unreadNotifications'] }}</span>
                @endif
            </button>

            {{-- Notifications Dropdown --}}
            <div
                x-show="open"
                x-cloak
                @click.away="open = false"
                class="absolute right-0 mt-2 w-80 rounded-2xl border border-forest/10 bg-white p-4 shadow-xl text-ink z-50"
            >
                <div class="flex items-center justify-between border-b border-forest/10 pb-2">
                    <p class="font-semibold text-forest text-sm">Notifications</p>
                </div>
                <div class="mt-3 space-y-2.5 text-xs">
                    @forelse ($nav['notifications'] as $note)
                        <div class="rounded-xl bg-ivory-deep/60 p-2.5">
                            <p class="font-medium text-forest">{{ $note->title }}</p>
                            <p class="mt-0.5 text-[11px] text-sage">{{ $note->body }}</p>
                        </div>
                    @empty
                        <p class="py-2 text-[11px] text-sage">You're all caught up.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- User Profile Pill --}}
        <div class="relative" x-data="{ profileOpen: false }">
            <button
                type="button"
                @click="profileOpen = !profileOpen"
                class="flex items-center gap-3 rounded-full border border-forest/10 bg-white p-1.5 pr-3 shadow-sm transition hover:border-gold"
            >
                @if ($nav['isDemo'])
                    <img src="{{ asset('images/dashboard/avatar.png') }}" alt="" class="size-8 rounded-full object-cover border border-emerald/20">
                @else
                    <span class="grid size-8 place-items-center rounded-full border border-gold/40 bg-emerald text-xs font-bold text-ivory">{{ $nav['initial'] }}</span>
                @endif
                <span class="hidden text-xs font-semibold text-forest sm:inline-block">{{ $userName ?? 'Guest' }}</span>
                <svg class="size-4 text-forest/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- Profile Dropdown --}}
            <div
                x-show="profileOpen"
                x-cloak
                @click.away="profileOpen = false"
                class="absolute right-0 mt-2 w-56 rounded-2xl border border-forest/10 bg-white p-2 shadow-xl z-50 text-xs text-ink"
            >
                <div class="px-3 py-2 border-b border-forest/10">
                    <p class="font-semibold text-forest">{{ $userName ?? 'Guest' }}</p>
                    <p class="text-sage text-[11px] truncate">{{ $userEmail ?? '' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-forest hover:bg-ivory-deep transition">Return to Marketing Site</a>
                    <a href="{{ route('buybox') }}" class="block rounded-lg px-3 py-2 text-forest hover:bg-ivory-deep transition font-semibold text-gold">My Buy Box ({{ $nav['listings'] }})</a>
                    <a href="{{ route('settings') }}" class="block rounded-lg px-3 py-2 text-forest hover:bg-ivory-deep transition">Account Settings</a>
                </div>
                <div class="border-t border-forest/10 pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left rounded-lg px-3 py-2 font-semibold text-red-600 hover:bg-red-50 transition">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
