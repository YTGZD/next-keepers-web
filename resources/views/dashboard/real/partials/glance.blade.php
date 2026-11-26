{{-- Asking price, revenue and earnings drawn to one scale. Expects $glance (list of [label, amount]), $glanceMax and a unique $gid. --}}
<section class="rounded-3xl border border-forest/10 bg-white p-6 shadow-sm" aria-labelledby="glance-heading-{{ $gid }}">
    <h2 id="glance-heading-{{ $gid }}" class="flex items-center gap-2 font-display text-lg font-semibold text-forest"><span class="h-4 w-1 rounded-full bg-gold" aria-hidden="true"></span> Figures at a glance</h2>
    <div class="mt-5 space-y-3 text-xs" role="img" aria-label="{{ $glance->map(fn ($r) => $r[0].' '.\App\Models\Business::formatCompactCurrency($r[1]))->implode(', ') }}">
        @foreach ($glance as [$label, $amount])
            <div class="grid grid-cols-[6.5rem_1fr_4.5rem] items-center gap-3">
                <span class="text-sage">{{ $label }}</span>
                <span class="h-3 rounded-full bg-ivory-deep/70"><span class="block h-3 rounded-full {{ $loop->first ? 'bg-gold' : ($loop->index === 1 ? 'bg-forest' : 'bg-emerald') }}" style="width: {{ max(2, round($amount / $glanceMax * 100, 1)) }}%"></span></span>
                <span class="text-right font-semibold text-forest tabular">{{ \App\Models\Business::formatCompactCurrency($amount) }}</span>
            </div>
        @endforeach
    </div>
    <p class="mt-4 text-[11px] text-sage">Drawn to the same scale. Figures as listed, not verified.</p>
</section>
