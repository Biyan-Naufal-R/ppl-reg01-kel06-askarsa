@extends('layouts.app')
@section('title','Laporkan '.$labelJenis.' — ASKARSA')
@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / Laporkan {{ $labelJenis }}</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">Laporkan {{ $labelJenis }}</h1>
    <p class="mt-2 text-sm text-slate-500">Bantu kami menjaga kualitas konten di ASKARSA. Laporan Anda akan diperiksa oleh tim terkait.</p>

    <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]" novalidate>
        @csrf
        <input type="hidden" name="jenis_objek" value="{{ $jenisObjek }}">
        <input type="hidden" name="objek_id" value="{{ $objek->id }}">

        <div class="card p-7">
            <h2 class="text-lg font-bold text-askarsa-ink">Detail laporan</h2>

            <label class="field-label mt-5" for="kategori">Kategori laporan *</label>
            <select class="field bg-slate-50 @error('kategori') !border-red-400 @enderror" id="kategori" name="kategori" required>
                <option value="">Pilih kategori</option>
                @foreach ($kategoriList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('kategori') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            @error('kategori')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <label class="field-label mt-5" for="penjelasan">Penjelasan *</label>
            <textarea class="field min-h-32 @error('penjelasan') !border-red-400 @enderror" id="penjelasan" name="penjelasan" rows="5" placeholder="Jelaskan masalah yang Anda temukan (minimal 10 karakter)">{{ old('penjelasan') }}</textarea>
            @error('penjelasan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <label class="field-label mt-5" for="bukti">Bukti (opsional)</label>
            <input class="field bg-slate-50 @error('bukti') !border-red-400 @enderror" type="file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf">
            <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau PDF • maksimal 5 MB</p>
            @error('bukti')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <div class="mt-5 rounded-lg bg-askarsa-mint p-4 text-sm text-askarsa-ink">Bukti disimpan di penyimpanan privat dan hanya dapat dibuka oleh Anda selaku pelapor.</div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Kirim Laporan</button>
                <a href="{{ url()->previous() }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <aside class="card h-fit p-7 lg:sticky lg:top-24">
            <h2 class="text-lg font-bold text-askarsa-ink">{{ $labelJenis }} yang dilaporkan</h2>
            <p class="muted mt-5">Nama objek</p>
            <p class="font-semibold">{{ $labelObjek }}</p>
            <p class="muted mt-4">Pelapor</p>
            <p class="font-semibold">{{ auth()->user()->name }}</p>
            <p class="muted mt-4">Status awal</p>
            <p class="font-semibold">Menunggu Pemeriksaan</p>
        </aside>
    </form>
</section>
@endsection
