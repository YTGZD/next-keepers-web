@php
    $steps = [
        [
            'key' => 'discover', 'title' => 'Discover', 'icon' => 'M21 21l-4.3-4.3M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z',
            'lead' => 'Find the businesses worth a closer look.',
            'body' => 'Search businesses for sale in one place, filtered by industry, location, size and price, so you start from your criteria instead of from a pile of listings.',
            'points' => ['Filter by industry, geography, revenue, SDE and asking price', 'Save searches and build a watchlist', 'See new opportunities that match your buy box'],
        ],
        [
            'key' => 'enrich', 'title' => 'Enrich', 'icon' => 'M12 3l1.8 4.6L18.5 9l-4.7 1.4L12 15l-1.8-4.6L5.5 9l4.7-1.4L12 3zM19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15z',
            'lead' => 'Turn raw listings into clean, comparable data.',
            'body' => 'We standardize financials, categorize industries and add local market context. Every important field keeps its source and last-updated date, and anything missing is shown as missing.',
            'points' => ['Cleaned, standardized financial fields', 'Source and update date on key data', 'Market context for the location and industry'],
        ],
        [
            'key' => 'evaluate', 'title' => 'Evaluate', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'lead' => 'Judge every deal against what you actually want.',
            'body' => 'Fit scores compare each business with your buy box, and ratios like multiples and margins sit next to the data confidence behind them. Scores are decision support, not investment advice.',
            'points' => ['Fit score against your buy box', 'Multiples, margins and data-confidence signals', 'Strengths, risks and open questions listed side by side'],
        ],
        [
            'key' => 'connect', 'title' => 'Connect', 'icon' => 'M17 20h5v-2a4 4 0 00-5-3.9M9 11a4 4 0 100-8 4 4 0 000 8zm-7 9v-1a5 5 0 0110 0v1M16 3.1a4 4 0 010 7.8',
            'lead' => 'Reach the owners behind the businesses.',
            'body' => 'As the platform grows, serious buyers will be able to start conversations with owners open to passing the business on. Owner connections are on the roadmap, not live yet.',
            'points' => ['Request information on businesses you care about', 'Owner and broker conversations (coming)', 'A pipeline to track deals from first look to close (coming)'],
        ],
    ];
@endphp

<section id="platform" class="bg-ivory py-20 sm:py-28" x-data="{ step: 'discover' }">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="max-w-2xl" data-reveal>
            <x-eyebrow>The platform</x-eyebrow>
            <h2 class="mt-4 font-display text-4xl font-medium leading-tight tracking-tight text-forest sm:text-5xl">From scattered listings to a clear next step.</h2>
            <p class="mt-5 text-lg text-ink/75">Four steps, one workflow. Pick a step to see how it works.</p>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-5" role="tablist" aria-label="Platform workflow" data-reveal>
                <div class="grid gap-3">
                    @foreach ($steps as $i => $s)
                        <button
                            type="button" role="tab" id="tab-{{ $s['key'] }}" aria-controls="panel-{{ $s['key'] }}"
                            @click="step = '{{ $s['key'] }}'"
                            :aria-selected="(step === '{{ $s['key'] }}').toString()"
                            :tabindex="step === '{{ $s['key'] }}' ? 0 : -1"
                            @keydown.arrow-down.prevent="$el.nextElementSibling?.focus(); $el.nextElementSibling?.click()"
                            @keydown.arrow-up.prevent="$el.previousElementSibling?.focus(); $el.previousElementSibling?.click()"
                            :class="step === '{{ $s['key'] }}' ? 'border-gold bg-white shadow-lg shadow-forest/10' : 'border-forest/10 bg-white/50 hover:border-forest/30'"
                            class="flex items-center gap-4 rounded-2xl border p-4 text-left transition"
                        >
                            <span :class="step === '{{ $s['key'] }}' ? 'bg-forest text-gold' : 'bg-ivory-deep text-emerald'" class="grid size-12 shrink-0 place-items-center rounded-xl transition">
                                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/></svg>
                            </span>
                            <span>
                                <span class="block text-xs font-semibold uppercase tracking-widest text-gold">Step {{ $i + 1 }}</span>
                                <span class="block font-display text-xl text-forest">{{ $s['title'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-7" data-reveal style="--reveal-delay: 120ms">
                @foreach ($steps as $s)
                    <div
                        role="tabpanel" id="panel-{{ $s['key'] }}" aria-labelledby="tab-{{ $s['key'] }}"
                        x-show="step === '{{ $s['key'] }}'" x-cloak
                        x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0 translate-y-2"
                        class="h-full rounded-3xl bg-forest p-8 text-ivory sm:p-10"
                    >
                        <h3 class="font-display text-3xl leading-snug text-gold-soft">{{ $s['lead'] }}</h3>
                        <p class="mt-4 text-lg leading-relaxed text-ivory/80">{{ $s['body'] }}</p>
                        <ul class="mt-7 space-y-3">
                            @foreach ($s['points'] as $point)
                                <li class="flex gap-3 text-ivory/90">
                                    <svg class="mt-1 size-5 shrink-0 text-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
