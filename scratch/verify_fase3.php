<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Models\PelaporanBulanan\Sop08RekapKegiatan;
use App\Models\PelaporanBulanan\Sop08RekapIsuTl;
use App\Models\PelaporanBulanan\Sop08Feedback;
use App\Models\PelaporanBulanan\Sop08MasterParameter;
use App\Services\PelaporanBulanan\Sop08Service;
use Carbon\Carbon;

echo "=========================================================\n";
echo "VERIFIKASI FASE 3: MODUL PELAPORAN BULANAN & KONSOLIDASI (SOP 08)\n";
echo "=========================================================\n\n";

$service = app(Sop08Service::class);
$params = Sop08MasterParameter::getActive();
$periode = '2026-09-01';

// Pastikan data kalender ada
$existingReports = Sop08KalenderLaporan::where('periode', $periode)->get();
if ($existingReports->isEmpty()) {
    echo "[!] Tidak ada kalender untuk periode {$periode}, generate sekarang...\n";
    $service->generateKalenderForPeriode($periode, false);
    $existingReports = Sop08KalenderLaporan::where('periode', $periode)->get();
}
echo "[✓] Total Kalender Laporan {$periode}: " . $existingReports->count() . "\n";

// Skenario: Ambil laporan pertama dan isi kegiatan & isu untuk menguji kalkulasi
$firstKal = $existingReports->first();
echo "[*] Laporan pengujian: {$firstKal->report_id} ({$firstKal->nama_unit})\n";

// Inisiasi kegiatan & isu jika belum ada
$service->syncFromOtherModules($firstKal->report_id);

// Simulasikan 1 laporan berstatus APPROVED/FINAL & 100% lengkap
$firstKal->status_final = 'APPROVED/FINAL';
$firstKal->kelengkapan = 100.00;
$firstKal->tanggal_submit = '2026-10-02';
$firstKal->ketepatan = 'TEPAT WAKTU';
$firstKal->quality_score = 92.50;
$firstKal->save();

// Tambahkan beberapa isu simulasi pada Rekap Isu & TL
Sop08RekapIsuTl::updateOrCreate(
    ['report_id' => $firstKal->report_id, 'issue_id' => 'ISU-TEST-001'],
    [
        'periode' => $periode,
        'kode_unit' => $firstKal->kode_unit,
        'nama_unit' => $firstKal->nama_unit,
        'resident_auditor' => $firstKal->resident_auditor,
        'area' => 'Teller Kas',
        'jenis_isu' => 'FINDING',
        'risk_level' => 'HIGH',
        'uraian_isu' => 'Uji Coba Temuan High Risk Teller',
        'status_tl' => 'OVERDUE',
        'berulang' => 'YA',
        'flag_signifikan' => 'YA',
        'sumber_sop' => 'SOP 08',
    ]
);

// -------------------------------------------------------------
// 1. VERIFIKASI KONSOLIDASI (WP-RA-08-J - 21 KOLOM)
// -------------------------------------------------------------
echo "\n--- 1. UJI KONSOLIDASI (WP-RA-08-J - 21 KOLOM) ---\n";
$konsolidasiData = $service->getKonsolidasiData($periode);
assert(!empty($konsolidasiData), "Data konsolidasi tidak boleh kosong");

$firstRow = collect($konsolidasiData)->firstWhere('report_id', $firstKal->report_id);
assert($firstRow !== null, "Baris {$firstKal->report_id} harus ada di konsolidasi");

echo "Report ID: " . $firstRow['report_id'] . "\n";
echo "Kelengkapan: " . $firstRow['kelengkapan'] . "%\n";
echo "Status Final: " . $firstRow['status_final'] . "\n";
echo "Status Konsolidasi: " . $firstRow['status_konsolidasi'] . "\n";
echo "Kegiatan: " . $firstRow['kegiatan'] . ", Populasi: " . $firstRow['populasi'] . ", Sampel: " . $firstRow['sampel'] . ", Diuji: " . $firstRow['diuji'] . "\n";
echo "Exception: " . $firstRow['exception'] . ", Finding: " . $firstRow['finding'] . ", High Risk: " . $firstRow['high_risk'] . ", Overdue: " . $firstRow['tl_overdue'] . ", Signifikan: " . $firstRow['isu_signifikan'] . "\n";

// Cek formula Kolom U
assert($firstRow['status_konsolidasi'] === 'SIAP KONSOLIDASI', "Status Konsolidasi harus SIAP KONSOLIDASI karena Approved/Final & 100%");
assert($firstRow['high_risk'] >= 1, "High risk harus teragregasi >= 1");
assert($firstRow['tl_overdue'] >= 1, "TL Overdue harus teragregasi >= 1");
assert($firstRow['isu_signifikan'] >= 1, "Isu signifikan harus teragregasi >= 1");
echo "[✓] Uji Konsolidasi 21 Kolom: PASSED\n";

// -------------------------------------------------------------
// 2. VERIFIKASI TREN ISU (WP-RA-08-K - 10 KOLOM & 13 AREA)
// -------------------------------------------------------------
echo "\n--- 2. UJI MATRIKS TREN ISU (WP-RA-08-K - 13 AREA & SKOR FORMULA) ---\n";
$trenData = $service->getTrenIsuData($periode);
assert(count($trenData) === 13, "Harus ada tepat 13 area standar yang dihitung");

$tellerRow = collect($trenData)->firstWhere('area', 'Teller Kas');
assert($tellerRow !== null, "Area Teller Kas harus ditemukan");
echo "Area: " . $tellerRow['area'] . "\n";
echo "Isu Bulan Ini: " . $tellerRow['isu_bulan_ini'] . "\n";
echo "Isu Bulan Lalu: " . $tellerRow['isu_bulan_lalu'] . "\n";
echo "Perubahan: " . $tellerRow['perubahan'] . "\n";
echo "High Risk: " . $tellerRow['high_risk'] . "\n";
echo "TL Overdue: " . $tellerRow['tl_overdue'] . "\n";
echo "Berulang: " . $tellerRow['berulang'] . "\n";
echo "Isu Signifikan: " . $tellerRow['isu_signifikan'] . "\n";
echo "Skor Perhatian: " . $tellerRow['skor_perhatian'] . "\n";
echo "Status: " . $tellerRow['status'] . "\n";

// Hitung skor perhatian manual:
// MIN(100, (E*25) + (F*15) + (G*10) + (MAX(D,0)*5))
$e = $tellerRow['high_risk'];
$f = $tellerRow['tl_overdue'];
$g = $tellerRow['berulang'];
$d = $tellerRow['perubahan'];
$expectedSkor = min(100, ($e * 25) + ($f * 15) + ($g * 10) + (max($d, 0) * 5));
assert($tellerRow['skor_perhatian'] === $expectedSkor, "Kalkulasi skor perhatian harus persis sesuai rumus");

if ($tellerRow['skor_perhatian'] >= 60) {
    assert($tellerRow['status'] === 'HIGH ATTENTION', "Status harus HIGH ATTENTION jika skor >= 60");
} elseif ($tellerRow['skor_perhatian'] >= 30) {
    assert($tellerRow['status'] === 'PANTAU', "Status harus PANTAU jika skor >= 30");
} else {
    assert($tellerRow['status'] === 'NORMAL', "Status harus NORMAL jika skor < 30");
}
echo "[✓] Uji Matriks Tren Isu & Formula Skor: PASSED\n";

// -------------------------------------------------------------
// 3. VERIFIKASI DASHBOARD SOP 08 (WP-RA-08-L - 12 KPIS & MONITORING)
// -------------------------------------------------------------
echo "\n--- 3. UJI DASHBOARD EKSEKUTIF SOP 08 (WP-RA-08-L) ---\n";
$dashboardData = $service->getDashboardData($periode);

// 12 KPIs
assert(isset($dashboardData['kpi']['laporan_wajib']), "KPI Laporan Wajib harus ada");
assert(isset($dashboardData['kpi']['sudah_submit']), "KPI Sudah Submit harus ada");
assert(isset($dashboardData['kpi']['tepat_waktu']), "KPI Tepat Waktu harus ada");
assert(isset($dashboardData['kpi']['terlambat_belum']), "KPI Terlambat/Belum harus ada");
assert(isset($dashboardData['kpi']['approved_final']), "KPI Approved/Final harus ada");
assert(isset($dashboardData['kpi']['rata_rata_quality']), "KPI Rata-rata Quality harus ada");
assert(isset($dashboardData['kpi']['exception']), "KPI Exception harus ada");
assert(isset($dashboardData['kpi']['finding']), "KPI Finding harus ada");
assert(isset($dashboardData['kpi']['high_risk']), "KPI High Risk harus ada");
assert(isset($dashboardData['kpi']['tl_overdue']), "KPI TL Overdue harus ada");
assert(isset($dashboardData['kpi']['isu_signifikan']), "KPI Isu Signifikan harus ada");
assert(isset($dashboardData['kpi']['unit_high_attention']), "KPI Unit High Attention harus ada");

echo "12 KPI Values:\n";
foreach ($dashboardData['kpi'] as $k => $v) {
    echo "  - {$k}: {$v}\n";
}

// 5 Status summary
echo "5 Status Summary:\n";
foreach ($dashboardData['status_summary'] as $s => $cnt) {
    echo "  - {$s}: {$cnt}\n";
}

// Unit Monitoring Table
$unitMonFirst = collect($dashboardData['unit_monitoring'])->firstWhere('kode_unit', $firstKal->kode_unit);
assert($unitMonFirst !== null, "Unit monitoring harus mencakup {$firstKal->kode_unit}");
echo "Unit Monitoring: {$unitMonFirst['unit']} -> High Risk: {$unitMonFirst['high_risk']}, Overdue: {$unitMonFirst['overdue']}, Signifikan: {$unitMonFirst['isu_signifikan']}, Quality: {$unitMonFirst['quality']} => Status: {$unitMonFirst['perhatian']}\n";
assert($unitMonFirst['perhatian'] === 'PERLU PERHATIAN', "Unit harus PERLU PERHATIAN karena high risk / overdue / signifikan > 0");
echo "[✓] Uji Dashboard SOP 08: PASSED\n";

// -------------------------------------------------------------
// 4. VERIFIKASI FEEDBACK FLOW (WP-RA-08-M)
// -------------------------------------------------------------
echo "\n--- 4. UJI FEEDBACK REGISTER & SIKLUS PERBAIKAN (WP-RA-08-M) ---\n";
$fbId = $service->generateFeedbackId($periode);
assert(str_starts_with($fbId, 'FB-202609-'), "Format Feedback ID harus FB-202609-XXX");
echo "Generated Feedback ID: {$fbId}\n";

// Reviewer membuat feedback baru
$feedback = Sop08Feedback::create([
    'feedback_id' => $fbId,
    'report_id' => $firstKal->report_id,
    'kode_unit' => $firstKal->kode_unit,
    'nama_unit' => $firstKal->nama_unit,
    'tanggal' => '2026-10-05',
    'diberikan_kepada' => $firstKal->resident_auditor . ' (' . $firstKal->nama_unit . ')',
    'jenis_feedback' => 'Kualitas Analisis',
    'uraian_feedback' => 'Analisis penyebab pada KKA Teller Kas perlu diperdalam faktor internal control.',
    'pic' => $firstKal->resident_auditor,
    'target' => '2026-10-12',
    'status' => 'OPEN',
    'verifikasi_reviewer' => 'BELUM DIVERIFIKASI',
]);
assert($feedback->status === 'OPEN', "Status awal harus OPEN");
assert($feedback->verifikasi_reviewer === 'BELUM DIVERIFIKASI', "Verifikasi awal harus BELUM DIVERIFIKASI");
echo "[✓] Feedback Created: {$feedback->feedback_id}\n";

// RA menindaklanjuti
$feedback->tindakan_perbaikan = 'Telah diperbarui analisis 5-Why pada KKA Teller Kas dan ditambahkan matriks SOP Teller.';
$feedback->bukti = 'KKA Teller v2.0 Hal 5';
$feedback->tanggal_selesai = '2026-10-08';
$feedback->status = 'MENUNGGU VERIFIKASI';
$feedback->save();

$feedback->refresh();
assert($feedback->status === 'MENUNGGU VERIFIKASI', "Status harus menjadi MENUNGGU VERIFIKASI setelah RA selesai");
echo "[✓] RA Tindak Lanjut Berhasil Disimpan\n";

// Reviewer memverifikasi & menutup feedback
$feedback->verifikasi_reviewer = 'MEMADAI / CLOSED';
$feedback->status = 'CLOSED';
$feedback->catatan = 'Perbaikan memadai dan bukti kertas kerja telah terverifikasi.';
$feedback->save();

$feedback->refresh();
assert($feedback->status === 'CLOSED', "Status harus CLOSED");
assert($feedback->verifikasi_reviewer === 'MEMADAI / CLOSED', "Verifikasi Reviewer harus MEMADAI / CLOSED");
echo "[✓] Reviewer Verifikasi & Status CLOSED: PASSED\n";

// -------------------------------------------------------------
// 5. VERIFIKASI RBAC ISOLASI CABANG
// -------------------------------------------------------------
echo "\n--- 5. UJI ISOLASI CABANG (RBAC SCOPE) ---\n";
$targetBranch = $firstKal->kode_unit;
$scopedKonsolidasi = $service->getKonsolidasiData($periode, [$targetBranch]);
assert(count($scopedKonsolidasi) === 1, "Konsolidasi untuk RA terisolasi hanya boleh berisi 1 cabang");
assert($scopedKonsolidasi[0]['kode_unit'] === $targetBranch, "Kode unit harus persis cabang RA");

$scopedDashboard = $service->getDashboardData($periode, [$targetBranch]);
assert(count($scopedDashboard['unit_monitoring']) === 1, "Unit monitoring Dashboard untuk RA hanya boleh 1 cabang");
echo "[✓] Isolasi Cabang (RBAC Scope): PASSED\n";

echo "\n=========================================================\n";
echo "SEMUA PENGUJIAN FASE 3 SOP 08 LENGKAP & LOLOS 100%!\n";
echo "=========================================================\n";
