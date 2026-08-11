@props(['booking', 'property', 'title' => 'Guest Welcome', 'state' => null])

@php
    $brandColor = \App\Models\Setting::getValue('brand_color', '#082b49');
    $favicon = \App\Models\Setting::getValue('favicon');
    $weather = ($property->latitude && $property->longitude)
        ? app(\App\Services\WeatherService::class)->getCurrent((float) $property->latitude, (float) $property->longitude)
        : null;
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ $property->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>:root { --brand: {{ $brandColor }}; }</style>
    @if($favicon)
        <link rel="icon" href="{{ url('/img/'.$favicon) }}">
    @endif
</head>
<body class="guest-canvas text-slate-950 antialiased">

@if(session('success') || $errors->any())
    <div class="mx-auto mt-4 w-full max-w-[390px] px-4">
        @if(session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-800">
                {{ $errors->first() }}
            </div>
        @endif
    </div>
@endif
@if(in_array($state, ['checkout_notice', 'checkout_available'], true))
    <div class="mx-auto mt-4 w-full max-w-[390px] px-4">
        <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">
            @if($state === 'checkout_notice')
                Check-out is coming up. Your check-out time is {{ $booking->effectiveCheckoutTimeFormatted() }} tomorrow.
            @else
                You're checking out today. Check-out time is {{ $booking->effectiveCheckoutTimeFormatted() }}.
            @endif
        </div>
    </div>
@endif

<main class="guest-stage">
    {{ $slot }}
</main>

<div id="toast-container" class="pointer-events-none fixed right-4 top-4 z-[99999] flex flex-col gap-2"></div>

@if(session('identity_complete'))
    <div id="completion-prompt" class="fixed inset-0 z-[9998] grid place-items-center bg-slate-950/35 px-4 backdrop-blur-sm">
        <div class="w-full max-w-[340px] rounded-lg bg-white p-6 text-center shadow-xl">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-emerald-500 text-white">
                <x-icon name="check" class="h-8 w-8" />
            </span>
            <h2 class="mt-4 text-xl font-semibold">Check-in details received</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Your information was submitted securely.</p>
            <button type="button" data-close-completion class="guest-primary-btn mt-5 w-full">Continue</button>
        </div>
    </div>
@endif

</body>
</html>
