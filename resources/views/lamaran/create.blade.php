@extends('layouts.app')
@section('title','Lamar Pekerjaan — ASKARSA')
@section('content')
<section class="container-app py-10">
    <p class="text-xs text-slate-500">Beranda / Lamar Pekerjaan</p>
    <h1 class="mt-3 text-4xl font-black text-askarsa-ink">Lamar Pekerjaan</h1>
    <p class="mt-2 text-sm text-slate-500">Tunjukkan keahlian dan pengalaman yang membuat Anda cocok untuk peluang ini.</p>

    <form method="POST" action="{{ route('lamaran.store', $job['slug']) }}" enctype="multipart/form-data" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]" novalidate>
        @csrf
        <div class="card p-7">
            <h2 class="text-lg font-bold text-askarsa-ink">Informasi lamaran</h2>

            <label class="field-label mt-5" for="nama">Nama pelamar</label>
            <input class="field bg-slate-50" id="nama" value="{{ auth()->user()->name }}" readonly>
            <p class="mt-1 text-xs text-slate-500">Terisi otomatis dari akun Anda.</p>

            <label class="field-label mt-5" for="cv">CV / portofolio *</label>
            <div id="dropzone" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center @error('cv') border-red-400 @enderror">
                <p class="font-semibold text-askarsa-ink">Pilih berkas dari perangkat</p>
                <p class="mt-2 text-xs text-slate-500">PDF • maksimal 5 MB</p>
                <button type="button" id="pickBtn" class="btn-secondary mt-4 !py-2">Pilih Berkas</button>
                <input type="file" id="cv" name="cv" accept="application/pdf,.pdf" class="hidden" required>
                <div id="fileChosen" class="mt-4 hidden rounded-lg border border-slate-200 bg-white p-3 text-left">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p id="fileName" class="text-sm font-semibold text-askarsa-ink"></p>
                            <p id="fileSize" class="text-xs text-slate-500"></p>
                        </div>
                        <button type="button" id="removeBtn" class="text-xs font-semibold text-red-600">Hapus</button>
                    </div>
                </div>
            </div>
            <p id="cvError" class="mt-2 text-xs text-red-600">{{ $errors->first('cv') }}</p>

            <label class="field-label mt-5" for="pesan">Pesan untuk pemberi kerja</label>
            <textarea class="field min-h-28 @error('pesan') !border-red-400 @enderror" id="pesan" name="pesan" rows="4" placeholder="Ceritakan singkat pengalaman Anda">{{ old('pesan') }}</textarea>
            @error('pesan')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

            <div class="mt-5 rounded-lg bg-askarsa-mint p-4 text-sm text-askarsa-ink">CV hanya dapat diakses oleh Anda dan pemberi kerja yang terkait dengan lowongan ini. Berkas tidak ditampilkan di halaman publik.</div>

            <div class="mt-5 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Kirim Lamaran</button>
                <a href="{{ route('jobs.show', $job['slug']) }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <aside class="card h-fit p-7 lg:sticky lg:top-24">
            <h2 class="text-lg font-bold text-askarsa-ink">Lowongan yang dilamar</h2>
            <span class="pill mt-4">{{ $job['type'] }}</span>
            <p class="muted mt-5">Judul lowongan</p><p class="font-semibold">{{ $job['title'] }}</p>
            <p class="muted mt-4">Pemberi kerja</p><p class="font-semibold">{{ $job['company'] }}</p>
            <p class="muted mt-4">Bayaran</p><p class="font-semibold">{{ $job['salary'] }}</p>
            <p class="muted mt-4">Metode & durasi</p><p class="font-semibold">{{ $job['mode'] }} • {{ $job['durasi'] ?? '-' }}</p>
        </aside>
    </form>
</section>
<script>
const MAX_SIZE = 5 * 1024 * 1024;
const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('cv');
const pickBtn = document.getElementById('pickBtn');
const chosen = document.getElementById('fileChosen');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
const removeBtn = document.getElementById('removeBtn');
const cvError = document.getElementById('cvError');
function showError(msg){ cvError.textContent = msg; dropzone.classList.add('border-red-400'); }
function clearError(){ if (!@json((bool)$errors->has('cv'))) cvError.textContent = ''; dropzone.classList.remove('border-red-400'); }
function formatSize(bytes){ return bytes < 1024*1024 ? Math.round(bytes/1024)+' KB' : (bytes/(1024*1024)).toFixed(1)+' MB'; }
function setFile(file){
    clearError();
    if (!file) return;
    const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
    if (!isPdf) { fileInput.value=''; return showError('Berkas harus berformat PDF.'); }
    if (file.size > MAX_SIZE) { fileInput.value=''; return showError('Ukuran berkas melebihi 5 MB.'); }
    fileName.textContent = file.name; fileSize.textContent = formatSize(file.size); chosen.classList.remove('hidden');
}
pickBtn?.addEventListener('click', () => fileInput.click());
fileInput?.addEventListener('change', () => setFile(fileInput.files[0]));
removeBtn?.addEventListener('click', () => { fileInput.value=''; chosen.classList.add('hidden'); clearError(); });
['dragenter','dragover'].forEach(ev => dropzone?.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('bg-askarsa-mint'); }));
['dragleave','drop'].forEach(ev => dropzone?.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('bg-askarsa-mint'); }));
dropzone?.addEventListener('drop', e => { const file = e.dataTransfer.files[0]; if (!file) return; const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; setFile(file); });
</script>
@endsection
