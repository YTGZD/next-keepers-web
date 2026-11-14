<x-layouts.dashboard title="Profile & Settings | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="{
        activeTab: 'profile',
        toastMessage: '',
        showToast: false,
        triggerToast(msg) {
            this.toastMessage = msg;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 3500);
        },
        profile: {
            name: {{ \Illuminate\Support\Js::from($profile['name'] ?? '') }},
            email: {{ \Illuminate\Support\Js::from($profile['email'] ?? '') }},
            phone: {{ \Illuminate\Support\Js::from($profile['phone'] ?? '') }},
            title: {{ \Illuminate\Support\Js::from($profile['title'] ?? '') }},
            company: {{ \Illuminate\Support\Js::from($profile['company'] ?? '') }},
            location: {{ \Illuminate\Support\Js::from($profile['location'] ?? '') }},
            bio: {{ \Illuminate\Support\Js::from($profile['bio'] ?? '') }},
            buyer_type: {{ \Illuminate\Support\Js::from($profile['buyer_type'] ?? '') }},
            target_deal_size: {{ \Illuminate\Support\Js::from($profile['target_deal_size'] ?? '') }},
            min_sde: {{ \Illuminate\Support\Js::from($profile['min_sde'] ?? '') }},
            linkedin: {{ \Illuminate\Support\Js::from($profile['linkedin'] ?? '') }},
            website: {{ \Illuminate\Support\Js::from($profile['website'] ?? '') }}
        },
        pwdError: '',
        pwdBusy: false,
        async updatePassword() {
            this.pwdError = '';
            if (!this.pwd.current || !this.pwd.new) { this.pwdError = 'Enter your current and new password.'; return; }
            if (this.pwd.new.length < 8) { this.pwdError = 'The new password must be at least 8 characters.'; return; }
            if (this.pwd.new !== this.pwd.confirm) { this.pwdError = 'The new passwords do not match.'; return; }
            this.pwdBusy = true;
            try {
                const res = await fetch('{{ route('settings.password') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                    body: JSON.stringify({ current_password: this.pwd.current, new_password: this.pwd.new })
                });
                if (res.status === 401) { return; }
                const body = await res.json().catch(() => ({}));
                if (!res.ok) { this.pwdError = body.errors?.current_password?.[0] || body.errors?.new_password?.[0] || body.message || 'Could not update the password.'; return; }
                this.pwd.current = ''; this.pwd.new = ''; this.pwd.confirm = '';
                this.triggerToast('Password updated.');
            } finally { this.pwdBusy = false; }
        },
        pwd: {
            current: '',
            new: '',
            confirm: '',
            show: false
        },
        twoFactor: true,
        notifications: {
            instantDeals: true,
            weeklyIntel: true,
            messages: true,
            marketing: false
        }
    }">
        {{-- Toast notification banner --}}
        <div
            x-show="showToast"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="fixed top-6 right-6 z-50 flex items-center gap-2.5 rounded-2xl bg-forest-deep px-5 py-3 text-xs font-semibold text-ivory shadow-2xl border border-gold/40"
        >
            <span class="grid size-5 place-items-center rounded-full bg-emerald text-white text-[10px]">✓</span>
            <span x-text="toastMessage"></span>
        </div>

        {{-- Hero Profile Banner Header --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-forest-deep text-ivory shadow-md">
            {{-- Panorama Background Cover --}}
            <div class="relative h-44 sm:h-52 w-full overflow-hidden bg-forest-deep">
                <img
                    src="{{ asset('images/dashboard/mountain-clean.jpg') }}"
                    alt="Mountain Landscape Cover"
                    class="size-full object-cover object-center opacity-45 mix-blend-luminosity"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-forest-deep via-forest-deep/60 to-transparent"></div>
            </div>

            {{-- User Details Card Overlay --}}
            <div class="relative px-6 sm:px-8 pb-6 pt-0 flex flex-col md:flex-row md:items-end md:justify-between gap-6 -mt-16 sm:-mt-20">
                <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                    {{-- Avatar with Change Photo badge --}}
                    <div class="relative size-24 sm:size-28 rounded-2xl border-4 border-white bg-forest-deep shadow-xl overflow-hidden shrink-0">
                        @if ($isDemoPreview)
                            <img src="{{ asset('images/dashboard/avatar.png') }}" alt="" class="size-full object-cover">
                        @else
                            <span class="grid size-full place-items-center bg-emerald font-display text-4xl font-bold text-ivory">{{ strtoupper(mb_substr($profile['name'] ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>

                    {{-- Name, Title, Badges --}}
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-ivory" x-text="profile.name">
                                {{ $profile['name'] ?? '' }}
                            </h1>
                            <span class="inline-flex items-center gap-1 rounded-full bg-gold/20 text-gold border border-gold/40 px-2.5 py-0.5 text-[11px] font-bold shadow-sm">
                                <span>★</span>
                                <span>{{ $profile['membership'] ?? 'Free account' }}</span>
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-ivory/80 flex flex-wrap items-center gap-2">
                            <span x-text="profile.title"></span>
                            <span class="text-ivory/40">&bull;</span>
                            <span x-text="profile.location"></span>
                        </p>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-ivory/60 pt-0.5">
                            <span>Member since {{ $profile['member_since'] ?? '' }}</span>
                            <span>&bull;</span>
                            <span>{{ $profile['nda_status'] ?? 'No NDA on file' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Quick Stats --}}
                <div class="flex flex-wrap items-center gap-2.5 self-start md:self-end">
                    <div class="rounded-xl border border-ivory/15 bg-white/10 backdrop-blur-sm px-3.5 py-2 text-center min-w-[90px]">
                        <div class="font-display text-base font-bold text-gold">{{ $stats['active_buy_box_matches'] ?? 0 }}</div>
                        <div class="text-[10px] text-ivory/70">Buy Box Matches</div>
                    </div>
                    <div class="rounded-xl border border-ivory/15 bg-white/10 backdrop-blur-sm px-3.5 py-2 text-center min-w-[90px]">
                        <div class="font-display text-base font-bold text-ivory">{{ $stats['saved_opportunities'] ?? 0 }}</div>
                        <div class="text-[10px] text-ivory/70">Watchlist</div>
                    </div>
                    <div class="rounded-xl border border-ivory/15 bg-white/10 backdrop-blur-sm px-3.5 py-2 text-center min-w-[90px]">
                        <div class="font-display text-base font-bold text-emerald">{{ $stats['viewed_deals'] ?? 0 }}</div>
                        <div class="text-[10px] text-ivory/70">Deals Reviewed</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tab Navigation Pills --}}
        <section class="flex flex-wrap items-center gap-2 border-b border-forest/10 pb-4">
            <button
                type="button"
                @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-2 cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profile &amp; About</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-2 cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Password &amp; Security</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'criteria'"
                :class="activeTab === 'criteria' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-2 cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Acquisition Criteria</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'notifications'"
                :class="activeTab === 'notifications' ? 'bg-forest-deep text-ivory shadow-sm' : 'bg-white text-forest/75 border border-forest/15 hover:bg-forest/5'"
                class="rounded-full px-4 py-2 text-xs font-semibold transition inline-flex items-center gap-2 cursor-pointer"
            >
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifications &amp; Alerts</span>
            </button>
        </section>

        {{-- ============================================== --}}
        {{-- TAB 1: PROFILE & ABOUT --}}
        {{-- ============================================== --}}
        <section x-show="activeTab === 'profile'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Personal &amp; Professional Information</h3>
                    <p class="text-xs text-forest/60 mt-1">Manage your identity, bio, and contact information visible to verified sellers and brokers.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Full Name --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Full Name</label>
                        <input
                            type="text"
                            x-model="profile.name"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>

                    {{-- Email Address --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-forest-deep">Email Address</label>
                        </div>
                        <input
                            type="email"
                            x-model="profile.email"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>

                    {{-- Phone Number --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Phone Number</label>
                        <input
                            type="text"
                            x-model="profile.phone"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>

                    {{-- Location --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Primary Location</label>
                        <input
                            type="text"
                            x-model="profile.location"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>

                    {{-- Professional Title --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Professional Title / Headline</label>
                        <input
                            type="text"
                            x-model="profile.title"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>

                    {{-- Company / Entity --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Operating Entity / HoldCo</label>
                        <input
                            type="text"
                            x-model="profile.company"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm"
                        >
                    </div>
                </div>

                {{-- Bio / About --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-forest-deep">About &amp; Acquisition Philosophy</label>
                    <textarea
                        rows="4"
                        x-model="profile.bio"
                        class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none focus:ring-1 focus:ring-forest shadow-sm leading-relaxed"
                    ></textarea>
                    <p class="text-[11px] text-forest/50">Describe your operating background, deal thesis, and what makes you a trusted steward for the business.</p>
                </div>

                {{-- Links --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-forest/10">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">LinkedIn Profile</label>
                        <input
                            type="url"
                            x-model="profile.linkedin"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Website / Portfolio</label>
                        <input
                            type="url"
                            x-model="profile.website"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-forest/10">
                    <button
                        type="button"
                        @click="fetch('{{ route('settings.profile') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify(profile) }); triggerToast('Profile changes saved successfully.')"
                        class="rounded-xl bg-forest-deep px-6 py-2.5 text-xs font-bold text-ivory hover:bg-forest transition shadow-md cursor-pointer"
                    >
                        Save Profile Changes
                    </button>
                </div>
            </div>
        </section>

        {{-- ============================================== --}}
        {{-- TAB 2: PASSWORD & SECURITY --}}
        {{-- ============================================== --}}
        <section x-show="activeTab === 'security'" x-cloak class="space-y-6">
            {{-- Password Reset Card --}}
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Password Reset</h3>
                    <p class="text-xs text-forest/60 mt-1">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <div class="space-y-4 max-w-xl">
                    {{-- Current Password --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Current Password</label>
                        <div class="relative">
                            <input
                                :type="pwd.show ? 'text' : 'password'"
                                x-model="pwd.current"
                                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                                class="w-full rounded-xl border border-forest/15 bg-white py-2.5 pl-3.5 pr-10 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                            >
                            <button type="button" @click="pwd.show = !pwd.show" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-forest/40 hover:text-forest">
                                <span x-text="pwd.show ? '👁️' : '🔒'" class="text-xs"></span>
                            </button>
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">New Password</label>
                        <input
                            :type="pwd.show ? 'text' : 'password'"
                            x-model="pwd.new"
                            placeholder="Enter new strong password"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Confirm New Password</label>
                        <input
                            :type="pwd.show ? 'text' : 'password'"
                            x-model="pwd.confirm"
                            placeholder="Re-enter new password"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>

                    {{-- Security Checklist --}}
                    <div class="rounded-xl bg-forest/5 p-4 border border-forest/10 space-y-1.5 text-[11px] text-forest/80">
                        <div class="font-bold text-forest-deep">Password Requirements:</div>
                        <div class="flex items-center gap-2 text-emerald"><span>✓</span> <span>Minimum 8 characters in length</span></div>
                    </div>

                    <p x-show="pwdError" x-cloak x-text="pwdError" class="text-xs font-semibold text-red-700" role="alert"></p>
                    <button
                        type="button"
                        @click="updatePassword()"
                        :disabled="pwdBusy"
                        class="rounded-xl bg-forest-deep px-6 py-2.5 text-xs font-bold text-ivory hover:bg-forest transition shadow-md cursor-pointer disabled:opacity-60"
                    >
                        Update Password
                    </button>
                </div>
            </div>

            {{-- 2FA: not built yet, so shown honestly as upcoming --}}
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-base">🛡️</span>
                    <h4 class="font-display text-base font-bold text-forest-deep">Two-Factor Authentication (2FA)</h4>
                    <span class="rounded-full bg-forest/5 text-forest/70 border border-forest/15 px-2.5 py-0.5 text-[10px] font-bold">Coming soon</span>
                </div>
                <p class="mt-1 text-xs text-forest/70 leading-relaxed max-w-xl">Authenticator-app verification is planned and not active yet. Use a strong, unique password in the meantime.</p>
            </div>

            {{-- Active Sessions --}}
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm space-y-2">
                <h4 class="font-display text-base font-bold text-forest-deep">Sessions</h4>
                <p class="text-xs text-forest/70">You are signed in on this device. Use Log out in the top bar to end the session.</p>
            </div>
        </section>

        {{-- ============================================== --}}
        {{-- TAB 3: ACQUISITION CRITERIA & PREFERENCES --}}
        {{-- ============================================== --}}
        <section x-show="activeTab === 'criteria'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm space-y-6">
                <div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Acquisition Criteria &amp; Buyer Profile</h3>
                    <p class="text-xs text-forest/60 mt-1">This information trains your Buy Box matching algorithm to deliver higher fit scores.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Buyer Classification --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Buyer Classification</label>
                        <select class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm">
                            <option>Individual Searcher / Operator</option>
                            <option>Self-Funded Searcher</option>
                            <option>Traditional Search Fund</option>
                            <option>Independent Sponsor / Family Office</option>
                            <option>Strategic Corporate Acquirer</option>
                        </select>
                    </div>

                    {{-- Target Enterprise Value --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Target Enterprise Value</label>
                        <input
                            type="text"
                            value="$1.0M – $5.0M"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>

                    {{-- Min SDE Threshold --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Minimum SDE Threshold</label>
                        <input
                            type="text"
                            value="$300,000 / year"
                            class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm"
                        >
                    </div>

                    {{-- Capital Readiness --}}
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-forest-deep">Financing &amp; Capital Readiness</label>
                        <select class="w-full rounded-xl border border-forest/15 bg-white py-2.5 px-3.5 text-xs font-medium text-ink focus:border-forest focus:outline-none shadow-sm">
                            <option>SBA 7(a) Pre-Qualified + Equity Backed</option>
                            <option>All-Cash Buyer</option>
                            <option>Committed Institutional Equity</option>
                            <option>Conventional Commercial Bank Backed</option>
                        </select>
                    </div>
                </div>

                {{-- Focus Industries Tags --}}
                <div class="space-y-2 pt-2 border-t border-forest/10">
                    <label class="block text-xs font-bold text-forest-deep">Target Industry Sectors</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['HVAC & Climate Control', 'Commercial Restoration', 'Healthcare Distribution', 'Automotive Services', 'Precision Light Manufacturing'] as $ind)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-forest/5 px-3 py-1 text-xs font-semibold text-forest-deep border border-forest/10">
                                <span>{{ $ind }}</span>
                                <span class="text-forest/40 hover:text-rose-600 cursor-pointer">✕</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Target Geography --}}
                <div class="space-y-2 pt-2 border-t border-forest/10">
                    <label class="block text-xs font-bold text-forest-deep">Target Geographic Footprint</label>
                    <p class="text-[11px] text-forest/60">Northeast Region: New York (Rochester, Buffalo, Syracuse, Albany, NYC), Pennsylvania, Massachusetts, Connecticut.</p>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-forest/10">
                    <button
                        type="button"
                        @click="fetch('{{ route('settings.criteria') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify({ buyer_type: profile.buyer_type, target_deal_size: profile.target_deal_size, min_sde: profile.min_sde }) }); triggerToast('Acquisition criteria updated.')"
                        class="rounded-xl bg-forest-deep px-6 py-2.5 text-xs font-bold text-ivory hover:bg-forest transition shadow-md cursor-pointer"
                    >
                        Save Acquisition Criteria
                    </button>
                </div>
            </div>
        </section>

        {{-- ============================================== --}}
        {{-- TAB 4: NOTIFICATIONS & ALERTS --}}
        {{-- ============================================== --}}
        <section x-show="activeTab === 'notifications'" x-cloak class="space-y-6">
            <div class="rounded-2xl border border-forest/10 bg-white p-6 sm:p-8 shadow-sm space-y-5">
                <div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Notification Preferences</h3>
                    <p class="text-xs text-forest/60 mt-1">Configure when and how Next Keepers alerts you about new opportunities.</p>
                </div>

                <div class="divide-y divide-forest/10 text-xs">
                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-forest-deep">Instant High-Fit Deal Alerts</div>
                            <div class="text-forest/60 mt-0.5">Receive immediate notification when a new listing matches ≥ 90% of your Buy Box.</div>
                        </div>
                        <input type="checkbox" x-model="notifications.instantDeals" class="size-4.5 rounded border-forest/30 text-forest cursor-pointer">
                    </div>

                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-forest-deep">Weekly Market Intelligence Digest</div>
                            <div class="text-forest/60 mt-0.5">Summary of M&amp;A multiple shifts, transaction volume, and regional trends.</div>
                        </div>
                        <input type="checkbox" x-model="notifications.weeklyIntel" class="size-4.5 rounded border-forest/30 text-forest cursor-pointer">
                    </div>

                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-forest-deep">Broker &amp; Owner Inquiries</div>
                            <div class="text-forest/60 mt-0.5">Alerts when business owners or listing intermediaries respond to your inquiries.</div>
                        </div>
                        <input type="checkbox" x-model="notifications.messages" class="size-4.5 rounded border-forest/30 text-forest cursor-pointer">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-forest/10">
                    <button
                        type="button"
                        @click="fetch('{{ route('settings.notifications') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}' }, body: JSON.stringify(notifications) }); triggerToast('Notification preferences saved.')"
                        class="rounded-xl bg-forest-deep px-6 py-2.5 text-xs font-bold text-ivory hover:bg-forest transition shadow-md cursor-pointer"
                    >
                        Save Preferences
                    </button>
                </div>
            </div>
        </section>
    </div>
</x-layouts.dashboard>
