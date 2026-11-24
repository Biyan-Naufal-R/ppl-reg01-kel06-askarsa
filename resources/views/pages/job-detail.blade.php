@extends('layouts.app')
@section('title',$job['title'].' — ASKARSA')
@section('content')
@php
    $db = ! empty($job['lowongan_id']);
    $status = $job['status'] ?? 'aktif';
    $statusLabel = ['aktif' => 'Aktif', 'ditutup' => 'Ditutup', 'nonaktif' => 'Nonaktif'][$status] ?? 'Aktif';
    $statusClass = ['aktif' => 'pill', 'ditutup' => 'pill bg-amber-50 text-amber-800', 'nonaktif' => 'pill bg-slate-100 text-slate-600'][$status] ?? 'pill';
    $dipublikasikan = $job['created_at'] ?? '21 Oktober 2026';
    $batas = ! empty($job['batas_lamaran']) ? \Illuminate\Support\Carbon::parse($job['batas_lamaran'])->translatedFormat('d F Y') : '5 November 2026';
@endphp
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / {{ $job['type'] }} / {{ $job['title'] }}</p>
    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_300px]">
        <article class="space-y-5">
            <div class="card">
                <p class="font-bold">{{ $job['company'] }}</p>
                <p class="muted">{{ $job['location'] }}</p>
                <h1 class="mt-5 text-3xl font-black">{{ $job['title'] }}</h1>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if(! empty($job['jenis']))
                        @include('components.job-type-mode-badges',['job'=>$job])
                        @include('components.job-jenis-badge',['job'=>$job])
                    @else
                        <span class="pill">{{ $job['type'] }}</span><span class="pill">{{ $job['mode'] }}</span>
                    @endif
                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <p class="muted mt-4">Dipublikasikan {{ $dipublikasikan }} • Batas lamaran {{ $batas }}</p>
                <h2 class="mt-6 font-bold">Tentang pekerjaan</h2>
                <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $job['deskripsi'] ?? $job['company'].' membuka kesempatan untuk bergabung dan membantu operasional serta pengembangan usaha lokal di Banyumas.' }}</p>
                <h3 class="mt-5 font-bold">Tanggung jawab & persyaratan</h3>
                @if(! empty($job['persyaratan']))
                    <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $job['persyaratan'] }}</p>
                @else
                    <ul class="mt-2 list-disc space-y-2 pl-5 text-sm text-slate-600"><li>Menyelesaikan pekerjaan sesuai brief dan tenggat.</li><li>Mampu berkomunikasi dan bekerja sama.</li><li>Memiliki pengalaman atau portofolio relevan.</li></ul>
                @endif
            </div>
        </article>
        <aside class="card h-fit">
            <h2 class="font-bold">Ringkasan peluang</h2>
            <p class="muted mt-4">{{ in_array($job['type'],['Freelance','Part Time'],true)?'Bayaran proyek':'Gaji / bulan' }}</p>
            <p class="mt-1 text-2xl font-black text-askarsa">{{ $job['salary'] }}</p>
            <p class="muted mt-4">Lokasi</p><p class="text-sm font-semibold">{{ $job['location'] }}</p>
            <p class="muted mt-4">Metode kerja</p><p class="text-sm font-semibold">{{ $job['mode'] }}</p>
            @if(! empty($job['durasi']))
                <p class="muted mt-4">Durasi proyek</p><p class="text-sm font-semibold">{{ $job['durasi'] }}</p>
            @endif

            @if($errors->has('lamaran'))
                <p class="mt-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first('lamaran') }}</p>
            @endif

            @if($db)
                @auth
                    @if($lamaranSaya)
                        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">Anda sudah melamar lowongan ini. Status: <b>{{ \App\Models\Lamaran::labelStatus()[$lamaranSaya->status] ?? $lamaranSaya->status }}</b>.</div>
                        <a href="{{ route('dashboard.applications') }}" class="btn-secondary mt-3 w-full">Lihat Lamaran Saya</a>
                    @elseif(($lowongan?->user_id ?? null) === auth()->id())
                        <a href="{{ route('dashboard.posts') }}" class="btn-secondary mt-6 w-full">Kelola Postingan Saya</a>
                    @elseif($lowongan?->bisaDilamar())
                        <a href="{{ route('lamaran.create', $job['slug']) }}" class="btn-primary mt-6 w-full">Lamar Sekarang</a>
                    @else
                        <button class="btn-secondary mt-6 w-full cursor-not-allowed opacity-60" disabled>Lamaran Tidak Tersedia</button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-primary mt-6 w-full">Lamar Sekarang</a>
                @endauth
            @else
                <a href="{{ route('login') }}" class="btn-primary mt-6 w-full">Lamar Sekarang</a>
            @endif
            <a href="{{ route('login') }}" class="btn-secondary mt-3 w-full">Laporkan Lowongan</a>
        </aside>
    </div>
</section>
@endsection
