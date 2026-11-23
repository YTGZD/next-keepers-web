{{-- The deal box list. Expects $matchCount, $cards, $criteria, $savedIds. Re-rendered live by DealBoxController::results. --}}
<div class="space-y-4">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="bb-matches" class="font-display text-xl font-semibold text-forest">
            {{ number_format($matchCount) }} {{ \Illuminate\Support\Str::plural('listing', $matchCount) }} {{ $criteria ? 'fit this box' : 'published' }}
        </h2>
        @if ($matchCount > count($cards))
            <p class="text-xs text-sage">Showing the best {{ count($cards) }}</p>
        @endif
    </div>
    @if ($criteria)
        <p class="text-xs text-sage">{{ implode(' · ', $criteria) }}</p>
    @endif

    @forelse ($cards as $card)
        @include('dashboard.real.card', ['card' => $card, 'saved' => in_array($card['id'], $savedIds, true)])
    @empty
        @include('dashboard.real.empty', $criteria
            ? ['title' => 'Nothing fits this box yet', 'body' => 'Nothing published fits these criteria right now. Loosen a slider, widen the location, or check back as more listings are published.']
            : ['title' => 'No listings published yet', 'body' => 'We are reviewing listings now. They appear here as soon as they are published.'])
    @endforelse
</div>
