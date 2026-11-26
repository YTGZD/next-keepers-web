@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Back to dashboard'])
{{-- Dark page header used across the dashboard. The slot holds stat tiles (see <x-dash-stat>). --}}
<header {{ $attributes->class('relative isolate overflow-hidden rounded-[2rem] bg-forest-deep text-ivory shadow-lg') }}>
    <img src="{{ asset('images/dashboard/mountain-clean.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-25 mix-blend-luminosity">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-forest-deep via-forest-deep/90 to-forest/60" aria-hidden="true"></div>
    <div class="flex flex-col gap-6 px-6 py-7 sm:px-9 sm:py-9 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-2xl">
            @if ($back)
                <a href="{{ $back }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gold hover:text-gold-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">&larr; {{ $backLabel }}</a>
            @endif
            <h1 class="mt-2 font-display text-3xl font-medium tracking-tight text-ivory sm:text-4xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-2 text-sm leading-relaxed text-ivory/75">{{ $subtitle }}</p>
            @endif
        </div>
        @if (! $slot->isEmpty())
            <dl class="flex flex-wrap gap-3">{{ $slot }}</dl>
        @endif
    </div>
</header>
