@props([
    'title' => 'Next Keepers: Good businesses deserve better discovery',
    'description' => 'Next Keepers helps you find, evaluate, and acquire established businesses with business discovery, financial intelligence, and market insights in one place. Join the early-access list.',
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#10291F">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Next Keepers">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/key.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Next Keepers',
            'url' => config('app.url'),
            'description' => 'Acquisition intelligence platform for business buyers.',
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <a href="#main" class="sr-only z-[100] rounded-full bg-gold px-4 py-2 font-semibold text-forest-deep focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    @include('layouts.partials.header')

    <main id="main">
        {{ $slot }}
    </main>

    @include('layouts.partials.footer')
</body>
</html>
