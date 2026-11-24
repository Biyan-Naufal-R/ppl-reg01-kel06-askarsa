@extends('layouts.app')

@section('title', 'Edit Profil / Akun — ASKARSA')

@section('content')
@php
    $jasa = $user->profilJasa;
    $jasaAktif = filter_var(old('jasa_aktif', $user->punyaJasaAktif()), FILTER_VALIDATE_BOOLEAN);
    $kategoriTerpilih = old('kategori', $jasa?->kategori ?? []) ?? [];
    $jasaTersedia = filter_var(old('tersedia', $jasa?->tersedia ?? true), FILTER_VALIDATE_BOOLEAN);
@endphp
<section class="container-app py-10">
    <p class="text-xs text-slate-400">Beranda / Akun / Edit Profil</p>

    <div class="mx-auto mt-4 max-w-3xl">
        <div class="mb-6">
            <a class="text-sm font-semibold text-askarsa hover:underline" href="{{ route('dashboard.profile') }}">← Kembali ke profil</a>
            <p class="eyebrow mt-5">Pengaturan akun</p>
            <h1 class="mt-2 text-3xl font-black">Edit profil / akun</h1>
            <p class="muted mt-2">Perbarui informasi profil, email, domisili, atau password Anda.</p>
        </div>

        <form class="space-y-6" id="profile-form" method="post" action="{{ route('dashboard.profile.update') }}">
            @csrf
            @method('PUT')

            <section class="card jasa-toggle space-y-5">
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
                        @foreach (config('domisili.kecamatan') as $kecamatan)
                            <option value="{{ $kecamatan }}" @selected(old('domisili', $user->domisili) === $kecamatan)>{{ $kecamatan }}</option>
                        @endforeach
                    </select>
                    @error('domisili')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-200 bg-askarsa-mint/60 p-4">
                    <div>
                        <p class="text-sm font-bold text-askarsa-ink">Status profil jasa</p>
                        <p class="mt-1 text-xs text-slate-500">Aktifkan agar profil tampil di katalog jasa. Nonaktifkan agar profil tidak muncul sama sekali.</p>
                    </div>
                    <label class="relative inline-flex shrink-0 cursor-pointer items-center gap-3">
                        <span class="text-xs font-semibold text-slate-500">{{ $jasaAktif ? 'Aktif' : 'Nonaktif' }}</span>
                        <input class="peer sr-only" id="toggle-jasa" name="jasa_aktif" type="checkbox" value="1" @checked($jasaAktif) aria-label="Aktifkan jasa minta tolong">
                        <span class="relative h-7 w-12 rounded-full bg-slate-300 transition after:absolute after:left-[3px] after:top-[3px] after:size-[22px] after:rounded-full after:bg-white after:shadow peer-checked:bg-askarsa peer-checked:after:translate-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-askarsa/40" aria-hidden="true"></span>
                    </label>
                </div>

                <div id="jasa-section" class="rounded-xl border border-slate-200 bg-askarsa-surface/70 p-5">
                    <h3 class="font-bold text-askarsa-ink">Profil Jasa Minta Tolong</h3>
                    <p class="mt-1 text-xs text-slate-500">Profil jasa disimpan terpisah dari data akun. Saat aktif, profil tetap tampil di katalog meskipun sedang tidak tersedia.</p>

                    <div class="mt-5">
                        <span class="field-label">Ketersediaan jasa</span>
                        <div class="flex flex-wrap gap-3">
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm">
                                <input type="radio" name="tersedia" value="1" @checked($jasaTersedia)>
                                <span>Tersedia</span>
                            </label>
                            <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm">
                                <input type="radio" name="tersedia" value="0" @checked(! $jasaTersedia)>
                                <span>Tidak tersedia</span>
                            </label>
                        </div>
                        @error('tersedia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-xs text-slate-500">Pilih "Tidak tersedia" agar profil tetap tampil di katalog dengan label tersebut dan tidak bisa dipesan. Saat pesanan diterima, status berubah otomatis; setelah selesai, profil kembali tersedia dan dapat diatur lagi.</p>
                    </div>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="nama_profil">Nama Profil Jasa</label>
                            <input class="field" id="nama_profil" name="nama_profil" type="text" maxlength="255" placeholder="Contoh: Joanna Siap Bantu" value="{{ old('nama_profil', $jasa?->nama_profil) }}">
                            @error('nama_profil')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="field-label" for="tarif">Tarif (Rp)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-500">Rp</span>
                                <input class="field pl-10" id="tarif" name="tarif" type="number" min="0" step="1000" placeholder="25000" value="{{ old('tarif', $jasa?->tarif) }}">
                            </div>
                            @error('tarif')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <span class="field-label">Kategori Bantuan</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach (config('jasa.kategori') as $nilai => $label)
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="checkbox" name="kategori[]" value="{{ $nilai }}" @checked(in_array($nilai, $kategoriTerpilih))>
                                    <span class="inline-flex rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-askarsa-ink transition hover:border-askarsa peer-checked:border-askarsa peer-checked:bg-askarsa peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-askarsa/30">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kategori')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('kategori.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-xs text-slate-500">Pilih satu atau lebih kategori.</p>
                    </div>

                    <div class="mt-5">
                        <label class="field-label" for="deskripsi">Deskripsi</label>
                        <textarea class="field" id="deskripsi" name="deskripsi" rows="4" maxlength="1000" placeholder="Ceritakan bantuan apa yang bisa Anda berikan…">{{ old('deskripsi', $jasa?->deskripsi) }}</textarea>
                        @error('deskripsi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button class="btn-primary" type="submit">Simpan</button>
                    </div>
                </div>
            </section>

            <section id="password-settings" class="card space-y-5">
                <div>
                    <h2 class="text-lg font-bold">Ubah password</h2>
                    <p class="mt-1 text-sm text-slate-500">Kosongkan jika tidak ingin mengganti password.</p>
                </div>

                <div>
                    <label class="field-label" for="current_password">Password saat ini</label>
                    <input class="field" id="current_password" name="current_password" type="password" autocomplete="current-password">
                    <button class="mt-2 text-sm font-semibold text-askarsa hover:underline" type="button" data-password-toggle="current_password" data-show-label="Tampilkan" data-hide-label="Sembunyikan" aria-label="Tampilkan password saat ini" aria-pressed="false">Tampilkan</button>
                    @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password">Password baru</label>
                    <input class="field" id="password" name="password" type="password" autocomplete="new-password" minlength="8">
                    <button class="mt-2 text-sm font-semibold text-askarsa hover:underline" type="button" data-password-toggle="password" data-show-label="Tampilkan" data-hide-label="Sembunyikan" aria-label="Tampilkan password baru" aria-pressed="false">Tampilkan</button>
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="password_confirmation">Konfirmasi password baru</label>
                    <input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8">
                    <button class="mt-2 text-sm font-semibold text-askarsa hover:underline" type="button" data-password-toggle="password_confirmation" data-show-label="Tampilkan" data-hide-label="Sembunyikan" aria-label="Tampilkan konfirmasi password baru" aria-pressed="false">Tampilkan</button>
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
