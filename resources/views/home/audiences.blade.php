@php
    $cards = [
        ['buyer', 'Buyers', 'Find and evaluate the right acquisition opportunities.', 'Explore for Buyers', 'buyer.jpg', 'M3 17l6-6 4 4 8-8M14 7h7v7', 'A buyer reviewing documents in an office', 'lg:row-span-2'],
        ['owner', 'Business Owners', 'Plan your exit with confidence.', 'Explore for Owners', 'owner.jpg', 'M4 9l1-5h14l1 5M4 9v11h16V9M4 9a2.7 2.7 0 005.3 0 2.7 2.7 0 005.4 0A2.7 2.7 0 0020 9', 'A smiling small-business owner in her workshop', ''],
        ['investor', 'Investors', 'Discover attractive acquisition opportunities.', 'Explore for Investors', 'investor.jpg', 'M4 20V10m5 10V4m5 16v-7m5 7V8', 'An investor standing in an office overlooking a city', ''],
    ];
@endphp

<section id="audiences" class="bg-ivory py-20 sm:py-28" x-data>
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="flex items-center gap-4" data-reveal>
            <span class="h-px flex-1 bg-forest/15"></span>
            <x-eyebrow class="!text-forest/70">Who we serve</x-eyebrow>
            <span class="h-px flex-1 bg-forest/15"></span>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-[1.35fr_1fr] lg:grid-rows-2">
            @foreach ($cards as $i => [$key, $title, $text, $cta, $img, $icon, $alt, $span])
                <article data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="group relative isolate flex min-h-[26rem] overflow-hidden rounded-3xl bg-forest shadow-xl shadow-forest/15 transition duration-300 hover:-translate-y-1 hover:shadow-2xl {{ $span }}">
                    <img src="{{ asset('images/'.$img) }}" alt="{{ $alt }}" loading="lazy" decoding="async" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-[1.03] {{ ['investor' => 'object-top', 'owner' => 'object-[50%_20%]', 'buyer' => 'object-[60%_30%]'][$key] }}">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-forest-deep/90 via-forest-deep/30 to-transparent"></div>

                    <div class="mt-auto w-full p-4 sm:p-5">
                        <div class="rounded-2xl border border-ivory/60 bg-ivory/95 p-5 backdrop-blur transition group-hover:border-gold">
                            <span class="mb-3 grid size-10 place-items-center rounded-full bg-forest text-gold" aria-hidden="true">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <h3 class="font-display text-2xl text-forest">{{ $title }}</h3>
                            <p class="mt-1 text-sm text-ink/75">{{ $text }}</p>
                            <a href="#early-access" @click="$dispatch('set-interest', '{{ $key }}')" class="mt-4 inline-flex items-center gap-2 rounded text-sm font-semibold text-emerald transition hover:text-forest">
                                {{ $cta }} <span aria-hidden="true" class="transition group-hover:translate-x-1">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
