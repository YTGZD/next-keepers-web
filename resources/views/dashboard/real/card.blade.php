{{-- One real listing. Expects $card (Business::toRealCardArray) and optional $saved (bool). --}}
@php
    $saved = $saved ?? false;
    $bm = $card['benchmark'] ?? null;
    $stats = [
        ['Asking price', $card['asking']],
        ['Revenue', $card['revenue']],
        [$card['earnings_label'], $card['earnings']],
        ['Price / earnings', $card['multiple']],
    ];
@endphp
<article class="group/card rounded-3xl border border-forest/10 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-forest/25 hover:shadow-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0 sm:p-6">
    <div class="flex flex-col gap-5 sm:flex-row">
        <a href="{{ route('business.show', ['id' => $card['id']]) }}" tabindex="-1" aria-hidden="true" class="shrink-0">
            @include('dashboard.real.partials.photo', ['photo' => $card['photo'], 'industry' => $card['industry'], 'class' => 'h-40 w-full rounded-2xl sm:h-36 sm:w-44'])
        </a>

        <div class="flex min-w-0 flex-1 flex-col gap-3">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 space-y-1.5">
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold uppercase tracking-wider">
                        <span class="rounded-full bg-emerald/10 px-2.5 py-0.5 text-emerald">{{ $card['industry'] }}</span>
                        @if ($card['sub_industry'])<span class="text-sage">{{ $card['sub_industry'] }}</span>@endif
                    </p>
                    <h3 class="font-display text-xl font-semibold leading-snug text-forest">
                        <a href="{{ route('business.show', ['id' => $card['id']]) }}" class="rounded hover:text-emerald focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">{{ $card['name'] }}</a>
                    </h3>
                    <p class="flex flex-wrap items-center gap-x-1.5 text-xs text-sage">
                        <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        {{ $card['location'] }}
                        @if ($card['location_note'])<span class="text-forest/40">&middot;</span> {{ $card['location_note'] }}@endif
                        @if ($card['listed'])<span class="text-forest/40">&middot;</span> Listed {{ $card['listed'] }}@endif
                    </p>
                </div>

                @if ($card['score'] !== null)
                    <div class="hidden shrink-0 sm:block">
                        @include('dashboard.real.partials.score-ring', ['score' => $card['score'], 'size' => 84])
                    </div>
                @endif
            </div>

            @if ($card['excerpt'])
                <p class="text-[13px] leading-relaxed text-ink/70 line-clamp-2">{{ $card['excerpt'] }}</p>
            @endif

            @if ($card['badges'] ?? [])
                <ul class="flex flex-wrap gap-1.5" aria-label="Listing facts">
                    @foreach ($card['badges'] as $badge)
                        <li class="rounded-full bg-ivory px-2.5 py-1 text-[11px] font-medium text-forest/80 ring-1 ring-forest/10">{{ $badge }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($card['score'] !== null && ($card['met'] || $card['unknown_note']))
                <p class="text-[11px] leading-relaxed">
                    @if ($card['met'])<span class="text-emerald">&check; {{ implode(', ', $card['met']) }}</span>@endif
                    @if ($card['unknown_note'])<span class="text-forest/60">&nbsp;&middot; ? {{ $card['unknown_note'] }}</span>@endif
                </p>
            @endif
        </div>
    </div>

    <div class="mt-5 flex flex-col gap-4 border-t border-forest/5 pt-5 lg:flex-row lg:items-center lg:justify-between">
        <dl class="grid flex-1 grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4">
            @foreach ($stats as [$label, $value])
                <div>
                    <dt class="text-[11px] text-sage">{{ $label }}</dt>
                    @if ($value === 'Not disclosed')
                        <dd class="mt-0.5 text-xs font-medium text-forest/45">Not disclosed</dd>
                    @else
                        <dd class="mt-0.5 font-display text-xl font-semibold text-forest tabular">{{ $value }}</dd>
                    @endif
                </div>
            @endforeach
        </dl>
        <div class="flex shrink-0 items-center gap-2 text-xs">
            <button type="button"
                x-data="{ saved: {{ $saved ? 'true' : 'false' }} }"
                @click="saved = !saved; fetch('{{ route('watchlist.toggle', ['businessId' => $card['id']]) }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                :aria-pressed="saved.toString()"
                class="inline-flex items-center gap-1.5 rounded-xl border border-forest/15 px-3.5 py-2.5 font-semibold text-forest transition hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                <svg class="size-4" :fill="saved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
                <span x-text="saved ? 'Saved' : 'Save'">{{ $saved ? 'Saved' : 'Save' }}</span>
            </button>
            <a href="{{ route('business.show', ['id' => $card['id']]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-forest-deep px-4 py-2.5 font-semibold text-ivory transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">View details <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>

    @if ($bm && $bm['status'] !== 'no_benchmark')
        <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-2xl bg-ivory/70 px-3.5 py-2.5 text-[11px]">
            @if ($bm['signal'])
                @include('dashboard.real.partials.benchmark-signal', ['signal' => $bm['signal'], 'label' => $bm['signal_label']])
            @endif
            <span class="font-semibold text-forest tabular">{{ $bm['listing_pe'] === null ? 'Industry multiple '.\App\Support\ListingBenchmark::x($bm['industry_pe']) : 'Price / earnings '.$bm['headline'] }}</span>
            <span class="text-sage">
                {{ $bm['subsector'] }}, sold in {{ $bm['year'] }}@if ($bm['thin']) &middot; only {{ $bm['sales'] }} {{ $bm['sales'] === 1 ? 'sale' : 'sales' }}@endif
                @if ($bm['listing_pe'] === null) &middot; this listing's multiple isn't disclosed @endif
            </span>
        </div>
    @endif
</article>
