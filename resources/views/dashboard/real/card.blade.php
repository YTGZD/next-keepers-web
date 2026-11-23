{{-- One real listing. Expects $card (Business::toRealCardArray) and optional $saved (bool). --}}
@php($saved = $saved ?? false)
<article class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm transition hover:border-forest/30 hover:shadow-md">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex min-w-0 flex-1 gap-4">
        @include('dashboard.real.partials.photo', ['photo' => $card['photo'], 'industry' => $card['industry'], 'class' => 'h-20 w-24 shrink-0 rounded-2xl sm:h-24 sm:w-32'])
        <div class="min-w-0 flex-1 space-y-1.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sage">
                {{ $card['industry'] }}@if ($card['sub_industry']) &middot; {{ $card['sub_industry'] }}@endif
            </p>
            <h3 class="font-display text-lg font-semibold text-forest">
                <a href="{{ route('business.show', ['id' => $card['id']]) }}" class="rounded hover:text-emerald focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">{{ $card['name'] }}</a>
            </h3>
            <p class="text-xs text-sage">
                {{ $card['location'] }}
                @if ($card['location_note']) <span class="text-forest/40">&middot;</span> {{ $card['location_note'] }} @endif
                @if ($card['listed']) <span class="text-forest/40">&middot;</span> Listed {{ $card['listed'] }} @endif
            </p>
            @if ($card['excerpt'])
                <p class="pt-1 text-xs leading-relaxed text-ink/75 line-clamp-2">{{ $card['excerpt'] }}</p>
            @endif
        </div>
        </div>

        @if ($card['score'] !== null)
            <div class="shrink-0 rounded-2xl border border-forest/10 bg-ivory/60 px-4 py-3 text-xs lg:w-52">
                <p class="font-display text-xl font-semibold text-forest tabular">{{ $card['score'] }}<span class="text-sm text-sage">/100</span></p>
                <p class="text-[11px] text-sage">Buy box fit (known criteria met)</p>
                @if ($card['met'])
                    <p class="mt-1.5 text-[11px] text-emerald">&check; {{ implode(', ', $card['met']) }}</p>
                @endif
                @if ($card['unknown_note'])
                    <p class="mt-0.5 text-[11px] text-forest/60">? {{ $card['unknown_note'] }}</p>
                @endif
            </div>
        @endif
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-forest/5 pt-4 text-xs sm:grid-cols-5">
        <div><dt class="text-sage">Asking price</dt><dd class="font-semibold text-forest tabular">{{ $card['asking'] }}</dd></div>
        <div><dt class="text-sage">Revenue</dt><dd class="font-semibold text-forest tabular">{{ $card['revenue'] }}</dd></div>
        <div><dt class="text-sage">{{ $card['earnings_label'] }}</dt><dd class="font-semibold text-forest tabular">{{ $card['earnings'] }}</dd></div>
        <div><dt class="text-sage">Price / earnings</dt><dd class="font-semibold text-forest tabular">{{ $card['multiple'] }}</dd></div>
        <div class="col-span-2 flex items-end justify-start gap-2 sm:col-span-1 sm:justify-end">
            <button type="button"
                x-data="{ saved: {{ $saved ? 'true' : 'false' }} }"
                @click="saved = !saved; fetch('{{ route('watchlist.toggle', ['businessId' => $card['id']]) }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                :aria-pressed="saved.toString()"
                class="rounded-xl border border-forest/15 px-3 py-2 font-semibold text-forest transition hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                x-text="saved ? 'Saved' : 'Save'">{{ $saved ? 'Saved' : 'Save' }}</button>
            <a href="{{ route('business.show', ['id' => $card['id']]) }}" class="rounded-xl bg-forest-deep px-3 py-2 font-semibold text-ivory transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">View</a>
        </div>
    </dl>

    @php($bm = $card['benchmark'] ?? null)
    @if ($bm && $bm['status'] !== 'no_benchmark')
        <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-2xl bg-ivory/60 px-3 py-2 text-[11px]">
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
