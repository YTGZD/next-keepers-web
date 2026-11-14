<div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm" x-data="{ subscribed: false, email: '' }">
    {{-- Header --}}
    <div class="flex items-center gap-2">
        <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-xs font-bold uppercase tracking-widest text-forest">Stay Informed</h3>
    </div>

    <p class="mt-2 text-xs text-sage leading-relaxed">
        Get the latest market insights, new opportunities, and platform updates.
    </p>

    {{-- Success Message --}}
    <div x-show="subscribed" x-cloak class="mt-4 rounded-xl bg-emerald/10 p-3 text-xs text-emerald font-semibold text-center">
        Thank you for subscribing!
    </div>

    {{-- Subscription Form --}}
    <form x-show="!subscribed" @submit.prevent="if(email){ fetch('{{ route('newsletter.subscribe') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify({ email }) }); subscribed = true; }" class="mt-4 flex flex-col gap-2.5">
        <label for="newsletter-email" class="sr-only">Your email address</label>
        <div class="flex gap-2">
            <input
                id="newsletter-email"
                type="email"
                required
                x-model="email"
                placeholder="Your email address"
                class="w-full rounded-xl border border-forest/15 bg-white px-3.5 py-2.5 text-xs text-ink placeholder:text-sage focus:border-gold focus:outline-none focus:ring-1 focus:ring-gold"
            >
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-forest px-4 py-2.5 text-xs font-semibold text-ivory transition hover:bg-emerald shrink-0"
            >
                <span>Subscribe</span>
                <span aria-hidden="true">&rarr;</span>
            </button>
        </div>
    </form>
</div>
