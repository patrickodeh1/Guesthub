<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GuestHub</title>
    <meta name="description" content="GuestHub is a property management platform for guest ID verification, keyless check-in, GPS arrival confirmation, and a digital welcome guide.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-900">

    <header class="border-b border-slate-100">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-2.5">
                @if($siteLogo)
                    <img src="{{ url('/img/'.$siteLogo) }}" alt="GuestHub" class="h-8 w-auto object-contain">
                @else
                    <span class="text-lg font-semibold tracking-tight">GuestHub</span>
                @endif
            </div>
            <a href="#request-access" class="text-sm font-medium text-slate-600 hover:text-slate-900">Request access</a>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto max-w-3xl px-6 pt-20 pb-16 text-center">
            <h1 class="text-4xl font-semibold leading-tight tracking-tight text-slate-950 sm:text-5xl">
                A smoother way to run guest check-in.
            </h1>
            <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-slate-600">
                GuestHub is a property management platform built for short-term rental hosts. It handles ID
                verification, keyless check-in, arrival confirmation, and a branded welcome guide, so guests
                arrive smoothly and hosts spend less time managing it.
            </p>
            <div class="mt-9">
                <a href="#request-access" class="inline-flex items-center rounded-md bg-[#082b49] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0a3a63]">
                    Request early access
                </a>
            </div>
        </section>

        {{-- Feature list --}}
        <section class="border-t border-slate-100 bg-slate-50">
            <div class="mx-auto max-w-3xl px-6 py-16">
                <dl class="divide-y divide-slate-200">
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">ID verification</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            Guests upload a photo ID before arrival. Hosts review and approve from any device,
                            with automatic notifications if something needs to be resubmitted.
                        </dd>
                    </div>
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">Arrival confirmation</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            Check-in details unlock once a guest is confirmed on site, adding a layer of
                            verification without extra steps for the host.
                        </dd>
                    </div>
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">Keyless check-in</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            Once approved, guests unlock and lock the door themselves from their phone for the
                            length of their stay. No key handoffs, no missed arrivals.
                        </dd>
                    </div>
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">Digital welcome guide</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            WiFi, parking, amenities, checkout instructions, and local recommendations in one
                            guide guests can pull up on their phone, styled with your own branding.
                        </dd>
                    </div>
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">Automatic alerts</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            Guests are texted and emailed at every key step: registration received, approval,
                            check-in reminders, and checkout, so hosts don't have to chase anyone down.
                        </dd>
                    </div>
                    <div class="grid gap-1.5 py-6 sm:grid-cols-3 sm:gap-8">
                        <dt class="text-sm font-semibold text-slate-950">One dashboard</dt>
                        <dd class="text-sm leading-relaxed text-slate-600 sm:col-span-2">
                            Manage every guest, every property, and every open action item from a single
                            dashboard built for hosts running more than one listing.
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- Request access form --}}
        <section id="request-access" class="mx-auto max-w-xl px-6 py-20">
            <div class="text-center">
                <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Request early access</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    Tell us a little about yourself and we'll reach out with next steps.
                </p>
            </div>

            @if(session('success'))
                <div class="mt-8 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="post" action="{{ route('early-access.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="field-label">Full name</label>
                    <input name="name" type="text" value="{{ old('name') }}" required class="input mt-2">
                    @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" required class="input mt-2">
                    @error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Phone number</label>
                    <input name="phone" type="tel" value="{{ old('phone') }}" class="input mt-2">
                    @error('phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">I'm signing up as a</label>
                    <select name="role" required class="input mt-2">
                        <option value="">Select one</option>
                        <option value="host" @selected(old('role') === 'host')>Property host</option>
                        <option value="guest" @selected(old('role') === 'guest')>Guest</option>
                        <option value="other" @selected(old('role') === 'other')>Other</option>
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label">Anything else you'd like us to know? (optional)</label>
                    <textarea name="message" rows="3" class="input mt-2">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <button class="w-full rounded-md bg-[#082b49] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0a3a63]">Request early access</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="border-t border-slate-100">
        <div class="mx-auto max-w-5xl px-6 py-8 text-center text-sm text-slate-400">
            &copy; {{ now()->year }} GuestHub. All rights reserved.
        </div>
    </footer>

</body>
</html>
