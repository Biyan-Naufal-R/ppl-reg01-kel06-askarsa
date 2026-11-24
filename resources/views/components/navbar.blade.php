<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="container-app flex h-16 items-center justify-between">
        <div class="flex items-center gap-3">
            @auth
                <button type="button" data-sidebar-toggle aria-controls="activity-sidebar" aria-expanded="true" aria-label="Tampilkan/sembunyikan sidebar" class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            @endauth
            <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-askarsa">
                ASKARSA<span class="text-askarsa-gold">.</span>
            </a>
        </div>

        <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('services.index') }}">Jasa Minta Tolong</a>
            <a href="{{ route('jobs.freelance') }}">Freelance</a>
            <a href="{{ route('jobs.fulltime') }}">Full-Time</a>
        </nav>

        <div class="flex items-center gap-3">
            @auth
                <div class="relative">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full p-1.5 pr-2 text-sm font-semibold text-askarsa-ink hover:bg-slate-100"
                        data-account-toggle
                        aria-controls="account-menu"
                        aria-expanded="false"
                        aria-label="Menu akun {{ auth()->user()->name }}"
                    >
                        <span class="flex size-9 items-center justify-center rounded-full bg-askarsa text-sm font-bold text-white">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-36 truncate md:inline">{{ auth()->user()->name }}</span>
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div id="account-menu" class="absolute right-0 mt-2 hidden w-72 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="border-b border-slate-100 px-4 py-4">
                            <p class="truncate text-sm font-bold text-askarsa-ink">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="p-2 text-sm">
                            <a class="block rounded-lg px-3 py-2 hover:bg-slate-50" href="{{ route('dashboard.profile') }}">Profil saya</a>
                            <a class="block rounded-lg px-3 py-2 hover:bg-slate-50" href="{{ route('dashboard.edit-profile') }}">Edit profil / akun</a>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="w-full rounded-lg px-3 py-2 text-left text-red-700 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a class="hidden text-sm font-semibold md:inline" href="{{ route('login') }}">Login</a>
                <a class="btn-primary !px-4 !py-2" href="{{ route('register') }}">Daftar</a>
            @endauth

            <button type="button" class="md:hidden" data-menu-toggle="#mobile-menu" aria-label="Buka menu">☰</button>
        </div>
    </div>

    <nav id="mobile-menu" class="container-app hidden space-y-3 pb-4 text-sm md:hidden">
        <a class="block" href="{{ route('home') }}">Beranda</a>
        <a class="block" href="{{ route('services.index') }}">Jasa Minta Tolong</a>
        <a class="block" href="{{ route('jobs.freelance') }}">Freelance</a>
        <a class="block" href="{{ route('jobs.fulltime') }}">Full-Time</a>
        @auth
            <a class="block" href="{{ route('dashboard.profile') }}">Profil saya</a>
            <a class="block" href="{{ route('dashboard.edit-profile') }}">Edit profil / akun</a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="text-red-700">Logout</button>
            </form>
        @else
            <a class="block" href="{{ route('login') }}">Login</a>
            <a class="block" href="{{ route('register') }}">Daftar</a>
        @endauth
    </nav>
</header>
