@extends('layouts.app')
@section('title', 'Permintaan Jasa Masuk - Dalam Proses — ASKARSA')
@section('content')
<section class="container-app py-8">
    <p class="text-xs text-slate-400">Beranda / Kelola Aktivitas / Permintaan Jasa Masuk</p>
    <h1 class="mt-2 text-3xl font-black text-askarsa-ink">Permintaan Jasa Masuk</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau permintaan bantuan yang masuk dan kelola status pengerjaannya.</p>

    <div class="mt-6 border-b border-slate-200 text-sm font-semibold">
        <a href="{{ route('dashboard.permintaan.proses') }}" class="inline-block border-b-2 border-askarsa-ink px-1 pb-3 text-askarsa-ink">Dalam Proses</a>
        <a href="{{ route('dashboard.permintaan.riwayat') }}" class="ml-6 inline-block px-1 pb-3 text-slate-400 hover:text-askarsa-ink">Riwayat</a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($pesanan as $item)
            @php
                $inisial = collect(preg_split('/\s+/', trim($item->pelanggan->name ?? 'P')))->map(fn($k)=>\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($k,0,1)))->take(2)->implode('');
                $diproses = $item->status === \App\Models\Pesanan::STATUS_DIPROSES;
                $badge = $diproses ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600';
                $label = $diproses ? 'Diproses' : 'Menunggu';
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
                    @if ($item->status === \App\Models\Pesanan::STATUS_MENUNGGU)
                        <form method="POST" action="{{ route('pesanan.terima', $item) }}">@csrf <button class="rounded-lg bg-askarsa px-4 py-2 text-sm font-semibold text-white hover:bg-askarsa-dark">Terima</button></form>
                        <form method="POST" action="{{ route('pesanan.tolak', $item) }}" onsubmit="return confirm('Tolak permintaan ini?')" class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="text" name="alasan" required minlength="10" maxlength="500" placeholder="Alasan penolakan (min. 10 karakter)" class="w-56 rounded-lg border border-slate-200 px-3 py-2 text-sm @error('alasan') border-red-400 @enderror" aria-label="Alasan penolakan" aria-invalid="@error('alasan') true @else false @enderror" value="{{ old('alasan') }}">
                            <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Tolak</button>
                            @error('alasan') <p class="w-full text-xs text-red-600">{{ $message }}</p> @enderror
                        </form>
                    @elseif ($item->status === \App\Models\Pesanan::STATUS_DIPROSES)
                        <form method="POST" action="{{ route('pesanan.selesaikan', $item) }}" onsubmit="return confirm('Tandai pesanan ini selesai?')">@csrf <button class="rounded-lg bg-askarsa px-4 py-2 text-sm font-semibold text-white hover:bg-askarsa-dark">Tandai Selesai</button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada permintaan jasa masuk yang sedang diproses.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $pesanan->links() }}</div>
</section>
@endsection
