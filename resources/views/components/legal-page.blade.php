@props(['title', 'updated'])
<x-layouts.marketing :title="$title.' | Next Keepers'" :description="'Next Keepers '.strtolower($title).'.'">
    <section class="bg-forest-deep pb-14 pt-32 text-ivory">
        <div class="mx-auto max-w-3xl px-5 sm:px-8">
            <x-eyebrow>Legal</x-eyebrow>
            <h1 class="mt-3 font-display text-4xl font-medium tracking-tight sm:text-5xl">{{ $title }}</h1>
            <p class="mt-3 text-sm text-ivory/60">Last updated {{ $updated }}</p>
        </div>
    </section>
    <article class="mx-auto max-w-3xl px-5 py-14 text-ink/85 sm:px-8 [&_a]:text-emerald [&_a]:underline [&_h2]:mb-3 [&_h2]:mt-10 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:text-forest [&_li]:mt-1.5 [&_p]:mt-3 [&_p]:leading-relaxed [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-6">
        {{ $slot }}
    </article>
</x-layouts.marketing>
