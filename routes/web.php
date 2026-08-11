<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\FaceEnrollController;

// Otomatis redirect berdasarkan status login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('redirect-home') : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    // Titik masuk setelah login, mengarahkan sesuai role
    Route::get('/home', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('karyawan.index'),
            'pimpinan' => redirect()->route('rekap.index'),
            'karyawan' => redirect()->route('presensi.form'),
        };
    })->name('redirect-home');

    Route::get('/presensi', [AbsensiController::class, 'form'])->name('presensi.form');
    Route::post('/presensi', [AbsensiController::class, 'store'])->name('presensi.store');

    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

    Route::middleware('role:admin')->group(function () {
        // Route untuk CRUD Karyawan
        Route::resource('karyawan', KaryawanController::class)->except('show');

        // Route untuk kelola Wajah
        Route::get('/karyawan/{karyawan}/enroll-wajah', [FaceEnrollController::class, 'form'])
            ->name('karyawan.enroll-wajah');
        Route::post('/karyawan/{karyawan}/enroll-wajah', [FaceEnrollController::class, 'store'])
            ->name('karyawan.enroll-wajah.store');
        Route::delete('/karyawan/{karyawan}/hapus-wajah', [FaceEnrollController::class, 'destroy'])
            ->name('karyawan.hapus-wajah');
    });
});