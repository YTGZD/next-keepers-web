{{-- Below / in line / above industry pill. Expects $signal and $label. --}}
<span @class([
    'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold',
    'bg-emerald/10 text-emerald' => $signal === 'below',
    'bg-forest/5 text-forest/80' => $signal === 'in_line',
    'bg-gold/20 text-forest' => $signal === 'above',
])>
    <span aria-hidden="true">{{ ['below' => '▼', 'in_line' => '●', 'above' => '▲'][$signal] ?? '' }}</span>
    {{ $label }}
</span>
