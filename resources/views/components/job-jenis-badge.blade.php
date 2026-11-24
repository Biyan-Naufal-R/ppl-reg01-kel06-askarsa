@php
    $info = ! empty($job['jenis']) ? config('lowongan.jenis.'.$job['jenis']) : null;
@endphp
@if ($info)
    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold {{ $info['warna'] }}">{{ $info['label'] }}</span>
@endif
