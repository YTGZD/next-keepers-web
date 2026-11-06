@php
    // ILLUSTRATIVE sample values for the marketing preview only. Multiples = asking price / SDE.
    $rows = [
        ['HVAC Services Company', 'Rochester, NY', '$1.8M', '$420K', '$1.9M', '4.5x', 92],
        ['Medical Equipment Distributor', 'Buffalo, NY', '$2.4M', '$580K', '$2.6M', '4.5x', 89],
        ['Auto Repair & Service', 'Albany, NY', '$1.1M', '$310K', '$1.2M', '3.9x', 87],
        ['Precision Manufacturing', 'Syracuse, NY', '$3.2M', '$720K', '$3.5M', '4.9x', 85],
    ];
    $market = [
        ['Business activity', 78],
        ['Small-business lending', 64],
        ['Housing permits', 52],
        ['Industry concentration', 71],
    ];
    $analysis = [
        ['Buy-box match', 92, 'Reported'],
        ['Financial quality', 84, 'Estimated'],
        ['Data confidence', 71, 'Third-party'],
        ['Owner transition readiness', null, 'Missing'],
    ];
    $tabs = [
        'opportunities' => 'Opportunities',
        'market' => 'Market intelligence',
        'analytics' => 'Deal analytics',
    ];
@endphp

<section id="intelligence" class="bg-ivory-deep/60 py-20 sm:py-28" x-data="{ tab: 'opportunities' }">
    <div class="mx-auto max-w-7xl px-5 sm:px-8">
        <div class="grid items-end gap-6 lg:grid-cols-2" data-reveal>
            <div>
                <x-eyebrow>Deal intelligence</x-eyebrow>
                <h2 class="mt-4 font-display text-4xl font-medium leading-tight tracking-tight text-forest sm:text-5xl">Research a deal like an analyst, not a browser.</h2>
            </div>
            <p class="text-lg text-ink/75">Business profiles show the numbers, where each number came from, and what is still unknown. Estimates are never presented as facts.</p>
        </div>

        <div class="mt-12 overflow-hidden rounded-3xl border border-forest/10 bg-white shadow-2xl shadow-forest/15" data-reveal style="--reveal-delay: 100ms">
            {{-- window chrome --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-forest/10 bg-forest px-5 py-3">
                <div class="flex items-center gap-1.5" aria-hidden="true"><span class="size-2.5 rounded-full bg-ivory/25"></span><span class="size-2.5 rounded-full bg-ivory/25"></span><span class="size-2.5 rounded-full bg-ivory/25"></span></div>
                <div role="tablist" aria-label="Product preview" class="flex rounded-full bg-forest-deep p-1 text-xs font-medium">
                    @foreach ($tabs as $key => $label)
                        <button
                            type="button" role="tab" @click="tab = '{{ $key }}'"
                            :aria-selected="(tab === '{{ $key }}').toString()"
                            :class="tab === '{{ $key }}' ? 'bg-gold text-forest-deep' : 'text-ivory/75 hover:text-ivory'"
                            class="rounded-full px-3.5 py-1.5 transition sm:px-4"
                        >{{ $label }}</button>
                    @endforeach
                </div>
                <x-sample-badge class="text-gold-soft" />
            </div>

            <div class="min-h-[26rem] p-5 sm:p-8">
                {{-- Opportunities --}}
                <div x-show="tab === 'opportunities'" role="tabpanel">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-left text-sm tabular">
                            <caption class="sr-only">Sample acquisition opportunities</caption>
                            <thead class="text-xs uppercase tracking-wider text-sage">
                                <tr class="border-b border-forest/10">
                                    <th scope="col" class="pb-3 pr-4 font-semibold">Business</th>
                                    <th scope="col" class="pb-3 pr-4 font-semibold">Revenue</th>
                                    <th scope="col" class="pb-3 pr-4 font-semibold">SDE</th>
                                    <th scope="col" class="pb-3 pr-4 font-semibold">Asking</th>
                                    <th scope="col" class="pb-3 pr-4 font-semibold">Multiple</th>
                                    <th scope="col" class="pb-3 text-right font-semibold">Fit score</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-forest/10">
                                @foreach ($rows as [$name, $place, $rev, $sde, $ask, $mult, $fit])
                                    <tr>
                                        <th scope="row" class="py-4 pr-4 font-medium text-forest">{{ $name }}<span class="block text-xs font-normal text-sage">{{ $place }}</span></th>
                                        <td class="py-4 pr-4">{{ $rev }}</td>
                                        <td class="py-4 pr-4">{{ $sde }}</td>
                                        <td class="py-4 pr-4">{{ $ask }}</td>
                                        <td class="py-4 pr-4">{{ $mult }}</td>
                                        <td class="py-4 text-right"><span class="inline-grid size-10 place-items-center rounded-full bg-emerald/10 font-semibold text-emerald">{{ $fit }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-t border-forest/10 pt-5 text-xs text-sage">
                        <span class="font-semibold uppercase tracking-wider text-forest">How we label data</span>
                        <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-emerald"></span> Reported by owner or broker</span>
                        <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-gold"></span> Estimated by the platform</span>
                        <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full border border-sage"></span> Missing, shown as missing</span>
                    </div>
                </div>

                {{-- Market intelligence --}}
                <div x-show="tab === 'market'" x-cloak role="tabpanel" class="grid gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="font-display text-2xl text-forest">Western New York</h3>
                        <p class="mt-1 text-sm text-sage">Sample index, 0 to 100 (illustrative)</p>
                        <div class="mt-6 space-y-5">
                            @foreach ($market as [$label, $value])
                                <div>
                                    <div class="flex justify-between text-sm"><span class="font-medium text-forest">{{ $label }}</span><span class="tabular text-sage">{{ $value }}</span></div>
                                    <div class="mt-2 h-2.5 rounded-full bg-ivory-deep" role="img" aria-label="{{ $label }}: {{ $value }} out of 100">
                                        <div class="h-full rounded-full bg-gradient-to-r from-emerald to-gold" style="width: {{ $value }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-2xl bg-navy p-6 text-ivory">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gold">What this will show</p>
                        <p class="mt-3 text-ivory/85">Regional demographics, industry trends, lending activity and competitor density next to every business, so you can judge the market behind the numbers.</p>
                        <p class="mt-4 text-sm text-ivory/60">Market indicators will connect to validated data sources as they come online. Values here are placeholders.</p>
                    </div>
                </div>

                {{-- Deal analytics --}}
                <div x-show="tab === 'analytics'" x-cloak role="tabpanel" class="grid gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="font-display text-2xl text-forest">HVAC Services Company</h3>
                        <p class="mt-1 text-sm text-sage">Fit analysis against a sample buy box</p>
                        <div class="mt-6 space-y-5">
                            @foreach ($analysis as [$label, $value, $source])
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-forest">{{ $label }}</span>
                                        <span class="flex items-center gap-2 text-xs text-sage">{{ $source }} <span class="tabular text-sm font-semibold text-forest">{{ $value ?? 'N/A' }}</span></span>
                                    </div>
                                    <div class="mt-2 h-2.5 rounded-full bg-ivory-deep">
                                        @if ($value !== null)
                                            <div class="h-full rounded-full {{ $source === 'Estimated' ? 'bg-gold' : 'bg-emerald' }}" style="width: {{ $value }}%"></div>
                                        @else
                                            <div class="h-full rounded-full border border-dashed border-sage/60"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-2xl border border-forest/10 bg-ivory p-6">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald">Questions to ask next</p>
                        <ul class="mt-4 space-y-3 text-sm text-ink/85">
                            <li class="flex gap-3"><span class="mt-0.5 text-gold" aria-hidden="true">&#9679;</span> How dependent is revenue on the owner's personal relationships?</li>
                            <li class="flex gap-3"><span class="mt-0.5 text-gold" aria-hidden="true">&#9679;</span> What share of revenue is recurring maintenance contracts?</li>
                            <li class="flex gap-3"><span class="mt-0.5 text-gold" aria-hidden="true">&#9679;</span> Owner transition plans are not available yet. Ask directly.</li>
                        </ul>
                        <p class="mt-5 border-t border-forest/10 pt-4 text-xs text-sage">AI-generated observations are always labeled. Fit scores support your decision; they are not investment advice.</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-5 text-center text-sm text-ink/60">The preview above uses illustrative sample data and does not represent real listings.</p>
    </div>
</section>
