{{-- Empty state. Expects $title, $body, optional $action ([label, url]). --}}
<div class="rounded-3xl border border-dashed border-forest/20 bg-white p-8 text-center">
    <h3 class="font-display text-lg font-semibold text-forest">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-lg text-sm text-sage">{{ $body }}</p>
    @isset($action)
        <a href="{{ $action[1] }}" class="mt-4 inline-flex rounded-full bg-gold px-5 py-2.5 text-xs font-bold text-forest-deep transition hover:bg-gold-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-forest">{{ $action[0] }}</a>
    @endisset
</div>
