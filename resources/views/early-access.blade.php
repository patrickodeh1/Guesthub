<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GuestHub — Coming Soon</title>
    <meta name="description" content="GuestHub is a property management platform that handles guest ID verification, keyless check-in, GPS arrival confirmation, and a digital welcome guide — all in one place.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <div class="flex items-center gap-3">
                @if($siteLogo)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($siteLogo) }}" alt="GuestHub" class="h-9 w-auto">
                @else
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-[#082b49] text-white"><x-icon name="guide" class="h-5 w-5" /></span>
                @endif
                <span class="text-lg font-semibold">GuestHub</span>
            </div>
            <a href="#early-access" class="btn-primary hidden sm:inline-flex">Get Early Access</a>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="bg-[#082b49] text-white">
            <div class="mx-auto max-w-6xl px-5 py-20 text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-200">Coming soon</p>
                <h1 class="mx-auto mt-4 max-w-3xl text-4xl font-semibold leading-tight sm:text-5xl">
                    Effortless guest check-in for short-term rental properties.
                </h1>
                <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-300">
                    GuestHub handles ID verification, keyless smart-lock check-in, GPS arrival confirmation, and a fully
                    branded digital welcome guide — so hosts spend less time managing arrivals and guests get a smooth,
                    self-serve experience from booking to checkout.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="#early-access" class="btn-primary">Request Early Access</a>
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-200 hover:text-white">Existing admin? Sign in →</a>
                </div>
            </div>
        </section>

        {{-- Benefits --}}
        <section class="mx-auto max-w-6xl px-5 py-16">
            <div class="text-center">
                <p class="eyebrow">What GuestHub does</p>
                <h2 class="mt-2 text-3xl font-semibold text-slate-950">Everything you need to run a hands-off arrival.</h2>
            </div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="upload" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">Secure ID verification</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Guests upload a photo ID before arrival. Hosts review and approve from any device, with automatic guest notifications if something needs to be resubmitted.</p>
                </div>
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="map" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">GPS arrival confirmation</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Check-in details unlock automatically once a guest is confirmed on-site, adding a simple layer of verification without extra steps for the host.</p>
                </div>
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="guide" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">Keyless, self-service check-in</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Once approved, guests unlock and lock the door themselves right from their phone during their stay — no key handoffs, no missed arrivals.</p>
                </div>
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="folder" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">Branded digital welcome guide</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">WiFi, parking, amenities, checkout instructions, and local recommendations — all in one guide guests can pull up on their phone, styled with your own logo.</p>
                </div>
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="security" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">Automatic guest alerts</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Guests are texted and emailed automatically at every key step — registration received, approval, check-in reminders, and checkout — so hosts don't have to chase anyone down.</p>
                </div>
                <div class="card card-pad">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-teal-50 text-teal-800"><x-icon name="logs" class="h-5 w-5" /></span>
                    <h3 class="mt-4 font-semibold text-slate-950">One dashboard, every property</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Manage every guest, every property, and every action item from a single admin dashboard built for hosts managing more than one listing.</p>
                </div>
            </div>
        </section>

        {{-- Early access form --}}
        <section id="early-access" class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-3xl px-5 py-16">
                <div class="text-center">
                    <p class="eyebrow">Early access</p>
                    <h2 class="mt-2 text-3xl font-semibold text-slate-950">Get early access to GuestHub.</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Tell us a bit about yourself and we'll reach out with next steps. Whether you're a property host or interested as a guest, we'd love to hear from you.</p>
                </div>

                @if(session('success'))
                    <div class="mt-8 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                @endif

                <form method="post" action="{{ route('early-access.store') }}" class="mt-8 grid gap-4 rounded-lg border border-slate-200 bg-white p-6 shadow-xs sm:grid-cols-2">
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
                    <div class="sm:col-span-2">
                        <label class="field-label">Anything else you'd like us to know? (optional)</label>
                        <textarea name="message" rows="3" class="input mt-2">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <button class="btn-primary w-full">Request Early Access</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-6xl px-5 py-8 text-center text-sm text-slate-500">
            &copy; {{ now()->year }} GuestHub. All rights reserved.
        </div>
    </footer>

</body>
</html>
