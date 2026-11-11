<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
Route::get('/',[PageController::class,'home'])->name('home');
Route::get('/jasa',[PageController::class,'services'])->name('services.index');
Route::get('/jasa/{slug}',[PageController::class,'service'])->name('services.show');
Route::get('/freelance',fn()=>app(PageController::class)->jobs(request(),'freelance'))->name('jobs.freelance');
Route::get('/full-time',fn()=>app(PageController::class)->jobs(request(),'fulltime'))->name('jobs.fulltime');
Route::get('/lowongan/{slug}',[PageController::class,'job'])->name('jobs.show');
Route::get('/login',fn()=>app(PageController::class)->auth('login'))->name('login');
Route::get('/daftar',fn()=>app(PageController::class)->auth('register'))->name('register');
Route::prefix('aktivitas')->name('dashboard.')->group(function(){foreach(['profile'=>'Profil Saya','edit-profile'=>'Edit Profil','service-form'=>'Kelola Profil Jasa','orders'=>'Pesanan Jasa','incoming'=>'Permintaan Jasa Masuk','posts'=>'Postingan Saya','applications'=>'Lamaran Saya','candidates'=>'Pelamar Masuk','reports'=>'Laporan Saya','create-job'=>'Buat Lowongan','apply'=>'Lamar Pekerjaan','book'=>'Pesan Jasa'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->dashboard($label))->name($key);});
Route::prefix('admin')->name('admin.')->group(function(){foreach(['dashboard'=>'Dashboard Admin','reports'=>'Laporan & Moderasi','report-detail'=>'Detail Laporan','content'=>'Kelola Lowongan & Profil Jasa','users'=>'Kelola Akun','master'=>'Master Data'] as $key=>$label) Route::get('/'.$key,fn()=>app(PageController::class)->admin($label))->name($key);});
