<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DanaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\KonfirmasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\TransparansiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TransparansiController::class, 'index'])->name('transparansi.index');
Route::get('/transparansi/{laporan}', [TransparansiController::class, 'show'])->name('transparansi.show');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth.pengguna')->name('logout');

Route::middleware('auth.pengguna')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::get('/laporan/{laporan}/ubah', [LaporanController::class, 'edit'])->name('laporan.edit');
    Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])->name('laporan.update');
    Route::delete('/laporan/{laporan}', [LaporanController::class, 'destroy'])->name('laporan.destroy');

    Route::post('/laporan/pemeriksaan/{pemeriksaan}/isi', [LaporanController::class, 'isiPemeriksaan'])->name('laporan.isi-pemeriksaan');

    Route::post('/laporan/pemeriksaan/{pemeriksaan}/tinjau', [LaporanController::class, 'tinjau'])->name('laporan.tinjau');

    Route::put('/laporan/{laporan}/tutup', [LaporanController::class, 'tutup'])->name('laporan.tutup');

    Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
    Route::post('/penugasan/{laporan}/pemeriksaan', [PenugasanController::class, 'tugaskanPemeriksaan'])->name('penugasan.tugaskan-pemeriksaan');
    Route::post('/penugasan/pemeriksaan/{pemeriksaan}/pelaksanaan', [PenugasanController::class, 'tugaskanPelaksanaan'])->name('penugasan.tugaskan-pelaksanaan');
    Route::post('/penugasan/pelaksanaan/{penindaklanjutan}/mulai', [PenugasanController::class, 'mulai'])->name('penugasan.mulai');
    Route::post('/penugasan/pelaksanaan/{penindaklanjutan}/progres', [PenugasanController::class, 'simpanProgres'])->name('penugasan.progres');

    Route::get('/inventaris', [InventarisController::class, 'index'])->name('inventaris.index');
    Route::post('/inventaris', [InventarisController::class, 'store'])->name('inventaris.store');

    Route::post('/konfirmasi/{penindaklanjutan}', [KonfirmasiController::class, 'store'])->name('konfirmasi.store');

    Route::get('/pengajuan-dana', [DanaController::class, 'index'])->name('dana.index');
    Route::post('/pengajuan-dana', [DanaController::class, 'store'])->name('dana.store');
    Route::post('/pengajuan-dana/{pengajuan}/keputusan', [DanaController::class, 'keputusan'])->name('dana.keputusan');
    Route::post('/pengajuan-dana/{pengajuan}/revisi', [DanaController::class, 'revisi'])->name('dana.revisi');
    
});
