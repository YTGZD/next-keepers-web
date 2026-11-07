@php
    $items = config('testimonials.items', []);
    // Until real, permissioned testimonials exist, show the mission statement in the same layout.
    $quotes = count($items)
        ? $items
        : [['quote' => "Preserving value. Building what's next.", 'name' => 'The Next Keepers mission', 'role' => null]];
    $many = count($quotes) > 1;
@endphp

<section id="mission" class="relative isolate overflow-hidden bg-ivory-deep/60" aria-label="{{ $many ? 'What people say' : 'Our mission' }}">
    <div class="mx-auto max-w-7xl lg:min-h-[17rem]">
        <div class="relative z-10 flex flex-col justify-center px-5 py-14 sm:px-8 lg:min-h-[17rem] lg:w-1/2 lg:py-16" x-data="{ i: 0, n: {{ count($quotes) }} }" data-reveal>
            <svg class="mb-4 h-10 w-12 text-gold" viewBox="0 0 48 40" fill="currentColor" aria-hidden="true">
                <path d="M0 40V24C0 10 8 2 21 0l1.6 5C15 7 11.6 12 11.3 18H21v22H0zm27 0V24C27 10 35 2 48 0l1.6 5C42 7 38.6 12 38.3 18H48v22H27z"/>
            </svg>

            @foreach ($quotes as $n => $q)
                <figure @if ($many) x-show="i === {{ $n }}" @if ($n) x-cloak @endif x-transition.opacity.duration.300ms aria-live="polite" @endif>
                    <blockquote>
                        <p class="max-w-lg font-display text-2xl italic leading-snug text-forest sm:text-3xl">{{ $q['quote'] }}</p>
                    </blockquote>
                    <figcaption class="mt-5 text-sm text-ink/70">
                        <span class="font-semibold text-forest">&mdash; {{ $q['name'] }}</span>
                        @if (! empty($q['role']))<span class="mx-2 text-ink/30" aria-hidden="true">|</span>{{ $q['role'] }}@endif
                    </figcaption>
                </figure>
            @endforeach

            @if ($many)
                <div class="mt-6 flex items-center gap-4">
                    <button type="button" @click="i = (i - 1 + n) % n" class="grid size-9 place-items-center rounded-full border border-forest/25 text-forest transition hover:bg-forest hover:text-ivory" aria-label="Previous quote">&larr;</button>
                    <div class="flex gap-2" role="group" aria-label="Choose a quote">
                        @foreach ($quotes as $n => $q)
                            <button type="button" @click="i = {{ $n }}" :aria-current="(i === {{ $n }}).toString()" :class="i === {{ $n }} ? 'bg-gold' : 'bg-forest/25'" class="size-2.5 rounded-full transition" aria-label="Quote {{ $n + 1 }} of {{ count($quotes) }}"></button>
                        @endforeach
                    </div>
                    <button type="button" @click="i = (i + 1) % n" class="grid size-9 place-items-center rounded-full border border-forest/25 text-forest transition hover:bg-forest hover:text-ivory" aria-label="Next quote">&rarr;</button>
                </div>
            @endif
        </div>
    </div>

    {{-- scenery runs to the right edge of the screen, fading in from the left --}}
    <div class="relative h-48 sm:h-60 lg:absolute lg:inset-y-0 lg:right-0 lg:h-auto lg:w-[54%]">
        <x-mountains class="absolute inset-0 size-full [mask-image:linear-gradient(to_bottom,transparent,black_30%)] lg:[clip-path:polygon(14%_0,100%_0,100%_100%,0_100%)] lg:[mask-image:linear-gradient(to_right,transparent,black_24%)]" />
    </div>
</section>
