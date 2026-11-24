@extends('layouts.app')
@section('title','Lamaran Saya — ASKARSA')
@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Kelola Aktivitas / Lamaran Saya</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">Lamaran Saya</h1>
    <p class="mt-2 text-sm text-slate-500">Ikuti perkembangan lamaran pekerjaan yang telah Anda kirim.</p>
    <div class="mt-5 rounded-lg bg-askarsa-mint p-4 text-sm text-askarsa-ink">CV di halaman ini adalah berkas milik Anda. Hanya Anda dan pemberi kerja terkait yang dapat mengakses berkas lamaran.</div>

    <div class="card mt-6 overflow-x-auto">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-56 flex-1">
                <label class="field-label" for="q">Cari</label>
                <input class="field" id="q" name="q" value="{{ $q }}" placeholder="Cari judul atau pemberi kerja">
            </div>
            <div class="min-w-48">
                <label class="field-label" for="status">Status</label>
                <select class="field" id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach(\App\Models\Lamaran::labelStatus() as $nilai => $label)
                        <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-primary">Terapkan</button>
        </form>

        <table class="mt-5 w-full min-w-[920px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-3">Lowongan & pemberi kerja</th><th class="p-3">Tanggal kirim</th><th class="p-3">Status lamaran</th><th class="p-3">CV saya</th><th class="p-3">Tindakan</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lamarans as $lamaran)
                    @php $warna = \App\Models\Lamaran::warnaStatus()[$lamaran->status] ?? 'bg-slate-100 text-slate-600'; @endphp
                    <tr>
                        <td class="p-3"><p class="font-semibold">{{ $lamaran->lowongan?->judul }}</p><p class="text-xs text-slate-500">{{ $lamaran->lowongan?->perusahaan }}</p></td>
                        <td class="p-3">{{ $lamaran->created_at?->format('d M Y') }}</td>
                        <td class="p-3"><span class="inline-flex rounded-md border px-2.5 py-1 text-[11px] font-semibold {{ $warna }}">{{ \App\Models\Lamaran::labelStatus()[$lamaran->status] ?? $lamaran->status }}</span></td>
                        <td class="p-3"><p>{{ $lamaran->cv_nama }}</p><a class="text-xs font-semibold text-askarsa" href="{{ route('lamaran.cv', $lamaran) }}">Lihat CV Saya ↗</a></td>
                        <td class="p-3"><a class="font-semibold text-askarsa" href="{{ route('jobs.show', $lamaran->lowongan?->slug ?? '') }}">Lihat Lowongan →</a></td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-slate-500" colspan="5">Belum ada lamaran yang dikirim.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-5 text-xs text-slate-500">Status ditentukan oleh pemberi kerja. Berkas CV tidak dapat diakses pengguna lain.</p>
</section>
@endsection
