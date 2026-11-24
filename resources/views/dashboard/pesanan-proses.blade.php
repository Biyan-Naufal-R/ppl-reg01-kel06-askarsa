@extends('layouts.app')
@section('title', 'Pesanan Jasa - Dalam Proses — ASKARSA')
@section('content')
<section class="container-app py-8">
    <p class="text-xs text-slate-400">Beranda / Kelola Aktivitas / Pesanan Jasa</p>
    <h1 class="mt-2 text-3xl font-black text-askarsa-ink">Pesanan Jasa</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau pesanan bantuan yang sedang Anda tunggu atau sedang dikerjakan.</p>

    <div class="mt-6 border-b border-slate-200 text-sm font-semibold">
        <a href="{{ route('dashboard.pesanan.proses') }}" class="inline-block border-b-2 border-askarsa-ink px-1 pb-3 text-askarsa-ink">Dalam Proses</a>
        <a href="{{ route('dashboard.pesanan.riwayat') }}" class="ml-6 inline-block px-1 pb-3 text-slate-400 hover:text-askarsa-ink">Riwayat</a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($pesanan as $item)
            @php
                $profil = $item->penyedia->profilJasa;
                $inisial = collect(preg_split('/\s+/', trim($item->penyedia->name ?? 'P')))->map(fn($k)=>\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($k,0,1)))->take(2)->implode('');
                $badge = $item->status === \App\Models\Pesanan::STATUS_DIPROSES
                    ? 'bg-amber-100 text-amber-800'
                    : 'bg-slate-100 text-slate-600';
                $label = $item->status === \App\Models\Pesanan::STATUS_DIPROSES ? 'Diproses' : 'Menunggu';
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex gap-3">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-600">{{ $inisial }}</div>
                        <div>
                            <h2 class="font-bold text-askarsa-ink">{{ $profil->nama_profil ?? $item->penyedia->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $item->penyedia->domisili ?? '-' }}</p>
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
                    @if ($item->status === \App\Models\Pesanan::STATUS_MENUNGGU)
                        <form method="POST" action="{{ route('pesanan.batalkan', $item) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
                            @csrf
                            <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Batalkan</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada pesanan jasa yang sedang diproses.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $pesanan->links() }}</div>
</section>
@endsection
