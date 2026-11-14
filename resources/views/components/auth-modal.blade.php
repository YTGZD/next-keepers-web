@php
    $authOld = old('_auth_mode');
    $openOnLoad = $errors->any() && in_array($authOld, ['login', 'register'], true);
@endphp
<div
    x-data="{
        open: {{ $openOnLoad ? 'true' : 'false' }},
        mode: {{ \Illuminate\Support\Js::from($authOld ?: 'login') }},
        email: {{ \Illuminate\Support\Js::from(old('email', '')) }},
        password: '',
        name: {{ \Illuminate\Support\Js::from(old('name', '')) }},
        showPassword: false,
        init() {
            window.addEventListener('open-auth', (e) => {
                this.mode = e.detail?.mode || 'login';
                this.open = true;
            });
            const wanted = new URLSearchParams(window.location.search).get('auth');
            if (wanted === 'login' || wanted === 'register') {
                this.mode = wanted;
                this.open = true;
            }
        }
    }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-forest-deep/85 backdrop-blur-md"
        @click="open = false"
    ></div>

    {{-- Modal Panel (Two-Column Mountain Card) --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative w-full max-w-4xl overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl shadow-black/60 grid grid-cols-1 lg:grid-cols-12 min-h-[580px]"
    >
        {{-- Close button --}}
        <button
            type="button"
            @click="open = false"
            aria-label="Close dialog"
            class="absolute right-4 top-4 z-20 grid size-8 place-items-center rounded-full text-forest/60 hover:bg-forest/10 hover:text-forest transition"
        >
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Left Hero Column with Hiker on Mountain Rock --}}
        <div class="lg:col-span-6 relative overflow-hidden bg-forest-deep flex flex-col justify-between p-6 sm:p-8 min-h-[280px] lg:min-h-full">
            <img
                src="{{ asset('images/auth/login-hero.png') }}"
                alt="Your Next Acquisition Awaits - Next Keepers"
                class="absolute inset-0 size-full object-cover object-left-top"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-forest-deep/40 via-transparent to-transparent pointer-events-none"></div>

            <div class="relative z-10 w-44 h-12"></div>

            <div class="relative z-10 pt-2 text-[11px] text-ivory/80">
                Next Keepers Platform &copy; {{ date('Y') }}
            </div>
        </div>

        {{-- Right Form Column --}}
        <div class="lg:col-span-6 bg-[#fbfaf8] p-8 flex flex-col justify-between">
            <div>
                {{-- Tabs --}}
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="mode = 'login'"
                        :class="mode === 'login' ? 'bg-forest-deep text-ivory shadow-sm' : 'border border-forest/15 text-forest/80 hover:bg-forest/5'"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl py-2 text-xs font-semibold transition"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Sign In</span>
                    </button>
                    <button
                        type="button"
                        @click="mode = 'register'"
                        :class="mode === 'register' ? 'bg-forest-deep text-ivory shadow-sm' : 'border border-forest/15 text-forest/80 hover:bg-forest/5'"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl py-2 text-xs font-semibold transition"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Create Account</span>
                    </button>
                </div>

                <div class="mt-6">
                    <h3 class="font-display text-2xl font-bold tracking-tight text-forest-deep" x-text="mode === 'login' ? 'Welcome Back' : 'Create Your Account'"></h3>
                    <p class="mt-0.5 text-xs text-forest/70" x-text="mode === 'login' ? 'Sign in to your Next Keepers account' : 'Get started with your free acquisition account'"></p>
                </div>

                <form method="POST" :action="mode === 'login' ? '{{ route('login.post') }}' : '{{ route('register.post') }}'" class="mt-5 space-y-3">
                    @csrf
                    <input type="hidden" name="_auth_mode" :value="mode">

                    @if ($errors->any())
                        <p role="alert" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-800">{{ $errors->first() }}</p>
                    @endif

                    <div x-show="mode === 'register'" x-cloak>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-forest/40">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input
                                type="text"
                                name="name"
                                x-model="name"
                                :required="mode === 'register'"
                                autocomplete="name"
                                placeholder="Full Name"
                                class="w-full rounded-xl border border-forest/15 bg-white py-2.5 pl-10 pr-4 text-xs font-medium text-ink shadow-sm focus:border-forest focus:outline-none"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-forest/40">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                required
                                autocomplete="email"
                                x-model="email"
                                placeholder="Email Address"
                                class="w-full rounded-xl border border-forest/15 bg-white py-2.5 pl-10 pr-4 text-xs font-medium text-ink shadow-sm focus:border-forest focus:outline-none"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-forest/40">
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                minlength="8"
                                :autocomplete="mode === 'login' ? 'current-password' : 'new-password'"
                                x-model="password"
                                placeholder="Password"
                                class="w-full rounded-xl border border-forest/15 bg-white py-2.5 pl-10 pr-10 text-xs font-medium text-ink shadow-sm focus:border-forest focus:outline-none"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 grid w-10 place-items-center text-forest/40 hover:text-forest"
                            >
                                <svg x-show="!showPassword" class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 text-forest/80 cursor-pointer">
                            <input type="checkbox" name="remember" value="1" checked class="size-3.5 rounded border-forest/30 text-forest focus:ring-forest">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-forest-deep py-2.5 text-xs font-bold text-ivory hover:bg-forest shadow-md transition mt-1"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span x-text="mode === 'login' ? 'Sign In' : 'Create Account'"></span>
                    </button>
                </form>

                <div class="relative my-3.5 flex items-center justify-center">
                    <div class="w-full border-t border-forest/15"></div>
                    <span class="absolute bg-[#fbfaf8] px-3 text-[11px] font-medium text-forest/50">or</span>
                </div>

                <a
                    href="{{ route('dashboard') }}"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-forest/15 bg-white py-2.5 text-xs font-semibold text-forest shadow-sm hover:bg-forest/5 transition"
                >
                    Explore the sample dashboard (no sign-up) <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="mt-4 pt-3 border-t border-forest/10 space-y-1.5 text-center text-xs">
                <p class="text-forest/75">
                    <span x-text="mode === 'login' ? 'New here?' : 'Already have an account?'"></span>
                    <button
                        type="button"
                        @click="mode = mode === 'login' ? 'register' : 'login'"
                        class="font-bold text-forest hover:text-emerald underline ml-1"
                        x-text="mode === 'login' ? 'Create one now &rarr;' : 'Sign in here &rarr;'"
                    ></button>
                </p>
                <div class="flex items-center justify-center gap-1.5 text-[10px] text-forest/50">
                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>We never sell your data. See our Privacy Policy.</span>
                </div>
            </div>
        </div>
    </div>
</div>
