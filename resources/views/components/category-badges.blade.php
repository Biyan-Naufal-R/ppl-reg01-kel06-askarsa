@php
    $warnaKategori = [
        'antar-jemput' => 'border-sky-200 bg-sky-50 text-sky-700',
        'temani' => 'border-violet-200 bg-violet-50 text-violet-700',
        'titip-beli' => 'border-rose-200 bg-rose-50 text-rose-700',
        'bantuan-rumah' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
        'antar-barang' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',
        'perbaikan' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
    ];
@endphp
@foreach ($kategori ?? [] as $nilai)
    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold {{ $warnaKategori[$nilai] ?? 'border-slate-200 bg-slate-50 text-slate-600' }}">
        {{ config('jasa.kategori.'.$nilai, $nilai) }}
    </span>
@endforeach
