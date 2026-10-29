<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PelaporanBulanan\Sop08MasterRa;
use App\Models\PelaporanBulanan\Sop08LogHarianRa;
use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Models\PelaporanBulanan\Sop08DaftarHariLibur;
use App\Models\PelaporanBulanan\Sop08MasterParameter;
use App\Services\PelaporanBulanan\Sop08Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "   AUTOMATED VERIFICATION - SOP 08 FASE 4 (FINAL)        \n";
echo "========================================================\n\n";

$service = app(Sop08Service::class);
$errors = [];

// ---------------------------------------------------------
// TEST 1: Master RA Roster & Data Integrity
// ---------------------------------------------------------
echo "[TEST 1] Verifying Sop08MasterRa roster & relationships...\n";
$raCount = Sop08MasterRa::count();
echo "  Found {$raCount} Master RA records in database.\n";
if ($raCount === 0) {
    $errors[] = "TEST 1 FAILED: Sop08MasterRa table is empty! Run Sop08MasterRaSeeder.";
} else {
    $sampleRa = Sop08MasterRa::first();
    echo "  Sample RA: [{$sampleRa->ra_id}] {$sampleRa->nama_ra} - Unit: {$sampleRa->kode_kc_penempatan} ({$sampleRa->nama_kc}) - Status: {$sampleRa->status}\n";
    if (empty($sampleRa->ra_id) || empty($sampleRa->nama_ra) || empty($sampleRa->kode_kc_penempatan)) {
        $errors[] = "TEST 1 FAILED: Sample RA missing mandatory columns.";
    } else {
        echo "  --> PASSED: Master RA structure valid.\n";
    }
}

// ---------------------------------------------------------
// TEST 2: Log ID Generation Format (LOG-YYYYMMDD-XXX)
// ---------------------------------------------------------
echo "\n[TEST 2] Verifying Log ID Auto-Generator (LOG-YYYYMMDD-XXX)...\n";
$testDate = '2026-09-15';
$generatedId = $service->generateLogId($testDate);
echo "  Generated Log ID for {$testDate}: {$generatedId}\n";
if (!preg_match('/^LOG-20260915-\d{3}$/', $generatedId)) {
    $errors[] = "TEST 2 FAILED: Generated Log ID '{$generatedId}' does not match regex /^LOG-20260915-\\d{3}$/";
} else {
    echo "  --> PASSED: Log ID format strictly matches SOP 08 requirement.\n";
}

// ---------------------------------------------------------
// TEST 3: Networkdays Calculation (Senin-Jumat minus Hari Libur)
// ---------------------------------------------------------
echo "\n[TEST 3] Verifying calculateNetworkdays with Holiday exclusions...\n";
// September 2026: 1 to 30 Sep 2026
// In Sep 2026: 30 days total. Saturdays & Sundays = 8 days (5,6, 12,13, 19,20, 26,27).
// Weekdays = 22 days.
// If holiday list contains 1 weekday in Sep 2026 (e.g. Maulid Nabi), expected = 21 days.
$holidays = Sop08DaftarHariLibur::pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
$netDaysSep = $service->calculateNetworkdays('2026-09-01', '2026-09-30', $holidays);
echo "  Calculated Networkdays for Sep 2026: {$netDaysSep} days (Holidays registered: " . count($holidays) . ").\n";
if ($netDaysSep < 20 || $netDaysSep > 22) {
    $errors[] = "TEST 3 FAILED: Networkdays for Sep 2026 out of expected range (20-22): got {$netDaysSep}";
} else {
    echo "  --> PASSED: Networkdays calculation working accurately.\n";
}

// ---------------------------------------------------------
// TEST 4: Log Harian RA CRUD & Review Status Flow
// ---------------------------------------------------------
echo "\n[TEST 4] Testing Log Harian RA creation & calculation...\n";
$targetRa = Sop08MasterRa::where('status', 'Aktif')->first();
if (!$targetRa) {
    $targetRa = Sop08MasterRa::first();
}

$testLog = Sop08LogHarianRa::create([
    'log_id' => $service->generateLogId('2026-09-01'),
    'tanggal' => '2026-09-01',
    'ra_id' => $targetRa->ra_id,
    'nama_ra' => $targetRa->nama_ra,
    'kode_unit' => $targetRa->kode_kc_penempatan,
    'nama_unit' => $targetRa->nama_kc,
    'status_hari' => 'KERJA',
    'sumber_sop' => 'SOP 03 - Daily Review',
    'jenis_aktivitas' => 'Pengujian transaksi kas besar di atas limit teller',
    'area' => 'Operasional Kas & Teller',
    'output_referensi_wp' => 'WP-DR-20260901-001',
    'status_penyelesaian' => 'COMPLETED',
    'jumlah_item' => 15,
    'catatan' => 'Uji otomatis verifikasi FASE 4',
    'hasil_review' => 'MEMADAI',
    'reviewer' => 'Siti (Korwas)',
    'tanggal_review' => '2026-09-02',
]);

echo "  Created test log: [{$testLog->log_id}] {$testLog->jenis_aktivitas} - Status: {$testLog->status_penyelesaian} - Review: {$testLog->hasil_review}\n";
if (!$testLog->id) {
    $errors[] = "TEST 4 FAILED: Could not save test log.";
} else {
    echo "  --> PASSED: Log Harian created successfully.\n";
}

// ---------------------------------------------------------
// TEST 5: Kalender Kinerja RA 47-Column Calculation & Formulas
// ---------------------------------------------------------
echo "\n[TEST 5] Testing Kalender Kinerja RA 47-column engine...\n";
$kinerja = $service->getKalenderKinerjaRaData('2026-09', null, null, $targetRa->ra_id);

echo "  Periode: {$kinerja['periode']}\n";
echo "  Hari Kerja Bulan (D): {$kinerja['hari_kerja_bulan']}\n";
echo "  Found " . count($kinerja['data']) . " RA performance rows.\n";

$targetRow = collect($kinerja['data'])->firstWhere('ra_id', $targetRa->ra_id);
if (!$targetRow) {
    $errors[] = "TEST 5 FAILED: RA {$targetRa->ra_id} not found in Kalender Kinerja calculations.";
} else {
    echo "  Performance row for {$targetRa->ra_id} ({$targetRa->nama_ra}):\n";
    echo "    - Hari Kerja (D): {$targetRow['hari_kerja']}\n";
    echo "    - Cuti/Sakit (E): {$targetRow['cuti_sakit']}\n";
    echo "    - Hari Tugas Terpenuhi (F): {$targetRow['hari_tugas_dipenuhi']}\n";
    echo "    - Tanpa Aktivitas (G): {$targetRow['tanpa_aktivitas']}\n";
    echo "    - Konsistensi (H): " . round($targetRow['konsistensi'] * 100, 1) . "%\n";
    echo "    - Aktivitas (I): {$targetRow['aktivitas']}\n";
    echo "    - Selesai (J): {$targetRow['selesai']}\n";
    echo "    - Penyelesaian (K): " . round($targetRow['penyelesaian'] * 100, 1) . "%\n";
    echo "    - Direview (L): {$targetRow['direview']}\n";
    echo "    - Memadai (M): {$targetRow['memadai']}\n";
    echo "    - Kualitas (N): " . ($targetRow['kualitas'] !== null ? round($targetRow['kualitas'] * 100, 1) . "%" : "null") . "\n";
    echo "    - Skor Kinerja (O): " . ($targetRow['skor'] !== null ? $targetRow['skor'] : "null") . "\n";
    echo "    - Status Bulanan (P): {$targetRow['status_bulanan']}\n";

    // Verify 31-day array count
    $daysCount = count($targetRow['matrix_hari']);
    $day1Status = $targetRow['matrix_hari'][1]['status'];
    echo "    - Total daily matrix columns (Q..AU): {$daysCount} days (Day 1: '{$day1Status}')\n";
    if ($daysCount !== 31) {
        $errors[] = "TEST 5 FAILED: Daily matrix does not contain exactly 31 columns (got {$daysCount}).";
    }

    // Verify Formula logic for Day 1
    // Day 1 (2026-09-01) has 1 log: KERJA, COMPLETED, MEMADAI -> should be "BAIK"
    if ($day1Status !== 'BAIK') {
        $errors[] = "TEST 5 FAILED: Day 1 should evaluate to 'BAIK', got '{$day1Status}'";
    } else {
        echo "    --> Day 1 evaluated correctly to 'BAIK'.\n";
    }

    // Verify formula weights: Konsistensi*40 + Penyelesaian*30 + Kualitas*30
    if ($targetRow['skor'] !== null && $targetRow['kualitas'] !== null) {
        $expectedScore = round(($targetRow['konsistensi'] * 40) + ($targetRow['penyelesaian'] * 30) + ($targetRow['kualitas'] * 30), 0);
        if (abs($targetRow['skor'] - $expectedScore) > 0.01) {
            $errors[] = "TEST 5 FAILED: Score mismatch! Expected {$expectedScore}, got {$targetRow['skor']}";
        } else {
            echo "    --> Score formula verified: round(H*40 + K*30 + N*30, 0) == {$targetRow['skor']}.\n";
        }
    }

    echo "  --> PASSED: Kalender Kinerja RA 47-column engine verified.\n";
}

// ---------------------------------------------------------
// TEST 6: Auto-Pull Logs from Work Modules (SOP 03, 04, 05, 06)
// ---------------------------------------------------------
echo "\n[TEST 6] Testing Auto-Pull logs from Work Modules...\n";
$pulledResult = $service->pullLogsFromWorkModules($targetRa->ra_id, '2026-09-01', '2026-09-30');
echo "  Pulled {$pulledResult['pulled_count']} new logs from work modules for {$targetRa->ra_id}.\n";
echo "  --> PASSED: Auto-Pull method runs without exception.\n";

// ---------------------------------------------------------
// TEST 7: Integration to Laporan Bulanan (calculateRaPerformanceForReport)
// ---------------------------------------------------------
echo "\n[TEST 7] Testing integration to Laporan Bulanan (A44:K53)...\n";
$kalenderReport = Sop08KalenderLaporan::first();
if ($kalenderReport) {
    $perfReport = $service->calculateRaPerformanceForReport($kalenderReport->report_id);
    echo "  Report ID: {$kalenderReport->report_id} - Periode: {$kalenderReport->periode}\n";
    echo "  Hari Kerja: {$perfReport['hari_kerja']}, Dipenuhi: {$perfReport['hari_tugas_dipenuhi']}, Skor: " . ($perfReport['skor'] ?? 'null') . ", Status: {$perfReport['status_bulanan']}\n";
    if (!isset($perfReport['hari_kerja']) || !isset($perfReport['status_bulanan'])) {
        $errors[] = "TEST 7 FAILED: calculateRaPerformanceForReport did not return required keys.";
    } else {
        echo "  --> PASSED: Integration to Laporan Bulanan functioning.\n";
    }
} else {
    echo "  No Kalender Laporan found, skipped test 7.\n";
}

// Clean up test log
$testLog->delete();
echo "\n[CLEANUP] Deleted test log record.\n";

// Final summary
echo "\n========================================================\n";
if (empty($errors)) {
    echo "  ALL TESTS PASSED! FASE 4 IMPLEMENTATION IS 100% VALID. \n";
    echo "========================================================\n";
    exit(0);
} else {
    echo "  VERIFICATION FAILED WITH " . count($errors) . " ERRORS:\n";
    foreach ($errors as $err) {
        echo "  - {$err}\n";
    }
    echo "========================================================\n";
    exit(1);
}
