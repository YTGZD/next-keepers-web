{{-- Decorative mountain landscape (SVG placeholder). Replace with a photo in public/images when you have one. --}}
<svg viewBox="0 0 700 260" preserveAspectRatio="xMidYMax slice" {{ $attributes }} aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="mt-sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#dfe7e1"/>
            <stop offset="1" stop-color="#f5f2ea"/>
        </linearGradient>
        <radialGradient id="mt-sun" cx="0.72" cy="0.22" r="0.5">
            <stop offset="0" stop-color="#e3c27d" stop-opacity="0.55"/>
            <stop offset="1" stop-color="#e3c27d" stop-opacity="0"/>
        </radialGradient>
        <linearGradient id="mt-far" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#8fa59d"/>
            <stop offset="1" stop-color="#b9c8c0"/>
        </linearGradient>
        <linearGradient id="mt-mid" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#4f7466"/>
            <stop offset="1" stop-color="#7d9a8c"/>
        </linearGradient>
        <linearGradient id="mt-front" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#1b604a"/>
            <stop offset="1" stop-color="#10291f"/>
        </linearGradient>
    </defs>

    <rect width="700" height="260" fill="url(#mt-sky)"/>
    <rect width="700" height="260" fill="url(#mt-sun)"/>

    {{-- distant range with snow caps --}}
    <path fill="url(#mt-far)" d="M0 260V150L70 92 112 124 190 38 252 110 300 78 382 136 452 66 522 120 602 56 700 112V260Z"/>
    <g fill="#f7f9f6" opacity="0.95">
        <path d="M190 38L166 70 180 64 191 78 204 62 216 71Z"/>
        <path d="M452 66L430 94 443 89 453 100 466 88 478 97Z"/>
        <path d="M602 56L580 86 593 80 603 93 616 79 628 88Z"/>
        <path d="M70 92L54 114 64 110 71 119 80 108 88 116Z"/>
    </g>

    {{-- mid ridges --}}
    <path fill="url(#mt-mid)" d="M0 260V192L88 150 170 186 262 140 342 190 432 150 522 196 612 150 700 184V260Z"/>

    {{-- foreground forest --}}
    <path fill="url(#mt-front)" d="M0 260V222L120 206 250 224 390 208 530 226 700 210V260Z"/>
    @for ($i = 0; $i < 34; $i++)
        @php
            $x = $i * 21 + (($i * 7) % 11);
            $h = 26 + (($i * 13) % 22);
            $base = 232 + (($i * 5) % 14);
        @endphp
        <path fill="#10291f" opacity="{{ 0.82 + (($i * 3) % 10) / 100 }}" d="M{{ $x }} {{ $base }}L{{ $x + 9 }} {{ $base - $h }}L{{ $x + 18 }} {{ $base }}Z"/>
    @endfor
    <rect y="246" width="700" height="14" fill="#10291f"/>
</svg>
