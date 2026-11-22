<x-layouts.dashboard title="Dashboard | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6">
        @include('dashboard.partials.kpi-cards')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <a href="{{ route('buybox') }}" class="flex flex-col items-start justify-between gap-4 rounded-3xl border border-gold/40 bg-gradient-to-r from-forest via-forest-deep to-emerald p-5 text-ivory shadow-lg transition hover:border-gold sm:flex-row sm:items-center focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                    <div>
                        @if ($criteria)
                            <p class="font-display text-base font-bold text-gold">My Buy Box &middot; {{ number_format($matchCount) }} {{ \Illuminate\Support\Str::plural('match', $matchCount) }}</p>
                            <p class="mt-1 text-xs text-ivory/80">{{ implode(' · ', $criteria) }}</p>
                        @else
                            <p class="font-display text-base font-bold text-gold">Set up your buy box</p>
                            <p class="mt-1 text-xs text-ivory/80">Pick industries, states or counties, and a price range to see which listings fit.</p>
                        @endif
                    </div>
                    <span class="shrink-0 rounded-full bg-gold px-5 py-2.5 text-xs font-bold text-forest-deep">{{ $criteria ? 'Edit buy box' : 'Set up buy box' }} &rarr;</span>
                </a>

                <section class="space-y-4" aria-labelledby="matches-heading">
                    <div class="flex items-center justify-between">
                        <h2 id="matches-heading" class="font-display text-xl font-semibold text-forest">Top matches</h2>
                        <a href="{{ route('discover') }}" class="text-xs font-semibold text-emerald hover:text-forest">Browse all listings &rarr;</a>
                    </div>

                    @if ($published === 0)
                        @include('dashboard.real.empty', ['title' => 'No listings published yet', 'body' => 'We are reviewing listings now. They appear here as soon as they are published, and your buy box will be matched against them.'])
                    @elseif (! $criteria)
                        @include('dashboard.real.empty', ['title' => number_format($published).' listings to explore', 'body' => 'Set up your buy box to rank them by fit, or browse them all in Discover.', 'action' => ['Browse listings', route('discover')]])
                    @elseif (! $cards)
                        @include('dashboard.real.empty', ['title' => 'Nothing matches your buy box yet', 'body' => 'Try widening the location or price range. Listings that do not disclose a figure are still shown, so the filters are not too strict on missing data.', 'action' => ['Edit buy box', route('buybox')]])
                    @else
                        @foreach ($cards as $card)
                            @include('dashboard.real.card', ['card' => $card, 'saved' => in_array($card['id'], $savedIds, true)])
                        @endforeach
                    @endif
                </section>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <div class="rounded-3xl border border-forest/10 bg-white p-5 text-xs leading-relaxed text-ink/80 shadow-sm">
                    <h2 class="font-display text-base font-semibold text-forest">How to read these listings</h2>
                    <ul class="mt-3 list-disc space-y-1.5 pl-4">
                        <li>Figures are as listed by the seller or broker. Nothing is verified by Next Keepers.</li>
                        <li>"Not disclosed" means the listing did not give that figure. It is never counted as zero.</li>
                        <li>Fit is the share of your buy box criteria a listing is known to meet.</li>
                        <li>Many listings give only a county or state, not a city.</li>
                    </ul>
                </div>
                @include('dashboard.partials.philosophy-card')
            </aside>
        </div>
    </div>
</x-layouts.dashboard>
