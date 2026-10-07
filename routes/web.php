<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditPlanController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RawMetricController;
use App\Http\Controllers\CriticalOverrideController;
use App\Http\Controllers\CoverageController;
use App\Http\Controllers\SchedulingController;
use App\Http\Controllers\FinalAuditPlanController;
use App\Http\Controllers\MasterSetupController;
use App\Http\Controllers\Offsite\OffsiteController;
use App\Http\Controllers\Offsite\KkaController;
use App\Http\Controllers\Offsite\DailyRegisterController;
use App\Http\Controllers\Offsite\AuditLogController;
use App\Http\Controllers\Onsite\OnsiteController;
use App\Http\Controllers\Onsite\PermintaanDataController;
use App\Http\Controllers\Onsite\ObservasiController;

Route::get('/debug-test', function () {
    $unit = \App\Models\Unit::find(2);

    $wp = \App\Models\WpOffsite::where('unit_id', $unit->id)
        ->whereYear('periode_mulai', request('tahun', date('Y')))
        ->whereMonth('periode_mulai', request('bulan', date('m')))
        ->first();

    return response()->json([
        'wp_found' => (bool) $wp,
        'kode_wp' => $wp?->kode_wp,
    ]);
});

// Auth
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // AUDIT PLAN (approval workflow)
    // ==========================================
    Route::prefix('audit-plans')->name('audit-plan.')->group(function () {
        Route::get('/', [AuditPlanController::class, 'index'])->name('index');
        Route::get('/create', [AuditPlanController::class, 'create'])->name('create')->middleware('role:pimsie');
        Route::post('/', [AuditPlanController::class, 'store'])->name('store')->middleware('role:pimsie');
        Route::get('/{id}', [AuditPlanController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [AuditPlanController::class, 'approve'])->name('approve')->middleware('role:kadiv_skai,kabag_ra,admin');
    });

    // ==========================================
    // SOP 01 — MODUL AUDIT PLAN BARU
    // ==========================================

    // Master Unit (RA TIDAK diizinkan — RA hanya menginput raw metrics via raw-metrics.index) 
    Route::prefix('units')->name('units.')->middleware('role:ra,kadiv_skai,kabag_ra,pimsie,admin')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/create', [UnitController::class, 'create'])->name('create')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::post('/', [UnitController::class, 'store'])->name('store')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::get('/{unit}', [UnitController::class, 'show'])->name('show');
        Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::put('/{unit}', [UnitController::class, 'update'])->name('update')->middleware('role:kadiv_skai,kabag_ra,admin');
    });

    // Risk Scoring index (RA TIDAK diizinkan)
    Route::get('/risk-scoring', [UnitController::class, 'riskScoringIndex'])->name('risk-scoring.index')->middleware('role:kadiv_skai,kabag_ra,pimsie,admin');

    // Assignment RA index (RA TIDAK diizinkan)
    Route::get('/assignment-ra', [CoverageController::class, 'assignmentIndex'])->name('assignment-ra.index')->middleware('role:kadiv_skai,kabag_ra,pimsie,admin');

    // Raw Metrics (RA memegang akses utama di sini — HANYA input raw metrics)
    Route::prefix('raw-metrics')->name('raw-metrics.')->middleware('role:kabag_ra,ra,admin')->group(function () {
        Route::get('/', [RawMetricController::class, 'index'])->name('index');
        Route::get('/{unit}/form', [RawMetricController::class, 'create'])->name('create');
        Route::post('/{unit}', [RawMetricController::class, 'store'])->name('store');
    });

    // Critical Override (hanya dipakai dari detail unit — menu terpisah sudah dihapus)
    Route::prefix('critical-override')->name('critical-override.')->middleware('role:kabag_ra,kadiv_skai,admin')->group(function () {
        Route::post('/{unit}', [CriticalOverrideController::class, 'store'])->name('store');
        Route::patch('/{override}/status', [CriticalOverrideController::class, 'updateStatus'])->name('status');
    });

    // Coverage
    Route::prefix('coverage')->name('coverage.')->middleware('role:kabag_ra,kadiv_skai,admin')->group(function () {
        Route::get('/', [CoverageController::class, 'index'])->name('index');
        Route::post('/generate-all', [CoverageController::class, 'generateAll'])->name('generate-all');
        Route::post('/assign-all', [CoverageController::class, 'assignAll'])->name('assign-all');
        Route::get('/{unit}', [CoverageController::class, 'show'])->name('show');
        Route::post('/{unit}', [CoverageController::class, 'store'])->name('store');
    });

    // Scheduling
    Route::prefix('scheduling')->name('scheduling.')->middleware('role:kabag_ra,kadiv_skai,pimsie,admin')->group(function () {
        Route::get('/', [SchedulingController::class, 'index'])->name('index');
        Route::post('/generate-all', [SchedulingController::class, 'generateAll'])->name('generate-all')->middleware('role:kabag_ra,kadiv_skai,admin');
        Route::post('/{unit}/override-frequency', [SchedulingController::class, 'overrideFrequency'])->name('override-frequency')->middleware('role:kabag_ra,kadiv_skai,admin');
        Route::patch('/visit/{visit}/override', [SchedulingController::class, 'overrideVisit'])->name('override-visit')->middleware('role:kabag_ra,kadiv_skai,admin');
        Route::patch('/visit/{visit}/status', [SchedulingController::class, 'updateVisitStatus'])->name('visit-status');
        Route::get('/capacity', [SchedulingController::class, 'capacity'])->name('capacity');
        Route::get('/{unit}', [SchedulingController::class, 'unitSchedule'])->name('unit');
    });

    // Final Audit Plan
    Route::prefix('final-audit-plan')->name('final-audit-plan.')->group(function () {
        Route::get('/', [FinalAuditPlanController::class, 'index'])->name('index');
        Route::get('/change-log', [FinalAuditPlanController::class, 'changeLog'])->name('change-log');
        Route::post('/change-log', [FinalAuditPlanController::class, 'storeChangeLog'])->name('change-log.store');
        Route::post('/generate-all', [FinalAuditPlanController::class, 'generateAll'])->name('generate-all')->middleware('role:kabag_ra,kadiv_skai,admin');
        Route::get('/{finalAuditPlan}', [FinalAuditPlanController::class, 'show'])->name('show');
    });

    // ==========================================
    // MASTER SETUP / PENGATURAN MODUL (ADMIN ONLY)
    // ==========================================
    Route::prefix('master-setup')->name('master-setup.')->middleware('role:admin')->group(function () {
        Route::get('/', [MasterSetupController::class, 'index'])->name('index');
        Route::post('/field-weights', [MasterSetupController::class, 'storeFieldWeights'])->name('field-weights');
        Route::post('/bidang-weights', [MasterSetupController::class, 'storeBidangWeights'])->name('bidang-weights');
    });

    // =========================================================================
    // MODUL ONSITE AUDIT
    // =========================================================================
    Route::prefix('onsite')->name('onsite.')->middleware('role:ra,kabag_ra,kadiv_skai,admin')->group(function () {
        Route::get('/', [OnsiteController::class, 'index'])->name('index');
        Route::get('/create', [OnsiteController::class, 'create'])->name('create');
        Route::post('/', [OnsiteController::class, 'store'])->name('store');
        Route::get('/{visit}/upload', [OnsiteController::class, 'uploadForm'])->name('upload');
        Route::post('/{visit}/upload', [OnsiteController::class, 'uploadProcess'])->name('upload.process');
        Route::get('/{visit}/kka', [OnsiteController::class, 'kka'])->name('kka');
        Route::get('/{visit}/kka/data', [OnsiteController::class, 'kkaData'])->name('kka.data');
        Route::patch('/kka/{finding}', [OnsiteController::class, 'updateKka'])->name('kka.update');
        Route::patch('/kka/{finding}/review', [OnsiteController::class, 'reviewKka'])->name('kka.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        // KKA Observasi Lingkungan
        Route::get('/{visit}/observasi', [ObservasiController::class, 'create'])->name('observasi.create');
        Route::post('/{visit}/observasi', [ObservasiController::class, 'store'])->name('observasi.store');
        Route::get('/{visit}/observasi/{observasi}', [ObservasiController::class, 'show'])->name('observasi.show');
        Route::patch('/observasi/item/{item}', [ObservasiController::class, 'updateItem'])->name('observasi.item.update');
        Route::patch('/observasi/item/{item}/review', [ObservasiController::class, 'reviewItem'])->name('observasi.item.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        Route::get('/{visit}/permintaan', [PermintaanDataController::class, 'create'])->name('permintaan.create');
        Route::post('/{visit}/permintaan', [PermintaanDataController::class, 'store'])->name('permintaan.store');
        Route::get('/{visit}/permintaan/{permintaan}/print', [PermintaanDataController::class, 'print'])->name('permintaan.print');
    });

    // =========================================================================
    // MODUL OFFSITE AUDIT (BLADE UI)
    // =========================================================================
    Route::prefix('offsite')->group(function () {

        // Rekapitulasi Cabang — Admin/Kabag/Kadiv
        Route::get('/', [OffsiteController::class, 'index'])
            ->name('offsite.index')
            ->middleware('role:admin,kabag_ra,kadiv_skai');

        // Riwayat Upload
        Route::get('/history', [AuditLogController::class, 'index'])
            ->name('offsite.history.index')
            ->middleware('role:admin,kabag_ra,kadiv_skai,ra');

        // Upload DUMP — RA & Admin
        Route::get('/upload', [OffsiteController::class, 'create'])
            ->name('offsite.upload.create')
            ->middleware('role:ra,admin,kabag_ra,kadiv_skai');
        Route::post('/upload', [OffsiteController::class, 'upload'])
            ->name('offsite.upload.store')
            ->middleware('role:ra,admin,kabag_ra,kadiv_skai');

        // KKA Offsite — semua role bisa lihat
        Route::get('/kka', [KkaController::class, 'index'])->name('offsite.kka.index');
        Route::get('/kka/data', [KkaController::class, 'data'])->name('offsite.kka.data');

        // Update KKA oleh RA
        Route::post('/kka/{id}/ra', [KkaController::class, 'updateRa'])
            ->name('offsite.kka.update.ra')
            ->middleware('role:ra,admin');

        // Update KKA oleh Admin/Kabag (review)
        Route::put('/kka/{id}/admin', [KkaController::class, 'updateAdmin'])
            ->name('offsite.kka.update.admin')
            ->middleware('role:admin,kabag_ra,kadiv_skai');

        // Konfirmasi temuan
        Route::post('/kka/{id}/konfirmasi', [KkaController::class, 'konfirmasiCabang'])
            ->name('offsite.kka.konfirmasi')
            ->middleware('role:ra,admin,kabag_ra');

        // Register Harian
        Route::get('/register', [DailyRegisterController::class, 'index'])->name('offsite.register.index');
        Route::get('/register/data', [DailyRegisterController::class, 'data'])->name('offsite.register.data');
    });

});