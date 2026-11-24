@extends('layouts.app')
@section('title','Postingan Saya — ASKARSA')
@section('content')
@php
    $statusLabel = ['aktif' => 'Aktif', 'ditutup' => 'Ditutup', 'nonaktif' => 'Nonaktif'];
    $statusClass = ['aktif' => 'bg-askarsa-mint text-askarsa', 'ditutup' => 'bg-amber-50 text-amber-800', 'nonaktif' => 'bg-rose-50 text-rose-700'];
@endphp
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Kelola Aktivitas / Postingan Saya</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">Postingan Saya</h1>
    <p class="mt-2 text-sm text-slate-500">Kelola lowongan yang Anda publikasikan dan pantau jumlah pelamar.</p>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $total }} lowongan • {{ $aktif }} masih menerima lamaran</p>
        <a href="{{ route('dashboard.create-job') }}" class="btn-primary">Buat Lowongan</a>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-56 flex-1">
                <label class="field-label" for="q">Cari</label>
                <input class="field" id="q" name="q" value="{{ $q }}" placeholder="Cari judul atau perusahaan">
            </div>
            <div class="min-w-48">
                <label class="field-label" for="status">Status</label>
                <select class="field" id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach($statusLabel as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-primary">Terapkan</button>
        </form>

        <table class="mt-5 w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-3">Lowongan</th><th class="p-3">Jenis & metode</th><th class="p-3">Lokasi</th><th class="p-3">Status</th><th class="p-3">Tindakan</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lowongans as $item)
                    <tr>
                        <td class="p-3">
                            <p class="font-semibold text-askarsa-ink">{{ $item->judul }}</p>
                            <p class="text-xs text-slate-500">{{ $item->perusahaan }} • {{ $item->lamarans_count }} pelamar</p>
                        </td>
                        <td class="p-3">{{ $item->kategori }} • {{ config('lowongan.metode.'.$item->metode, $item->metode) }}</td>
                        <td class="p-3">{{ $item->kecamatan }}</td>
                        <td class="p-3"><span class="inline-flex rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $statusClass[$item->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $statusLabel[$item->status] ?? $item->status }}</span></td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                <a class="text-askarsa" href="{{ route('jobs.show', $item->slug) }}">Detail →</a>
                                <a class="text-slate-600 hover:text-askarsa" href="{{ route('dashboard.create-job.edit', $item) }}">Edit</a>
                                <form method="POST" action="{{ route('dashboard.posts.toggle', $item) }}">@csrf<button class="text-slate-600 hover:text-askarsa">{{ $item->status === 'nonaktif' ? 'Aktifkan' : 'Nonaktifkan' }}</button></form>
                                <form method="POST" action="{{ route('dashboard.posts.tutup', $item) }}">@csrf<button class="text-slate-600 hover:text-askarsa">{{ $item->status === 'ditutup' ? 'Buka' : 'Tutup' }}</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-slate-500" colspan="5">Belum ada lowongan. Buat lowongan pertama Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
