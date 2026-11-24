@extends('layouts.app')

@section('title', $service['name'].' — ASKARSA')

@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / Jasa Minta Tolong / {{ $service['name'] }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_300px]">
        <article class="card">
            <span class="pill">{{ $service['tersedia'] ?? true ? 'Tersedia' : 'Tidak tersedia' }}</span>
            <h1 class="mt-3 text-3xl font-black">{{ $service['name'] }}</h1>
            <p class="muted">{{ $service['district'] }}, Kabupaten Banyumas</p>
            <hr class="my-6">
            <p class="eyebrow">{{ $service['category'] }}</p>
            <h2 class="mt-2 text-2xl font-bold">Bantuan harian tanpa repot.</h2>
            <p class="mt-4 text-slate-600">{{ $service['description'] ?: 'Penyedia belum menambahkan deskripsi.' }}</p>
            <h3 class="mt-6 font-bold">Layanan yang dapat dibantu</h3>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-600">
                <li>Bantuan kebutuhan rumah dan urusan harian</li>
                <li>Antar barang kecil atau dokumen</li>
                <li>Bantuan praktis sesuai kesepakatan</li>
            </ul>
            <div class="mt-8 rounded-lg bg-askarsa-mint p-4 text-sm">Wilayah layanan: Purwokerto dan wilayah sekitar • Jadwal disepakati langsung.</div>
        </article>

        <aside class="card h-fit">
            <p class="muted">Tarif awal layanan</p>
            <p class="mt-2 text-3xl font-black text-askarsa">{{ $service['price'] }}</p>
            <p class="muted mt-2">Per bantuan • menyesuaikan kebutuhan dan jarak layanan.</p>

            @guest
                <a href="{{ route('login') }}" class="btn-primary mt-6 w-full">Pesan Jasa</a>
            @else
                @if ($penyedia && (int) $penyedia->id === (int) auth()->id())
                    <p class="mt-6 rounded-lg bg-askarsa-mint p-3 text-sm text-askarsa-ink">Ini jasa milik Anda sendiri.</p>
                    <button class="btn-primary mt-3 w-full opacity-50" type="button" disabled>Pesan Jasa</button>
                @elseif (auth()->user()->punyaJasaAktif())
                    <p class="mt-6 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Untuk memesan jasa orang lain, nonaktifkan terlebih dahulu profil jasa Anda melalui halaman Edit Profil / Akun.</p>
                    <button class="btn-primary mt-3 w-full opacity-50" type="button" disabled>Pesan Jasa</button>
                @elseif (! $penyedia?->profilJasa?->aktif)
                    <p class="mt-6 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">Penyedia jasa ini belum aktif.</p>
                    <button class="btn-primary mt-3 w-full opacity-50" type="button" disabled>Pesan Jasa</button>
                @elseif (! $penyedia?->profilJasa?->tersedia)
                    <p class="mt-6 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">Penyedia sedang tidak tersedia. Silakan cek kembali nanti.</p>
                    <button class="btn-primary mt-3 w-full opacity-50" type="button" disabled>Pesan Jasa</button>
                @else
                    <p class="mt-6 text-sm text-slate-600">Isi form pemesanan untuk menjelaskan kebutuhan Anda secara lengkap.</p>
                    @error('jasa')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    <a href="{{ route('pesanan.create', $service['slug']) }}" class="btn-primary mt-4 w-full">Pesan Jasa</a>
                @endif

                <a href="{{ route('dashboard.reports') }}" class="btn-secondary mt-3 w-full">Laporkan Profil</a>
            @endguest

            <p class="mt-4 text-xs text-slate-500">@guest Login diperlukan untuk memesan atau melaporkan profil.@endguest</p>
        </aside>
    </div>
</section>
@endsection
