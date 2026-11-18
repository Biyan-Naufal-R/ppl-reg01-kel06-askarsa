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
                    <dd class="mt-1 font-medium">••••••••</dd>
                </div>
            </dl>
        </div>
    </div>
</section>
@endsection
