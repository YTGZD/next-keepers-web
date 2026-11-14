<x-layouts.dashboard title="{{ $business['name'] }} | Next Keepers">
    <div class="space-y-6 max-w-7xl mx-auto pb-10" x-data="{
        activeTab: 'overview',
        inWatchlist: {{ !empty($inWatchlist) ? 'true' : 'false' }},
        inPipeline: {{ !empty($inPipeline) ? 'true' : 'false' }},
        moreInfoModal: false,
        contactModal: false,
        inquiryName: {{ json_encode($userName) }},
        inquiryEmail: {{ json_encode($userEmail) }},
        inquiryEntity: 'Individual Searcher / Operator',
        toggleWatchlist() {
            this.inWatchlist = !this.inWatchlist;
            fetch('{{ route('watchlist.toggle', ['businessId' => $business['id']]) }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                }
            });
        },
        togglePipeline() {
            this.inPipeline = !this.inPipeline;
            fetch('{{ route('pipeline.toggle', ['businessId' => $business['id']]) }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                }
            });
        },
        submitInquiry() {
            fetch('{{ route('business.inquire', ['businessId' => $business['id']]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.inquiryName,
                    email: this.inquiryEmail,
                    buyer_entity: this.inquiryEntity,
                    type: this.moreInfoModal ? 'dossier' : 'advisory_contact'
                })
            });
            this.inPipeline = true;
            alert('Your inquiry for {{ addslashes($business['name']) }} has been received. Our team will contact you shortly.');
            this.moreInfoModal = false;
            this.contactModal = false;
        }
    }">
        {{-- Back Navigation --}}
        <div>
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 text-xs font-semibold text-forest hover:text-emerald transition"
            >
                <span aria-hidden="true">&larr;</span>
                <span>Back to Results</span>
            </a>
        </div>

        {{-- Hero Listing Header Banner --}}
        <section class="relative overflow-hidden rounded-3xl border border-forest/10 bg-white shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                {{-- Left Text Info --}}
                <div class="p-6 sm:p-8 lg:col-span-7 space-y-4">
                    <h1 class="font-display text-3xl font-medium tracking-tight text-forest sm:text-4xl">
                        {{ $business['name'] }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-sage font-medium">
                        <span class="inline-flex items-center gap-1.5 text-forest">
                            <svg class="size-4 text-forest shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $business['location'] }}</span>
                        </span>
                        <span class="text-forest/20" aria-hidden="true">|</span>
                        <span class="inline-flex items-center gap-1.5 text-forest">
                            <svg class="size-4 text-forest shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $business['industry'] }}</span>
                        </span>
                    </div>

                    <p class="text-sm text-ink/80 leading-relaxed max-w-xl">
                        {{ $business['tagline'] }}
                    </p>

                    {{-- Badges / Tags --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald/10 px-3 py-1 text-xs font-semibold text-emerald">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Profitable Business</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-forest/5 px-3 py-1 text-xs font-medium text-forest/90">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Owner-Operated</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-forest/5 px-3 py-1 text-xs font-medium text-forest/90">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <span>Growth Opportunity</span>
                        </span>
                    </div>
                </div>

                {{-- Right Hero Technician Photo --}}
                <div class="h-64 sm:h-72 lg:h-full lg:col-span-5 relative overflow-hidden bg-forest-deep">
                    <img
                        src="{{ asset($business['hero_image']) }}"
                        alt="HVAC technician working on rooftop air handler"
                        class="h-full w-full object-cover object-center"
                    >
                    <div class="absolute inset-0 bg-gradient-to-r from-white via-transparent to-transparent hidden lg:block opacity-30"></div>
                </div>
            </div>
        </section>

        {{-- Top 5 Key Metric Summary Cards --}}
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
            {{-- Card 1: Revenue --}}
            <div class="flex items-center gap-3.5 rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm">
                <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-display text-xl font-bold text-forest tabular">{{ $business['revenue'] ?? '$1.8M' }}</span>
                        <span class="text-xs font-semibold text-emerald tabular">&uarr; 8%</span>
                    </div>
                    <p class="text-xs text-sage mt-0.5">Annual Revenue</p>
                </div>
            </div>

            {{-- Card 2: SDE --}}
            <div class="flex items-center gap-3.5 rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm">
                <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-display text-xl font-bold text-forest tabular">{{ $business['sde'] ?? '$420K' }}</span>
                        <span class="text-xs font-semibold text-emerald tabular">&uarr; 10%</span>
                    </div>
                    <p class="text-xs text-sage mt-0.5">SDE</p>
                </div>
            </div>

            {{-- Card 3: Asking Price --}}
            <div class="flex items-center gap-3.5 rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm">
                <div class="grid size-11 place-items-center rounded-2xl bg-teal-50 text-teal-700 shrink-0">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <span class="font-display text-xl font-bold text-forest tabular">{{ $business['asking_price'] ?? '$1.9M' }}</span>
                    <p class="text-xs text-sage mt-0.5">Asking Price</p>
                </div>
            </div>

            {{-- Card 4: SDE Multiple --}}
            <div class="flex items-center gap-3.5 rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm">
                <div class="grid size-11 place-items-center rounded-2xl bg-blue-50 text-blue-700 shrink-0">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <span class="font-display text-xl font-bold text-forest tabular">{{ $business['sde_multiple'] ?? '3.2x' }}</span>
                    <p class="text-xs text-sage mt-0.5">SDE Multiple</p>
                </div>
            </div>

            {{-- Card 5: Employees --}}
            <div class="flex items-center gap-3.5 rounded-2xl border border-forest/10 bg-white p-4 sm:p-5 shadow-sm col-span-2 sm:col-span-1">
                <div class="grid size-11 place-items-center rounded-2xl bg-emerald/10 text-emerald shrink-0">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <span class="font-display text-xl font-bold text-forest tabular">{{ $business['employees'] ?? 12 }}</span>
                    <p class="text-xs text-sage mt-0.5">Employees</p>
                </div>
            </div>
        </section>

        {{-- Horizontal Tab Navigation --}}
        <div class="border-b border-forest/10">
            <nav class="flex flex-wrap gap-2 sm:gap-6 text-xs sm:text-sm font-semibold" aria-label="Business Detail Tabs">
                <button
                    type="button"
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Overview
                </button>
                <button
                    type="button"
                    @click="activeTab = 'financials'"
                    :class="activeTab === 'financials' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Financials
                </button>
                <button
                    type="button"
                    @click="activeTab = 'market'"
                    :class="activeTab === 'market' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Market Intelligence
                </button>
                <button
                    type="button"
                    @click="activeTab = 'details'"
                    :class="activeTab === 'details' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Business Details
                </button>
                <button
                    type="button"
                    @click="activeTab = 'location'"
                    :class="activeTab === 'location' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Location &amp; Demographics
                </button>
                <button
                    type="button"
                    @click="activeTab = 'sources'"
                    :class="activeTab === 'sources' ? 'border-b-2 border-forest text-forest pb-3 font-bold' : 'text-sage hover:text-forest pb-3 transition'"
                >
                    Data Sources
                </button>
            </nav>
        </div>

        {{-- Main Content 2-Column Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Area (8 cols) --}}
            <div class="space-y-6 lg:col-span-8">
                {{-- Top 3-Module Row: Overview Text + Video Media + Business Highlights --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Business Overview --}}
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="h-3.5 w-1 rounded-full bg-gold"></span>
                                <h2 class="text-xs font-bold uppercase tracking-widest text-forest">Business Overview</h2>
                            </div>
                            <p class="mt-3 text-xs text-ink/80 leading-relaxed">
                                {{ $business['overview']['description'] }}
                            </p>
                        </div>
                        <ul class="mt-4 space-y-2 text-xs font-medium text-forest">
                            @foreach ($business['overview']['checklist'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg class="size-4 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Facility Video Media Preview --}}
                    <div class="relative overflow-hidden rounded-3xl border border-forest/10 bg-forest-deep shadow-sm min-h-[14rem] group cursor-pointer">
                        <img
                            src="{{ asset($business['overview']['facility_image']) }}"
                            alt="Mechanical room facility"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                        >
                        {{-- Circular Play Button Overlay --}}
                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 backdrop-blur-[1px] group-hover:bg-black/30 transition">
                            <span class="grid size-12 place-items-center rounded-full bg-forest text-ivory shadow-lg shadow-black/40 group-hover:scale-110 transition duration-200">
                                <svg class="size-5 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </div>
                    </div>

                    {{-- Business Highlights --}}
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="h-3.5 w-1 rounded-full bg-gold"></span>
                            <h2 class="text-xs font-bold uppercase tracking-widest text-forest">Business Highlights</h2>
                        </div>
                        <div class="mt-4 space-y-4 text-xs">
                            <div class="flex items-center justify-between border-b border-forest/5 pb-2.5">
                                <span class="flex items-center gap-2 text-sage">
                                    <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Founded</span>
                                </span>
                                <span class="font-semibold text-forest tabular">2012</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-forest/5 pb-2.5">
                                <span class="flex items-center gap-2 text-sage">
                                    <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Employees</span>
                                </span>
                                <span class="font-semibold text-forest tabular">12</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-forest/5 pb-2.5">
                                <span class="flex items-center gap-2 text-sage">
                                    <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <span>Service Area</span>
                                </span>
                                <span class="font-semibold text-forest text-right">Rochester, NY<br><span class="text-[10px] text-sage font-normal">(50-mile radius)</span></span>
                            </div>
                            <div class="flex items-center justify-between pt-0.5">
                                <span class="flex items-center gap-2 text-sage">
                                    <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <span>Annual Growth</span>
                                </span>
                                <span class="font-semibold text-forest tabular">8% (last 3 years)</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom Row: Financial Performance Chart + Market Insights --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Financial Performance Chart --}}
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="size-4 text-forest" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <h3 class="text-xs font-bold uppercase tracking-widest text-forest">Financial Performance</h3>
                            </div>
                            {{-- Chart Legend --}}
                            <div class="flex items-center gap-3 text-[11px] font-semibold text-sage">
                                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-forest"></span> Revenue</span>
                                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-gold"></span> SDE</span>
                            </div>
                        </div>

                        {{-- Grouped Bar Chart Visual --}}
                        <div class="mt-6">
                            <div class="flex items-end justify-between h-48 border-b border-forest/10 pb-2 px-4 gap-6">
                                {{-- 2022 --}}
                                <div class="flex-1 flex flex-col items-center gap-2">
                                    <div class="w-full flex items-end justify-center gap-2 h-36">
                                        <div class="w-7 bg-forest rounded-t-md relative group flex flex-col justify-end" style="height: 75%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-forest text-ivory px-1.5 py-0.5 rounded transition">$1.5M</span>
                                        </div>
                                        <div class="w-7 bg-gold rounded-t-md relative group flex flex-col justify-end" style="height: 25%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-gold text-forest-deep px-1.5 py-0.5 rounded transition">$350K</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-sage tabular">2022</span>
                                </div>

                                {{-- 2023 --}}
                                <div class="flex-1 flex flex-col items-center gap-2">
                                    <div class="w-full flex items-end justify-center gap-2 h-36">
                                        <div class="w-7 bg-forest rounded-t-md relative group flex flex-col justify-end" style="height: 80%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-forest text-ivory px-1.5 py-0.5 rounded transition">$1.6M</span>
                                        </div>
                                        <div class="w-7 bg-gold rounded-t-md relative group flex flex-col justify-end" style="height: 27%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-gold text-forest-deep px-1.5 py-0.5 rounded transition">$370K</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-sage tabular">2023</span>
                                </div>

                                {{-- 2024 --}}
                                <div class="flex-1 flex flex-col items-center gap-2">
                                    <div class="w-full flex items-end justify-center gap-2 h-36">
                                        <div class="w-7 bg-forest rounded-t-md relative group flex flex-col justify-end" style="height: 95%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-forest text-ivory px-1.5 py-0.5 rounded transition">$1.8M</span>
                                        </div>
                                        <div class="w-7 bg-gold rounded-t-md relative group flex flex-col justify-end" style="height: 32%">
                                            <span class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] font-bold bg-gold text-forest-deep px-1.5 py-0.5 rounded transition">$420K</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-sage tabular">2024</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Market Insights (Rochester, NY) --}}
                    <div class="rounded-3xl border border-forest/10 bg-white p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="size-4 text-forest" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <h3 class="text-xs font-bold uppercase tracking-widest text-forest">Market Insights</h3>
                                </div>
                                <span class="text-xs font-semibold text-sage">Rochester, NY</span>
                            </div>

                            {{-- Regional Trend Line Chart --}}
                            <div class="mt-4 h-24 w-full">
                                <svg class="h-full w-full" viewBox="0 0 240 80" fill="none">
                                    <defs>
                                        <linearGradient id="market-fade" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#1B604A" stop-opacity="0.25"/>
                                            <stop offset="100%" stop-color="#1B604A" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M5 65 Q 40 55, 70 50 T 130 35 T 190 20 T 235 15 L 235 75 L 5 75 Z" fill="url(#market-fade)"/>
                                    <path d="M5 65 Q 40 55, 70 50 T 130 35 T 190 20 T 235 15" stroke="#1B604A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="5" cy="65" r="3.5" fill="#1B604A"/>
                                    <circle cx="70" cy="50" r="3.5" fill="#1B604A"/>
                                    <circle cx="130" cy="35" r="3.5" fill="#1B604A"/>
                                    <circle cx="190" cy="20" r="3.5" fill="#1B604A"/>
                                    <circle cx="235" cy="15" r="3.5" fill="#1B604A"/>
                                </svg>
                            </div>

                            {{-- Metrics list --}}
                            <div class="mt-3 space-y-2 text-xs">
                                <div class="flex items-center justify-between border-b border-forest/5 pb-1.5">
                                    <span class="text-sage">Population Growth</span>
                                    <span class="font-bold text-emerald tabular">+2.1%</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-forest/5 pb-1.5">
                                    <span class="text-sage">Housing Permits</span>
                                    <span class="font-bold text-emerald tabular">+12.3%</span>
                                </div>
                                <div class="flex items-center justify-between border-b border-forest/5 pb-1.5">
                                    <span class="text-sage">Industry Growth</span>
                                    <span class="font-bold text-emerald tabular">+3.5%</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sage">Business Activity</span>
                                    <span class="font-bold text-emerald tabular">+6.8%</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border border-forest/15 py-2.5 text-xs font-semibold text-forest hover:border-gold hover:text-emerald transition"
                            >
                                <span>View Full Market Report</span>
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Action & Score Panel (4 cols) --}}
            <div class="space-y-5 lg:col-span-4">
                {{-- Acquisition Fit Score Card --}}
                <div class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="h-3.5 w-1 rounded-full bg-gold"></span>
                        <h2 class="text-xs font-bold uppercase tracking-widest text-forest">Acquisition Fit Score</h2>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-6">
                        {{-- Circular Gauge --}}
                        <div class="relative grid size-28 shrink-0 place-items-center">
                            <svg class="size-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="#ebe6d8" stroke-width="10"/>
                                <circle
                                    cx="50" cy="50" r="42"
                                    fill="none"
                                    stroke="#1B604A"
                                    stroke-width="10"
                                    stroke-dasharray="263.89"
                                    stroke-dashoffset="21.11"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="font-display text-3xl font-extrabold text-forest tabular">{{ $business['fit_score']['score'] ?? 92 }}</span>
                                <span class="text-[10px] font-semibold text-sage">/100</span>
                            </div>
                        </div>

                        {{-- Fit Checklist --}}
                        <ul class="space-y-2 text-xs font-medium text-forest/90">
                            <li class="flex items-center gap-2">
                                <svg class="size-4 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Industry Match</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="size-4 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Financials</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="size-4 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Location</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="size-4 text-emerald shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Size</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-4 pt-3 border-t border-forest/10 flex justify-end">
                        <a href="#fit-breakdown" class="text-xs font-semibold text-forest hover:text-emerald transition inline-flex items-center gap-1">
                            <span>View Details</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2.5">
                    {{-- Add to Watchlist --}}
                    <button
                        type="button"
                        @click="toggleWatchlist()"
                        :class="inWatchlist ? 'bg-emerald text-ivory' : 'bg-forest text-ivory hover:bg-emerald'"
                        class="w-full flex items-center justify-center gap-2 rounded-xl py-3 text-xs font-semibold shadow-sm transition"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        <span x-text="inWatchlist ? 'Saved in Watchlist' : 'Add to Watchlist'"></span>
                    </button>

                    {{-- Add to Pipeline --}}
                    <button
                        type="button"
                        @click="togglePipeline()"
                        :class="inPipeline ? 'bg-[#b88c3a] text-forest-deep' : 'bg-gold text-forest-deep hover:bg-gold-soft'"
                        class="w-full flex items-center justify-center gap-2 rounded-xl py-3 text-xs font-bold shadow-sm transition"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        <span x-text="inPipeline ? 'In Acquisition Pipeline' : 'Add to Pipeline'"></span>
                    </button>

                    {{-- Request More Info --}}
                    <button
                        type="button"
                        @click="moreInfoModal = true"
                        class="w-full flex items-center justify-center gap-2 rounded-xl border border-forest/15 bg-white py-3 text-xs font-semibold text-forest hover:border-gold hover:text-emerald shadow-sm transition"
                    >
                        <svg class="size-4 text-forest/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Request More Info</span>
                    </button>
                </div>

                {{-- Why This Business? Card --}}
                <div class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm">
                    <h2 class="font-display text-base font-bold text-forest">Why This Business?</h2>
                    <ul class="mt-4 space-y-3 text-xs text-ink/85">
                        @foreach ($business['why_this_business'] as $point)
                            <li class="flex items-start gap-2.5">
                                <svg class="size-4 text-emerald shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span class="leading-relaxed">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Mountain Explore / Contact CTA Card --}}
                <div class="relative overflow-hidden rounded-3xl bg-forest-deep p-6 text-ivory shadow-lg">
                    <img
                        src="{{ asset('images/business/mountain-contact.png') }}"
                        alt="Snow-capped mountain landscape"
                        class="absolute inset-0 size-full object-cover -z-10 opacity-40"
                    >
                    <div class="relative z-10 space-y-2">
                        <h3 class="font-display text-lg font-bold text-ivory">Ready to explore this opportunity?</h3>
                        <p class="text-xs text-ivory/80">Get in touch with our team for more details.</p>
                        <div class="pt-3">
                            <button
                                type="button"
                                @click="contactModal = true"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-full bg-gold py-2.5 text-xs font-bold text-forest-deep shadow hover:bg-gold-soft transition"
                            >
                                <span>Contact Us</span>
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact / Request Info Modal --}}
        <div
            x-show="moreInfoModal || contactModal"
            x-cloak
            @keydown.escape.window="moreInfoModal = false; contactModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-forest-deep/80 backdrop-blur-sm"
        >
            <div
                @click.away="moreInfoModal = false; contactModal = false"
                class="w-full max-w-md rounded-3xl border border-ivory/20 bg-forest-deep p-6 sm:p-8 text-ivory shadow-2xl space-y-4"
            >
                <div class="flex items-center justify-between border-b border-ivory/10 pb-3">
                    <h3 class="font-display text-xl text-ivory" x-text="moreInfoModal ? 'Request Business Dossier' : 'Contact Advisory Team'"></h3>
                    <button type="button" @click="moreInfoModal = false; contactModal = false" class="text-ivory/60 hover:text-ivory">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-xs text-ivory/70">
                    Submit your inquiry for <strong class="text-gold">{{ $business['name'] }}</strong> ({{ $business['location'] }}). An NDA and confidential information memorandum will be shared upon approval.
                </p>
                <form @submit.prevent="submitInquiry()" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-ivory/80">Your Name</label>
                        <input type="text" required x-model="inquiryName" class="mt-1 w-full rounded-xl border border-ivory/20 bg-ivory/10 px-3 py-2 text-xs text-ivory">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ivory/80">Email</label>
                        <input type="email" required x-model="inquiryEmail" class="mt-1 w-full rounded-xl border border-ivory/20 bg-ivory/10 px-3 py-2 text-xs text-ivory">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ivory/80">Buyer Entity / Background</label>
                        <input type="text" x-model="inquiryEntity" placeholder="Individual Searcher / Family Office / Strategic" class="mt-1 w-full rounded-xl border border-ivory/20 bg-ivory/10 px-3 py-2 text-xs text-ivory">
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gold py-2.5 text-xs font-bold text-forest-deep hover:bg-gold-soft transition">
                        Submit Inquiry &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
