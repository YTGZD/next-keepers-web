<x-layouts.dashboard :title="$listing->name.' | Next Keepers'" data="real">
    <div class="mx-auto max-w-6xl space-y-6 pb-10" x-data="{ saved: {{ $inWatchlist ? 'true' : 'false' }}, inquiryOpen: false }">
        @if (session('status'))
            <p class="rounded-2xl border border-emerald/30 bg-emerald/10 px-4 py-3 text-sm text-forest" role="status">{{ session('status') }} We'll reply to {{ $userEmail }}.</p>
        @endif
        <a href="{{ route('discover') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-emerald hover:text-forest">&larr; Back to listings</a>

        <header class="overflow-hidden rounded-3xl border border-forest/10 bg-white shadow-sm">
            @if ($photo = $listing->photoUrl())
                <img src="{{ $photo }}" alt="Photo of {{ $listing->name }}" decoding="async" class="aspect-[16/7] w-full bg-ivory object-cover">
            @endif
            <div class="p-6 sm:p-8">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sage">{{ $card['industry'] }}@if ($card['sub_industry']) &middot; {{ $card['sub_industry'] }}@endif</p>
            <h1 class="mt-1 font-display text-3xl font-medium tracking-tight text-forest sm:text-4xl">{{ $listing->name }}</h1>
            <p class="mt-2 text-sm text-sage">
                {{ $listing->location }}
                @if ($card['location_note']) &middot; {{ $card['location_note'] }} @endif
                @if ($card['listed']) &middot; Listed {{ $card['listed'] }} @endif
            </p>
            <div class="mt-5 flex flex-wrap gap-2 text-xs">
                <button type="button"
                    @click="saved = !saved; fetch('{{ route('watchlist.toggle', ['businessId' => $listing->id]) }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                    :aria-pressed="saved.toString()"
                    class="rounded-xl border border-forest/15 px-4 py-2 font-semibold text-forest hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                    x-text="saved ? 'Saved to watchlist' : 'Save to watchlist'">{{ $inWatchlist ? 'Saved to watchlist' : 'Save to watchlist' }}</button>
                <button type="button" @click="inquiryOpen = !inquiryOpen" :aria-expanded="inquiryOpen.toString()" class="rounded-xl bg-forest-deep px-4 py-2 font-semibold text-ivory hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Ask about this listing</button>
                @if ($listing->source_url)
                    <a href="{{ $listing->source_url }}" target="_blank" rel="noopener noreferrer nofollow" class="rounded-xl border border-forest/15 px-4 py-2 font-semibold text-forest hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">View original listing{{ $listing->source ? ' on '.$listing->source : '' }} &nearr;</a>
                @endif
            </div>

            <form x-show="inquiryOpen" x-cloak method="POST" action="{{ route('business.inquire', ['businessId' => $listing->id]) }}" class="mt-5 grid gap-3 rounded-2xl border border-forest/10 bg-ivory/50 p-4 text-sm sm:grid-cols-2">
                @csrf
                <input type="hidden" name="type" value="info">
                <input type="hidden" name="name" value="{{ $userName }}">
                <input type="hidden" name="email" value="{{ $userEmail }}">
                <label class="sm:col-span-2 text-xs text-sage">We pass your question to the Next Keepers team, who reply by email. We don't contact the broker on your behalf without asking you first.
                    <textarea name="message" rows="3" maxlength="2000" required class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold" placeholder="What would you like to know?"></textarea>
                </label>
                <button type="submit" class="justify-self-start rounded-xl bg-gold px-4 py-2 text-xs font-bold text-forest-deep hover:bg-gold-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-forest">Send question</button>
            </form>
            </div>
        </header>

        <section aria-labelledby="figures-heading" class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="figures-heading" class="font-display text-lg font-semibold text-forest">Figures as listed</h2>
                <p class="text-[11px] text-sage">Not verified by Next Keepers{{ $listing->earnings_basis && $listing->earnings_basis !== 'None' ? ' · Earnings basis: '.$listing->earnings_basis : '' }}</p>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($figures as $figure)
                    <div class="rounded-2xl bg-ivory/60 p-3">
                        <dt class="text-[11px] text-sage">{{ $figure['label'] }}</dt>
                        <dd class="mt-0.5 font-display text-lg font-semibold text-forest tabular">{{ $figure['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
            @if ($cashNeeded && $cashNeeded != $listing->asking_price)
                <p class="mt-3 text-xs text-sage">Estimated cash needed for the business (price without real estate, plus equipment or inventory sold on top): {{ \App\Models\Business::formatCompactCurrency($cashNeeded) }}.</p>
            @endif
        </section>

        @include('dashboard.real.partials.benchmark', ['bm' => $card['benchmark']])

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <section class="space-y-6 lg:col-span-8">
                <div class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-lg font-semibold text-forest">Description</h2>
                    <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink/80">{{ $listing->description ?: 'The listing has no description.' }}</div>
                    @if (in_array('DESC_TRUNCATED', $listing->qa_flags ?? [], true))
                        <p class="mt-3 text-xs text-sage">This is the listing's short summary. The full description is on the original listing.</p>
                    @endif
                </div>
            </section>

            <aside class="space-y-6 lg:col-span-4">
                @if ($card['score'] !== null)
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 text-xs shadow-sm">
                        <h2 class="font-display text-base font-semibold text-forest">Fit with your buy box</h2>
                        <p class="mt-2 font-display text-2xl font-semibold text-forest tabular">{{ $card['score'] }}<span class="text-sm text-sage">/100</span></p>
                        @if ($card['met'])<p class="mt-1 text-emerald">&check; Meets: {{ implode(', ', $card['met']) }}</p>@endif
                        @if ($card['unknown_note'])<p class="mt-1 text-forest/60">? {{ $card['unknown_note'] }}</p>@endif
                        <a href="{{ route('buybox') }}" class="mt-3 inline-block font-semibold text-emerald hover:text-forest">Edit buy box &rarr;</a>
                    </div>
                @endif

                @if ($facts)
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 shadow-sm">
                        <h2 class="font-display text-base font-semibold text-forest">Details</h2>
                        <dl class="mt-3 space-y-2 text-xs">
                            @foreach ($facts as $label => $value)
                                <div class="flex justify-between gap-4 border-b border-forest/5 pb-2 last:border-0">
                                    <dt class="text-sage">{{ $label }}</dt>
                                    <dd class="text-right font-semibold text-forest">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="rounded-3xl border border-forest/10 bg-white p-5 text-xs leading-relaxed text-ink/80 shadow-sm">
                    <h2 class="font-display text-base font-semibold text-forest">About this data</h2>
                    <p class="mt-2">Source: {{ $listing->source ?? 'Not recorded' }}{{ $listing->scraped_on ? ', collected '.$listing->scraped_on->format('M j, Y') : '' }}. The listing may have changed or sold since.</p>
                    @if ($listing->qa_notes)
                        <p class="mt-2"><span class="font-semibold text-forest">Data notes:</span> {{ $listing->qa_notes }}</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layouts.dashboard>
