<aside
    x-data="{ mobileOpen: false }"
    @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col justify-between bg-forest-deep px-5 py-6 text-ivory transition-transform duration-300 lg:static lg:translate-x-0 border-r border-ivory/10 shadow-xl"
>
    <div>
        {{-- Logo Lockup (Click to visit Landing Page) --}}
        <div class="flex items-center justify-between pb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                <span class="grid size-10 place-items-center rounded-xl border border-ivory/30 bg-emerald/40 font-display text-lg font-semibold text-ivory shadow-inner group-hover:scale-105 transition">NK</span>
                <div>
                    <span class="font-display text-xl tracking-tight text-ivory group-hover:text-gold transition block leading-tight">Next Keepers</span>
                    <span class="text-[10px] text-ivory/60 block">&larr; Landing Page</span>
                </div>
            </a>
            <button
                type="button"
                @click="mobileOpen = false"
                class="grid size-8 place-items-center rounded-lg text-ivory/60 hover:text-ivory lg:hidden"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav Items --}}
        <nav class="mt-4 space-y-1.5" aria-label="Dashboard Sidebar">
            {{-- Dashboard --}}
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <svg class="size-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-gold' : 'text-ivory/60' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Discover Businesses --}}
            <a
                href="{{ route('discover') }}"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('discover') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <svg class="size-5 shrink-0 {{ request()->routeIs('discover') ? 'text-gold' : 'text-ivory/60' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" stroke-width="2"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                    <circle cx="11" cy="11" r="2" stroke-width="2"/>
                </svg>
                <span>Discover Businesses</span>
            </a>

            {{-- Market Intelligence --}}
            <a
                href="{{ route('market.intelligence') }}"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('market.intelligence') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <svg class="size-5 shrink-0 {{ request()->routeIs('market.intelligence') ? 'text-gold' : 'text-ivory/60' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Market Intelligence</span>
            </a>

            {{-- My Buy Box --}}
            <a
                href="{{ route('buybox') }}"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('buybox') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <span class="flex items-center gap-3.5">
                    <svg class="size-5 shrink-0 {{ request()->routeIs('buybox') ? 'text-gold' : 'text-ivory/60' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="8" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3" stroke-width="2"/>
                        <path stroke-linecap="round" d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
                    </svg>
                    <span>My Buy Box</span>
                </span>
            </a>

            {{-- Watchlist --}}
            <a
                href="{{ route('watchlist') }}"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('watchlist') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <span class="flex items-center gap-3.5">
                    <svg class="size-5 shrink-0 {{ request()->routeIs('watchlist') ? 'text-gold' : 'text-ivory/60' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span>Watchlist</span>
                </span>
                @if ($nav['watchlist'] > 0)
                    <span class="rounded-full bg-gold px-2 py-0.5 text-[11px] font-bold text-forest-deep">{{ $nav['watchlist'] }}</span>
                @endif
            </a>

            {{-- Pipeline --}}
            <a
                href="#pipeline"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium text-ivory/80 transition hover:bg-forest/50 hover:text-ivory"
            >
                <svg class="size-5 shrink-0 text-ivory/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Pipeline</span>
            </a>

            {{-- Messages (Active link to /messages with badge 5) --}}
            <a
                href="{{ route('messages') }}"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('messages') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <span class="flex items-center gap-3.5">
                    <svg class="size-5 {{ request()->routeIs('messages') ? 'text-gold' : 'text-ivory/60' }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Messages</span>
                </span>
                @if ($nav['unreadMessages'] > 0)
                    <span class="rounded-full bg-gold px-2 py-0.5 text-[11px] font-bold text-forest-deep">{{ $nav['unreadMessages'] }}</span>
                @endif
            </a>

            {{-- Reports --}}
            <a
                href="#reports"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium text-ivory/80 transition hover:bg-forest/50 hover:text-ivory"
            >
                <svg class="size-5 text-ivory/60 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Reports</span>
            </a>

            {{-- Settings --}}
            <a
                href="{{ route('settings') }}"
                class="flex items-center gap-3.5 rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('settings') ? 'bg-forest font-semibold text-gold shadow-sm' : 'text-ivory/80 hover:bg-forest/50 hover:text-ivory transition' }}"
            >
                <svg class="size-5 {{ request()->routeIs('settings') ? 'text-gold' : 'text-ivory/60' }} shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Settings</span>
            </a>
        </nav>
    </div>

    {{-- Bottom Promo Card & Help Footer --}}
    <div class="space-y-5 pt-6">
        <div class="relative overflow-hidden rounded-2xl border border-ivory/15 bg-forest p-4 text-center shadow-lg">
            <div class="relative mx-auto h-24 w-full overflow-hidden rounded-xl bg-forest-deep">
                <img
                    src="{{ asset('images/dashboard/sidebar-unlock.png') }}"
                    alt="Golden Keyhole"
                    class="h-full w-full object-cover object-center"
                    onerror="this.src='{{ asset('images/key.jpg') }}'"
                >
            </div>
            <p class="mt-3 text-xs font-semibold tracking-tight text-ivory">Unlock More Opportunities</p>
            <button
                type="button"
                class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-full bg-gold py-2 text-xs font-bold text-forest-deep shadow transition hover:bg-gold-soft"
            >
                <span>Upgrade Plan</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>

        <div class="flex items-center gap-3 border-t border-ivory/10 pt-4 text-xs text-ivory/70">
            <span class="grid size-8 place-items-center rounded-full bg-forest text-gold">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/></svg>
            </span>
            <div>
                <p class="font-semibold text-ivory">Need Help?</p>
                <a href="mailto:contact@nextkeepers.com" class="text-sage hover:text-gold transition">Contact Us</a>
            </div>
        </div>
    </div>
</aside>
