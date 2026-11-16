@extends('pesanan.layout')

@section('judul', 'Detail Pesanan')

@section('isi')
    <p><a href="{{ route('pesanan.riwayat') }}">&laquo; Kembali ke riwayat</a></p>

    <div class="card">
        <h1>{{ $pesanan->judul }}</h1>
        <span class="badge {{ $pesanan->status }}">{{ $pesanan->status }}</span>

        <p>{{ $pesanan->deskripsi }}</p>
        <div>Pelanggan: {{ $pesanan->pelanggan->name }}</div>
        <div>Penyedia: {{ $pesanan->penyedia->name }}</div>
        <div>Harga: Rp {{ number_format($pesanan->harga, 0, ',', '.') }}</div>
        <div>Dibuat: {{ $pesanan->created_at->format('d-m-Y H:i') }}</div>

        @if ($pesanan->selesai_pada)
            <div>Selesai: {{ $pesanan->selesai_pada->format('d-m-Y H:i') }}</div>
        @endif
        @if ($pesanan->catatan_penyelesaian)
            <div>Catatan penyelesaian: {{ $pesanan->catatan_penyelesaian }}</div>
        @endif
    </div>

    @if ($bisaSelesaikan)
        <form method="POST" action="{{ route('pesanan.selesaikan', $pesanan) }}" class="card">
            @csrf
            <label for="catatan_penyelesaian">Catatan penyelesaian (opsional)</label>
            <textarea id="catatan_penyelesaian" name="catatan_penyelesaian" rows="3" maxlength="500">{{ old('catatan_penyelesaian') }}</textarea>
            <p><button type="submit">Tandai Selesai</button></p>
        </form>
    @endif
@endsection