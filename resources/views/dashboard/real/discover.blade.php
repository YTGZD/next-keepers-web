<x-layouts.dashboard title="Discover Businesses | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10">
        <header>
            <h1 class="font-display text-3xl font-medium tracking-tight text-forest">Discover businesses</h1>
            <p class="mt-1 text-sm text-sage">{{ number_format($total) }} published {{ \Illuminate\Support\Str::plural('listing', $total) }}. Figures are as listed, not verified.</p>
        </header>

        <form method="GET" action="{{ route('discover') }}" class="grid grid-cols-1 gap-3 rounded-3xl border border-forest/10 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6" role="search">
            <label class="lg:col-span-2">
                <span class="sr-only">Search</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, type or place" class="w-full rounded-xl border border-forest/15 px-3 py-2 text-sm focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
            </label>
            <label>
                <span class="sr-only">Industry</span>
                <select name="industry" class="w-full rounded-xl border border-forest/15 px-3 py-2 text-sm focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                    <option value="">All industries</option>
                    @foreach ($industries as $industry)
                        <option value="{{ $industry }}" @selected($filters['industry'] === $industry)>{{ $industry }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">State</span>
                <select name="state" class="w-full rounded-xl border border-forest/15 px-3 py-2 text-sm focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                    <option value="">All states</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" @selected($filters['state'] === $state)>{{ $state }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">Sort</span>
                <select name="sort" class="w-full rounded-xl border border-forest/15 px-3 py-2 text-sm focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                    <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                    <option value="price_low" @selected($filters['sort'] === 'price_low')>Asking price: low to high</option>
                    <option value="price_high" @selected($filters['sort'] === 'price_high')>Asking price: high to low</option>
                    <option value="revenue_high" @selected($filters['sort'] === 'revenue_high')>Revenue: high to low</option>
                    <option value="earnings_high" @selected($filters['sort'] === 'earnings_high')>Earnings: high to low</option>
                </select>
            </label>
            <div class="flex items-center gap-3">
                @if ($hasCriteria)
                    <label class="flex items-center gap-2 text-xs text-forest">
                        <input type="checkbox" name="matches" value="1" @checked($filters['matches']) class="rounded border-forest/30 accent-[#1B604A]">
                        Buy box only
                    </label>
                @endif
                <button type="submit" class="ml-auto rounded-xl bg-forest-deep px-4 py-2 text-xs font-semibold text-ivory hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Apply</button>
            </div>
        </form>

        <section class="space-y-4" aria-label="Listings">
            @forelse ($cards as $card)
                @include('dashboard.real.card', ['card' => $card, 'saved' => in_array($card['id'], $savedIds, true)])
            @empty
                @if ($total === 0)
                    @include('dashboard.real.empty', ['title' => 'No listings published yet', 'body' => 'We are reviewing listings now. They appear here as soon as they are published.'])
                @else
                    @include('dashboard.real.empty', ['title' => 'No listings match these filters', 'body' => 'Try another industry or state, or clear the search.', 'action' => ['Clear filters', route('discover')]])
                @endif
            @endforelse
        </section>

        @if ($page->hasPages())
            <nav class="flex items-center justify-between text-xs text-sage" aria-label="Pages">
                <span>Showing {{ $page->firstItem() }}–{{ $page->lastItem() }} of {{ number_format($page->total()) }}</span>
                <span class="flex gap-2">
                    @if ($page->previousPageUrl())<a href="{{ $page->previousPageUrl() }}" class="rounded-lg border border-forest/15 bg-white px-3 py-1.5 font-semibold text-forest hover:border-gold">&larr; Previous</a>@endif
                    @if ($page->nextPageUrl())<a href="{{ $page->nextPageUrl() }}" class="rounded-lg border border-forest/15 bg-white px-3 py-1.5 font-semibold text-forest hover:border-gold">Next &rarr;</a>@endif
                </span>
            </nav>
        @endif
    </div>
</x-layouts.dashboard>
