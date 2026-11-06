@php
    $interests = ['buyer' => 'Buying a business', 'owner' => 'Owning a business', 'investor' => 'Investing'];
    $old = old('interest', 'buyer');
@endphp

<section id="early-access" class="bg-ivory px-5 py-16 sm:px-8 sm:py-24">
    <div
        x-data="earlyAccess({ interest: @js($old), status: @js(session('signup') === 'success' ? 'success' : 'idle') })"
        @set-interest.window="pick($event.detail)"
        class="mx-auto grid max-w-6xl gap-10 overflow-hidden rounded-[2rem] bg-forest-deep p-8 text-ivory shadow-2xl shadow-forest/30 sm:p-12 lg:grid-cols-2 lg:gap-16"
    >
        <div data-reveal>
            <x-eyebrow>Become a founding member</x-eyebrow>
            <h2 class="mt-4 font-display text-4xl font-medium leading-tight tracking-tight sm:text-5xl">Your next acquisition starts with better intelligence.</h2>
            <p class="mt-5 text-ivory/75">Join the Next Keepers early-access community and be among the first to explore the platform.</p>
            <ul class="mt-6 space-y-2 text-sm text-ivory/70">
                <li class="flex gap-2"><span class="text-gold" aria-hidden="true">&#10003;</span> We email you when early access opens, in the order people join.</li>
                <li class="flex gap-2"><span class="text-gold" aria-hidden="true">&#10003;</span> No payment required, and no spam.</li>
            </ul>
        </div>

        <div data-reveal style="--reveal-delay: 120ms">
            {{-- success --}}
            <div x-show="status === 'success'" x-cloak role="status" class="grid h-full place-items-center rounded-2xl border border-gold/40 bg-forest p-8 text-center">
                <div>
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-gold text-forest-deep">
                        <svg class="size-7" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <h3 class="mt-5 font-display text-2xl">You're on the list.</h3>
                    <p class="mt-2 text-sm text-ivory/75">Thank you. We'll be in touch when early access opens.</p>
                </div>
            </div>

            <form
                x-show="status !== 'success'"
                method="POST" action="{{ route('early-access.store') }}"
                @submit.prevent="submit($event)"
                novalidate class="space-y-5"
            >
                @csrf
                {{-- honeypot: real people never see or fill this --}}
                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-ivory/90">I'm interested as someone</legend>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($interests as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="interest" value="{{ $value }}" class="peer sr-only" x-model="interest">
                                <span class="inline-block rounded-full border border-ivory/25 px-4 py-2 text-sm transition peer-checked:border-gold peer-checked:bg-gold peer-checked:text-forest-deep peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-gold hover:border-gold">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label for="ea-name" class="text-sm font-medium text-ivory/90">Name <span class="text-ivory/50">(optional)</span></label>
                    <input id="ea-name" name="name" type="text" autocomplete="name" maxlength="120" value="{{ old('name') }}"
                        class="mt-2 w-full rounded-xl border border-ivory/20 bg-ivory px-4 py-3 text-ink placeholder:text-sage focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/40">
                </div>

                <div>
                    <label for="ea-email" class="text-sm font-medium text-ivory/90">Email address</label>
                    <input id="ea-email" name="email" type="email" required autocomplete="email" maxlength="254" placeholder="you@company.com" value="{{ old('email') }}"
                        :aria-invalid="errors.email ? 'true' : 'false'" aria-describedby="ea-email-error"
                        class="mt-2 w-full rounded-xl border border-ivory/20 bg-ivory px-4 py-3 text-ink placeholder:text-sage focus:border-gold focus:outline-none focus:ring-2 focus:ring-gold/40">
                    <p id="ea-email-error" class="mt-1.5 text-sm text-red-300" x-text="errors.email ? errors.email[0] : ''">@error('email'){{ $message }}@enderror</p>
                </div>

                <div>
                    <label class="flex cursor-pointer items-start gap-3 text-sm text-ivory/80">
                        <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                            class="mt-0.5 size-5 shrink-0 rounded border-ivory/30 accent-[#c99b43]">
                        <span>I agree to receive early-access updates from Next Keepers and to the <a href="{{ route('privacy') }}" class="underline decoration-gold underline-offset-2 hover:text-gold-soft" target="_blank">Privacy Policy</a>.</span>
                    </label>
                    <p class="mt-1.5 text-sm text-red-300" x-text="errors.consent ? errors.consent[0] : ''">@error('consent'){{ $message }}@enderror</p>
                </div>

                <p x-show="message" x-cloak role="alert" class="rounded-lg bg-red-950/60 px-4 py-3 text-sm text-red-200" x-text="message"></p>

                <x-button type="submit" class="w-full" x-bind:disabled="status === 'sending'">
                    <span x-show="status !== 'sending'">Get Early Access <span aria-hidden="true">&rarr;</span></span>
                    <span x-show="status === 'sending'" x-cloak>Sending&hellip;</span>
                </x-button>

                <p class="flex items-center gap-2 text-xs text-ivory/55">
                    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"/></svg>
                    No payment required. Your information is kept confidential and never sold.
                </p>
            </form>
        </div>
    </div>
</section>
