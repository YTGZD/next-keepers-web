@php
    // [interest key, title, text, link label, image, icon path, alt text]
    $cards = [
        ['buyer', 'Buyers', 'Find and evaluate the right acquisition opportunities.', 'Explore for Buyers', 'buyer', 'M3 17l6-6 4 4 8-8M14 7h7v7', 'A buyer reviewing documents in an office'],
        ['owner', 'Business Owners', 'Plan your exit with confidence.', 'Explore for Owners', 'owner', 'M4 9l1-5h14l1 5M4 9v11h16V9M4 9a2.7 2.7 0 005.3 0 2.7 2.7 0 005.4 0A2.7 2.7 0 0020 9', 'A smiling small-business owner in her workshop'],
        ['investor', 'Investors', 'Discover attractive acquisition opportunities.', 'Explore for Investors', 'investor', 'M4 20V10m5 10V4m5 16v-7m5 7V8', 'An investor in an office overlooking a city'],
    ];
@endphp

<section id="audiences" class="bg-ivory py-16 sm:py-24" x-data>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="flex items-center gap-4" data-reveal>
            <span class="h-px flex-1 bg-forest/15"></span>
            <x-eyebrow class="!text-forest/70">Who we serve</x-eyebrow>
            <span class="h-px flex-1 bg-forest/15"></span>
        </div>

        <div class="mt-10 mx-auto grid max-w-xl gap-6 lg:max-w-none lg:grid-cols-3">
            @foreach ($cards as $i => [$key, $title, $text, $cta, $img, $icon, $alt])
                <article
                    data-reveal style="--reveal-delay: {{ $i * 90 }}ms"
                    class="group relative isolate aspect-[4/3.1] overflow-hidden rounded-2xl bg-forest shadow-lg shadow-forest/15 ring-1 ring-forest/10 transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-forest/25"
                >
                    <picture>
                        <source srcset="{{ asset('images/'.$img.'.webp') }}" type="image/webp">
                        <img src="{{ asset('images/'.$img.'.jpg') }}" alt="{{ $alt }}" loading="lazy" decoding="async" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-[1.03] ">
                    </picture>

                    {{-- frosted info panel, lower right, icon straddling its top edge (per the approved design) --}}
                    <div class="absolute bottom-3 right-3 w-[64%] rounded-xl border border-white/70 bg-ivory/90 px-4 pb-4 pt-8 shadow-lg backdrop-blur-md transition group-hover:border-gold sm:w-[58%] lg:w-[68%] xl:w-[60%]">
                        <span class="absolute -top-5 left-1/2 grid size-10 -translate-x-1/2 place-items-center rounded-full bg-forest text-gold ring-4 ring-ivory/90" aria-hidden="true">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <h3 class="text-center font-display text-xl text-forest">{{ $title }}</h3>
                        <p class="mt-1 text-center text-[13px] leading-snug text-ink/75">{{ $text }}</p>
                        <a href="#early-access" @click="$dispatch('set-interest', '{{ $key }}')" class="mt-3 flex items-center justify-center gap-1.5 rounded text-[13px] font-semibold text-emerald transition hover:text-forest">
                            {{ $cta }} <span aria-hidden="true" class="transition group-hover:translate-x-1">&rarr;</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
