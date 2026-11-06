@props(['variant' => 'gold', 'href' => null, 'type' => 'button'])
@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-full px-6 py-3 text-sm font-semibold transition duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold disabled:cursor-not-allowed disabled:opacity-60 ' . match ($variant) {
        'gold' => 'bg-gold text-forest-deep shadow-[0_8px_24px_-8px_rgba(201,155,67,0.7)] hover:-translate-y-0.5 hover:bg-gold-soft active:translate-y-0',
        'dark' => 'bg-forest text-ivory hover:-translate-y-0.5 hover:bg-emerald active:translate-y-0',
        'ghost' => 'border border-ivory/30 text-ivory hover:border-gold hover:text-gold-soft',
        'outline' => 'border border-forest/25 text-forest hover:border-forest hover:bg-forest hover:text-ivory',
    };
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
