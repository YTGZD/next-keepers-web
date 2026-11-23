{{-- One deal box slider. Expects $id, $label, $min, $max (end labels) and $slider (key in the dealBox component's sliders). --}}
<div>
    <div class="flex items-baseline justify-between gap-3">
        <label for="{{ $id }}" class="text-xs text-forest/80">{{ $label }}</label>
        <span class="text-sm font-bold text-forest tabular" x-text="sliderText('{{ $slider }}')" aria-hidden="true"></span>
    </div>
    <input id="{{ $id }}" type="range" min="0" :max="sliders.{{ $slider }}.stops.length - 1" step="1"
        x-model.number="positions.{{ $slider }}" :style="`--fill: ${positions.{{ $slider }} / (sliders.{{ $slider }}.stops.length - 1) * 100}%`" :aria-valuetext="sliderText('{{ $slider }}')"
        class="deal-range mt-2 w-full">
    <div class="mt-0.5 flex justify-between text-[11px] text-sage" aria-hidden="true">
        <span>{{ $min }}</span><span>{{ $max }}</span>
    </div>
</div>
