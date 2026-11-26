@props(['label', 'value', 'note' => null])
<div class="min-w-[8.5rem] rounded-2xl border border-ivory/15 bg-ivory/5 px-4 py-3 backdrop-blur-sm">
    <dd class="font-display text-2xl font-semibold leading-none text-ivory tabular">{{ $value }}</dd>
    <dt class="mt-1.5 text-[11px] font-medium uppercase tracking-wider text-ivory/65">{{ $label }}</dt>
    @if ($note)<p class="mt-0.5 text-[10px] text-ivory/50">{{ $note }}</p>@endif
</div>
