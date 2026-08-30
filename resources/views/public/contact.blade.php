<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact | Guest Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Guest Hub</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Contact</h1>
                </div>
                <nav class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                    <a href="{{ route('legal.terms') }}" class="underline hover:text-slate-900">Terms</a>
                    <a href="{{ route('legal.privacy') }}" class="underline hover:text-slate-900">Privacy Policy</a>
                    <a href="{{ route('privacy-request') }}" class="underline hover:text-slate-900">Privacy Request</a>
                </nav>
            </div>
        </header>

        <main class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-xl font-semibold text-slate-950">Support and privacy questions</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
                For guest support, reservation questions, notices, or privacy requests, contact us directly.
            </p>

            <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                <p class="font-medium text-slate-900">Email</p>
                <p class="mt-2"><a href="mailto:{{ $contactEmail }}" class="font-medium underline">{{ $contactEmail }}</a></p>
                <div class="mt-5">
                    <a href="{{ route('privacy-request') }}" class="btn-secondary">Submit a privacy request</a>
                </div>
            </div>
        </main>

        <footer class="mt-8 text-center text-sm text-slate-500">
            <p>{{ \App\Models\Setting::getValue('site_copyright', '© Dreamzone Media LLC d/b/a Guest Hub') }} | <a href="{{ route('legal.terms') }}" class="underline hover:text-slate-900">Terms of Service</a> | <a href="{{ route('legal.privacy') }}" class="underline hover:text-slate-900">Privacy Policy</a></p>
        </footer>
    </div>
</body>
</html>
