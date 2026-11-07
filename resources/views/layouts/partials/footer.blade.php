@php
    $social = array_filter(config('nextkeepers.social'));
    $email = config('nextkeepers.contact_email');
    $links = [
        ['Platform', route('home').'#platform'],
        ['Market Intelligence', route('home').'#intelligence'],
        ['For Buyers', route('home').'#audiences'],
        ['For Owners', route('home').'#audiences'],
        ['About', route('home').'#mission'],
    ];
    $icons = [
        'linkedin' => ['LinkedIn', 'M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.75h3.96V21H3V9.75zm6.44 0h3.8v1.54h.05c.53-.95 1.82-1.95 3.75-1.95 4 0 4.74 2.5 4.74 5.75V21h-3.96v-5.17c0-1.23-.02-2.82-1.8-2.82-1.8 0-2.07 1.35-2.07 2.73V21H9.44V9.75z'],
        'youtube' => ['YouTube', 'M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.27 5 12 5 12 5s-6.27 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2C2 8.78 2 12 2 12s0 3.22.4 4.8a2.5 2.5 0 0 0 1.76 1.77C5.73 19 12 19 12 19s6.27 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77C22 15.22 22 12 22 12s0-3.22-.4-4.8zM10 15V9l5.2 3L10 15z'],
        'x' => ['X', 'M17.75 3h3.07l-6.7 7.66L22 21h-6.17l-4.83-6.32L5.47 21H2.4l7.17-8.2L2 3h6.33l4.37 5.78L17.75 3zm-1.08 16.17h1.7L7.4 4.73H5.58l11.09 14.44z'],
    ];
@endphp
<footer class="bg-forest-deep text-ivory/80">
    <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
            <a href="{{ route('home') }}" aria-label="Next Keepers home" class="w-fit rounded-lg"><x-logo class="-ml-2" /></a>

            <nav aria-label="Footer" class="flex flex-wrap gap-x-7 gap-y-3 text-sm">
                @foreach ($links as [$label, $href])
                    <a href="{{ $href }}" class="transition hover:text-gold-soft">{{ $label }}</a>
                @endforeach
                @if ($email)
                    <a href="mailto:{{ $email }}" class="transition hover:text-gold-soft">Contact</a>
                @endif
            </nav>

            @if ($social)
                <ul class="flex gap-3">
                    @foreach ($social as $key => $url)
                        <li>
                            <a href="{{ $url }}" rel="noopener" target="_blank" aria-label="{{ $icons[$key][0] }}" class="grid size-10 place-items-center rounded-full border border-ivory/20 transition hover:border-gold hover:text-gold-soft">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $icons[$key][1] }}"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-ivory/10 pt-6 text-xs text-ivory/60 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Next Keepers. All rights reserved.</p>
            <p class="flex gap-5">
                <a href="{{ route('privacy') }}" class="hover:text-gold-soft">Privacy Policy</a>
                <a href="{{ route('terms') }}" class="hover:text-gold-soft">Terms of Service</a>
            </p>
        </div>
    </div>
</footer>
