<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.welcome');
})->name('welcome');

// Guest routes (Login)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'loginProses'])->name('loginProses');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Export Laporan User (Excel & PDF) - placed before resource route
    Route::get('user/export/excel', [UserController::class, 'exportExcel'])->name('user.export.excel');
    Route::get('user/export/pdf', [UserController::class, 'exportPdf'])->name('user.export.pdf');

    // User CRUD
    Route::resource('user', UserController::class);
    Route::get('users', [UserController::class, 'index'])->name('user'); // Alias for backwards compatibility

    // Export Laporan Tugas (Excel & PDF) - placed before parameterized routes
    Route::get('tugas/export/excel', [TugasController::class, 'exportExcel'])->name('tugas.export.excel');
    Route::get('tugas/export/pdf', [TugasController::class, 'exportPdf'])->name('tugas.export.pdf');

    // Tugas (Task Delegation & Follow-Up)
    Route::get('tugas', [TugasController::class, 'index'])->name('tugas.index');
    Route::get('daftar-tugas', [TugasController::class, 'index'])->name('tugas'); // Alias
    Route::get('tugas/create', [TugasController::class, 'create'])->name('tugas.create');
    Route::post('tugas', [TugasController::class, 'store'])->name('tugas.store');
    Route::get('tugas/{tugas}', [TugasController::class, 'show'])->name('tugas.show');
    Route::get('tugas/{tugas}/edit', [TugasController::class, 'edit'])->name('tugas.edit');
    Route::put('tugas/{tugas}', [TugasController::class, 'update'])->name('tugas.update');
    Route::delete('tugas/{tugas}', [TugasController::class, 'destroy'])->name('tugas.destroy');

    // Tindak Lanjut Petugas (Upload file max 10MB atau link evidence & kirim balik)
    Route::get('tugas/{tugas}/tindak-lanjut', [TugasController::class, 'tindakLanjutForm'])->name('tugas.tindak-lanjut');
    Route::post('tugas/{tugas}/kirim-balik', [TugasController::class, 'kirimBalik'])->name('tugas.kirim-balik');

    // Delegasikan Tugas ke Petugas (oleh Kepala Sub Bagian / Admin)
    Route::post('tugas/{tugas}/delegasikan', [TugasController::class, 'delegasikanKePetugas'])->name('tugas.delegasikan');

    // Verifikasi Admin / Kepala Sub Bagian
    Route::post('tugas/{tugas}/verifikasi', [TugasController::class, 'verifikasi'])->name('tugas.verifikasi');

    // Buka Kembali / Delegasikan Ulang Tugas yang Sudah Closed
    Route::post('tugas/{tugas}/buka-kembali', [TugasController::class, 'bukaKembali'])->name('tugas.buka-kembali');

    // Pengaturan Akun (Profile Photo, Telepon/Kontak, Password)
    Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

    // Push & In-App Notifikasi Tugas
    Route::get('notifikasi/poll', [NotifikasiController::class, 'poll'])->name('notifikasi.poll');
    Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::get('notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
});
