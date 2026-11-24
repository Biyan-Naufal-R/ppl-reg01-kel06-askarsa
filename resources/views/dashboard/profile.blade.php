@extends('layouts.app')

@section('title', 'Profil Saya — ASKARSA')

@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-400">Beranda / Akun / Profil Saya</p>

    <div class="mx-auto mt-4 max-w-3xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Akun ASKARSA</p>
                <h1 class="mt-2 text-3xl font-black">Profil Saya</h1>
                <p class="muted mt-2">Informasi profil dan akun Anda.</p>
            </div>
            <a class="btn-primary" href="{{ route('dashboard.edit-profile') }}">Edit profil / akun</a>
        </div>

        <div class="card mt-6">
            <div class="flex items-center gap-4 border-b border-slate-100 pb-5">
                <span class="flex size-14 items-center justify-center rounded-full bg-askarsa text-xl font-bold text-white">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                </span>
                <div>
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">Anggota ASKARSA sejak {{ $user->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <dl class="grid gap-5 pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama lengkap</dt>
                    <dd class="mt-1 font-medium">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                    <dd class="mt-1 font-medium">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Domisili</dt>
                    <dd class="mt-1 font-medium">{{ $user->domisili ?: 'Belum diisi' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Password</dt>
                    <dd class="mt-1 flex flex-wrap items-center gap-3">
                        <span class="text-sm text-slate-600">Disimpan aman dan tidak dapat ditampilkan.</span>
                        <a class="text-sm font-semibold text-askarsa hover:underline" href="{{ route('dashboard.edit-profile') }}#password-settings">Ubah password</a>
                    </dd>
                </div>
            </dl>
        </div>

        @if ($user->profilJasa)
            <div class="card mt-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="eyebrow">Profil jasa saya</p>
                        <h2 class="mt-1 text-xl font-bold">{{ $user->profilJasa->nama_profil }}</h2>
                    </div>
                    <a class="text-sm font-semibold text-askarsa hover:underline" href="{{ route('dashboard.edit-profile') }}">Kelola profil jasa →</a>
                </div>
                <dl class="mt-5 grid gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status profil</dt>
                        <dd class="mt-1 font-medium">{{ $user->profilJasa->aktif ? 'Aktif' : 'Nonaktif (tidak muncul di katalog)' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ketersediaan</dt>
                        <dd class="mt-1 font-medium {{ $user->profilJasa->tersedia ? 'text-askarsa' : 'text-askarsa-danger' }}">{{ $user->profilJasa->tersedia ? 'Tersedia' : 'Tidak tersedia' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori bantuan</dt>
                        <dd class="mt-1 font-medium">{{ collect($user->profilJasa->kategori ?? [])->map(fn (string $kategori) => config('jasa.kategori.'.$kategori, $kategori))->implode(', ') ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        @endif
    </div>
</section>
@endsection
