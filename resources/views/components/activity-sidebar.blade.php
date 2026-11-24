@php
    $is = fn (...$patterns) => request()->routeIs(...$patterns);
    $activeClass = 'flex items-center gap-2 font-semibold text-askarsa-ink';
    $inactiveClass = 'block font-semibold text-slate-700 hover:text-askarsa';
    $subActive = 'flex items-center gap-2 pl-3 text-[13px] font-semibold text-askarsa-ink';
    $subInactive = 'block pl-6 text-[13px] text-slate-500 hover:text-askarsa';
@endphp
<aside id="activity-sidebar" class="sticky top-16 hidden h-[calc(100vh-64px)] w-[250px] shrink-0 overflow-y-auto border-r border-slate-200 bg-white px-5 py-8 shadow-sm lg:block">
    <p class="mb-7 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Kelola Aktivitas</p>

    <nav class="space-y-1 text-sm" aria-label="Kelola Aktivitas">
        <a href="{{ route('dashboard.pesanan.proses') }}" class="{{ $is('dashboard.pesanan.proses', 'dashboard.pesanan.riwayat', 'dashboard.orders') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.pesanan.proses', 'dashboard.pesanan.riwayat', 'dashboard.orders'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Pesanan Jasa
        </a>
        <a href="{{ route('dashboard.pesanan.proses') }}" class="{{ $is('dashboard.pesanan.proses') ? $subActive : $subInactive }}">
            @if($is('dashboard.pesanan.proses'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Dalam Proses
        </a>
        <a href="{{ route('dashboard.pesanan.riwayat') }}" class="{{ $is('dashboard.pesanan.riwayat') ? $subActive : $subInactive }}">
            @if($is('dashboard.pesanan.riwayat'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Riwayat
        </a>

        <a href="{{ route('dashboard.permintaan.proses') }}" class="mt-3 {{ $is('dashboard.permintaan.*', 'dashboard.incoming') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.permintaan.*', 'dashboard.incoming'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Permintaan Jasa Masuk
        </a>
        <a href="{{ route('dashboard.permintaan.proses') }}" class="{{ $is('dashboard.permintaan.proses') ? $subActive : $subInactive }}">
            @if($is('dashboard.permintaan.proses'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Dalam Proses
        </a>
        <a href="{{ route('dashboard.permintaan.riwayat') }}" class="{{ $is('dashboard.permintaan.riwayat') ? $subActive : $subInactive }}">
            @if($is('dashboard.permintaan.riwayat'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Riwayat
        </a>

        <a href="{{ route('dashboard.posts') }}" class="mt-3 {{ $is('dashboard.posts') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.posts'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Postingan Saya
        </a>
        <a href="{{ route('dashboard.applications') }}" class="{{ $is('dashboard.applications') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.applications'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Lamaran Saya
        </a>
        <a href="{{ route('dashboard.candidates') }}" class="{{ $is('dashboard.candidates') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.candidates'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Pelamar Masuk
        </a>
        <a href="{{ route('dashboard.reports') }}" class="{{ $is('dashboard.reports') ? $activeClass : $inactiveClass }}">
            @if($is('dashboard.reports'))<span class="size-2 rounded-full bg-askarsa-ink"></span>@endif
            Laporan Saya
        </a>
    </nav>

    <p class="mt-12 text-xs leading-relaxed text-slate-400">Atur pesanan, peluang, dan laporan dalam satu tempat.</p>
</aside>