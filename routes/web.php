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

// UI Routes
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    // Dummy login: langsung redirect ke dashboard
    return redirect()->route('dashboard');
})->name('login.post');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// ==========================================
// 1. MASTER DATA & STRUKTUR CABANG
// ==========================================
Route::prefix('cabangs')->group(function () {
    Route::get('/', [CabangController::class, 'index'])->name('cabang.index');           // Lihat semua cabang & anak cabang
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
    Route::get('/', [AuditPlanController::class, 'index'])->name('rencana.input');               // List Audit Plan
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

// UI Routes (Wired to Database using Generic List View)
Route::get('/rencana/scoring', function () { 
    $data = \App\Models\ParameterAudit::all();
    return view('generic-list', [
        'title' => 'Scoring Parameter Audit',
        'columns' => ['Parameter', 'Bobot', 'Deskripsi'],
        'fields' => ['nama_parameter', 'bobot', 'deskripsi'],
        'data' => $data
    ]); 
})->name('rencana.scoring');

Route::get('/rencana/approval', function () { 
    $data = \App\Models\AuditPlan::with('cabang', 'raUser')->where('status_approval', '!=', 'draft')->get();
    return view('generic-list', [
        'title' => 'Approval Audit Plan',
        'columns' => ['Tahun Periode', 'Jadwal Mulai', 'Jadwal Selesai', 'Status Approval'],
        'fields' => ['tahun_periode', 'jadwal_mulai', 'jadwal_selesai', 'status_approval'],
        'data' => $data
    ]); 
})->name('rencana.approval');

Route::get('/pelaksanaan/penugasan', function () { 
    $data = \App\Models\KertasKerjaAudit::with('auditPlan')->get();
    return view('generic-list', [
        'title' => 'Penugasan Audit (KKA)',
        'columns' => ['Bidang Audit', 'Sub Bidang', 'Tanggal Pemeriksaan', 'Status'],
        'fields' => ['bidang_audit', 'sub_bidang', 'tanggal_pemeriksaan', 'status_kka'],
        'data' => $data
    ]); 
})->name('pelaksanaan.penugasan');

Route::get('/pelaksanaan/audit', function () { 
    // Jika KertasHasilAudit belum ada, ini bisa error. Kita akan skip relasi with() jika error atau biarkan all()
    // KertasHasilAudit mungkin belum dibuat model-nya sesuai di struktur folder atau fields beda. Kita cek fields yang umum.
    $data = class_exists(\App\Models\KertasHasilAudit::class) ? \App\Models\KertasHasilAudit::all() : [];
    return view('generic-list', [
        'title' => 'Pelaksanaan Audit (KHA)',
        'columns' => ['ID', 'KKA ID', 'Status', 'Tanggal Dibuat'],
        'fields' => ['id', 'kka_id', 'status_kha', 'created_at'],
        'data' => $data
    ]); 
})->name('pelaksanaan.audit');

Route::get('/tindak-lanjut/monitoring', function () { 
    $data = \App\Models\TemuanAudit::all();
    return view('generic-list', [
        'title' => 'Monitoring Temuan',
        'columns' => ['Deskripsi Temuan', 'Tingkat Risiko', 'Target Penyelesaian', 'Status'],
        'fields' => ['deskripsi_temuan', 'tingkat_risiko', 'target_penyelesaian', 'status_temuan'],
        'data' => $data
    ]); 
})->name('tindaklanjut.monitoring');

Route::get('/tindak-lanjut/penyelesaian', function () { 
    $data = \App\Models\TindakLanjut::all();
    return view('generic-list', [
        'title' => 'Penyelesaian Tindak Lanjut',
        'columns' => ['Status Verifikasi', 'Tanggal Dibuat'],
        'fields' => ['status_verifikasi', 'created_at'],
        'data' => $data
    ]); 
})->name('tindaklanjut.penyelesaian');

Route::get('/reporting/sistem', function () { 
    $data = \App\Models\ScoringAudit::all();
    return view('generic-list', [
        'title' => 'Sistem Skor',
        'columns' => ['Total Skor', 'Peringkat', 'Keterangan'],
        'fields' => ['total_skor', 'peringkat', 'keterangan'],
        'data' => $data
    ]); 
})->name('reporting.sistem');

Route::get('/reporting/laporan', function () { 
    $data = \App\Models\LaporanAudit::all();
    return view('generic-list', [
        'title' => 'Laporan Audit',
        'columns' => ['Nomor Laporan', 'Tanggal Laporan', 'Status'],
        'fields' => ['nomor_laporan', 'tanggal_laporan', 'status_laporan'],
        'data' => $data
    ]); 
})->name('reporting.laporan');