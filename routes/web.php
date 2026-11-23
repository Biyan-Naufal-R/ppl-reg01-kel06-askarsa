<?php

use App\Http\Controllers\PesananController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Penyelesaian dan riwayat pesanan (F-05)
Route::middleware('auth')->prefix('pesanan')->name('pesanan.')->group(function () {
    Route::post('/', [PesananController::class, 'store'])->name('store');
    Route::get('/riwayat', [PesananController::class, 'riwayat'])->name('riwayat');
    Route::get('/{pesanan}', [PesananController::class, 'show'])->name('show');
    Route::post('/{pesanan}/selesaikan', [PesananController::class, 'selesaikan'])->name('selesaikan');
});

Route::get('/',[PageController::class,'home'])->name('home');
Route::get('/jasa',[PageController::class,'services'])->name('services.index');
Route::get('/jasa/{slug}',[PageController::class,'service'])->name('services.show');
Route::get('/freelance',fn()=>app(PageController::class)->jobs(request(),'freelance'))->name('jobs.freelance');
Route::get('/full-time',fn()=>app(PageController::class)->jobs(request(),'fulltime'))->name('jobs.fulltime');
Route::get('/lowongan/{slug}',[PageController::class,'job'])->name('jobs.show');
Route::get('/login',fn()=>app(PageController::class)->auth('login'))->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/daftar',fn()=>app(PageController::class)->auth('register'))->name('register');
Route::post('/daftar', [RegisteredUserController::class, 'store'])->name('register.store');
Route::middleware('auth')->prefix('aktivitas')->name('dashboard.')->group(function () {
    Route::get('/profile', [AccountController::class, 'show'])->name('profile');
    Route::get('/edit-profile', [AccountController::class, 'edit'])->name('edit-profile');
    Route::put('/edit-profile', [AccountController::class, 'update'])->name('profile.update');
});
Route::prefix('aktivitas')->name('dashboard.')->group(function(){foreach(['service-form'=>'Kelola Profil Jasa','orders'=>'Pesanan Jasa','incoming'=>'Permintaan Jasa Masuk','posts'=>'Postingan Saya','applications'=>'Lamaran Saya','candidates'=>'Pelamar Masuk','reports'=>'Laporan Saya','create-job'=>'Buat Lowongan','apply'=>'Lamar Pekerjaan','book'=>'Pesan Jasa'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->dashboard($label))->name($key);});
Route::prefix('admin')->name('admin.')->group(function(){foreach(['dashboard'=>'Dashboard Admin','reports'=>'Laporan & Moderasi','report-detail'=>'Detail Laporan','content'=>'Kelola Lowongan & Profil Jasa','users'=>'Kelola Akun','master'=>'Master Data'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->admin($label))->name($key);});
