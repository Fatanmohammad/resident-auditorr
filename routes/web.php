<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\AuditPlanController;
use App\Http\Controllers\ParameterAuditController;
use App\Http\Controllers\KertasKerjaAuditController;
use App\Http\Controllers\KertasHasilAuditController;
use App\Http\Controllers\TemuanAuditController;
use App\Http\Controllers\TindakLanjutController;
use App\Http\Controllers\MonitoringAuditController;
use App\Http\Controllers\ScoringAuditController;
use App\Http\Controllers\LaporanAuditController;
use App\Http\Controllers\DashboardController;

// Auth
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // MASTER DATA
    // ==========================================
    Route::prefix('cabangs')->name('cabang.')->middleware('role:kadiv_skai,kabag_ra')->group(function () {
        Route::get('/', [CabangController::class, 'index'])->name('index');
        Route::post('/', [CabangController::class, 'store'])->name('store');
        Route::get('/{id}', [CabangController::class, 'show'])->name('show');
    });

    // ==========================================
    // 1. INPUT PARAMETER (RKAT RA)
    // ==========================================
    Route::prefix('parameters')->name('parameter.')->group(function () {
        Route::get('/', [ParameterAuditController::class, 'index'])->name('index');
        Route::post('/', [ParameterAuditController::class, 'store'])->name('store')->middleware('role:kadiv_skai,kabag_ra');
        Route::put('/{id}', [ParameterAuditController::class, 'update'])->name('update')->middleware('role:kadiv_skai,kabag_ra');
        Route::delete('/{id}', [ParameterAuditController::class, 'destroy'])->name('destroy')->middleware('role:kadiv_skai,kabag_ra');
    });

    // ==========================================
    // 2. PENJADWALAN AUDIT RA — hanya PIMSIE yang buat, Kabag & Kadiv yang approve
    // ==========================================
    Route::prefix('audit-plans')->name('audit-plan.')->group(function () {
        Route::get('/', [AuditPlanController::class, 'index'])->name('index');
        Route::get('/create', [AuditPlanController::class, 'create'])->name('create')->middleware('role:pimsie');
        Route::post('/', [AuditPlanController::class, 'store'])->name('store')->middleware('role:pimsie');
        Route::get('/{id}', [AuditPlanController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AuditPlanController::class, 'approve'])->name('approve')->middleware('role:kadiv_skai,kabag_ra');
    });

    // ==========================================
    // 3. PELAKSANAAN AUDIT (KKA) — PIMSIE hanya lihat
    // ==========================================
    Route::prefix('kka')->name('kka.')->group(function () {
        Route::get('/', [KertasKerjaAuditController::class, 'indexAll'])->name('index');
        Route::get('/plan/{auditPlanId}', [KertasKerjaAuditController::class, 'index'])->name('byPlan');
        Route::get('/create', [KertasKerjaAuditController::class, 'create'])->name('create')->middleware('role:ra');
        Route::post('/', [KertasKerjaAuditController::class, 'store'])->name('store')->middleware('role:ra');
        Route::get('/{id}', [KertasKerjaAuditController::class, 'show'])->name('show');
        Route::post('/{id}/review', [KertasKerjaAuditController::class, 'review'])->name('review')->middleware('role:kabag_ra,kadiv_skai');
    });

    // ==========================================
    // 4. TEMUAN AUDIT — PIMSIE lihat temuan signifikan & berulang
    // ==========================================
    Route::prefix('temuans')->name('temuan.')->group(function () {
        Route::get('/', [TemuanAuditController::class, 'indexAll'])->name('index');
        Route::get('/kka/{kkaId}', [TemuanAuditController::class, 'index'])->name('byKka');
        Route::get('/create', [TemuanAuditController::class, 'create'])->name('create')->middleware('role:ra');
        Route::post('/', [TemuanAuditController::class, 'store'])->name('store')->middleware('role:ra');
        Route::get('/{id}', [TemuanAuditController::class, 'show'])->name('show');
    });

    // ==========================================
    // 5. MONITORING TINDAK LANJUT
    // ==========================================
    Route::prefix('tindak-lanjut')->name('tindak-lanjut.')->group(function () {
        Route::get('/', [TindakLanjutController::class, 'index'])->name('index');
        Route::post('/respon', [TindakLanjutController::class, 'storeRespon'])->name('respon')->middleware('role:auditee,ra');
        Route::post('/{id}/verifikasi', [TindakLanjutController::class, 'verifikasiRa'])->name('verifikasi')->middleware('role:ra');
    });

    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [MonitoringAuditController::class, 'index'])->name('index');
        Route::get('/plan/{auditPlanId}', [MonitoringAuditController::class, 'show'])->name('show');
        Route::post('/sync/{auditPlanId}', [MonitoringAuditController::class, 'syncMonitoring'])->name('sync');
    });

    // ==========================================
    // 6. SCORING & LAPORAN — PIMSIE bisa tarik laporan
    // ==========================================
    Route::prefix('scoring')->name('scoring.')->group(function () {
        Route::get('/', [ScoringAuditController::class, 'index'])->name('index');
        Route::post('/kalkulasi', [ScoringAuditController::class, 'hitungSkor'])->name('kalkulasi')->middleware('role:kadiv_skai,kabag_ra');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanAuditController::class, 'index'])->name('index');
        Route::get('/plan/{auditPlanId}', [LaporanAuditController::class, 'show'])->name('show');
        Route::post('/generate', [LaporanAuditController::class, 'generate'])->name('generate')->middleware('role:kabag_ra,kadiv_skai');
        Route::post('/{id}/approve', [LaporanAuditController::class, 'approve'])->name('approve')->middleware('role:kabag_ra,kadiv_skai');
    });
});
