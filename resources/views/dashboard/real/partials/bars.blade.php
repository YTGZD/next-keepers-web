{{-- Horizontal count bars. Expects $rows (list of label/count), optional $href (callable label => url|null). --}}
@php $max = max(1, collect($rows)->max('count')); @endphp
<ul class="space-y-2.5 text-xs">
    @foreach ($rows as $row)
        @php
            $url = isset($href) ? $href($row['label']) : null;
            $width = max(3, round($row['count'] / $max * 100));
        @endphp
        <li>
            @if ($url)
                <a href="{{ $url }}" class="group block rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold">
            @else
                <div class="group">
            @endif
                <span class="flex items-baseline justify-between gap-3">
                    <span class="truncate font-medium text-forest group-hover:text-emerald">{{ $row['label'] }}</span>
                    <span class="shrink-0 text-sage tabular">{{ number_format($row['count']) }}</span>
                </span>
                <span class="mt-1 block h-1.5 rounded-full bg-ivory-deep/70">
                    <span class="block h-1.5 rounded-full bg-emerald/80 group-hover:bg-emerald" style="width: {{ $width }}%"></span>
                </span>
            @if ($url)
                </a>
            @else
                </div>
            @endif
        </li>
    @endforeach
</ul>
