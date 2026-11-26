{{--
    Circular score. Expects $score (0-100) and optional $size (px, default 96) and $label.
    Static (no animation), so it respects prefers-reduced-motion.
--}}
@php
    $size = $size ?? 96;
    $r = 40;
    $circ = round(2 * M_PI * $r, 1);
    $offset = round($circ * (1 - max(0, min(100, $score)) / 100), 1);
@endphp
<div class="relative shrink-0" style="width: {{ $size }}px; height: {{ $size }}px" role="img" aria-label="{{ $label ?? 'Buy box fit' }}: {{ $score }} out of 100">
    <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90" aria-hidden="true">
        <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="#ebe6d8" stroke-width="9"/>
        <circle cx="50" cy="50" r="{{ $r }}" fill="none" stroke="#1b604a" stroke-width="9" stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}"/>
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
        <span class="font-display font-semibold text-forest tabular" style="font-size: {{ round($size * 0.28) }}px">{{ $score }}</span>
        <span class="mt-0.5 text-[9px] font-medium uppercase tracking-wider text-sage">Fit</span>
    </div>
</div>
