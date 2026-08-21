<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GuestHub</title>
    <meta name="description" content="GuestHub handles guest ID verification, keyless check-in, and a digital welcome guide for short-term rentals.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-900">

    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- Left: brand panel --}}
        <div class="relative hidden flex-col justify-between overflow-hidden bg-[#f4ede4] px-12 py-10 lg:flex">
            <div class="flex items-center gap-2.5">
                @if($siteLogo)
                    <img src="{{ url('/img/'.$siteLogo) }}" alt="GuestHub" class="h-8 w-auto object-contain">
                @else
                    <span class="text-lg font-semibold tracking-tight">GuestHub</span>
                @endif
            </div>

            <div class="max-w-sm">
                <h1 class="text-4xl font-semibold leading-tight tracking-tight text-slate-950">
                    Run your rentals from one place.
                </h1>
                <p class="mt-4 text-base leading-relaxed text-slate-700">
                    GuestHub manages guest verification, bookings, and check-in for short-term rental hosts.
                </p>

                <div class="mt-10 space-y-5 border-t border-black/10 pt-8">
                    <div>
                        <p class="text-sm font-semibold text-slate-950">Verify guests before they arrive</p>
                        <p class="mt-0.5 text-sm leading-relaxed text-slate-600">ID checks and approvals happen ahead of check-in day.</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-950">Track every booking in one view</p>
                        <p class="mt-0.5 text-sm leading-relaxed text-slate-600">See who's checked in, who's due, and what needs attention.</p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-950">Automatic guest communication</p>
                        <p class="mt-0.5 text-sm leading-relaxed text-slate-600">Reminders and updates go out without you sending them.</p>
                    </div>
                </div>
            </div>

            <p class="max-w-sm text-xs text-slate-500">
                Built for hosts managing one property or many.
            </p>
        </div>

        {{-- Right: form --}}
        <div class="flex items-center justify-center px-6 py-14 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    @if($siteLogo)
                        <img src="{{ url('/img/'.$siteLogo) }}" alt="GuestHub" class="h-8 w-auto object-contain">
                    @else
                        <span class="text-lg font-semibold tracking-tight">GuestHub</span>
                    @endif
                </div>

                <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Get early access</h2>
                <p class="mt-2 text-sm text-slate-600">A few details and we'll reach out.</p>

                @if(session('success'))
                    <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
                @endif

                <form method="post" action="{{ route('early-access.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label class="field-label">Name</label>
                        <input name="name" type="text" value="{{ old('name') }}" required class="input mt-2">
                        @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" required class="input mt-2">
                        @error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input name="phone" type="tel" value="{{ old('phone') }}" class="input mt-2">
                        @error('phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label">I'm a</label>
                        <select name="role" required class="input mt-2">
                            <option value="">Select one</option>
                            <option value="host" @selected(old('role') === 'host')>Property host</option>
                            <option value="guest" @selected(old('role') === 'guest')>Guest</option>
                            <option value="other" @selected(old('role') === 'other')>Other</option>
                        </select>
                        @error('role')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label">Notes (optional)</label>
                        <textarea name="message" rows="3" class="input mt-2">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-md bg-[#082b49] px-6 py-3 text-sm font-semibold text-white hover:bg-[#0a3a63]">Join the waitlist</button>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
