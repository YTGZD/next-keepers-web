{{--
    Multi-select dropdown inside the dealBox Alpine component.
    Expects $field (criteria key), $label, $all ("All industries"), $options (JS expression giving [{value, label}]).
--}}
<div class="relative" x-data="{ open: false, filter: '' }" @keydown.escape.prevent.stop="open = false; $refs.button.focus()" @click.outside="open = false">
    <span class="mb-1 block text-xs text-forest/80" id="ms-{{ $field }}-label">{{ $label }}</span>
    <button type="button" x-ref="button" @click="open = !open; if (open) $nextTick(() => $refs.search?.focus())"
        :aria-expanded="open.toString()" aria-haspopup="true" aria-labelledby="ms-{{ $field }}-label ms-{{ $field }}-value"
        class="flex w-full items-center justify-between gap-2 rounded-lg border border-forest/15 bg-ivory/70 px-3 py-2 text-left text-sm text-forest transition hover:border-forest/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
        <span id="ms-{{ $field }}-value" class="truncate" x-text="summary('{{ $field }}', '{{ $all }}')">{{ $all }}</span>
        <svg class="h-4 w-4 shrink-0 text-forest/60" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
    </button>
    <div x-show="open" x-cloak x-transition.opacity.duration.100ms
        class="absolute left-0 right-0 z-30 mt-1 rounded-xl border border-forest/15 bg-white p-2 shadow-lg" role="group" aria-labelledby="ms-{{ $field }}-label">
        <template x-if="{{ $options }}.length > 8">
            <input type="search" x-ref="search" x-model="filter" placeholder="Filter" aria-label="Filter {{ strtolower($label) }}"
                class="mb-2 w-full rounded-lg border border-forest/15 px-2.5 py-1.5 text-xs focus:border-gold focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
        </template>
        <div class="max-h-60 space-y-0.5 overflow-y-auto">
            <template x-for="option in {{ $options }}.filter(o => o.label.toLowerCase().includes(filter.toLowerCase()))" :key="option.value">
                <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-xs text-forest hover:bg-ivory">
                    <input type="checkbox" :value="option.value" x-model="criteria.{{ $field }}" class="rounded border-forest/30 accent-[#1B604A]">
                    <span x-text="option.label"></span>
                </label>
            </template>
            <p x-show="{{ $options }}.length === 0" class="px-2 py-1.5 text-xs text-sage">Nothing to choose yet.</p>
        </div>
        <div class="mt-2 flex justify-between border-t border-forest/10 pt-2">
            <button type="button" @click="criteria.{{ $field }} = []" class="rounded px-2 py-1 text-xs font-semibold text-emerald hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">{{ $all }}</button>
            <button type="button" @click="open = false; $refs.button.focus()" class="rounded px-2 py-1 text-xs font-semibold text-forest hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">Done</button>
        </div>
    </div>
</div>
