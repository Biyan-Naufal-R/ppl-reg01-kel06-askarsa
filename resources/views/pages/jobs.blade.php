@extends('layouts.app') @section('title','Katalog Lowongan — ASKARSA') @section('content')
@if ($type === 'freelance')
@php
    $filters = $filters ?? [];
    $adaFilter = ! empty(array_filter($filters, fn ($nilai) => $nilai !== null && $nilai !== ''));
@endphp
<section class="bg-askarsa-mint py-10"><div class="container-app"><p class="text-xs text-slate-500">Beranda / Freelance</p><p class="eyebrow mt-6">Katalog freelance</p><h1 class="mt-2 text-4xl font-black">Keahlian Anda, peluang berikutnya.</h1><p class="muted mt-2">Temukan peluang kerja dari pemberi kerja lokal di Kabupaten Banyumas.</p>
<form method="GET" action="{{ route('jobs.freelance') }}" class="mt-6 flex gap-3">
    <input class="field" type="search" name="q" value="{{ old('q', $filters['q'] ?? '') }}" placeholder="Cari judul atau pemberi kerja">
    @foreach(['kecamatan','kategori','metode','jenis','gaji_min','gaji_max'] as $kunci)
        @if(isset($filters[$kunci]))
            <input type="hidden" name="{{ $kunci }}" value="{{ $filters[$kunci] }}">
        @endif
    @endforeach
    <button class="btn-primary" type="submit">Cari</button>
</form>
</div></section>

<section class="container-app grid gap-6 py-10 lg:grid-cols-[240px_1fr]">
    <aside class="card h-fit">
        <h2 class="font-bold">Filter pencarian</h2>
        <form method="GET" action="{{ route('jobs.freelance') }}">
            <input type="hidden" name="q" value="{{ old('q', $filters['q'] ?? '') }}">

            <label class="field-label mt-5" for="job-kecamatan">Kecamatan</label>
            <select class="field" id="job-kecamatan" name="kecamatan">
                <option value="">Semua</option>
                @foreach ($kecamatanList as $kecamatan)
                    <option value="{{ $kecamatan }}" @selected(old('kecamatan', $filters['kecamatan'] ?? '') === $kecamatan)>{{ $kecamatan }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="job-kategori">Kategori pekerjaan</label>
            <select class="field" id="job-kategori" name="kategori">
                <option value="">Semua</option>
                @foreach ($kategoriList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('kategori', $filters['kategori'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="job-metode">Metode kerja</label>
            <select class="field" id="job-metode" name="metode">
                <option value="">Semua</option>
                @foreach ($metodeList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('metode', $filters['metode'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="job-jenis">Jenis pekerjaan</label>
            <select class="field" id="job-jenis" name="jenis">
                <option value="">Semua</option>
                @foreach ($jenisList as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('jenis', $filters['jenis'] ?? '') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="field-label mt-5" for="job-gaji-min">Bayaran minimal</label>
            <input class="field" id="job-gaji-min" type="number" min="0" step="100000" name="gaji_min" value="{{ old('gaji_min', $filters['gaji_min'] ?? '') }}" placeholder="Mis. 500000">

            <label class="field-label mt-5" for="job-gaji-max">Bayaran maksimal</label>
            <input class="field" id="job-gaji-max" type="number" min="0" step="100000" name="gaji_max" value="{{ old('gaji_max', $filters['gaji_max'] ?? '') }}" placeholder="Mis. 1500000">

            <button class="btn-primary mt-5 w-full" type="submit">Terapkan Filter</button>
        </form>
        <a class="mt-3 block text-sm text-slate-500 hover:text-askarsa" href="{{ route('jobs.freelance') }}">Reset filter</a>
    </aside>

    <div>
        <div class="flex justify-between"><b>{{ count($jobs) }} proyek freelance ditemukan</b><span class="text-xs text-slate-500">Urutkan: Terbaru</span></div>
        @if ($adaFilter)
            <p class="mt-2 text-xs text-slate-500">
                Filter aktif:
                @if (! empty($filters['q'])) kata kunci “{{ $filters['q'] }}” @endif
                @if (! empty($filters['kecamatan'])) · kecamatan {{ $filters['kecamatan'] }} @endif
                @if (! empty($filters['kategori'])) · kategori {{ $kategoriList[$filters['kategori']] ?? $filters['kategori'] }} @endif
                @if (! empty($filters['metode'])) · metode {{ $metodeList[$filters['metode']] ?? $filters['metode'] }} @endif
                @if (! empty($filters['jenis'])) · jenis {{ $jenisList[$filters['jenis']] ?? $filters['jenis'] }} @endif
                @if (isset($filters['gaji_min'])) · bayaran ≥ Rp{{ number_format($filters['gaji_min'], 0, ',', '.') }} @endif
                @if (isset($filters['gaji_max'])) · bayaran ≤ Rp{{ number_format($filters['gaji_max'], 0, ',', '.') }} @endif
            </p>
        @endif
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @forelse($jobs as $job)
                @include('components.job-card',['job'=>$job])
            @empty
                <p class="text-sm text-slate-500 md:col-span-2">Tidak ada lowongan yang cocok dengan filter ini. Coba ubah atau reset filter.</p>
            @endforelse
        </div>
    </div>
</section>
@else
<section class="bg-askarsa-mint py-10"><div class="container-app"><p class="text-xs text-slate-500">Beranda / {{ $type==='freelance'?'Freelance':'Full-Time' }}</p><p class="eyebrow mt-6">Katalog {{ $type==='freelance'?'freelance':'full-time' }}</p><h1 class="mt-2 text-4xl font-black">{{ $type==='freelance'?'Keahlian Anda, peluang berikutnya.':'Temukan tempat Anda bertumbuh.' }}</h1><p class="muted mt-2">Temukan peluang kerja dari pemberi kerja lokal di Kabupaten Banyumas.</p><form class="mt-6 flex gap-3"><input class="field" placeholder="Cari judul atau pemberi kerja"><button class="btn-primary">Cari</button></form></div></section>
<section class="container-app grid gap-6 py-10 lg:grid-cols-[240px_1fr]"><aside class="card h-fit"><h2 class="font-bold">Filter pencarian</h2>@foreach(['Kecamatan','Kategori pekerjaan','Metode kerja','Bayaran / proyek'] as $label)<label class="field-label mt-5">{{ $label }}</label><select class="field"><option>Semua</option></select>@endforeach<button class="btn-primary mt-5 w-full">Terapkan Filter</button><button class="mt-3 text-sm text-slate-500">Reset filter</button></aside><div><div class="flex justify-between"><b>{{ count($jobs)*6 }} {{ $type==='freelance'?'proyek freelance':'lowongan full-time' }}</b><span class="text-xs text-slate-500">Urutkan: Terbaru</span></div><div class="mt-4 grid gap-4 md:grid-cols-2">@foreach($jobs as $job) @include('components.job-card',['job'=>$job]) @endforeach</div></div></section>
@endif
@endsection
