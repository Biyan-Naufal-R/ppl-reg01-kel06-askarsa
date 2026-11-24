<article class="card"><p class="text-xs text-slate-500">{{ $job['company'] }}</p><h3 class="mt-1 font-bold">{{ $job['title'] }}</h3><div class="mt-3 flex @if(! empty($job['jenis'])) flex-wrap @endif gap-2">@if(! empty($job['jenis']))
        @include('components.job-type-mode-badges',['job'=>$job])
    @else
        <span class="pill">{{ $job['type'] }}</span><span class="pill">{{ $job['mode'] }}</span>
    @endif
    @include('components.job-jenis-badge',['job'=>$job])</div><p class="mt-4 text-sm text-slate-500">{{ $job['location'] }}</p><p class="mt-3 font-bold text-askarsa">{{ $job['salary'] }}</p>@if(! empty($job['pendidikan_label']))
<p class="mt-2 text-xs text-slate-500">Pendidikan: {{ $job['pendidikan_label'] }}@if(! empty($job['jurusan'])) • {{ $job['jurusan'] }}@endif</p>@endif
<p class="mt-3 text-xs text-slate-400">21 Oktober 2026 • Baru dipublikasikan</p><a class="mt-3 inline-block text-sm font-bold text-askarsa" href="{{ route('jobs.show',$job['slug']) }}">Lihat Detail →</a></article>
