<?php

use Illuminate\Support\Facades\Route;

// Import Seluruh Controller yang Sudah Dibuat
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

/*
|--------------------------------------------------------------------------
| Web & API Routes - Aplikasi Resident Auditor (RA) PT Bank Sulteng
|--------------------------------------------------------------------------
*/

// Halaman Utama / Landing Page Tes Koneksi Backend
Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'message' => 'Backend API Resident Auditor PT Bank Sulteng Berhasil Berjalan!',
        'timestamp' => now()
    ]);
});

// ==========================================
// 1. MASTER DATA & STRUKTUR CABANG
// ==========================================
Route::prefix('cabangs')->group(function () {
    Route::get('/', [CabangController::class, 'index']);           // Lihat semua cabang & anak cabang
    Route::post('/', [CabangController::class, 'store']);          // Tambah cabang
    Route::get('/{id}', [CabangController::class, 'show']);        // Detail cabang
});

Route::prefix('parameters')->group(function () {
    Route::get('/', [ParameterAuditController::class, 'index']);   // Parameter KAT/RA
    Route::post('/', [ParameterAuditController::class, 'store']);  // Tambah parameter
});

// ==========================================
// 2. SIKLUS PERENCANAAN AUDIT (AUDIT PLAN)
// ==========================================
Route::prefix('audit-plans')->group(function () {
    Route::get('/', [AuditPlanController::class, 'index']);               // List Audit Plan
    Route::post('/', [AuditPlanController::class, 'store']);              // Buat Audit Plan baru
    Route::post('/{id}/approve', [AuditPlanController::class, 'approve']); // Approval Berjenjang (RA -> Kabag -> Kadiv)
});

// ==========================================
// 3. SIKLUS PELAKSANAAN AUDIT (KKA & KHA)
// ==========================================
Route::prefix('kka')->group(function () {
    Route::get('/plan/{auditPlanId}', [KertasKerjaAuditController::class, 'index']); // KKA per Audit Plan
    Route::post('/', [KertasKerjaAuditController::class, 'store']);                  // Buat KKA baru
    Route::post('/{id}/review', [KertasKerjaAuditController::class, 'review']);      // Review KKA oleh Kabag/Kadiv
});

Route::prefix('kha')->group(function () {
    Route::get('/kka/{kkaId}', [KertasHasilAuditController::class, 'index']);      // KHA per KKA
    Route::post('/', [KertasHasilAuditController::class, 'store']);                // Simpan/Update KHA
    Route::post('/{id}/approve', [KertasHasilAuditController::class, 'approve']);   // Approval KHA
});

// ==========================================
// 4. TEMUAN AUDIT & MONITORING TINDAK LANJUT
// ==========================================
Route::prefix('temuans')->group(function () {
    Route::get('/kka/{kkaId}', [TemuanAuditController::class, 'index']); // Temuan per KKA
    Route::post('/', [TemuanAuditController::class, 'store']);           // Catat Temuan Audit
});

Route::prefix('tindak-lanjut')->group(function () {
    Route::post('/respon', [TindakLanjutController::class, 'storeRespon']);             // Auditee Upload Bukti TL
    Route::post('/{id}/verifikasi', [TindakLanjutController::class, 'verifikasiRa']);    // RA Verifikasi TL
});

Route::prefix('monitoring')->group(function () {
    Route::get('/plan/{auditPlanId}', [MonitoringAuditController::class, 'show']);           // Data Monitoring
    Route::post('/sync/{auditPlanId}', [MonitoringAuditController::class, 'syncMonitoring']); // Sinkronisasi Otomatis
});

// ==========================================
// 5. SCORING AKHIR & LAPORAN AUDIT
// ==========================================
Route::prefix('scoring')->group(function () {
    Route::post('/kalkulasi', [ScoringAuditController::class, 'hitungSkor']); // Hitung Otomatis Skor & Peringkat
});

Route::prefix('laporan')->group(function () {
    Route::get('/plan/{auditPlanId}', [LaporanAuditController::class, 'show']);      // Lihat Laporan
    Route::post('/generate', [LaporanAuditController::class, 'generate']);            // Buat Draft Laporan
    Route::post('/{id}/approve', [LaporanAuditController::class, 'approve']);         // Approval Laporan (Kadiv SKAI)
});