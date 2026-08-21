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
                    Guest check-in, handled.
                </h1>
                <p class="mt-4 text-base leading-relaxed text-slate-700">
                    Tell us about your properties. We'll be in touch about early access.
                </p>
            </div>

            <svg viewBox="0 0 400 260" class="absolute bottom-0 right-0 h-64 w-auto opacity-90" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="230" cy="235" rx="150" ry="18" fill="#e8dcc8"/>
                <rect x="120" y="60" width="120" height="90" rx="6" fill="#082b49"/>
                <rect x="132" y="72" width="40" height="30" rx="2" fill="#f4ede4"/>
                <rect x="180" y="72" width="40" height="30" rx="2" fill="#f4ede4"/>
                <rect x="132" y="112" width="40" height="30" rx="2" fill="#f4ede4"/>
                <rect x="180" y="112" width="24" height="38" rx="2" fill="#c9622a"/>
                <path d="M110 60 L180 15 L250 60 Z" fill="#0a3a63"/>
                <rect x="270" y="150" width="10" height="70" rx="2" fill="#5b8a5a"/>
                <ellipse cx="275" cy="145" rx="28" ry="34" fill="#6ea56a"/>
                <circle cx="60" cy="80" r="26" fill="#e3c48a"/>
                <rect x="40" y="180" width="40" height="12" rx="3" fill="#0a3a63"/>
            </svg>
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
