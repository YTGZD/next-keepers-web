<x-layouts.dashboard title="My Buy Box | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10">
        <header>
            <h1 class="font-display text-3xl font-medium tracking-tight text-forest">My buy box</h1>
            <p class="mt-1 text-sm text-sage">What you're looking for. Listings are matched on what they disclose; a figure a listing doesn't give is never counted against it.</p>
        </header>

        @if (session('status'))
            <p class="rounded-2xl border border-emerald/30 bg-emerald/10 px-4 py-3 text-sm text-forest" role="status">{{ session('status') }}</p>
        @endif

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <form method="POST" action="{{ route('buybox.update') }}" class="space-y-5 rounded-3xl border border-forest/10 bg-white p-5 text-sm shadow-sm lg:col-span-4">
                @csrf
                @if ($errors->any())
                    <div class="rounded-xl bg-rose-50 p-3 text-xs text-rose-800" role="alert">
                        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif

                <fieldset>
                    <legend class="font-semibold text-forest">Industries</legend>
                    <div class="mt-2 space-y-1.5 text-xs">
                        @foreach ($industryOptions as $industry)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="industries[]" value="{{ $industry }}" @checked(in_array($industry, old('industries', $box->industries ?? []), true)) class="rounded border-forest/30 accent-[#1B604A]">
                                {{ $industry }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="font-semibold text-forest">States</legend>
                    @if ($stateOptions)
                        <div class="mt-2 grid max-h-40 grid-cols-4 gap-1.5 overflow-y-auto text-xs">
                            @foreach ($stateOptions as $state)
                                <label class="flex items-center gap-1.5">
                                    <input type="checkbox" name="states[]" value="{{ $state }}" @checked(in_array($state, old('states', $box->states ?? []), true)) class="rounded border-forest/30 accent-[#1B604A]">
                                    {{ $state }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-[11px] text-sage">States with published listings.</p>
                    @else
                        <p class="mt-1 text-xs text-sage">States appear here once listings are published.</p>
                    @endif
                </fieldset>

                <label class="block">
                    <span class="font-semibold text-forest">Counties</span>
                    <input type="text" name="counties" value="{{ old('counties', implode(', ', $box->counties ?? [])) }}" placeholder="e.g. Erie, Monroe, Niagara" class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                    <span class="mt-1 block text-[11px] text-sage">Optional, separated by commas. Listings that give only a state won't match a county.</span>
                </label>

                <fieldset class="grid grid-cols-2 gap-3">
                    <legend class="col-span-2 font-semibold text-forest">Asking price ($)</legend>
                    <label class="text-xs text-sage">From<input type="number" name="min_asking_price" min="0" step="1000" value="{{ old('min_asking_price', $box->min_asking_price) }}" class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"></label>
                    <label class="text-xs text-sage">To<input type="number" name="max_asking_price" min="0" step="1000" value="{{ old('max_asking_price', $box->max_asking_price) }}" class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"></label>
                </fieldset>

                <div class="grid grid-cols-2 gap-3">
                    <label class="text-xs text-sage"><span class="font-semibold text-forest">Min. revenue ($)</span><input type="number" name="min_revenue" min="0" step="1000" value="{{ old('min_revenue', $box->min_revenue) }}" class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"></label>
                    <label class="text-xs text-sage"><span class="font-semibold text-forest">Min. earnings ($)</span><input type="number" name="min_earnings" min="0" step="1000" value="{{ old('min_earnings', $box->min_earnings) }}" class="mt-1 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"></label>
                </div>

                <label class="flex items-center gap-2 text-xs text-forest">
                    <input type="hidden" name="profitable_only" value="0">
                    <input type="checkbox" name="profitable_only" value="1" @checked(old('profitable_only', $box->profitable_only)) class="rounded border-forest/30 accent-[#1B604A]">
                    Profitable only (hides listings that don't disclose earnings)
                </label>

                <button type="submit" class="w-full rounded-2xl bg-gold py-3 text-xs font-bold text-forest-deep shadow transition hover:bg-gold-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-forest">Save and update matches</button>
            </form>

            <section class="space-y-4 lg:col-span-8" aria-labelledby="bb-matches">
                <h2 id="bb-matches" class="font-display text-xl font-semibold text-forest">
                    @if ($criteria) {{ number_format($matchCount) }} {{ \Illuminate\Support\Str::plural('match', $matchCount) }} @else Matches @endif
                </h2>
                @if ($criteria)
                    <p class="text-xs text-sage">{{ implode(' · ', $criteria) }}@if ($matchCount > count($cards)) · showing the best {{ count($cards) }}@endif</p>
                @endif

                @forelse ($cards as $card)
                    @include('dashboard.real.card', ['card' => $card, 'saved' => in_array($card['id'], $savedIds, true)])
                @empty
                    @if (! $criteria)
                        @include('dashboard.real.empty', ['title' => 'Set your criteria', 'body' => 'Choose at least one industry, location or figure, then save to see matching listings.'])
                    @else
                        @include('dashboard.real.empty', ['title' => 'No matches yet', 'body' => 'Nothing published fits these criteria right now. Widen the location or price range, or check back as more listings are published.'])
                    @endif
                @endforelse
            </section>
        </div>
    </div>
</x-layouts.dashboard>
