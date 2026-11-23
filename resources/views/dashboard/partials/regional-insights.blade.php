{{--
    Regional Insights: four published state-level measures on a clickable US map
    (App\Support\RegionalInsights). Hover or tab to a state to preview it, click to pin it.
--}}
<div class="lg:col-span-5 rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col"
    x-data="regionalInsights(@js($regional))" id="regional-insights">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-display text-base font-bold text-forest">Regional Insights</h2>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald/25 bg-emerald/5 px-2.5 py-0.5 text-[10px] font-semibold text-emerald">
            <span class="size-1.5 rounded-full bg-emerald" aria-hidden="true"></span> Published data
        </span>
    </div>

    {{-- Tabs --}}
    <div class="mt-3 flex flex-wrap gap-1.5 text-xs font-semibold" role="tablist" aria-label="Regional measure">
        @foreach ($regional['metrics'] as $key => $m)
            <button type="button" role="tab" @click="setTab('{{ $key }}')" :aria-selected="tab === '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-forest text-ivory shadow-sm border-forest' : 'bg-white text-forest/70 border-forest/10 hover:border-gold'"
                class="rounded-full border px-3 py-1.5 transition">{{ $m['tab'] }}</button>
        @endforeach
    </div>

    <p class="mt-4 text-xs font-medium text-sage" x-text="metric.title"></p>

    {{-- Map --}}
    <div class="mt-2 rounded-2xl border border-forest/5 bg-emerald/5 p-2" @mouseleave="hover = null">
        <svg viewBox="0 0 975 610" class="h-auto w-full" role="group" aria-label="U.S. states map. Select a state to see its figures.">
            @foreach ($regional['shapes'] as $s)
                <path d="{{ $s['d'] }}" :fill="fill('{{ $s['abbr'] }}')"
                    :stroke="active === '{{ $s['abbr'] }}' ? '#c99b43' : '#ffffff'" :stroke-width="active === '{{ $s['abbr'] }}' ? 3 : 0.8"
                    class="cursor-pointer transition-[fill] duration-300 focus:outline-none"
                    tabindex="0" role="button" :aria-label="label('{{ $s['abbr'] }}')" :aria-pressed="pinned === '{{ $s['abbr'] }}'"
                    @mouseenter="hover = '{{ $s['abbr'] }}'" @focus="hover = '{{ $s['abbr'] }}'" @blur="hover = null"
                    @click="toggle('{{ $s['abbr'] }}')" @keydown.enter.prevent="toggle('{{ $s['abbr'] }}')" @keydown.space.prevent="toggle('{{ $s['abbr'] }}')"/>
            @endforeach
        </svg>
        <div class="mt-1 flex items-center justify-end gap-2 px-1 text-[10px] text-sage tabular">
            <span x-text="legend[0]"></span>
            <span class="flex overflow-hidden rounded" aria-hidden="true">
                @foreach (['#dfe9e3', '#b4cfc1', '#7fae97', '#3f8064', '#1b604a'] as $c)
                    <span class="h-2 w-5" style="background: {{ $c }}"></span>
                @endforeach
            </span>
            <span x-text="legend[1]"></span>
        </div>
    </div>

    {{-- Readout --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" aria-live="polite">
        <div>
            <div class="flex items-center gap-2">
                <p class="text-xs font-medium text-sage" x-text="view.name"></p>
                <button type="button" x-show="pinned" x-cloak @click="pinned = null" class="text-[11px] font-semibold text-emerald hover:text-forest">Back to U.S.</button>
            </div>
            <p class="font-display text-2xl font-bold text-forest tabular" x-text="fmt(view.value)"></p>
            <dl class="mt-1.5 space-y-1 text-[11px]">
                <template x-for="r in view.rows" :key="r[0]">
                    <div class="flex justify-between gap-3"><dt class="text-sage" x-text="r[0]"></dt><dd class="font-semibold text-forest tabular" x-text="r[1]"></dd></div>
                </template>
            </dl>
            <p class="mt-1.5 text-[11px] text-sage" x-show="view.name !== 'United States'">U.S.: <span class="font-semibold text-forest" x-text="fmt(metric.us.value)"></span></p>
        </div>
        <div class="rounded-xl bg-forest/5 p-2.5 text-[11px] font-medium leading-relaxed text-forest self-start" x-text="metric.insight"></div>
    </div>

    <p class="mt-3 text-[10px] leading-relaxed text-sage">Source: <span x-text="metric.source"></span>. Hover or tab to a state to preview it; click to pin it.</p>
</div>
