<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PendaftaranWajahController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index']);

/* ---------- PUBLIK ---------- */
Route::get('/', [AbsenController::class, 'beranda'])->name('beranda');
Route::get('/absen', [AbsenController::class, 'kamera'])->name('absen');
Route::post('/absen/verifikasi', [AbsenController::class, 'verifikasi'])
    ->middleware('throttle:30,1')->name('absen.verifikasi');

/* ---------- LOGIN PETUGAS ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
});

/* ---------- PETUGAS (WAJIB LOGIN) ---------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.siswa.index'),
            'guru'  => redirect()->route('guru.wajah.index'),
            default => redirect()->route('rekap.index'),
        };
    })->name('dashboard');

    // Admin: kelola data siswa + akun pengguna
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('siswa', SiswaController::class)->except('show');
        Route::resource('pengguna', PenggunaController::class)->only(['index', 'edit', 'update']);
    });

    // Guru: pendaftaran wajah
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('wajah', [PendaftaranWajahController::class, 'index'])->name('wajah.index');
        Route::post('wajah', [PendaftaranWajahController::class, 'store'])->name('wajah.store');
    });

    // Guru + Sekretaris: rekap (hanya Guru yang boleh ubah Izin/Sakit)
    Route::middleware('role:guru,sekretaris')->prefix('rekap')->name('rekap.')->group(function () {
        Route::get('/', [RekapController::class, 'index'])->name('index');
        Route::post('{siswa}/status', [RekapController::class, 'ubahStatus'])
            ->middleware('role:guru')->name('status');
    });
});