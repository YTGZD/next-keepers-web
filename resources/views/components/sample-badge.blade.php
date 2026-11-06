{{-- Marks mock/illustrative values so they are never mistaken for real data (blueprint, section 10). --}}
<span {{ $attributes->class(['inline-flex items-center rounded-full border border-current/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider']) }}>{{ $slot->isEmpty() ? 'Sample data' : $slot }}</span>
