@extends('layouts.app')
@section('title', ($lowongan ? 'Edit Lowongan' : 'Buat Lowongan').' — ASKARSA')
@section('content')
@php
    $isEdit = (bool) $lowongan;
    $action = $isEdit ? route('dashboard.create-job.update', $lowongan) : route('dashboard.create-job.store');
@endphp
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / Kelola Aktivitas / {{ $isEdit ? 'Edit Lowongan' : 'Buat Lowongan' }}</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">{{ $isEdit ? 'Edit Lowongan' : 'Buat Lowongan' }}</h1>
    <p class="mt-2 text-sm text-slate-500">Temukan rekan yang tepat untuk menyelesaikan proyek Anda.</p>

    <form method="POST" action="{{ $action }}" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]" novalidate>
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="space-y-6">
            <div class="card p-7">
                <h2 class="text-lg font-bold text-askarsa-ink">Informasi pekerjaan</h2>

                <label class="field-label mt-5" for="kategori">Jenis lowongan *</label>
                <select class="field @error('kategori') !border-red-400 @enderror" id="kategori" name="kategori" required>
                    <option value="">Pilih jenis lowongan</option>
                    @foreach(config('lowongan.kategori') as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('kategori', $lowongan->kategori ?? 'Freelance') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="field-label mt-5" for="judul">Judul lowongan *</label>
                <input class="field @error('judul') !border-red-400 @enderror" id="judul" name="judul" value="{{ old('judul', $lowongan->judul ?? '') }}" required>
                @error('judul')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="field-label mt-5" for="perusahaan">Nama pemberi kerja / perusahaan *</label>
                <input class="field @error('perusahaan') !border-red-400 @enderror" id="perusahaan" name="perusahaan" value="{{ old('perusahaan', $lowongan->perusahaan ?? auth()->user()->name) }}" required>
                @error('perusahaan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="field-label mt-5" for="jenis">Kategori pekerjaan *</label>
                <select class="field @error('jenis') !border-red-400 @enderror" id="jenis" name="jenis" required>
                    <option value="">Pilih kategori pekerjaan</option>
                    @foreach(config('lowongan.jenis') as $nilai => $info)
                        <option value="{{ $nilai }}" @selected(old('jenis', $lowongan->jenis ?? '') === $nilai)>{{ $info['label'] }}</option>
                    @endforeach
                </select>
                @error('jenis')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="field-label mt-5" for="deskripsi">Deskripsi pekerjaan *</label>
                <textarea class="field min-h-28 @error('deskripsi') !border-red-400 @enderror" id="deskripsi" name="deskripsi" rows="4" required>{{ old('deskripsi', $lowongan->deskripsi ?? '') }}</textarea>
                @error('deskripsi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="bayaran">Bayaran proyek (Rp) *</label>
                        <input class="field @error('bayaran') !border-red-400 @enderror" id="bayaran" name="bayaran" inputmode="numeric" value="{{ old('bayaran', isset($lowongan) ? number_format($lowongan->gaji_min,0,',','.') : '') }}" required>
                        @error('bayaran')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="durasi">Durasi proyek *</label>
                        <input class="field @error('durasi') !border-red-400 @enderror" id="durasi" name="durasi" value="{{ old('durasi', $lowongan->durasi ?? '') }}" required>
                        @error('durasi')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="metode">Metode kerja *</label>
                        <select class="field @error('metode') !border-red-400 @enderror" id="metode" name="metode" required>
                            <option value="">Pilih metode kerja</option>
                            @foreach(config('lowongan.metode') as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('metode', $lowongan->metode ?? '') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Remote / On-site / Hybrid</p>
                        @error('metode')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="field-label" for="kecamatan">Kecamatan *</label>
                        <select class="field @error('kecamatan') !border-red-400 @enderror" id="kecamatan" name="kecamatan" required>
                            <option value="">Pilih kecamatan</option>
                            @foreach(config('domisili.kecamatan') as $kecamatan)
                                <option value="{{ $kecamatan }}" @selected(old('kecamatan', $lowongan->kecamatan ?? '') === $kecamatan)>{{ $kecamatan }}</option>
                            @endforeach
                        </select>
                        @error('kecamatan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card p-7">
                <h2 class="text-lg font-bold text-askarsa-ink">Persyaratan pelamar</h2>

                <label class="field-label mt-5" for="persyaratan">Persyaratan *</label>
                <textarea class="field min-h-28 @error('persyaratan') !border-red-400 @enderror" id="persyaratan" name="persyaratan" rows="4" required>{{ old('persyaratan', $lowongan->persyaratan ?? '') }}</textarea>
                @error('persyaratan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label class="field-label mt-5" for="batas_lamaran">Batas lamaran *</label>
                <input class="field @error('batas_lamaran') !border-red-400 @enderror" id="batas_lamaran" type="date" name="batas_lamaran" value="{{ old('batas_lamaran', isset($lowongan) ? $lowongan->batas_lamaran?->format('Y-m-d') : '') }}" required>
                @error('batas_lamaran')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <div class="mt-5 rounded-lg bg-askarsa-mint p-4 text-sm text-askarsa-ink">Lowongan yang dipublikasikan dapat diakses publik. Tidak diperkenankan meminta biaya pendaftaran kepada pelamar.</div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Publikasikan Lowongan' }}</button>
                    <a href="{{ route('dashboard.posts') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>

        <aside class="card h-fit p-7 lg:sticky lg:top-24">
            <h2 class="text-lg font-bold text-askarsa-ink">Panduan publikasi</h2>
            <p class="mt-4 text-sm text-slate-600">Tulis tanggung jawab, kompensasi, dan persyaratan secara jelas. Jangan mencantumkan data pribadi sensitif.</p>
            <div class="mt-5 rounded-lg bg-askarsa-mint p-4 text-sm text-askarsa-ink">Freelance/Part Time menampilkan durasi proyek. Pilihan filter katalog mengikuti jenis lowongan, kategori pekerjaan, metode kerja, kecamatan, dan bayaran.</div>
            <p class="mt-5 text-sm text-slate-500">Setelah publikasi, kelola lowongan di Postingan Saya dan pelamar di Pelamar Masuk.</p>
        </aside>
    </form>
</section>
<script>
document.getElementById('bayaran')?.addEventListener('input', function () {
    const digits = this.value.replace(/\D/g, '');
    this.value = digits ? Number(digits).toLocaleString('id-ID') : '';
});
</script>
@endsection
