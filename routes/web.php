<?php

use App\Http\Controllers\PesananController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\LamaranController;
use App\Http\Controllers\LowonganController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Penyelesaian dan riwayat pesanan (F-05)
Route::middleware('auth')->prefix('pesanan')->name('pesanan.')->group(function () {
    Route::post('/', [PesananController::class, 'store'])->name('store');
    Route::get('/riwayat', fn () => redirect()->route('dashboard.pesanan.riwayat'))->name('riwayat');
    Route::get('/{pesanan}', [PesananController::class, 'show'])->name('show');
    Route::post('/{pesanan}/terima', [PesananController::class, 'terima'])->name('terima');
    Route::post('/{pesanan}/tolak', [PesananController::class, 'tolak'])->name('tolak');
    Route::post('/{pesanan}/batalkan', [PesananController::class, 'batalkan'])->name('batalkan');
    Route::post('/{pesanan}/selesaikan', [PesananController::class, 'selesaikan'])->name('selesaikan');
});

Route::get('/',[PageController::class,'home'])->name('home');
Route::get('/jasa',[PageController::class,'services'])->name('services.index');
Route::get('/jasa/{slug}',[PageController::class,'service'])->name('services.show');
Route::middleware('auth')->get('/jasa/{slug}/pesan',[PesananController::class,'create'])->name('pesanan.create');
Route::get('/freelance',fn()=>app(PageController::class)->jobs(request(),'freelance'))->name('jobs.freelance');
Route::get('/full-time',fn()=>app(PageController::class)->jobs(request(),'fulltime'))->name('jobs.fulltime');
Route::get('/lowongan/{slug}',[PageController::class,'job'])->name('jobs.show');
Route::middleware('auth')->group(function(){
    Route::get('/lowongan/{slug}/lamar',[LamaranController::class,'create'])->name('lamaran.create');
    Route::post('/lowongan/{slug}/lamar',[LamaranController::class,'store'])->name('lamaran.store');
    Route::get('/lamaran/{lamaran}/cv',[LamaranController::class,'downloadCv'])->name('lamaran.cv');
    Route::post('/lamaran/{lamaran}/proses',[LamaranController::class,'proses'])->name('lamaran.proses');
    Route::post('/lamaran/{lamaran}/terima',[LamaranController::class,'terima'])->name('lamaran.terima');
    Route::post('/lamaran/{lamaran}/tolak',[LamaranController::class,'tolak'])->name('lamaran.tolak');
});
Route::get('/login',fn()=>app(PageController::class)->auth('login'))->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/daftar',fn()=>app(PageController::class)->auth('register'))->name('register');
Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
Route::middleware('auth')->prefix('aktivitas')->name('dashboard.')->group(function () {
    Route::get('/profile', [AccountController::class, 'show'])->name('profile');
    Route::get('/edit-profile', [AccountController::class, 'edit'])->name('edit-profile');
    Route::put('/edit-profile', [AccountController::class, 'update'])->name('profile.update');
    Route::get('/service-form', fn () => redirect()->route('dashboard.edit-profile'))->name('service-form');

    Route::get('/pesanan-jasa', [PesananController::class, 'pesananProses'])->name('pesanan.proses');
    Route::get('/pesanan-jasa/riwayat', [PesananController::class, 'pesananRiwayat'])->name('pesanan.riwayat');
    Route::get('/permintaan-jasa-masuk', [PesananController::class, 'permintaanProses'])->name('permintaan.proses');
    Route::get('/permintaan-jasa-masuk/riwayat', [PesananController::class, 'permintaanRiwayat'])->name('permintaan.riwayat');

    Route::get('/create-job', [LowonganController::class, 'create'])->name('create-job');
    Route::post('/create-job', [LowonganController::class, 'store'])->name('create-job.store');
    Route::get('/create-job/{lowongan}/edit', [LowonganController::class, 'edit'])->name('create-job.edit');
    Route::put('/create-job/{lowongan}', [LowonganController::class, 'update'])->name('create-job.update');
    Route::get('/posts', [LowonganController::class, 'index'])->name('posts');
    Route::post('/posts/{lowongan}/toggle', [LowonganController::class, 'toggleStatus'])->name('posts.toggle');
    Route::post('/posts/{lowongan}/tutup', [LowonganController::class, 'tutup'])->name('posts.tutup');

    Route::get('/applications', [LamaranController::class, 'index'])->name('applications');
    Route::get('/candidates', [LamaranController::class, 'masuk'])->name('candidates');

    // Backward-compatible route names used by sidebar/old links.
    Route::get('/orders', fn () => redirect()->route('dashboard.pesanan.proses'))->name('orders');
    Route::get('/incoming', fn () => redirect()->route('dashboard.permintaan.proses'))->name('incoming');
});
Route::prefix('aktivitas')->name('dashboard.')->group(function(){foreach(['reports'=>'Laporan Saya','apply'=>'Lamar Pekerjaan','book'=>'Pesan Jasa'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->dashboard($label))->name($key);});
Route::prefix('admin')->name('admin.')->group(function(){foreach(['dashboard'=>'Dashboard Admin','reports'=>'Laporan & Moderasi','report-detail'=>'Detail Laporan','content'=>'Kelola Lowongan & Profil Jasa','users'=>'Kelola Akun','master'=>'Master Data'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->admin($label))->name($key);});
