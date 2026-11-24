@extends('layouts.app')
@section('title','Katalog Jasa — ASKARSA')
@section('content')
@php
    $filters = $filters ?? [];
    $adaFilter = ! empty(array_filter($filters, fn ($nilai) => $nilai !== null && $nilai !== ''));
@endphp
<section class="bg-askarsa-mint py-10">
    <div class="container-app">
        <p class="text-xs text-slate-500">Beranda / Jasa Minta Tolong</p>
        <p class="eyebrow mt-6">Katalog jasa minta tolong</p>
        <h1 class="mt-2 text-4xl font-black">Bantuan dekat, urusan beres.</h1>
        <p class="muted mt-2">Penyedia jasa untuk kebutuhan sehari-hari di sekitar Anda.</p>
        <form method="GET" action="{{ route('services.index') }}" class="mt-6 flex gap-3">
            <input class="field" type="search" name="q" value="{{ old('q', $filters['q'] ?? '') }}" placeholder="Cari jenis bantuan atau nama penyedia">
            <button class="btn-primary" type="submit">Cari</button>
        </form>
    </div>
</section>

<section class="container-app grid gap-6 py-10 lg:grid-cols-[240px_1fr]">
    <aside class="card h-fit">
        <h2 class="font-bold">Filter pencarian</h2>
        <form method="GET" action="{{ route('services.index') }}">
            <input type="hidden" name="q" value="{{ old('q', $filters['q'] ?? '') }}">

            <label class="field-label mt-5" for="filter-kecamatan">Kecamatan</label>
            <select class="field" id="filter-kecamatan" name="kecamatan">
                <option value="">Semua kecamatan</option>
                @foreach ($kecamatanList as $kecamatan)
                    <option value="{{ $kecamatan }}" @selected(old('kecamatan', $filters['kecamatan'] ?? '') === $kecamatan)>{{ $kecamatan }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="filter-kategori">Kategori jasa</label>
            <select class="field" id="filter-kategori" name="kategori">
                <option value="">Semua kategori jasa</option>
                @foreach ($kategoriList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('kategori', $filters['kategori'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="filter-tarif-min">Tarif awal</label>
            <input class="field" id="filter-tarif-min" type="number" min="0" step="1000" name="tarif_min" value="{{ old('tarif_min', $filters['tarif_min'] ?? '') }}" placeholder="Mis. 20000">

            <label class="field-label mt-5" for="filter-tarif-max">Batas tarif</label>
            <input class="field" id="filter-tarif-max" type="number" min="0" step="1000" name="tarif_max" value="{{ old('tarif_max', $filters['tarif_max'] ?? '') }}" placeholder="Mis. 100000">

            <button class="btn-primary mt-5 w-full" type="submit">Terapkan Filter</button>
        </form>
        <a class="mt-3 block text-sm text-slate-500 hover:text-askarsa" href="{{ route('services.index') }}">Reset filter</a>
    </aside>

    <div>
        <div class="flex justify-between">
            <b>{{ count($services) }} profil jasa ditemukan</b>
            <span class="text-xs text-slate-500">Urutkan: Tersedia terlebih dahulu</span>
        </div>
        @if ($adaFilter)
            <p class="mt-2 text-xs text-slate-500">
                Filter aktif:
                @if (! empty($filters['q'])) kata kunci “{{ $filters['q'] }}” @endif
                @if (! empty($filters['kecamatan'])) · kecamatan {{ $filters['kecamatan'] }} @endif
                @if (! empty($filters['kategori'])) · kategori {{ $kategoriList[$filters['kategori']] ?? $filters['kategori'] }} @endif
                @if (isset($filters['tarif_min'])) · tarif ≥ Rp{{ number_format($filters['tarif_min'], 0, ',', '.') }} @endif
                @if (isset($filters['tarif_max'])) · tarif ≤ Rp{{ number_format($filters['tarif_max'], 0, ',', '.') }} @endif
            </p>
        @endif
        <p class="mt-2 text-xs text-slate-500">Profil berstatus "Tidak tersedia" tetap tampil di katalog, tetapi tidak menerima pesanan. Profil nonaktif tidak ditampilkan.</p>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse ($services as $service)
                @include('components.service-card',['service'=>$service])
            @empty
                <p class="text-sm text-slate-500 md:col-span-2">Tidak ada profil jasa yang cocok dengan filter ini. Coba ubah atau reset filter.</p>
            @endforelse
        </div>
        <p class="mt-6 text-sm text-slate-500">Menampilkan {{ count($services) }} profil jasa</p>
    </div>
</section>
@endsection
