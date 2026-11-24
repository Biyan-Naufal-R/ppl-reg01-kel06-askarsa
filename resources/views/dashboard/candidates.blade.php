@extends('layouts.app')
@section('title','Pelamar Masuk — ASKARSA')
@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Kelola Aktivitas / Pelamar Masuk</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">Pelamar Masuk</h1>
    <p class="mt-2 text-sm text-slate-500">Tinjau lamaran yang masuk ke lowongan Anda.</p>

    <div class="card mt-6 overflow-x-auto">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-56 flex-1">
                <label class="field-label" for="q">Cari</label>
                <input class="field" id="q" name="q" value="{{ $q }}" placeholder="Cari pelamar atau judul lowongan">
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

        <table class="mt-5 w-full min-w-[980px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-3">Pelamar</th><th class="p-3">Lowongan</th><th class="p-3">Tanggal kirim</th><th class="p-3">Status</th><th class="p-3">CV</th><th class="p-3">Tindakan</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($lamarans as $lamaran)
                    @php $warna = \App\Models\Lamaran::warnaStatus()[$lamaran->status] ?? 'bg-slate-100 text-slate-600'; @endphp
                    <tr>
                        <td class="p-3"><p class="font-semibold">{{ $lamaran->nama_pelamar }}</p><p class="text-xs text-slate-500">{{ $lamaran->email_pelamar }}</p></td>
                        <td class="p-3">{{ $lamaran->lowongan?->judul }}</td>
                        <td class="p-3">{{ $lamaran->created_at?->format('d M Y') }}</td>
                        <td class="p-3"><span class="inline-flex rounded-md border px-2.5 py-1 text-[11px] font-semibold {{ $warna }}">{{ \App\Models\Lamaran::labelStatus()[$lamaran->status] ?? $lamaran->status }}</span></td>
                        <td class="p-3"><a class="text-xs font-semibold text-askarsa" href="{{ route('lamaran.cv', $lamaran) }}">Lihat CV ↗</a></td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                @if($lamaran->status !== 'diproses')
                                    <form method="POST" action="{{ route('lamaran.proses', $lamaran) }}">@csrf<button class="text-sky-700 hover:underline">Proses</button></form>
                                @endif
                                @if($lamaran->status !== 'diterima')
                                    <form method="POST" action="{{ route('lamaran.terima', $lamaran) }}">@csrf<button class="text-emerald-700 hover:underline">Terima</button></form>
                                @endif
                                @if($lamaran->status !== 'ditolak')
                                    <form method="POST" action="{{ route('lamaran.tolak', $lamaran) }}">@csrf<button class="text-rose-700 hover:underline">Tolak</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-6 text-center text-slate-500" colspan="6">Belum ada pelamar masuk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
