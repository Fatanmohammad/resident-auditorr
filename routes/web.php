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
use App\Http\Controllers\Offsite\KkaController as OffsiteKkaController;
use App\Http\Controllers\Offsite\DailyRegisterController;
use App\Http\Controllers\Offsite\AuditLogController;
use App\Http\Controllers\Onsite\OnsiteController;
use App\Http\Controllers\Onsite\PermintaanDataController;
use App\Http\Controllers\Onsite\ObservasiController;
use App\Http\Controllers\Onsite\RekonController;
use App\Http\Controllers\Onsite\RekonKartuAtmController;
use App\Http\Controllers\Onsite\CashOpnameController;
use App\Http\Controllers\Onsite\TellerKasController;
use App\Http\Controllers\Onsite\DpkController;
use App\Http\Controllers\Onsite\KreditController;
use App\Http\Controllers\Onsite\BiayaController;
use App\Http\Controllers\Onsite\AtmController;
use App\Http\Controllers\Onsite\PengaduanController;
use App\Http\Controllers\Onsite\KkaIntegrationController;
use App\Http\Controllers\SurpriseAudit\SurpriseAuditController;
use App\Http\Controllers\Monitoring\MonitoringController;
use App\Http\Controllers\FraudIn\PemeriksaanAwalController;

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

    // Master Unit
    Route::prefix('units')->name('units.')->middleware('role:ra,kadiv_skai,kabag_ra,pimsie,admin')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/create', [UnitController::class, 'create'])->name('create')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::post('/', [UnitController::class, 'store'])->name('store')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::get('/{unit}', [UnitController::class, 'show'])->name('show');
        Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit')->middleware('role:kadiv_skai,kabag_ra,admin');
        Route::put('/{unit}', [UnitController::class, 'update'])->name('update')->middleware('role:kadiv_skai,kabag_ra,admin');
    });

    // Risk Scoring index
    Route::get('/risk-scoring', [UnitController::class, 'riskScoringIndex'])->name('risk-scoring.index')->middleware('role:kadiv_skai,kabag_ra,pimsie,admin');

    // Assignment RA index
    Route::get('/assignment-ra', [CoverageController::class, 'assignmentIndex'])->name('assignment-ra.index')->middleware('role:kadiv_skai,kabag_ra,pimsie,admin');

    // Raw Metrics
    Route::prefix('raw-metrics')->name('raw-metrics.')->middleware('role:kabag_ra,ra,admin')->group(function () {
        Route::get('/', [RawMetricController::class, 'index'])->name('index');
        Route::get('/{unit}/form', [RawMetricController::class, 'create'])->name('create');
        Route::post('/{unit}', [RawMetricController::class, 'store'])->name('store');
    });

    // Critical Override
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

    // Master Setup
    Route::prefix('master-setup')->name('master-setup.')->middleware('role:admin')->group(function () {
        Route::get('/', [MasterSetupController::class, 'index'])->name('index');
        Route::post('/field-weights', [MasterSetupController::class, 'storeFieldWeights'])->name('field-weights');
        Route::post('/bidang-weights', [MasterSetupController::class, 'storeBidangWeights'])->name('bidang-weights');
    });

    // =========================================================================
    // MODUL MONITORING TINDAK LANJUT (SOP 07)
    // =========================================================================
    Route::prefix('monitoring')->name('monitoring.')->middleware('role:ra,kabag_ra,kadiv_skai,admin')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');
        
        // Register TL
        Route::get('/register-tl', [MonitoringController::class, 'registerTl'])->name('register_tl');
        Route::post('/register-tl/store', [MonitoringController::class, 'storeRegisterTl'])->name('register_tl.store');
        
        // Rencana & Reminder
        Route::get('/rencana-reminder', [MonitoringController::class, 'rencanaReminder'])->name('rencana_reminder');
        
        // Log Bukti
        Route::get('/log-bukti', [MonitoringController::class, 'logBukti'])->name('log_bukti');
        Route::post('/log-bukti/store', [MonitoringController::class, 'storeLogBukti'])->name('log_bukti.store');
        
        // KKA Verifikasi
        Route::get('/kka-verifikasi', [MonitoringController::class, 'kkaVerifikasi'])->name('kka_verifikasi');
        Route::post('/kka-verifikasi/store', [MonitoringController::class, 'storeKkaVerifikasi'])->name('kka_verifikasi.store');
        
        // Closure Review
        Route::get('/closure-review', [MonitoringController::class, 'closureReview'])->name('closure_review');
        Route::post('/closure-review/store', [MonitoringController::class, 'storeClosureReview'])->name('closure_review.store');
        
        // Eskalasi & Perpanjangan
        Route::get('/eskalasi', [MonitoringController::class, 'eskalasi'])->name('eskalasi');
        Route::post('/eskalasi/store', [MonitoringController::class, 'storeEskalasi'])->name('eskalasi.store');
        
        // Status, Aging & KPI
        Route::get('/status-aging', [MonitoringController::class, 'statusAging'])->name('status_aging');
        Route::get('/kpi-ra', [MonitoringController::class, 'kpiRa'])->name('kpi_ra');
        Route::get('/ringkasan-bulanan', [MonitoringController::class, 'ringkasanBulanan'])->name('ringkasan_bulanan');
        
        // Export Excel
        Route::get('/export/{sheet}', [MonitoringController::class, 'exportExcel'])->name('export_excel');
    });

    // =========================================================================
    // MODUL PEMERIKSAAN AWAL INDIKASI FRAUD (SOP 09) - INDEPENDENT MODULE
    // =========================================================================
    Route::prefix('pemeriksaan-awal')->name('pemeriksaan_awal.')->middleware('role:ra,kabag_ra,kadiv_skai,admin')->group(function () {
        Route::get('/', [PemeriksaanAwalController::class, 'index'])->name('index');
        
        // Register Indikasi
        Route::get('/register', [PemeriksaanAwalController::class, 'register'])->name('register');
        Route::post('/register/store', [PemeriksaanAwalController::class, 'storeRegister'])->name('register.store');
        
        // Triase Awal
        Route::get('/triase', [PemeriksaanAwalController::class, 'triase'])->name('triase');
        Route::post('/triase/store', [PemeriksaanAwalController::class, 'storeTriase'])->name('triase.store');
        
        // Chain of Custody
        Route::get('/bukti-custody', [PemeriksaanAwalController::class, 'buktiCustody'])->name('bukti_custody');
        Route::post('/bukti-custody/store', [PemeriksaanAwalController::class, 'storeCustody'])->name('bukti_custody.store');
        
        // KKA Pemeriksaan
        Route::get('/kka', [PemeriksaanAwalController::class, 'kka'])->name('kka');
        Route::post('/kka/store', [PemeriksaanAwalController::class, 'storeKka'])->name('kka.store');
        
        // Keputusan & Eskalasi
        Route::get('/eskalasi', [PemeriksaanAwalController::class, 'eskalasi'])->name('eskalasi');
        Route::post('/eskalasi/store', [PemeriksaanAwalController::class, 'storeEskalasi'])->name('eskalasi.store');
        
        // Export Excel
        Route::get('/export/{sheet}', [PemeriksaanAwalController::class, 'exportExcel'])->name('export_excel');
    });

    // =========================================================================
    // MODUL SURPRISE AUDIT (SOP 05) - MANDIRI
    // =========================================================================
    Route::prefix('surprise-audit')->name('surprise_audit.')->middleware('role:ra,kabag_ra,kadiv_skai,admin')->group(function () {
        Route::get('/', [SurpriseAuditController::class, 'index'])->name('index');
        Route::get('/create', [SurpriseAuditController::class, 'create'])->name('create');
        Route::post('/assignment', [SurpriseAuditController::class, 'storeAssignment'])->name('assignment.store');
        Route::get('/{id}', [SurpriseAuditController::class, 'show'])->name('show');
        
        // Rute KKA Kas (SOP 05)
        Route::get('/{id}/kas', [SurpriseAuditController::class, 'showKas'])->name('kas');
        Route::post('/{id}/kas', [SurpriseAuditController::class, 'storeKas'])->name('kas.store');

        // Rute KKA Kas ATM (SOP 05 - WP-RA-05-D)
        Route::get('/{id}/atm', [SurpriseAuditController::class, 'atm'])->name('atm');
        Route::post('/{id}/atm', [SurpriseAuditController::class, 'storeAtm'])->name('atm.store');

        // Rute KKA Kartu ATM & PIN Mailer (SOP 05 - WP-RA-05-E)
        Route::get('/{id}/kartu', [SurpriseAuditController::class, 'showKartu'])->name('kartu');
        Route::post('/{id}/kartu', [SurpriseAuditController::class, 'storeKartu'])->name('kartu.store');

        // Rute KKA Dokumen Berharga (SOP 05 - WP-RA-05-G)
        Route::get('/{id}/dokumen-berharga', [SurpriseAuditController::class, 'showDokumenBerharga'])->name('dokumen_berharga');
        Route::post('/{id}/dokumen-berharga', [SurpriseAuditController::class, 'storeDokumenBerharga'])->name('dokumen_berharga.store');

        // Rute KKA Persediaan Barang Cetakan (SOP 05 - WP-RA-05-F)
        Route::get('/{id}/barang-cetakan', [SurpriseAuditController::class, 'showBarangCetakan'])->name('barang_cetakan');
        Route::post('/{id}/barang-cetakan', [SurpriseAuditController::class, 'storeBarangCetakan'])->name('barang_cetakan.store');

        // Rute KKA Dokumen Agunan (SOP 05 - WP-RA-05-H)
        Route::get('/{id}/agunan', [SurpriseAuditController::class, 'showAgunan'])->name('agunan');
        Route::post('/{id}/agunan', [SurpriseAuditController::class, 'storeAgunan'])->name('agunan.store');

        // Rute KKA Inventarisasi Aset (SOP 05 - WP-RA-05-I)
        Route::get('/{id}/aset', [SurpriseAuditController::class, 'showAset'])->name('aset');
        Route::post('/{id}/aset', [SurpriseAuditController::class, 'storeAset'])->name('aset.store');

        // Rute KKA Lingkungan dan CCTV (SOP 05 - WP-RA-05-J)
        Route::get('/{id}/lingkungan', [SurpriseAuditController::class, 'showLingkungan'])->name('lingkungan');
        Route::post('/{id}/lingkungan', [SurpriseAuditController::class, 'storeLingkungan'])->name('lingkungan.store');
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

        // Modul KKA Control & Integrasi (11 Area SOP 04.1)
        Route::get('/{visit}/kka/integration', [KkaIntegrationController::class, 'index'])->name('kka.integration.index');

        // KKA Sampling CBS: 1. Teller & Kas
        Route::get('/{visit}/kka/teller-kas', [TellerKasController::class, 'index'])->name('kka.teller_kas.index');
        Route::patch('/kka/teller-kas/{item}', [TellerKasController::class, 'update'])->name('kka.teller_kas.update');
        Route::patch('/kka/teller-kas/{item}/review', [TellerKasController::class, 'review'])->name('kka.teller_kas.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        // KKA Sampling CBS: 2. DPK & Layanan
        Route::get('/{visit}/kka/dpk', [DpkController::class, 'index'])->name('kka.dpk.index');
        Route::patch('/kka/dpk/{item}', [DpkController::class, 'update'])->name('kka.dpk.update');

        // KKA Sampling CBS: 3. Kredit
        Route::get('/{visit}/kka/kredit', [KreditController::class, 'index'])->name('kka.kredit.index');
        Route::patch('/kka/kredit/{item}', [KreditController::class, 'update'])->name('kka.kredit.update');

        // KKA Sampling CBS: 4. Biaya & Beban
        Route::get('/{visit}/kka/biaya', [BiayaController::class, 'index'])->name('kka.biaya.index');
        Route::patch('/kka/biaya/{item}', [BiayaController::class, 'update'])->name('kka.biaya.update');

        // KKA Sampling CBS: 5. Kartu ATM
        Route::get('/{visit}/kka/atm', [AtmController::class, 'index'])->name('kka.atm.index');
        Route::patch('/kka/atm/{item}', [AtmController::class, 'update'])->name('kka.atm.update');

        // KKA Sampling CBS: 6. Pengaduan Nasabah
        Route::get('/{visit}/kka/pengaduan', [PengaduanController::class, 'index'])->name('kka.pengaduan.index');
        Route::patch('/kka/pengaduan/{item}', [PengaduanController::class, 'update'])->name('kka.pengaduan.update');

        // Rekon Kartu ATM
        Route::get('/{visit}/rekon/katm', [RekonKartuAtmController::class, 'create'])->name('rekon.katm.create');
        Route::post('/{visit}/rekon/katm', [RekonKartuAtmController::class, 'store'])->name('rekon.katm.store');
        Route::get('/{visit}/rekon/katm/{rekon}', [RekonKartuAtmController::class, 'show'])->name('rekon.katm.show');
        Route::post('/rekon/katm/{rekon}/row', [RekonKartuAtmController::class, 'addRow'])->name('rekon.katm.row.add');
        Route::delete('/rekon/katm/item/{item}', [RekonKartuAtmController::class, 'deleteRow'])->name('rekon.katm.row.delete');
        Route::patch('/rekon/katm/item/{item}', [RekonKartuAtmController::class, 'updateItem'])->name('rekon.katm.item.update');
        Route::patch('/rekon/katm/item/{item}/review', [RekonKartuAtmController::class, 'reviewItem'])->name('rekon.katm.item.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        // Rekon Barang Cetakan
        Route::get('/{visit}/rekon/bct', [RekonController::class, 'createBct'])->name('rekon.bct.create');
        Route::post('/{visit}/rekon/bct', [RekonController::class, 'storeBct'])->name('rekon.bct.store');
        Route::get('/{visit}/rekon/bct/{rekon}', [RekonController::class, 'showBct'])->name('rekon.bct.show');
        Route::patch('/rekon/bct/item/{item}', [RekonController::class, 'updateBctItem'])->name('rekon.bct.item.update');
        Route::patch('/rekon/bct/item/{item}/review', [RekonController::class, 'reviewBctItem'])->name('rekon.bct.item.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        // Rekon Cek & BG
        Route::get('/{visit}/rekon/cekbg', [RekonController::class, 'createCekBg'])->name('rekon.cekbg.create');
        Route::post('/{visit}/rekon/cekbg', [RekonController::class, 'storeCekBg'])->name('rekon.cekbg.store');
        Route::get('/{visit}/rekon/cekbg/{rekon}', [RekonController::class, 'showCekBg'])->name('rekon.cekbg.show');
        Route::patch('/rekon/cekbg/item/{item}', [RekonController::class, 'updateCekBgItem'])->name('rekon.cekbg.item.update');
        Route::patch('/rekon/cekbg/item/{item}/review', [RekonController::class, 'reviewCekBgItem'])->name('rekon.cekbg.item.review')->middleware('role:admin,kabag_ra,kadiv_skai');

        // Cash Opname & Rekon ATM
        Route::get('/{visit}/cash-opname/create', [CashOpnameController::class, 'create'])->name('cash_opname.create');
        Route::post('/{visit}/cash-opname', [CashOpnameController::class, 'store'])->name('cash_opname.store');
        Route::get('/{visit}/cash-opname/{opname}', [CashOpnameController::class, 'show'])->name('cash_opname.show');
        Route::patch('/cash-opname/item/{item}', [CashOpnameController::class, 'updateItem'])->name('cash_opname.item.update');

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
        Route::get('/kka', [OffsiteKkaController::class, 'index'])->name('offsite.kka.index');
        Route::get('/kka/data', [OffsiteKkaController::class, 'data'])->name('offsite.kka.data');

        // Update KKA oleh RA
        Route::post('/kka/{id}/ra', [OffsiteKkaController::class, 'updateRa'])
            ->name('offsite.kka.update.ra')
            ->middleware('role:ra,admin');

        // Update KKA oleh Admin/Kabag (review)
        Route::put('/kka/{id}/admin', [OffsiteKkaController::class, 'updateAdmin'])
            ->name('offsite.kka.update.admin')
            ->middleware('role:admin,kabag_ra,kadiv_skai');

        // Konfirmasi temuan
        Route::post('/kka/{id}/konfirmasi', [OffsiteKkaController::class, 'konfirmasiCabang'])
            ->name('offsite.kka.konfirmasi')
            ->middleware('role:ra,admin,kabag_ra');

        // Register Harian
        Route::get('/register', [DailyRegisterController::class, 'index'])->name('offsite.register.index');
        Route::get('/register/data', [DailyRegisterController::class, 'data'])->name('offsite.register.data');
    });

});