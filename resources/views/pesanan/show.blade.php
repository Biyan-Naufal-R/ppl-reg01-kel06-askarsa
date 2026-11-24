@extends('layouts.app')
@section('title', 'Detail Pesanan — ASKARSA')
@section('content')
@php
    use App\Models\Pesanan;

    $isPenyedia = (int) $pesanan->penyedia_id === auth()->id();
    $backRoute = $isPenyedia ? route('dashboard.permintaan.proses') : route('dashboard.pesanan.proses');
    $backLabel = $isPenyedia ? 'Permintaan Jasa Masuk' : 'Pesanan Jasa';
    $badge = match($pesanan->status) {
        Pesanan::STATUS_SELESAI => 'bg-emerald-100 text-emerald-700',
        Pesanan::STATUS_DITOLAK, Pesanan::STATUS_DIBATALKAN => 'bg-red-100 text-red-700',
        Pesanan::STATUS_DIPROSES => 'bg-amber-100 text-amber-800',
        default => 'bg-slate-100 text-slate-600',
    };
    $label = match($pesanan->status) {
        Pesanan::STATUS_SELESAI => 'Selesai',
        Pesanan::STATUS_DITOLAK => 'Ditolak',
        Pesanan::STATUS_DIBATALKAN => 'Dibatalkan',
        Pesanan::STATUS_DIPROSES => 'Diproses',
        default => 'Menunggu',
    };
    $sudahDirespon = $pesanan->status !== Pesanan::STATUS_MENUNGGU;
@endphp
<section class="container-app py-8">
    <p class="text-xs text-slate-400"><a href="{{ route('home') }}" class="hover:text-askarsa">Beranda</a> / Kelola Aktivitas / <a href="{{ $backRoute }}" class="hover:text-askarsa">{{ $backLabel }}</a> / Detail</p>

    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black text-askarsa-ink">Detail Pesanan</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $pesanan->judul }}</p>
        </div>
        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $label }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Progres Pesanan</h2>
                <ol class="mt-4 space-y-3 text-sm">
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">✓</span>
                        <div><p class="font-semibold text-askarsa-ink">Permintaan dibuat</p><p class="text-xs text-slate-500">{{ $pesanan->created_at->format('d M Y H:i') }}</p></div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full {{ $sudahDirespon ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-[10px] font-bold">{{ $sudahDirespon ? '✓' : '•' }}</span>
                        <div>
                            <p class="font-semibold text-askarsa-ink">Menunggu respons penyedia</p>
                            <p class="text-xs text-slate-500">{{ $sudahDirespon ? 'Selesai' : 'Berlangsung' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">{{ $sudahDirespon ? '✓' : '•' }}</span>
                        <div>
                            <p class="font-semibold text-askarsa-ink">
                                @if(in_array($pesanan->status, [Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SELESAI], true))
                                    Diterima penyedia
                                @elseif($pesanan->status === Pesanan::STATUS_DITOLAK)
                                    Ditolak penyedia
                                @elseif($pesanan->status === Pesanan::STATUS_DIBATALKAN)
                                    Dibatalkan oleh pemesan
                                @else
                                    Menunggu keputusan penyedia
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">{{ $pesanan->direspon_pada?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full {{ $pesanan->status === Pesanan::STATUS_SELESAI ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} text-[10px] font-bold">{{ $pesanan->status === Pesanan::STATUS_SELESAI ? '✓' : '•' }}</span>
                        <div><p class="font-semibold text-askarsa-ink">Selesai</p><p class="text-xs text-slate-500">{{ $pesanan->selesai_pada?->format('d M Y H:i') ?? '-' }}</p></div>
                    </li>
                </ol>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Informasi Pemesan</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">Nama</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->nama_pemesan ?: $pesanan->pelanggan->name }}</dd></div>
                    <div><dt class="text-slate-400">Email</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->email_pemesan ?: $pesanan->pelanggan->email }}</dd></div>
                    <div><dt class="text-slate-400">Nomor Telepon</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->nomor_telepon ?: '-' }}</dd></div>
                    <div><dt class="text-slate-400">Domisili</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->domisili_pemesan ?: '-' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Detail Bantuan</h2>
                <dl class="mt-4 grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-400">Jenis Bantuan</dt><dd class="font-medium text-askarsa-ink">{{ collect($pesanan->jenis_bantuan ?? [])->map(fn($k) => config('jasa.kategori.'.$k, $k))->implode(', ') ?: '-' }}</dd></div>
                    <div><dt class="text-slate-400">Jadwal</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->tanggal_mulai?->translatedFormat('d M Y') ?? '-' }}{{ $pesanan->perkiraan_durasi ? ', '.$pesanan->perkiraan_durasi : '' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-400">Titik Ambil / Alamat Layanan</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->titik_ambil ?: $pesanan->alamat_lengkap ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-400">Titik Antar / Tujuan</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->titik_antar ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-400">Catatan Tambahan</dt><dd class="font-medium text-askarsa-ink">{{ $pesanan->catatan_tambahan ?: '-' }}</dd></div>
                </dl>
            </div>

            @if ($pesanan->alasan_penolakan)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-red-700">Alasan Penolakan</h2>
                    <p class="mt-3 text-sm text-red-900">{{ $pesanan->alasan_penolakan }}</p>
                </div>
            @endif

            @if ($pesanan->catatan_penyelesaian)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Catatan Status</h2>
                    <p class="mt-3 text-sm text-askarsa-ink">{{ $pesanan->catatan_penyelesaian }}</p>
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Ringkasan</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Penyedia</dt><dd class="font-medium text-askarsa-ink text-right">{{ $pesanan->penyedia->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Pelanggan</dt><dd class="font-medium text-askarsa-ink text-right">{{ $pesanan->pelanggan->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Dibuat</dt><dd class="font-medium text-askarsa-ink text-right">{{ $pesanan->created_at->format('d-m-Y H:i') }}</dd></div>
                    @if($pesanan->selesai_pada)
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Selesai</dt><dd class="font-medium text-askarsa-ink text-right">{{ $pesanan->selesai_pada->format('d-m-Y H:i') }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4 border-t border-slate-100 pt-3"><dt class="font-semibold text-askarsa-ink">Total</dt><dd class="font-bold text-askarsa">Rp {{ number_format($pesanan->harga, 0, ',', '.') }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3">
                @if ($bisaTerima)
                    <form method="POST" action="{{ route('pesanan.terima', $pesanan) }}">@csrf <button class="btn-primary w-full">Terima Pesanan</button></form>
                @endif
                @if ($bisaTolak)
                    <form method="POST" action="{{ route('pesanan.tolak', $pesanan) }}" class="space-y-3">
                        @csrf
                        <label class="block text-sm font-medium text-slate-600" for="alasan">Alasan penolakan <span class="text-red-600">*</span></label>
                        <textarea id="alasan" name="alasan" rows="3" minlength="10" maxlength="500" required class="field" placeholder="Contoh: Jadwal yang diminta tidak tersedia">{{ old('alasan') }}</textarea>
                        @error('alasan') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        <button class="w-full rounded-lg border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Tolak Pesanan</button>
                    </form>
                @endif
                @if ($bisaBatalkan)
                    <form method="POST" action="{{ route('pesanan.batalkan', $pesanan) }}" onsubmit="return confirm('Batalkan pesanan ini?')">@csrf <button class="w-full rounded-lg border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Batalkan Pesanan</button></form>
                @endif
                @if ($bisaSelesaikan)
                    <form method="POST" action="{{ route('pesanan.selesaikan', $pesanan) }}" onsubmit="return confirm('Tandai pesanan ini selesai?')" class="space-y-3">
                        @csrf
                        <label class="block text-sm font-medium text-slate-600" for="catatan_penyelesaian">Catatan penyelesaian (opsional)</label>
                        <textarea id="catatan_penyelesaian" name="catatan_penyelesaian" rows="3" maxlength="500" class="field">{{ old('catatan_penyelesaian') }}</textarea>
                        <button class="btn-primary w-full">Tandai Selesai</button>
                    </form>
                @endif
                <a href="{{ $backRoute }}" class="btn-outline w-full">Kembali</a>
            </div>
        </aside>
    </div>
</section>
@endsection
