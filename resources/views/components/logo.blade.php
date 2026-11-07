{{-- Official Next Keepers lockup. `on` = the background it sits on: "dark" (light wordmark) or "light" (dark wordmark). --}}
@props(['on' => 'dark', 'mark' => false])
@if ($mark)
    <img src="{{ asset('images/logo/mark.svg') }}" alt="Next Keepers" width="120" height="120" {{ $attributes->class(['size-10']) }}>
@else
    <img
        src="{{ asset('images/logo/lockup-on-'.$on.'.svg') }}"
        alt="Next Keepers" width="540" height="120"
        {{ $attributes->class(['h-12 w-auto']) }}
    >
@endif
