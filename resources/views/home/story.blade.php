<section id="story" class="relative isolate overflow-hidden bg-forest-deep text-ivory">
    <div class="mx-auto grid max-w-7xl items-center gap-4 px-5 sm:px-8 lg:min-h-[34rem] lg:grid-cols-2">
        <div class="relative z-10 py-16 lg:py-24" data-reveal>
            <x-eyebrow>A generation of opportunity</x-eyebrow>
            <h2 class="mt-4 font-display text-4xl font-medium leading-tight tracking-tight sm:text-5xl">
                Every great business deserves a <span class="text-gold">next keeper.</span>
            </h2>
            <p class="mt-6 max-w-lg text-lg leading-relaxed text-ivory/80">
                As business owners prepare for what's next, Next Keepers helps preserve their legacy and empowers the next generation of owners. The lock is a business built over a lifetime. The key is the person who will carry it forward.
            </p>
            <x-button variant="ghost" href="#mission" class="mt-8">Our Mission <span aria-hidden="true">&rarr;</span></x-button>
        </div>

        <div class="relative -mx-5 sm:-mx-8 lg:absolute lg:inset-y-0 lg:right-0 lg:mx-0 lg:w-[62%]" data-key>
            <picture>
                <source srcset="{{ asset('images/key.webp') }}" type="image/webp">
                <img
                    src="{{ asset('images/key.jpg') }}" width="1800" height="1004" loading="lazy" decoding="async"
                    alt="An ornate golden key resting on a dark green pedestal"
                    class="animate-key-float h-full w-full object-cover object-center [mask-image:linear-gradient(to_right,transparent,black_38%)] max-lg:[mask-image:linear-gradient(to_bottom,transparent,black_30%)]"
                >
            </picture>
        </div>
    </div>
</section>
