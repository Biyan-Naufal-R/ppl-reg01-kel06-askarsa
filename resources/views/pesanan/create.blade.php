@extends('layouts.app')

@section('title', 'Pesan Jasa — ASKARSA')

@section('content')
@php
    $durasiOptions = ['<1 jam', '1-2 jam', '2-4 jam', 'seharian', '>1 hari'];
@endphp
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / Jasa Minta Tolong / {{ $service['name'] }} / Pesan</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="card">
            <p class="eyebrow">Form pemesanan</p>
            <h1 class="mt-2 text-3xl font-black">Pesan jasa dengan mudah</h1>
            <p class="muted mt-2">Lengkapi detail kebutuhan Anda agar penyedia dapat membantu dengan lebih tepat.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('pesanan.store') }}">
                @csrf
                <input type="hidden" name="penyedia_id" value="{{ $penyedia->id }}">
                <input type="hidden" name="slug" value="{{ $service['slug'] }}">

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="nama_pemesan">Nama Lengkap</label>
                        <input class="field" id="nama_pemesan" name="nama_pemesan" type="text" value="{{ old('nama_pemesan', auth()->user()->name) }}" required>
                        @error('nama_pemesan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="nomor_telepon">Nomor Telepon</label>
                        <input class="field" id="nomor_telepon" name="nomor_telepon" type="text" value="{{ old('nomor_telepon') }}" placeholder="Contoh: 081234567890" required>
                        @error('nomor_telepon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="email_pemesan">Alamat Email</label>
                        <input class="field" id="email_pemesan" name="email_pemesan" type="email" value="{{ old('email_pemesan', auth()->user()->email) }}" required>
                        @error('email_pemesan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="domisili_pemesan">Domisili</label>
                        <select class="field" id="domisili_pemesan" name="domisili_pemesan" required>
                            <option value="">Pilih domisili</option>
                            @foreach (config('domisili.kecamatan') as $kecamatan)
                                <option value="{{ $kecamatan }}" @selected(old('domisili_pemesan', auth()->user()->domisili) === $kecamatan)>{{ $kecamatan }}</option>
                            @endforeach
                        </select>
                        @error('domisili_pemesan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="field-label" for="titik_ambil">Alamat Layanan / Titik Ambil</label>
                    <textarea class="field" id="titik_ambil" name="titik_ambil" rows="3" placeholder="Tuliskan lokasi layanan atau titik ambil" required>{{ old('titik_ambil') }}</textarea>
                    @error('titik_ambil')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="field-label" for="titik_antar">Titik Antar / Tujuan <span class="font-normal text-slate-400">{{ $wajibTitikAntar ? '(Wajib untuk layanan antar/titip beli/temani)' : '(Opsional)' }}</span></label>
                    <textarea class="field" id="titik_antar" name="titik_antar" rows="3" placeholder="Tuliskan titik antar/tujuan jika ada" @required($wajibTitikAntar)>{{ old('titik_antar') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Wajib untuk Antar Jemput, Antar Barang, Titip Beli, dan Temani. Opsional untuk kategori lain.</p>
                    @error('titik_antar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="tanggal_mulai">Tanggal Mulai Bantuan</label>
                        <input class="field" id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai') }}" min="{{ now()->toDateString() }}" required>
                        @error('tanggal_mulai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="perkiraan_durasi">Perkiraan Durasi</label>
                        <select class="field" id="perkiraan_durasi" name="perkiraan_durasi" required>
                            <option value="">Pilih durasi</option>
                            @foreach ($durasiOptions as $durasi)
                                <option value="{{ $durasi }}" @selected(old('perkiraan_durasi') === $durasi)>{{ $durasi }}</option>
                            @endforeach
                        </select>
                        @error('perkiraan_durasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <span class="field-label">Jenis Bantuan</span>
                    <div class="rounded-lg border border-slate-200 bg-askarsa-surface px-4 py-3">
                        <p class="text-xs text-slate-500">Otomatis dari profil jasa penyedia.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($jenisBantuan as $jenis)
                                <span class="pill">{{ $jenis }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <label class="field-label" for="catatan_tambahan">Catatan Tambahan</label>
                    <textarea class="field" id="catatan_tambahan" name="catatan_tambahan" rows="4" placeholder="Ceritakan kebutuhan Anda secara singkat...">{{ old('catatan_tambahan') }}</textarea>
                    @error('catatan_tambahan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                @error('jasa')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('penyedia_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('slug')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="flex flex-wrap gap-3">
                    <button class="btn-primary" type="submit">Kirim Permintaan</button>
                    <a class="btn-secondary" href="{{ route('services.show', $service['slug']) }}">Kembali</a>
                </div>
            </form>
        </div>

        <aside class="card h-fit">
            <p class="eyebrow">Ringkasan jasa</p>
            <div class="mt-4 flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-full bg-askarsa-mint font-bold text-askarsa">{{ collect(explode(' ', $penyedia->name))->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->implode('') }}</div>
                <div>
                    <h2 class="font-bold">{{ $service['name'] }}</h2>
                    <p class="text-xs text-slate-500">{{ $penyedia->name }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><span class="text-slate-500">Domisili</span><span class="font-medium text-right">{{ $service['district'] }}</span></div>
                <div class="flex justify-between gap-4"><span class="text-slate-500">Tarif</span><span class="font-semibold text-askarsa text-right">{{ $service['price'] }}</span></div>
            </div>

            <div class="mt-5">
                <p class="text-sm font-semibold">Jenis bantuan</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($jenisBantuan as $jenis)
                        <span class="pill">{{ $jenis }}</span>
                    @endforeach
                </div>
            </div>

            <div class="mt-5">
                <p class="text-sm font-semibold">Detail layanan</p>
                <p class="mt-2 text-sm text-slate-600">{{ $service['description'] ?: 'Penyedia belum menambahkan deskripsi.' }}</p>
            </div>
        </aside>
    </div>
</section>
@endsection
