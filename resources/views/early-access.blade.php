<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GuestHub</title>
    <meta name="description" content="GuestHub manages guest verification, properties, bookings, and check-in for short-term rental hosts.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4ede4] text-slate-900">

    <div class="mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12 lg:px-10">
        <div class="grid w-full items-center gap-14 lg:grid-cols-2 lg:gap-20">

            {{-- Left: value proposition --}}
            <div class="max-w-md">
                <div class="flex items-center gap-2.5">
                    @if($siteLogo)
                        <img src="{{ url('/img/'.$siteLogo) }}" alt="GuestHub" class="h-8 w-auto object-contain">
                    @else
                        <span class="text-lg font-semibold tracking-tight">GuestHub</span>
                    @endif
                </div>

                <h1 class="mt-8 text-4xl font-semibold leading-tight tracking-tight text-slate-950">
                    Everything you need to manage short-term rentals.
                </h1>
                <p class="mt-4 text-base leading-relaxed text-slate-700">
                    Verify guests, manage properties and bookings, automate check-in, and keep guests
                    informed, all from one dashboard.
                </p>

                <ul class="mt-8 space-y-3">
                    <li class="flex items-center gap-2.5 text-sm font-medium text-slate-800">
                        <svg class="h-4 w-4 shrink-0 text-[#082b49]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Guest verification
                    </li>
                    <li class="flex items-center gap-2.5 text-sm font-medium text-slate-800">
                        <svg class="h-4 w-4 shrink-0 text-[#082b49]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Property and booking management
                    </li>
                    <li class="flex items-center gap-2.5 text-sm font-medium text-slate-800">
                        <svg class="h-4 w-4 shrink-0 text-[#082b49]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Automated check-ins
                    </li>
                    <li class="flex items-center gap-2.5 text-sm font-medium text-slate-800">
                        <svg class="h-4 w-4 shrink-0 text-[#082b49]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        Guest messaging
                    </li>
                </ul>

                <p class="mt-10 text-xs text-slate-500">
                    Built for hosts managing one property or many.
                </p>
            </div>

            {{-- Right: form card --}}
            <div class="w-full max-w-[440px] justify-self-center lg:justify-self-end">
                <div class="rounded-2xl bg-white p-8 shadow-[0_4px_16px_-2px_rgba(15,23,42,0.08)]">
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Get early access</h2>
                    <p class="mt-1.5 text-sm text-slate-600">Be the first to know when we launch.</p>

                    @if(session('success'))
                        <div class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                    @endif

                    <form method="post" action="{{ route('early-access.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label class="field-label">Name</label>
                            <input name="name" type="text" value="{{ old('name') }}" required placeholder="Your full name" class="input mt-1.5">
                            @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input name="email" type="email" value="{{ old('email') }}" required placeholder="you@example.com" class="input mt-1.5">
                            @error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label">Phone</label>
                            <input name="phone" type="tel" value="{{ old('phone') }}" placeholder="(555) 000-0000" class="input mt-1.5">
                            @error('phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>

                        <details class="group rounded-md border border-slate-200">
                            <summary class="flex cursor-pointer list-none items-center justify-between px-3.5 py-2.5 text-sm font-medium text-slate-800">
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-slate-500 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    Optional details
                                </span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Host type, notes</span>
                            </summary>
                            <div class="space-y-4 border-t border-slate-200 px-3.5 pb-4 pt-4">
                                <div>
                                    <label class="field-label">I'm a</label>
                                    <select name="role" class="input mt-1.5">
                                        <option value="">Select one</option>
                                        <option value="host" @selected(old('role') === 'host')>Property host</option>
                                        <option value="guest" @selected(old('role') === 'guest')>Guest</option>
                                        <option value="other" @selected(old('role') === 'other')>Other</option>
                                    </select>
                                    @error('role')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="field-label">Message</label>
                                    <textarea name="message" rows="3" class="input mt-1.5">{{ old('message') }}</textarea>
                                    @error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </details>

                        <button class="w-full rounded-md bg-[#082b49] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0a3a63]">Reserve my spot</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
