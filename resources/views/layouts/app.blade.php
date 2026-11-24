<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ASKARSA — Satu Akun, Banyak Peluang')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    @include('components.navbar')

    @if (session('status'))
        <div id="status-toast" role="status" aria-live="polite" class="fixed right-5 top-20 z-50 flex max-w-sm items-start gap-3 rounded-xl border border-emerald-200 bg-white p-4 shadow-lg">
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700" aria-hidden="true">✓</span>
            <p class="flex-1 pt-1 text-sm font-medium text-emerald-900">{{ session('status') }}</p>
            <button type="button" class="text-slate-400 hover:text-slate-700" data-toast-close aria-label="Tutup notifikasi">×</button>
        </div>
    @endif

    @if (session('sukses'))
        <div id="status-toast" role="status" aria-live="polite" class="fixed right-5 top-20 z-50 flex max-w-sm items-start gap-3 rounded-xl border border-emerald-200 bg-white p-4 shadow-lg">
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700" aria-hidden="true">✓</span>
            <p class="flex-1 pt-1 text-sm font-medium text-emerald-900">{{ session('sukses') }}</p>
            <button type="button" class="text-slate-400 hover:text-slate-700" data-toast-close aria-label="Tutup notifikasi">×</button>
        </div>
    @endif

    @auth
        <div class="flex min-h-[calc(100vh-64px)]">
            @include('components.activity-sidebar')
            <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"></div>
            <div class="min-w-0 flex-1">
                <main>@yield('content')</main>
                @include('components.footer')
            </div>
        </div>
    @else
        <main>@yield('content')</main>
        @include('components.footer')
    @endauth
</body>
</html>
