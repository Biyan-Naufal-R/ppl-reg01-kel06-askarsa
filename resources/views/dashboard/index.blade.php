@extends('layouts.app')
@section('title', $page.' — ASKARSA')
@section('content')
<section class="container-app py-8">
    <p class="text-xs text-slate-400">Beranda / Kelola Aktivitas / {{ $page }}</p>
    <h1 class="mt-2 text-3xl font-black text-askarsa-ink">{{ $page }}</h1>
    <p class="mt-1 text-sm text-slate-500">Kelola aktivitas Anda dalam satu akun ASKARSA.</p>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Ringkasan aktivitas</p><p class="mt-2 text-2xl font-black text-askarsa">4</p><p class="text-xs text-slate-500">Item contoh pada halaman ini</p></div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Status akun</p><p class="mt-2 text-2xl font-black text-askarsa">Aktif</p><p class="text-xs text-slate-500">Data contoh / demo UI</p></div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-bold text-askarsa-ink">{{ $page }}</h2>
            <a class="btn-primary !px-4 !py-2 !text-sm" href="{{ route('dashboard.create-job') }}">Buat Lowongan</a>
        </div>
        <div class="mt-5 flex flex-wrap gap-3">
            <input class="field max-w-sm" placeholder="Cari judul atau nama">
            <select class="field max-w-xs"><option>Semua status</option><option>Dalam Proses</option><option>Riwayat</option></select>
            <button class="btn-secondary !py-2">Terapkan</button>
        </div>
        <table class="mt-5 w-full min-w-[650px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-3">Aktivitas</th><th class="p-3">Tanggal</th><th class="p-3">Status</th><th class="p-3">Tindakan</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                <tr><td class="p-3 font-semibold">Desain konten untuk UMKM</td><td class="p-3">23 Okt 2026</td><td class="p-3"><span class="pill">Dalam Proses</span></td><td class="p-3"><a class="font-bold text-askarsa" href="#">Lihat Detail →</a></td></tr>
                <tr><td class="p-3 font-semibold">Staf Administrasi</td><td class="p-3">22 Okt 2026</td><td class="p-3"><span class="pill">Menunggu Ditinjau</span></td><td class="p-3"><a class="font-bold text-askarsa" href="#">Lihat Detail →</a></td></tr>
                <tr><td class="p-3 font-semibold">Fotografer Produk UMKM</td><td class="p-3">21 Okt 2026</td><td class="p-3"><span class="pill">Selesai</span></td><td class="p-3"><a class="font-bold text-askarsa" href="#">Lihat Detail →</a></td></tr>
            </tbody>
        </table>
    </div>
</section>
@endsection
