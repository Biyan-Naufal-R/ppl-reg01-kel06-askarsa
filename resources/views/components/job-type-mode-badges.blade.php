@php
    $warnaKategori = config('lowongan.kategori_warna.'.$job['type'], 'border-slate-200 bg-slate-50 text-slate-600');
    $metode = $job['metode'] ?? '';
    $warnaMetode = config('lowongan.metode_warna.'.$metode, 'border-slate-200 bg-slate-50 text-slate-600');
    $labelMetode = config('lowongan.metode.'.$metode, $job['mode']);
@endphp
<span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold {{ $warnaKategori }}">{{ $job['type'] }}</span>
<span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold {{ $warnaMetode }}">{{ $labelMetode }}</span>
