<?php

use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Penyelesaian dan riwayat pesanan (F-05)
Route::middleware('auth')->prefix('pesanan')->name('pesanan.')->group(function () {
    Route::get('/riwayat', [PesananController::class, 'riwayat'])->name('riwayat');
    Route::get('/{pesanan}', [PesananController::class, 'show'])->name('show');
    Route::post('/{pesanan}/selesaikan', [PesananController::class, 'selesaikan'])->name('selesaikan');
});