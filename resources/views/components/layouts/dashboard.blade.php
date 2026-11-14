<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f4f2eb]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Next Keepers: Acquisition Intelligence Dashboard' }}</title>
    <meta name="description" content="Explore acquisition opportunities, deal analytics, and real-time market intelligence across the U.S.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <meta name="theme-color" content="#10291F">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-ink antialiased">
    <div class="flex min-h-screen">
        {{-- Left Sidebar --}}
        @include('dashboard.partials.sidebar')

        {{-- Main View Container --}}
        <div class="flex flex-1 flex-col overflow-x-hidden">
            {{-- Top Header Bar --}}
            @include('dashboard.partials.topbar')

            {{-- Main Dashboard Content --}}
            <div x-data class="border-b border-gold/30 bg-[#f7edd4] px-4 py-2.5 text-xs text-forest-deep sm:px-6 lg:px-8" role="note">
                <strong>Illustrative sample data.</strong>
                Listings, figures, messages and notifications in this dashboard are fictional and for preview only. Nothing here is a real business or a verified fact.
                @guest
                    <button type="button" @click="$dispatch('open-auth', { mode: 'register' })" class="ml-1 font-bold underline">Create a free account to save your own buy box &rarr;</button>
                @endguest
            </div>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

            {{-- Dashboard Footer Bar --}}
            @include('dashboard.partials.footer')
        </div>
    </div>

    <x-auth-modal />
</body>
</html>
