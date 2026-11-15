@extends('pesanan.layout')

@section('judul', 'Riwayat Pesanan')

@section('isi')
    <h1>Riwayat Pesanan</h1>

    <p class="filter">
        <a href="{{ route('pesanan.riwayat') }}" class="{{ $statusAktif ? '' : 'aktif' }}">Semua</a>
        <a href="{{ route('pesanan.riwayat', ['status' => 'selesai']) }}" class="{{ $statusAktif === 'selesai' ? 'aktif' : '' }}">Selesai</a>
        <a href="{{ route('pesanan.riwayat', ['status' => 'dibatalkan']) }}" class="{{ $statusAktif === 'dibatalkan' ? 'aktif' : '' }}">Dibatalkan</a>
    </p>

    @forelse ($pesanan as $item)
        <div class="card">
            <strong><a href="{{ route('pesanan.show', $item) }}">{{ $item->judul }}</a></strong>
            <span class="badge {{ $item->status }}">{{ $item->status }}</span>
            <div>Pelanggan: {{ $item->pelanggan->name }} | Penyedia: {{ $item->penyedia->name }}</div>
            <div>Harga: Rp {{ number_format($item->harga, 0, ',', '.') }}</div>
            <small>
                {{ $item->selesai_pada
                    ? 'Selesai pada '.$item->selesai_pada->format('d-m-Y H:i')
                    : 'Diperbarui '.$item->updated_at->format('d-m-Y H:i') }}
            </small>
        </div>
    @empty
        <div class="card">Belum ada riwayat pesanan.</div>
    @endforelse

    @if ($pesanan->hasPages())
        <p>
            @if ($pesanan->previousPageUrl())
                <a href="{{ $pesanan->previousPageUrl() }}">&laquo; Sebelumnya</a>
            @endif
            Halaman {{ $pesanan->currentPage() }} dari {{ $pesanan->lastPage() }}
            @if ($pesanan->nextPageUrl())
                <a href="{{ $pesanan->nextPageUrl() }}">Berikutnya &raquo;</a>
            @endif
        </p>
    @endif
@endsection