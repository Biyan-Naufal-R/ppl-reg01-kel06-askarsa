@extends('layouts.app')

@section('title', $mode === 'login' ? 'Login — ASKARSA' : 'Daftar — ASKARSA')

@section('content')
<section class="grid min-h-[calc(100vh-4rem)] lg:grid-cols-2">
    <div class="flex flex-col justify-center bg-askarsa px-8 py-16 text-white lg:px-16">
        <p class="eyebrow !text-askarsa-gold">Selamat datang di ASKARSA</p>
        <h1 class="mt-6 max-w-lg text-4xl font-black">
            {{ $mode === 'login' ? 'Peluang baik dimulai dari saling terhubung.' : 'Satu langkah kecil. Banyak kesempatan baik.' }}
        </h1>
        <p class="mt-5 max-w-md text-white/70">Menjadi bagian dari ekosistem saling bantu di Purwokerto dan Kabupaten Banyumas.</p>
        <ul class="mt-8 space-y-4 text-sm text-white/80">
            <li>✦ Temukan bantuan harian</li>
            <li>✦ Lamar peluang kerja lokal</li>
            <li>✦ Tawarkan jasa dan buka lowongan</li>
        </ul>
    </div>

    <div class="flex items-center justify-center px-6 py-14">
        <form class="w-full max-w-xl space-y-5" method="post" action="{{ $mode === 'register' ? route('register.store') : route('login.store') }}">
            @csrf
            <h2 class="text-3xl font-black">{{ $mode === 'login' ? 'Senang Anda kembali.' : 'Mulai dari sini.' }}</h2>
            <p class="muted">{{ $mode === 'login' ? 'Masuk untuk melanjutkan aktivitas Anda.' : 'Daftar gratis untuk menemukan peluang di sekitar Anda.' }}</p>

            @if ($mode === 'register')
                <div>
                    <label class="field-label" for="name">Nama lengkap *</label>
                    <input id="name" name="name" class="field" required maxlength="255" autocomplete="name" placeholder="Nama lengkap" value="{{ old('name') }}">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <div>
                <label class="field-label" for="email">Email *</label>
                <input id="email" name="email" type="email" class="field" required maxlength="255" autocomplete="email" placeholder="nama@email.com" value="{{ old('email') }}">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="field-label" for="password">Password *</label>
                <input id="password" name="password" type="password" class="field" required minlength="8" autocomplete="{{ $mode === 'register' ? 'new-password' : 'current-password' }}" placeholder="Minimal 8 karakter">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if ($mode === 'register')
                <div>
                    <label class="field-label" for="password_confirmation">Konfirmasi password *</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="field" required minlength="8" autocomplete="new-password">
                </div>

                <div>
                    <label class="field-label" for="domisili">Domisili / kecamatan *</label>
                    <select id="domisili" name="domisili" class="field" required>
                        <option value="Purwokerto Utara" @selected(old('domisili') === 'Purwokerto Utara')>Purwokerto Utara</option>
                        <option value="Sokaraja" @selected(old('domisili') === 'Sokaraja')>Sokaraja</option>
                        <option value="Baturaden" @selected(old('domisili') === 'Baturaden')>Baturaden</option>
                    </select>
                    @error('domisili')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <button class="btn-primary w-full">{{ $mode === 'login' ? 'Login' : 'Daftar' }}</button>
            <p class="text-sm">
                {{ $mode === 'login' ? 'Belum punya akun?' : 'Sudah punya akun?' }}
                <a class="font-bold text-askarsa" href="{{ $mode === 'login' ? route('register') : route('login') }}">
                    {{ $mode === 'login' ? 'Daftar' : 'Login' }}
                </a>
            </p>
        </form>
    </div>
</section>
@endsection
