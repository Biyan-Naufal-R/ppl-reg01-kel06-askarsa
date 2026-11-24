@php
    $tersedia = (bool) ($service['tersedia'] ?? true);
    $labelStatus = $service['status_label'] ?? ($tersedia ? 'Tersedia' : 'Tidak tersedia');
@endphp
<article class="card flex flex-col">
    <div class="flex items-center gap-3">
        <div class="grid h-11 w-11 place-items-center rounded-full bg-askarsa-mint font-bold text-askarsa">{{ $service['initials'] }}</div>
        <div>
            <h3 class="font-bold">{{ $service['name'] }}</h3>
            <p class="text-xs text-slate-500">{{ $service['district'] }}</p>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
        @include('components.category-badges', ['kategori' => $service['kategori'] ?? []])
    </div>
    <p class="mt-3 flex-1 text-sm text-slate-500">{{ $service['description'] }}</p>
    <div class="mt-4 flex items-center justify-between">
        <div>
            <p class="text-xs text-slate-400">Mulai dari</p>
            <p class="text-lg font-bold text-askarsa">{{ $service['price'] }}</p>
        </div>
        <span class="pill {{ $tersedia ? '' : 'pill-tidak-tersedia' }}">{{ $labelStatus }}</span>
    </div>
    <a href="{{ route('services.show',$service['slug']) }}" class="btn-secondary mt-4">Lihat Profil</a>
</article>
