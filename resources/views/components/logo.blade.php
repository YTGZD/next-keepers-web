{{-- Placeholder lockup (NK monogram + serif wordmark). Swap for the official SVG when supplied. --}}
@props(['size' => 'md'])
<span {{ $attributes->class(['inline-flex items-center gap-3']) }}>
    <span aria-hidden="true" @class([
        'grid place-items-center rounded-lg border border-ivory/35 bg-emerald/40 font-display font-semibold tracking-tighter text-ivory shadow-inner',
        'size-10 text-lg' => $size === 'md',
        'size-12 text-xl' => $size === 'lg',
    ])>NK</span>
    <span @class(['font-display tracking-tight text-ivory', 'text-xl' => $size === 'md', 'text-2xl' => $size === 'lg'])>Next Keepers</span>
</span>
