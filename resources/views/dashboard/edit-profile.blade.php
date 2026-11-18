@extends('layouts.app')

@section('title', 'Edit Profil / Akun — ASKARSA')

@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-400">Beranda / Akun / Edit Profil</p>

    <div class="mx-auto mt-4 max-w-3xl">
        <div class="mb-6">
            <a class="text-sm font-semibold text-askarsa hover:underline" href="{{ route('dashboard.profile') }}">← Kembali ke profil</a>
            <p class="eyebrow mt-5">Pengaturan akun</p>
            <h1 class="mt-2 text-3xl font-black">Edit profil / akun</h1>
            <p class="muted mt-2">Perbarui informasi profil, email, domisili, atau password Anda.</p>
        </div>

        <form class="space-y-6" method="post" action="{{ route('dashboard.profile.update') }}">
            @csrf
            @method('PUT')

            <section class="card space-y-5">
                <div>
                    <h2 class="text-lg font-bold">Informasi profil</h2>
                    <p class="mt-1 text-sm text-slate-500">Informasi ini digunakan pada akun ASKARSA Anda.</p>
                </div>

                <div>
                    <label class="field-label" for="name">Nama lengkap</label>
                    <input class="field" id="name" name="name" type="text" autocomplete="name" maxlength="255" required value="{{ old('name', $user->name) }}">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="email">Email</label>
                    <input class="field" id="email" name="email" type="email" autocomplete="email" maxlength="255" required value="{{ old('email', $user->email) }}">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="domisili">Domisili / kecamatan</label>
                    <select class="field" id="domisili" name="domisili" required>
                        @foreach (['Purwokerto Utara', 'Sokaraja', 'Baturaden'] as $domisili)
                            <option value="{{ $domisili }}" @selected(old('domisili', $user->domisili) === $domisili)>{{ $domisili }}</option>
                        @endforeach
                    </select>
                    @error('domisili')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="card space-y-5">
                <div>
                    <h2 class="text-lg font-bold">Ubah password</h2>
                    <p class="mt-1 text-sm text-slate-500">Kosongkan jika tidak ingin mengganti password.</p>
                </div>

                <div>
                    <label class="field-label" for="current_password">Password saat ini</label>
                    <input class="field" id="current_password" name="current_password" type="password" autocomplete="current-password">
                    @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password">Password baru</label>
                    <input class="field" id="password" name="password" type="password" autocomplete="new-password" minlength="8">
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Konfirmasi password baru</label>
                    <input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8">
                </div>
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <a class="btn-secondary" href="{{ route('dashboard.profile') }}">Batal</a>
                <button class="btn-primary" type="submit">Simpan perubahan</button>
            </div>
        </form>
    </div>
</section>
@endsection
