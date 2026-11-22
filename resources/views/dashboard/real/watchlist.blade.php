<x-layouts.dashboard title="Watchlist | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-medium tracking-tight text-forest">Watchlist</h1>
                <p class="mt-1 text-sm text-sage">Listings you saved.</p>
            </div>
            @if ($cards)
                <dl class="flex gap-6 text-xs">
                    <div><dt class="text-sage">Saved</dt><dd class="font-display text-xl font-semibold text-forest">{{ count($cards) }}</dd></div>
                    <div><dt class="text-sage">Combined asking price</dt><dd class="font-display text-xl font-semibold text-forest">{{ $totalAsking }}</dd></div>
                </dl>
            @endif
        </header>
        @if ($undisclosed > 0)
            <p class="text-xs text-sage">{{ $undisclosed }} saved {{ \Illuminate\Support\Str::plural('listing', $undisclosed) }} {{ $undisclosed === 1 ? "doesn't" : "don't" }} disclose a price and {{ $undisclosed === 1 ? 'is' : 'are' }} not in the total.</p>
        @endif

        <section class="space-y-4" aria-label="Saved listings">
            @forelse ($cards as $card)
                @include('dashboard.real.card', ['card' => $card, 'saved' => true])
            @empty
                @include('dashboard.real.empty', ['title' => 'Nothing saved yet', 'body' => 'Use Save on any listing to keep it here.', 'action' => ['Browse listings', route('discover')]])
            @endforelse
        </section>
    </div>
</x-layouts.dashboard>
