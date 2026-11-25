<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\PembayaranController as PetugasPembayaranController;
use App\Http\Controllers\Petugas\TagihanController as PetugasTagihanController;
use App\Http\Controllers\Petugas\VerifikasiController;
use App\Http\Controllers\ReferensiApiController;
use App\Http\Controllers\TaxCalculatorController;
use App\Http\Controllers\WajibPajak\DashboardController as WpDashboardController;
use App\Http\Controllers\WajibPajak\KendaraanController as WpKendaraanController;
use App\Http\Controllers\WajibPajak\PembayaranController as WpPembayaranController;
use App\Http\Controllers\WajibPajak\ProfileController as WpProfileController;
use App\Http\Controllers\WajibPajak\TagihanController as WpTagihanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kalkulator', [TaxCalculatorController::class, 'index'])->name('kalkulator.index');
Route::post('/kalkulator/hitung', [TaxCalculatorController::class, 'calculate'])->name('kalkulator.hitung');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/berkas/{berkas}', [BerkasController::class, 'show'])->name('berkas.show');
    Route::get('/api/referensi/njkb', [ReferensiApiController::class, 'njkb'])->name('api.referensi.njkb');
});

Route::middleware(['auth', 'role:wajib_pajak'])
    ->prefix('wajib-pajak')
    ->name('wp.')
    ->group(function (): void {
        Route::get('/dashboard', [WpDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [WpProfileController::class, 'edit'])->name('profil.edit');
        Route::put('/profil', [WpProfileController::class, 'update'])->name('profil.update');
        Route::resource('kendaraan', WpKendaraanController::class);
        Route::get('/tagihan', [WpTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/{tagihan}', [WpTagihanController::class, 'show'])->name('tagihan.show');
        Route::post('/tagihan/{tagihan}/bayar', [WpPembayaranController::class, 'store'])->name('tagihan.bayar');
        Route::post('/tagihan/{tagihan}/midtrans-token', [WpPembayaranController::class, 'createMidtransToken'])
            ->name('tagihan.midtrans.token');
        Route::post('/tagihan/{tagihan}/midtrans-sync', [WpPembayaranController::class, 'syncMidtrans'])
            ->name('tagihan.midtrans.sync');
        Route::get('/pembayaran/{pembayaran}/etbpkp', [WpPembayaranController::class, 'downloadEtbpkp'])
            ->name('pembayaran.etbpkp');
    });

Route::middleware(['auth', 'role:petugas'])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function (): void {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/verifikasi/{kendaraan}', [VerifikasiController::class, 'show'])->name('verifikasi.show');
        Route::post('/verifikasi/{kendaraan}/approve', [VerifikasiController::class, 'approve'])
            ->name('verifikasi.approve');
        Route::post('/verifikasi/{kendaraan}/reject', [VerifikasiController::class, 'reject'])
            ->name('verifikasi.reject');
        Route::get('/tagihan', [PetugasTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/pembayaran/{pembayaran}/etbpkp', [PetugasPembayaranController::class, 'downloadEtbpkp'])
            ->name('pembayaran.etbpkp');
    });
