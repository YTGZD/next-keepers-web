<x-layouts.marketing title="About Us | Next Keepers" description="Learn about the mission, philosophy, and vision behind Next Keepers — preserving legacy and empowering the next generation of business stewards.">
    {{-- Hero Section --}}
    <section class="relative isolate overflow-hidden bg-forest-deep text-ivory pt-32 pb-20 sm:pt-40 sm:pb-28">
        {{-- Ambient Glow and Mountain Silhouette --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute -right-40 top-0 h-[38rem] w-[50rem] rounded-full bg-emerald/25 blur-3xl"></div>
            <div class="absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-gold/15 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_30%,transparent_30%,#0a1a13_90%)]"></div>
        </div>

        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="max-w-3xl space-y-6">
                <x-eyebrow>Our Story &amp; Purpose</x-eyebrow>

                <h1 class="font-display text-4xl sm:text-6xl font-medium tracking-tight text-ivory leading-[1.1]">
                    Preserving value.<br>
                    <span class="text-gold">Building what's next.</span>
                </h1>

                <p class="text-lg sm:text-xl text-ivory/85 leading-relaxed font-light">
                    Small businesses are the bedrock of our communities and economy. Next Keepers was founded to modernize how essential businesses are discovered, evaluated, and transitioned to the next generation of passionate owners.
                </p>

                <div class="pt-4 flex flex-wrap items-center gap-4">
                    <a
                        href="{{ route('discover') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-gold px-7 py-3.5 text-sm font-semibold text-forest-deep shadow-lg shadow-gold/20 transition hover:bg-gold-soft hover:-translate-y-0.5"
                    >
                        <span>Explore Opportunities</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-ivory/25 bg-ivory/10 px-6 py-3.5 text-sm font-semibold text-ivory hover:bg-ivory/20 transition backdrop-blur-sm"
                    >
                        <span>Open Dashboard</span>
                    </a>
                </div>
            </div>

            {{-- 4 Impact Statistics Row --}}
            <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 border-t border-ivory/15 pt-10">
                <div class="rounded-2xl border border-ivory/10 bg-forest/40 backdrop-blur-md p-5 sm:p-6">
                    <div class="font-display text-3xl sm:text-4xl font-bold text-gold tabular">$10T+</div>
                    <div class="text-xs sm:text-sm font-semibold text-ivory mt-1">U.S. Wealth Transition</div>
                    <p class="text-xs text-ivory/60 mt-1">Baby-boomer owned business assets changing hands this decade.</p>
                </div>
                <div class="rounded-2xl border border-ivory/10 bg-forest/40 backdrop-blur-md p-5 sm:p-6">
                    <div class="font-display text-3xl sm:text-4xl font-bold text-emerald tabular">4.8M</div>
                    <div class="text-xs sm:text-sm font-semibold text-ivory mt-1">Active Small Businesses</div>
                    <p class="text-xs text-ivory/60 mt-1">Cash-flowing enterprises across commercial services, healthcare, and trades.</p>
                </div>
                <div class="rounded-2xl border border-ivory/10 bg-forest/40 backdrop-blur-md p-5 sm:p-6">
                    <div class="font-display text-3xl sm:text-4xl font-bold text-ivory tabular">92%</div>
                    <div class="text-xs sm:text-sm font-semibold text-ivory mt-1">Fit Accuracy</div>
                    <p class="text-xs text-ivory/60 mt-1">AI-driven matching tailored to buyer geographic and financial criteria.</p>
                </div>
                <div class="rounded-2xl border border-ivory/10 bg-forest/40 backdrop-blur-md p-5 sm:p-6">
                    <div class="font-display text-3xl sm:text-4xl font-bold text-gold-soft tabular">100%</div>
                    <div class="text-xs sm:text-sm font-semibold text-ivory mt-1">Verified Financials</div>
                    <p class="text-xs text-ivory/60 mt-1">Normalized Seller’s Discretionary Earnings (SDE) and revenue validation.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- The Story / Why Next Keepers Exists --}}
    <section class="py-20 sm:py-28 bg-[#fbfaf7] text-ink border-b border-forest/10">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <div class="lg:col-span-6 space-y-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-gold-soft font-sans">
                        A GENERATIONAL MOMENT
                    </span>

                    <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight text-forest-deep leading-tight">
                        Every great business deserves a <span class="text-forest">next keeper.</span>
                    </h2>

                    <p class="text-base sm:text-lg text-ink/80 leading-relaxed font-normal">
                        Every day in the United States, over 10,000 business founders reach retirement age. Many have spent thirty or forty years perfecting their craft—building HVAC networks, precision machine shops, medical equipment routes, and commercial fleets.
                    </p>

                    <p class="text-sm sm:text-base text-ink/75 leading-relaxed">
                        Yet the traditional acquisition process remains broken: opaque brokers, unverified listings, hidden debt, and aggressive private equity aggregators who strip out local management. Next Keepers exists to bridge this gap. We provide the intelligence and transparency that aspiring owner-operators need to step in as responsible stewards.
                    </p>

                    <div class="p-5 rounded-2xl border border-forest/15 bg-ivory shadow-sm">
                        <blockquote class="italic text-sm text-forest font-serif leading-relaxed">
                            &ldquo;Behind every listing is an owner who built something real and a community that depends on it. We build software so the next owner carries that work forward with clarity and confidence.&rdquo;
                        </blockquote>
                    </div>
                </div>

                <div class="lg:col-span-6 relative">
                    <div class="relative overflow-hidden rounded-3xl border border-forest/15 bg-forest-deep shadow-2xl">
                        <img
                            src="{{ asset('images/key.jpg') }}"
                            alt="Golden Key and Door"
                            class="size-full object-cover object-center max-h-[440px]"
                            onerror="this.src='{{ asset('images/dashboard/sidebar-unlock.png') }}'"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-forest-deep/90 via-transparent to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6 text-ivory">
                            <span class="inline-flex items-center gap-2 rounded-full bg-gold/20 border border-gold/40 px-3 py-1 text-xs font-bold text-gold mb-2">
                                🔑 The Key to Stewardship
                            </span>
                            <h3 class="font-display text-xl font-bold">Unlocking Legacy &amp; Opportunity</h3>
                            <p class="text-xs text-ivory/75 mt-1">Connecting seller legacies with future-ready operators.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Our 4 Guiding Principles --}}
    <section class="py-20 sm:py-28 bg-white text-ink">
        <div class="mx-auto max-w-7xl px-5 sm:px-8 space-y-16">
            <div class="max-w-2xl text-center mx-auto space-y-4">
                <x-eyebrow>Core Principles</x-eyebrow>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight text-forest-deep">
                    How We Approach Business Acquisition
                </h2>
                <p class="text-sm sm:text-base text-ink/75">
                    Our platform is built on four fundamental pillars that guide every feature, algorithm, and data model we deliver.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Principle 1 --}}
                <div class="rounded-3xl border border-forest/10 bg-[#fbfaf8] p-8 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="size-12 rounded-2xl bg-forest-deep text-gold grid place-items-center font-display font-bold text-xl shadow">
                        01
                    </div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Stewardship Over Speculation</h3>
                    <p class="text-sm text-ink/75 leading-relaxed">
                        We prioritize operators and searchers who intend to run and grow the business for the long haul. We believe in preserving local employee culture, retaining customer goodwill, and investing in sustainable growth rather than quick flips.
                    </p>
                </div>

                {{-- Principle 2 --}}
                <div class="rounded-3xl border border-forest/10 bg-[#fbfaf8] p-8 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="size-12 rounded-2xl bg-forest-deep text-emerald grid place-items-center font-display font-bold text-xl shadow">
                        02
                    </div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Rigor in Financial Transparency</h3>
                    <p class="text-sm text-ink/75 leading-relaxed">
                        No guesswork, vanity numbers, or broker embellishments. Every opportunity features normalized Seller’s Discretionary Earnings (SDE), verifiable multiple benchmarks, historical revenue curves, and asset depreciation schedules.
                    </p>
                </div>

                {{-- Principle 3 --}}
                <div class="rounded-3xl border border-forest/10 bg-[#fbfaf8] p-8 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="size-12 rounded-2xl bg-forest-deep text-gold grid place-items-center font-display font-bold text-xl shadow">
                        03
                    </div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">Regional &amp; Demographic Context</h3>
                    <p class="text-sm text-ink/75 leading-relaxed">
                        A business does not exist in a vacuum. We cross-reference municipal housing permits, population migration, SBA lending volume, and local industry concentration so buyers understand local macro tailwinds before making an offer.
                    </p>
                </div>

                {{-- Principle 4 --}}
                <div class="rounded-3xl border border-forest/10 bg-[#fbfaf8] p-8 shadow-sm hover:shadow-md transition space-y-4">
                    <div class="size-12 rounded-2xl bg-forest-deep text-teal-400 grid place-items-center font-display font-bold text-xl shadow">
                        04
                    </div>
                    <h3 class="font-display text-xl font-bold text-forest-deep">AI Built for Human Acquirers</h3>
                    <p class="text-sm text-ink/75 leading-relaxed">
                        We employ machine learning to parse unstructured dealer records, score buy-box compatibility, and spot operational upside—empowering individuals to evaluate deals with the sophistication of top-tier M&amp;A firms.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- The Platform Ecosystem --}}
    <section class="py-20 sm:py-28 bg-forest-deep text-ivory border-t border-ivory/10">
        <div class="mx-auto max-w-7xl px-5 sm:px-8 space-y-16">
            <div class="max-w-2xl text-center mx-auto space-y-4">
                <x-eyebrow>Our Platform</x-eyebrow>
                <h2 class="font-display text-3xl sm:text-4xl font-bold tracking-tight text-ivory">
                    An Integrated Acquisition Intelligence Engine
                </h2>
                <p class="text-sm sm:text-base text-ivory/75">
                    Everything an entrepreneur, search fund, or operator needs from initial discovery to closing.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Tool 1 --}}
                <div class="rounded-2xl border border-ivory/15 bg-forest/60 p-6 space-y-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-forest text-gold text-lg">🔍</span>
                    <h3 class="font-display text-lg font-bold text-ivory">Business Discovery</h3>
                    <p class="text-xs text-ivory/70 leading-relaxed">
                        Curated search across 2,480+ vetted companies with verified financials, customer concentration metrics, and owner-operator tags.
                    </p>
                    <a href="{{ route('discover') }}" class="text-xs font-bold text-gold hover:text-gold-soft inline-flex items-center gap-1 pt-2">
                        <span>Explore Discovery</span> &rarr;
                    </a>
                </div>

                {{-- Tool 2 --}}
                <div class="rounded-2xl border border-ivory/15 bg-forest/60 p-6 space-y-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-forest text-emerald text-lg">📊</span>
                    <h3 class="font-display text-lg font-bold text-ivory">Market Intelligence</h3>
                    <p class="text-xs text-ivory/70 leading-relaxed">
                        Live U.S. heatmaps, SBA 7(a) loan activity, county housing permits, and sector growth benchmarks to validate market demand.
                    </p>
                    <a href="{{ route('market.intelligence') }}" class="text-xs font-bold text-emerald hover:text-emerald-300 inline-flex items-center gap-1 pt-2">
                        <span>Explore Market Data</span> &rarr;
                    </a>
                </div>

                {{-- Tool 3 --}}
                <div class="rounded-2xl border border-ivory/15 bg-forest/60 p-6 space-y-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-forest text-gold text-lg">🎯</span>
                    <h3 class="font-display text-lg font-bold text-ivory">My Buy Box</h3>
                    <p class="text-xs text-ivory/70 leading-relaxed">
                        Define exact industry, revenue, SDE, and geographic boundaries. Receive automated 0–100 fit scores and instant listing alerts.
                    </p>
                    <a href="{{ route('buybox') }}" class="text-xs font-bold text-gold hover:text-gold-soft inline-flex items-center gap-1 pt-2">
                        <span>Configure Buy Box</span> &rarr;
                    </a>
                </div>

                {{-- Tool 4 --}}
                <div class="rounded-2xl border border-ivory/15 bg-forest/60 p-6 space-y-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-forest text-teal-300 text-lg">🔖</span>
                    <h3 class="font-display text-lg font-bold text-ivory">Active Watchlist</h3>
                    <p class="text-xs text-ivory/70 leading-relaxed">
                        Save high-potential opportunities, track diligence stages, model offer valuations, and manage seller correspondence.
                    </p>
                    <a href="{{ route('watchlist') }}" class="text-xs font-bold text-teal-300 hover:text-teal-200 inline-flex items-center gap-1 pt-2">
                        <span>View Watchlist</span> &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Call to Action --}}
    <section class="py-20 sm:py-28 bg-[#fbfaf7] text-ink border-t border-forest/10">
        <div class="mx-auto max-w-4xl px-5 sm:px-8 text-center space-y-8">
            <h2 class="font-display text-3xl sm:text-5xl font-bold tracking-tight text-forest-deep leading-tight">
                Ready to find a business you can lead?
            </h2>
            <p class="text-base sm:text-lg text-ink/75 max-w-2xl mx-auto leading-relaxed">
                Whether you are an aspiring entrepreneur, an experienced operator, or an institutional searcher, Next Keepers gives you the clarity to act decisively.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a
                    href="{{ route('register') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-forest-deep px-8 py-4 text-sm font-bold text-ivory shadow-xl hover:bg-forest transition hover:scale-105"
                >
                    <span>Get Started Free</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <a
                    href="mailto:contact@nextkeepers.com"
                    class="inline-flex items-center justify-center gap-2 rounded-full border border-forest/20 bg-white px-7 py-4 text-sm font-semibold text-forest hover:bg-forest/5 transition"
                >
                    <span>Contact Our Team</span>
                </a>
            </div>
        </div>
    </section>
</x-layouts.marketing>
