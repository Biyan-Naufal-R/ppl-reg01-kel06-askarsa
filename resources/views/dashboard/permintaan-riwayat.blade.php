@extends('layouts.app')
@section('title', 'Permintaan Jasa Masuk - Riwayat — ASKARSA')
@section('content')
<section class="container-app py-8">
    <p class="text-xs text-slate-400">Beranda / Kelola Aktivitas / Permintaan Jasa Masuk</p>
    <h1 class="mt-2 text-3xl font-black text-askarsa-ink">Permintaan Jasa Masuk</h1>
    <p class="mt-1 text-sm text-slate-500">Lihat kembali permintaan bantuan yang sudah selesai, ditolak, atau dibatalkan.</p>

    <div class="mt-6 border-b border-slate-200 text-sm font-semibold">
        <a href="{{ route('dashboard.permintaan.proses') }}" class="inline-block px-1 pb-3 text-slate-400 hover:text-askarsa-ink">Dalam Proses</a>
        <a href="{{ route('dashboard.permintaan.riwayat') }}" class="ml-6 inline-block border-b-2 border-askarsa-ink px-1 pb-3 text-askarsa-ink">Riwayat</a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($pesanan as $item)
            @php
                $inisial = collect(preg_split('/\s+/', trim($item->pelanggan->name ?? 'P')))->map(fn($k)=>\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($k,0,1)))->take(2)->implode('');
                $selesai = $item->status === \App\Models\Pesanan::STATUS_SELESAI;
                $badge = $selesai ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                $label = match($item->status) {
                    \App\Models\Pesanan::STATUS_SELESAI => 'Selesai',
                    \App\Models\Pesanan::STATUS_DITOLAK => 'Ditolak',
                    default => 'Dibatalkan',
                };
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex gap-3">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-600">{{ $inisial }}</div>
                        <div>
                            <h2 class="font-bold text-askarsa-ink">{{ $item->pelanggan->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $item->domisili_pemesan ?? $item->pelanggan->domisili ?? '-' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $item->judul }}</p>
                        </div>
                    </div>
                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                </div>
                <div class="mt-4 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-3">
                    <div><span class="text-slate-400">Tanggal Pesan</span><p class="font-medium text-askarsa-ink">{{ $item->created_at?->translatedFormat('d M Y') }}</p></div>
                    <div><span class="text-slate-400">Jadwal</span><p class="font-medium text-askarsa-ink">{{ $item->tanggal_mulai?->translatedFormat('d M Y') ?? '-' }}{{ $item->perkiraan_durasi ? ', '.$item->perkiraan_durasi : '' }}</p></div>
                    <div><span class="text-slate-400">Total</span><p class="font-medium text-askarsa-ink">Rp {{ number_format($item->harga, 0, ',', '.') }}</p></div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('pesanan.show', $item) }}" class="btn-outline !px-4 !py-2 !text-sm">Lihat Detail</a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada riwayat permintaan jasa masuk.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $pesanan->links() }}</div>
</section>
@endsection
