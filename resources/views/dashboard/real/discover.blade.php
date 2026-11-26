@php
    $input = 'w-full rounded-xl border border-forest/15 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-sage/70 focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold';
    $query = fn (array $over) => route('discover', array_filter(array_merge(request()->query(), ['page' => null], $over), fn ($v) => $v !== null && $v !== '' && $v !== false));
    $active = collect([
        'q' => $filters['q'] !== '' ? '“'.$filters['q'].'”' : null,
        'industry' => $filters['industry'] ?: null,
        'state' => $filters['state'] ?: null,
        'min_price' => $filters['min_price'] !== '' ? 'Price from $'.number_format((int) $filters['min_price']) : null,
        'max_price' => $filters['max_price'] !== '' ? 'Price up to $'.number_format((int) $filters['max_price']) : null,
        'min_revenue' => $filters['min_revenue'] !== '' ? 'Revenue from $'.number_format((int) $filters['min_revenue']) : null,
        'min_earnings' => $filters['min_earnings'] !== '' ? 'Earnings from $'.number_format((int) $filters['min_earnings']) : null,
        'disclosed' => $filters['disclosed'] ? 'Price and earnings disclosed' : null,
        'matches' => $filters['matches'] ? 'Buy box only' : null,
    ])->filter();
    $topIndustries = collect($byIndustry)->take(5);
@endphp
<x-layouts.dashboard title="Discover Businesses | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10">
        <x-dash-hero title="Discover businesses"
            :subtitle="number_format($total).' published '.\Illuminate\Support\Str::plural('listing', $total).'. Figures are as listed, not verified.'"
            :back="route('dashboard')">
            <x-dash-stat label="Listings" :value="number_format($total)" />
            <x-dash-stat label="Industries" :value="number_format(count($industries))" />
            <x-dash-stat label="States" :value="number_format(count($states))" />
            @if ($addedThisWeek > 0)
                <x-dash-stat label="Added this week" :value="number_format($addedThisWeek)" />
            @endif
        </x-dash-hero>

        {{-- Quick category chips --}}
        <nav class="flex flex-wrap items-center gap-2 text-xs" aria-label="Quick filters">
            <a href="{{ $query(['industry' => null]) }}" @if (! $filters['industry']) aria-current="true" @endif
                class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold {{ ! $filters['industry'] ? 'bg-forest-deep text-ivory shadow-sm' : 'border border-forest/15 bg-white text-forest hover:border-gold' }}">
                All listings <span class="tabular {{ ! $filters['industry'] ? 'text-gold' : 'text-sage' }}">{{ number_format($total) }}</span>
            </a>
            @foreach ($topIndustries as $row)
                @php $on = $filters['industry'] === $row['label']; @endphp
                <a href="{{ $query(['industry' => $row['label']]) }}" @if ($on) aria-current="true" @endif
                    class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold {{ $on ? 'bg-forest-deep text-ivory shadow-sm' : 'border border-forest/15 bg-white text-forest hover:border-gold' }}">
                    {{ $row['label'] }} <span class="tabular {{ $on ? 'text-gold' : 'text-sage' }}">{{ number_format($row['count']) }}</span>
                </a>
            @endforeach
        </nav>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[18.5rem_minmax(0,1fr)]">
            {{-- Filters --}}
            <form method="GET" action="{{ route('discover') }}" role="search" aria-label="Refine your search"
                x-data="{ open: window.matchMedia('(min-width: 1024px)').matches }"
                class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm lg:sticky lg:top-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold text-forest">Refine your search</h2>
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="rounded-lg px-2 py-1 text-xs font-semibold text-emerald hover:text-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold lg:hidden" x-text="open ? 'Hide' : 'Show'">Hide</button>
                </div>

                <div x-show="open" x-cloak class="mt-4 space-y-4 text-xs">
                    <label class="block">
                        <span class="mb-1 block font-semibold text-forest">Search</span>
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Name, type or place" class="{{ $input }}">
                    </label>
                    <label class="block">
                        <span class="mb-1 block font-semibold text-forest">Industry</span>
                        <select name="industry" class="{{ $input }}">
                            <option value="">All industries</option>
                            @foreach ($industries as $industry)
                                <option value="{{ $industry }}" @selected($filters['industry'] === $industry)>{{ $industry }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block font-semibold text-forest">State</span>
                        <select name="state" class="{{ $input }}">
                            <option value="">All states</option>
                            @foreach ($states as $state)
                                <option value="{{ $state }}" @selected($filters['state'] === $state)>{{ $state }}</option>
                            @endforeach
                        </select>
                    </label>

                    <fieldset>
                        <legend class="mb-1 font-semibold text-forest">Asking price</legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label><span class="sr-only">Minimum asking price</span><input type="text" inputmode="numeric" name="min_price" value="{{ $filters['min_price'] }}" placeholder="Min $" class="{{ $input }}"></label>
                            <label><span class="sr-only">Maximum asking price</span><input type="text" inputmode="numeric" name="max_price" value="{{ $filters['max_price'] }}" placeholder="Max $" class="{{ $input }}"></label>
                        </div>
                    </fieldset>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block"><span class="mb-1 block font-semibold text-forest">Min revenue</span><input type="text" inputmode="numeric" name="min_revenue" value="{{ $filters['min_revenue'] }}" placeholder="$" class="{{ $input }}"></label>
                        <label class="block"><span class="mb-1 block font-semibold text-forest">Min earnings</span><input type="text" inputmode="numeric" name="min_earnings" value="{{ $filters['min_earnings'] }}" placeholder="$" class="{{ $input }}"></label>
                    </div>
                    <p class="-mt-2 text-[11px] leading-snug text-sage">Listings that don't disclose a figure are left out when you set a minimum or maximum on it.</p>

                    <label class="block">
                        <span class="mb-1 block font-semibold text-forest">Sort by</span>
                        <select name="sort" class="{{ $input }}">
                            <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                            <option value="price_low" @selected($filters['sort'] === 'price_low')>Asking price: low to high</option>
                            <option value="price_high" @selected($filters['sort'] === 'price_high')>Asking price: high to low</option>
                            <option value="revenue_high" @selected($filters['sort'] === 'revenue_high')>Revenue: high to low</option>
                            <option value="earnings_high" @selected($filters['sort'] === 'earnings_high')>Earnings: high to low</option>
                        </select>
                    </label>

                    <div class="space-y-2 border-t border-forest/5 pt-3">
                        <label class="flex items-center gap-2 text-forest"><input type="checkbox" name="disclosed" value="1" @checked($filters['disclosed']) class="rounded border-forest/30 accent-[#1B604A]"> Only listings with price and earnings</label>
                        @if ($hasCriteria)
                            <label class="flex items-center gap-2 text-forest"><input type="checkbox" name="matches" value="1" @checked($filters['matches']) class="rounded border-forest/30 accent-[#1B604A]"> Buy box only</label>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" class="flex-1 rounded-xl bg-forest-deep px-4 py-2.5 text-xs font-semibold text-ivory transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Apply filters</button>
                        <a href="{{ route('discover') }}" class="rounded-xl px-3 py-2.5 text-xs font-semibold text-emerald hover:text-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Reset</a>
                    </div>
                </div>
            </form>

            <div class="min-w-0 space-y-6">
                @if ($total > 0)
                    {{-- Snapshot of what is published (all listings, not the filtered set) --}}
                    <section aria-labelledby="snapshot-heading" class="hidden grid-cols-1 gap-4 md:grid md:grid-cols-2">
                        <h2 id="snapshot-heading" class="sr-only">Where the listings are</h2>
                        <div class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-baseline justify-between">
                                <h3 class="font-display text-base font-semibold text-forest">By state</h3>
                                <span class="text-[11px] text-sage">Published listings</span>
                            </div>
                            @include('dashboard.real.partials.bars', ['rows' => $byState, 'href' => fn ($l) => $query(['state' => $l])])
                        </div>
                        <div class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-baseline justify-between">
                                <h3 class="font-display text-base font-semibold text-forest">By industry</h3>
                                <span class="text-[11px] text-sage">Published listings</span>
                            </div>
                            @include('dashboard.real.partials.bars', ['rows' => $byIndustry, 'href' => fn ($l) => $query(['industry' => $l])])
                        </div>
                    </section>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-sage" aria-live="polite">
                        @if ($page->total() > 0)
                            Showing <span class="font-semibold text-forest tabular">{{ $page->firstItem() }}–{{ $page->lastItem() }}</span> of <span class="font-semibold text-forest tabular">{{ number_format($page->total()) }}</span> {{ \Illuminate\Support\Str::plural('listing', $page->total()) }}
                        @else
                            No listings to show
                        @endif
                    </p>
                    @if ($active->isNotEmpty())
                        <ul class="flex flex-wrap items-center gap-1.5 text-[11px]" aria-label="Active filters">
                            @foreach ($active as $key => $label)
                                <li><a href="{{ $query([$key => null]) }}" class="inline-flex items-center gap-1 rounded-full bg-emerald/10 px-2.5 py-1 font-semibold text-emerald hover:bg-emerald/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold" aria-label="Remove filter: {{ $label }}">{{ $label }} <span aria-hidden="true">&times;</span></a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <section class="space-y-5" aria-label="Listings">
                    @forelse ($cards as $card)
                        @include('dashboard.real.card', ['card' => $card, 'saved' => in_array($card['id'], $savedIds, true)])
                    @empty
                        @if ($total === 0)
                            @include('dashboard.real.empty', ['title' => 'No listings published yet', 'body' => 'We are reviewing listings now. They appear here as soon as they are published.'])
                        @else
                            @include('dashboard.real.empty', ['title' => 'No listings match these filters', 'body' => 'Try another industry or state, loosen the price range, or clear the search.', 'action' => ['Clear filters', route('discover')]])
                        @endif
                    @endforelse
                </section>

                @if ($page->hasPages())
                    <nav class="flex items-center justify-between rounded-2xl border border-forest/10 bg-white px-4 py-3 text-xs text-sage shadow-sm" aria-label="Pages">
                        <span>Page <span class="font-semibold text-forest tabular">{{ $page->currentPage() }}</span> of <span class="tabular">{{ number_format($page->lastPage()) }}</span></span>
                        <span class="flex gap-2">
                            @if ($page->previousPageUrl())<a href="{{ $page->previousPageUrl() }}" class="rounded-lg border border-forest/15 bg-white px-3.5 py-2 font-semibold text-forest hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">&larr; Previous</a>@endif
                            @if ($page->nextPageUrl())<a href="{{ $page->nextPageUrl() }}" class="rounded-lg bg-forest-deep px-3.5 py-2 font-semibold text-ivory hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Next &rarr;</a>@endif
                        </span>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</x-layouts.dashboard>
