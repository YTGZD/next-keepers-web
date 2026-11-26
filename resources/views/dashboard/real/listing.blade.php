@php
    $earningsLabel = $listing->earningsLabel();
    $money = fn ($v) => $v === null ? null : \App\Models\Business::formatCompactCurrency($v);
    $kpis = [
        ['Asking price', $card['asking'], 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Revenue', $card['revenue'], 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
        [$earningsLabel, $card['earnings'], 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
        ['Price / earnings', $card['multiple'], 'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z'],
        ['Employees', $listing->employees ? number_format($listing->employees) : 'Not disclosed', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
    ];
    $glance = collect([['Asking price', $listing->asking_price], ['Revenue', $listing->annual_revenue], [$earningsLabel, $listing->earnings]])->filter(fn ($r) => $r[1] !== null)->values();
    $glanceMax = max(1, $glance->max(fn ($r) => $r[1]));
    $highlightKeys = ['Established', 'Employees', 'Listing type', 'Real estate', 'Seller financing', 'Reason for sale', 'Broker'];
    $highlights = collect($facts)->only($highlightKeys);
    $tabs = ['overview' => 'Overview', 'financials' => 'Financials', 'market' => 'Market multiples', 'details' => 'Business details', 'sources' => 'Data sources'];
@endphp
<x-layouts.dashboard :title="$listing->name.' | Next Keepers'" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10" x-data="{ saved: {{ $inWatchlist ? 'true' : 'false' }}, inquiryOpen: false, tab: 'overview' }">
        @if (session('status'))
            <p class="rounded-2xl border border-emerald/30 bg-emerald/10 px-4 py-3 text-sm text-forest" role="status">{{ session('status') }} We'll reply to {{ $userEmail }}.</p>
        @endif
        <a href="{{ route('discover') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-emerald hover:text-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">&larr; Back to listings</a>

        {{-- Hero --}}
        <header class="grid overflow-hidden rounded-[2rem] border border-forest/10 bg-white shadow-sm lg:grid-cols-[minmax(0,1fr)_26rem]">
            <div class="flex flex-col justify-center p-6 sm:p-9">
                <p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold uppercase tracking-wider">
                    <span class="rounded-full bg-emerald/10 px-2.5 py-0.5 text-emerald">{{ $card['industry'] }}</span>
                    @if ($card['sub_industry'])<span class="text-sage">{{ $card['sub_industry'] }}</span>@endif
                </p>
                <h1 class="mt-3 font-display text-3xl font-medium leading-tight tracking-tight text-forest sm:text-4xl">{{ $listing->name }}</h1>
                <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-sage">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    {{ $listing->location }}
                    @if ($card['location_note'])<span class="text-forest/40">&middot;</span> {{ $card['location_note'] }}@endif
                    @if ($card['listed'])<span class="text-forest/40">&middot;</span> Listed {{ $card['listed'] }}@endif
                </p>
                @if ($card['excerpt'])
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-ink/75">{{ $card['excerpt'] }}</p>
                @endif
                @if ($card['badges'])
                    <ul class="mt-5 flex flex-wrap gap-2" aria-label="Listing facts">
                        @foreach ($card['badges'] as $badge)
                            <li class="rounded-full bg-ivory px-3 py-1.5 text-xs font-medium text-forest ring-1 ring-forest/10">{{ $badge }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="min-h-56 bg-ivory lg:min-h-full">
                @if ($photo = $listing->photoUrl())
                    <img src="{{ $photo }}" alt="Photo of {{ $listing->name }}" decoding="async" class="h-full max-h-96 w-full object-cover lg:max-h-none">
                @else
                    <div class="relative isolate h-full min-h-56 w-full overflow-hidden bg-forest-deep" aria-hidden="true">
                        <img src="{{ asset('images/dashboard/mountain-clean.jpg') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40 mix-blend-luminosity">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-forest-deep/80 to-transparent"></div>
                        <span class="absolute bottom-4 left-4 rounded-full border border-ivory/20 bg-ivory/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-ivory/80 backdrop-blur-sm">No photo yet</span>
                    </div>
                @endif
            </div>
        </header>

        {{-- Key figures --}}
        <section aria-label="Key figures as listed" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
            @foreach ($kpis as [$label, $value, $icon])
                <div class="flex items-center gap-3 rounded-2xl border border-forest/10 bg-white p-4 shadow-sm">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald/10 text-emerald">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        @if ($value === 'Not disclosed')
                            <p class="text-xs font-medium text-forest/45">Not disclosed</p>
                        @else
                            <p class="truncate font-display text-2xl font-semibold leading-none text-forest tabular">{{ $value }}</p>
                        @endif
                        <p class="mt-1 truncate text-[11px] text-sage">{{ $label }}</p>
                    </div>
                </div>
            @endforeach
        </section>
        <p class="-mt-3 text-[11px] text-sage">Figures as listed, not verified by Next Keepers{{ $listing->earnings_basis && $listing->earnings_basis !== 'None' ? ' · Earnings basis: '.$listing->earnings_basis : '' }}.</p>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <div class="min-w-0 space-y-5 lg:col-span-8">
                {{-- Tabs --}}
                <div role="tablist" aria-label="Listing sections" class="flex gap-1 overflow-x-auto border-b border-forest/10"
                    @keydown.arrow-right.prevent="tab = Object.keys({{ \Illuminate\Support\Js::from($tabs) }})[(Object.keys({{ \Illuminate\Support\Js::from($tabs) }}).indexOf(tab) + 1) % {{ count($tabs) }}]; $nextTick(() => $refs['t_' + tab].focus())"
                    @keydown.arrow-left.prevent="tab = Object.keys({{ \Illuminate\Support\Js::from($tabs) }})[(Object.keys({{ \Illuminate\Support\Js::from($tabs) }}).indexOf(tab) + {{ count($tabs) - 1 }}) % {{ count($tabs) }}]; $nextTick(() => $refs['t_' + tab].focus())">
                    @foreach ($tabs as $key => $label)
                        <button type="button" role="tab" id="tab-{{ $key }}" x-ref="t_{{ $key }}" aria-controls="panel-{{ $key }}"
                            @click="tab = '{{ $key }}'" :aria-selected="(tab === '{{ $key }}').toString()" :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            :class="tab === '{{ $key }}' ? 'border-gold text-forest' : 'border-transparent text-sage hover:text-forest'"
                            class="-mb-px shrink-0 border-b-2 px-4 py-3 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">{{ $label }}</button>
                    @endforeach
                </div>

                {{-- Overview --}}
                <div role="tabpanel" id="panel-overview" aria-labelledby="tab-overview" x-show="tab === 'overview'" class="space-y-5">
                    <section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                        <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-forest"><span class="h-4 w-1 rounded-full bg-gold" aria-hidden="true"></span> Business overview</h2>
                        <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-ink/80">{{ $listing->description ?: 'The listing has no description.' }}</div>
                        @if (in_array('DESC_TRUNCATED', $listing->qa_flags ?? [], true))
                            <p class="mt-3 text-xs text-sage">This is the listing's short summary. The full description is on the original listing.</p>
                        @endif
                    </section>

                    @if ($highlights->isNotEmpty())
                        <section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                            <h2 class="flex items-center gap-2 font-display text-lg font-semibold text-forest"><span class="h-4 w-1 rounded-full bg-gold" aria-hidden="true"></span> Business highlights</h2>
                            <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                                @foreach ($highlights as $label => $value)
                                    <div class="flex justify-between gap-4 border-b border-forest/5 pb-2">
                                        <dt class="text-sage">{{ $label }}</dt>
                                        <dd class="text-right font-semibold text-forest">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    @endif

                    @if ($glance->count() > 1)
                        @include('dashboard.real.partials.glance', ['glance' => $glance, 'glanceMax' => $glanceMax, 'gid' => 'overview'])
                    @endif
                </div>

                {{-- Financials --}}
                <div role="tabpanel" id="panel-financials" aria-labelledby="tab-financials" x-show="tab === 'financials'" x-cloak class="space-y-5">
                    <section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="font-display text-lg font-semibold text-forest">Figures as listed</h2>
                            <p class="text-[11px] text-sage">Not verified by Next Keepers</p>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($figures as $figure)
                                <div class="rounded-2xl bg-ivory/70 p-4">
                                    <dt class="text-[11px] text-sage">{{ $figure['label'] }}</dt>
                                    @if ($figure['value'] === 'Not disclosed')
                                        <dd class="mt-1 text-xs font-medium text-forest/45">Not disclosed</dd>
                                    @else
                                        <dd class="mt-0.5 font-display text-xl font-semibold text-forest tabular">{{ $figure['value'] }}</dd>
                                    @endif
                                </div>
                            @endforeach
                        </dl>
                        @if ($cashNeeded && $cashNeeded != $listing->asking_price)
                            <p class="mt-4 text-xs leading-relaxed text-sage">Estimated cash needed for the business (price without real estate, plus equipment or inventory sold on top): <span class="font-semibold text-forest">{{ \App\Models\Business::formatCompactCurrency($cashNeeded) }}</span>.</p>
                        @endif
                    </section>
                    @if ($glance->count() > 1)
                        @include('dashboard.real.partials.glance', ['glance' => $glance, 'glanceMax' => $glanceMax, 'gid' => 'financials'])
                    @endif
                </div>

                {{-- Market multiples --}}
                <div role="tabpanel" id="panel-market" aria-labelledby="tab-market" x-show="tab === 'market'" x-cloak>
                    @include('dashboard.real.partials.benchmark', ['bm' => $card['benchmark']])
                </div>

                {{-- Details --}}
                <div role="tabpanel" id="panel-details" aria-labelledby="tab-details" x-show="tab === 'details'" x-cloak>
                    <section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                        <h2 class="font-display text-lg font-semibold text-forest">Business details</h2>
                        @if ($facts)
                            <dl class="mt-4 grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                                @foreach ($facts as $label => $value)
                                    <div class="flex justify-between gap-4 border-b border-forest/5 pb-2">
                                        <dt class="text-sage">{{ $label }}</dt>
                                        <dd class="text-right font-semibold text-forest">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="mt-3 text-sm text-sage">The listing doesn't give further details.</p>
                        @endif
                    </section>
                </div>

                {{-- Data sources --}}
                <div role="tabpanel" id="panel-sources" aria-labelledby="tab-sources" x-show="tab === 'sources'" x-cloak>
                    <section class="rounded-3xl border border-forest/10 bg-white p-6 text-sm leading-relaxed text-ink/80 shadow-sm">
                        <h2 class="font-display text-lg font-semibold text-forest">About this data</h2>
                        <p class="mt-3">Source: <span class="font-semibold text-forest">{{ $listing->source ?? 'Not recorded' }}</span>{{ $listing->scraped_on ? ', collected '.$listing->scraped_on->format('M j, Y') : '' }}. The listing may have changed or sold since.</p>
                        <p class="mt-2">Prices and financials are as the seller or broker listed them. Next Keepers has not verified them; always confirm with the broker.</p>
                        @if ($listing->qa_notes)
                            <p class="mt-2"><span class="font-semibold text-forest">Data notes:</span> {{ $listing->qa_notes }}</p>
                        @endif
                        @if ($listing->source_url)
                            <a href="{{ $listing->source_url }}" target="_blank" rel="noopener noreferrer nofollow" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald hover:text-forest">View original listing{{ $listing->source ? ' on '.$listing->source : '' }} &nearr;</a>
                        @endif
                    </section>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-5 lg:col-span-4 lg:sticky lg:top-4">
                <section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm" aria-labelledby="fit-heading">
                    <h2 id="fit-heading" class="flex items-center gap-2 font-display text-base font-semibold text-forest"><span class="h-4 w-1 rounded-full bg-gold" aria-hidden="true"></span> Fit with your buy box</h2>
                    @if ($card['score'] !== null)
                        <div class="mt-4 flex items-center gap-5">
                            @include('dashboard.real.partials.score-ring', ['score' => $card['score'], 'size' => 104])
                            <div class="min-w-0 text-xs leading-relaxed">
                                @if ($card['met'])<p class="text-emerald">&check; Meets: {{ implode(', ', $card['met']) }}</p>@endif
                                @if ($card['unknown_note'])<p class="mt-1 text-forest/60">? {{ $card['unknown_note'] }}</p>@endif
                                <p class="mt-1 text-sage">Share of your criteria this listing is known to meet.</p>
                            </div>
                        </div>
                        <a href="{{ route('buybox') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-emerald hover:text-forest">Edit buy box &rarr;</a>
                    @else
                        <p class="mt-3 text-xs leading-relaxed text-sage">Set up your buy box and every listing gets a fit score based on the criteria it is known to meet.</p>
                        <a href="{{ route('buybox') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-emerald hover:text-forest">Set up buy box &rarr;</a>
                    @endif
                </section>

                <section class="space-y-3 rounded-3xl border border-forest/10 bg-white p-6 shadow-sm" aria-label="Actions">
                    <button type="button"
                        @click="saved = !saved; fetch('{{ route('watchlist.toggle', ['businessId' => $listing->id]) }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                        :aria-pressed="saved.toString()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-forest-deep px-4 py-3 text-sm font-semibold text-ivory transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                        <svg class="size-4" :fill="saved ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/></svg>
                        <span x-text="saved ? 'Saved to watchlist' : 'Add to watchlist'">{{ $inWatchlist ? 'Saved to watchlist' : 'Add to watchlist' }}</span>
                    </button>
                    <button type="button" @click="inquiryOpen = !inquiryOpen" :aria-expanded="inquiryOpen.toString()"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gold px-4 py-3 text-sm font-bold text-forest-deep transition hover:bg-gold-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-forest">Ask about this listing</button>
                    @if ($listing->source_url)
                        <a href="{{ $listing->source_url }}" target="_blank" rel="noopener noreferrer nofollow" class="flex w-full items-center justify-center gap-2 rounded-xl border border-forest/15 px-4 py-3 text-sm font-semibold text-forest transition hover:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">View original listing{{ $listing->source ? ' on '.$listing->source : '' }} &nearr;</a>
                    @endif

                    <form x-show="inquiryOpen" x-cloak method="POST" action="{{ route('business.inquire', ['businessId' => $listing->id]) }}" class="grid gap-3 border-t border-forest/10 pt-4 text-sm">
                        @csrf
                        <input type="hidden" name="type" value="info">
                        <input type="hidden" name="name" value="{{ $userName }}">
                        <input type="hidden" name="email" value="{{ $userEmail }}">
                        <label class="text-xs leading-relaxed text-sage">We pass your question to the Next Keepers team, who reply by email. We don't contact the broker on your behalf without asking you first.
                            <textarea name="message" rows="4" maxlength="2000" required class="mt-2 w-full rounded-xl border border-forest/15 px-3 py-2 text-sm text-ink focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold" placeholder="What would you like to know?"></textarea>
                        </label>
                        <button type="submit" class="justify-self-start rounded-xl bg-forest-deep px-4 py-2.5 text-xs font-bold text-ivory hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Send question</button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-layouts.dashboard>
