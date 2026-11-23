<x-layouts.dashboard title="Deal Box | Next Keepers" data="real">
    <div class="mx-auto max-w-7xl space-y-6 pb-10" x-data="dealBox(@js($dealBox))">
        <header>
            <h1 class="font-display text-3xl font-medium tracking-tight text-forest">Deal box</h1>
            <p class="mt-1 text-sm text-sage">Drag the sliders; the list below updates as you go. Saved boxes are private to you, and the one you save last is what your dashboard matches on.</p>
        </header>

        <section class="space-y-5 rounded-3xl border border-forest/10 bg-white p-5 shadow-sm" aria-label="Deal box criteria">
            {{-- Saved boxes --}}
            <div class="flex flex-wrap items-end gap-3">
                <label class="flex items-center gap-3 text-xs text-forest/80">
                    <span>Saved box</span>
                    <select x-model="selectedId" @change="loadBox()" class="min-w-44 rounded-lg border border-forest/15 bg-ivory/70 px-3 py-2 text-sm text-forest focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                        <option value="">New box</option>
                        <template x-for="box in boxes" :key="box.id">
                            <option :value="String(box.id)" x-text="box.name" :selected="String(box.id) === selectedId"></option>
                        </template>
                    </select>
                </label>
                <label class="min-w-44 flex-1 sm:max-w-56">
                    <span class="sr-only">Box name</span>
                    <input type="text" x-model="name" maxlength="60" placeholder="Name this box" @keydown.enter.prevent="save()"
                        class="w-full rounded-lg border border-forest/15 bg-ivory/70 px-3 py-2 text-sm text-forest focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                </label>
                <button type="button" @click="save()" :disabled="busy"
                    class="rounded-lg bg-emerald px-5 py-2 text-sm font-semibold text-ivory transition hover:bg-forest focus:outline-none focus-visible:ring-2 focus-visible:ring-gold disabled:opacity-60">Save</button>
                <button type="button" @click="remove()" :disabled="busy || !selectedId"
                    class="rounded-lg border border-forest/20 bg-white px-4 py-2 text-sm font-medium text-forest transition hover:border-forest/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold disabled:opacity-50">Delete</button>
                <button type="button" @click="reset()"
                    class="rounded-lg border border-forest/20 bg-white px-4 py-2 text-sm font-medium text-forest transition hover:border-forest/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Reset to default</button>
                <p class="w-full text-xs sm:w-auto" :class="error ? 'text-rose-700' : 'text-emerald'" x-text="notice" role="status" aria-live="polite"></p>
            </div>

            {{-- Sliders --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-3">
                @include('dashboard.real.partials.slider', ['id' => 'db-price', 'label' => 'Price, without real estate', 'slider' => 'price', 'min' => '$50K', 'max' => 'No limit'])
                @include('dashboard.real.partials.slider', ['id' => 'db-sde', 'label' => 'SDE / cash flow', 'slider' => 'sde', 'min' => 'Any', 'max' => '$750K'])
                @include('dashboard.real.partials.slider', ['id' => 'db-multiple', 'label' => 'Price / SDE', 'slider' => 'multiple', 'min' => '0.5×', 'max' => 'No limit'])
                @include('dashboard.real.partials.slider', ['id' => 'db-years', 'label' => 'Years in business', 'slider' => 'years', 'min' => 'Any', 'max' => '50'])
            </div>

            {{-- Lists --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @include('dashboard.real.partials.multi-select', ['field' => 'industries', 'label' => 'Industries', 'all' => 'All industries', 'options' => 'industryOptions'])
                @include('dashboard.real.partials.multi-select', ['field' => 'states', 'label' => 'States', 'all' => 'All states', 'options' => 'stateOptions'])
                @include('dashboard.real.partials.multi-select', ['field' => 'counties', 'label' => 'Counties', 'all' => 'All counties', 'options' => 'countyOptions'])
                @include('dashboard.real.partials.multi-select', ['field' => 'sources', 'label' => 'Source', 'all' => 'All sources', 'options' => 'sourceOptions'])
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block">
                    <span class="mb-1 block text-xs text-forest/80">Real estate</span>
                    <select x-model="criteria.real_estate_pref" class="w-full rounded-lg border border-forest/15 bg-ivory/70 px-3 py-2 text-sm text-forest focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                        <option value="">Any</option>
                        <option value="leased">Leased, no real estate</option>
                        <option value="included">Real estate included or owned</option>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs text-forest/80">Keyword</span>
                    <input type="search" x-model.debounce.400ms="criteria.keyword" maxlength="120" placeholder="e.g. pizza, Amherst, Webster"
                        class="w-full rounded-lg border border-forest/15 bg-ivory/70 px-3 py-2 text-sm text-forest focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                </label>
            </div>

            {{-- Switches --}}
            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-forest">
                @foreach ([
                    'require_earnings' => 'Only listings that disclose earnings',
                    'seller_financing_only' => 'Seller financing offered',
                    'operating_only' => 'Operating businesses only',
                    'hide_delisted' => 'Hide delisted',
                    'hide_out_of_county' => 'Hide listings flagged as outside the county',
                ] as $key => $text)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" x-model="criteria.{{ $key }}" class="h-4 w-4 rounded border-forest/30 accent-[#1B604A] focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
                        {{ $text }}
                    </label>
                @endforeach
            </div>
            <p class="text-[11px] text-sage">Figures a listing doesn't disclose never count against it: a listing with no price still shows under a price limit, marked "not disclosed". Real estate and years in business work the same way.</p>
        </section>

        <section aria-labelledby="bb-matches" aria-live="polite" :aria-busy="loading.toString()" class="transition-opacity" :class="loading && 'opacity-60'">
            <div x-ref="results">
                @include('dashboard.real.deal-box-results')
            </div>
        </section>
    </div>
</x-layouts.dashboard>
