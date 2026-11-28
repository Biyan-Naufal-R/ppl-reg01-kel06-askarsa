@extends('layouts.app')
@section('title', 'Laporan Saya — ASKARSA')
@section('content')
<section class="container-app py-8">
    <p class="text-xs text-slate-400">Beranda / Kelola Aktivitas / Laporan Saya</p>
    <h1 class="mt-2 text-3xl font-black text-askarsa-ink">Laporan Saya</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau laporan yang Anda kirim. Hanya laporan milik Anda yang ditampilkan di sini.</p>

    <div class="mt-6 space-y-4">
        @forelse ($laporan as $item)
            @php
                $badge = $item->status === \App\Models\Laporan::STATUS_MENUNGGU
                    ? 'bg-amber-100 text-amber-800'
                    : 'bg-emerald-100 text-emerald-700';
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs text-slate-400">{{ $item->objek_type === 'profil_jasa' ? 'Profil Jasa' : 'Lowongan' }}</p>
                        <h2 class="font-bold text-askarsa-ink">{{ $item->labelObjek() }}</h2>
                        <p class="mt-1 text-xs text-slate-400">{{ $item->labelKategori() }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $item->labelStatus() }}</span>
                </div>
                <div class="mt-4 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
                    <div><span class="text-slate-400">Tanggal Lapor</span><p class="font-medium text-askarsa-ink">{{ $item->created_at?->translatedFormat('d M Y') }}</p></div>
                    <div><span class="text-slate-400">Penjelasan</span><p class="font-medium text-askarsa-ink">{{ $item->penjelasan }}</p></div>
                </div>
                @if ($item->bukti_path)
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <a href="{{ route('laporan.bukti', $item) }}" class="btn-outline !px-4 !py-2 !text-sm">Lihat Bukti</a>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada laporan yang Anda kirim.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $laporan->links() }}</div>
</section>
@endsection
