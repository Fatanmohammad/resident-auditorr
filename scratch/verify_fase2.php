<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Models\PelaporanBulanan\Sop08ReviewLaporan;
use App\Models\PelaporanBulanan\Sop08KoreksiApproval;
use App\Models\PelaporanBulanan\Sop08IsuSignifikan;
use App\Models\PelaporanBulanan\Sop08DistribusiQuality;
use App\Models\PelaporanBulanan\Sop08ChecklistLaporan;
use App\Models\PelaporanBulanan\Sop08RekapKegiatan;
use App\Models\PelaporanBulanan\Sop08RekapIsuTl;
use App\Models\PelaporanBulanan\Sop08MasterParameter;
use App\Services\PelaporanBulanan\Sop08Service;

echo "=== STARTING FASE 2 SOP 08 VERIFICATION ===\n\n";

$service = app(Sop08Service::class);

// 1. Ambil atau buat 1 Kalender Laporan dummy untuk testing
$periode = '2026-10-01';
$kalender = Sop08KalenderLaporan::firstOrCreate(
    ['report_id' => 'REP-202610-TEST'],
    [
        'periode' => $periode,
        'kode_unit' => '999',
        'nama_unit' => 'KC Test Verifikasi',
        'resident_auditor' => 'Auditor Verifikator',
        'tanggal_cutoff' => '2026-10-31',
        'jatuh_tempo' => '2026-11-05',
        'target_submit' => '2026-11-05',
        'tanggal_submit' => '2026-11-04',
        'target_review' => '2026-11-10',
        'tanggal_review' => '2026-11-08',
        'ketepatan' => 'TEPAT WAKTU',
        'kelengkapan' => 100.0,
        'status_laporan' => 'SUBMITTED',
        'status_review' => 'UNDER REVIEW',
        'status_final' => 'UNDER REVIEW',
        'quality_score' => 85.0,
        'masuk_konsolidasi' => 'BELUM',
    ]
);

$reportId = $kalender->report_id;
echo "[1] Testing Kalender Laporan: {$reportId}\n";

// Inisiasi data Fase 2
$service->initFase2Records($reportId);

// Pastikan checklist ada
$checklist = Sop08ChecklistLaporan::firstOrCreate(
    ['report_id' => $reportId],
    [
        'status_kesiapan' => 'SIAP REVIEW',
        'persen_lengkap' => 100.0,
        'jumlah_gap' => 0,
    ]
);

// 2. Test Rumus Review Laporan
echo "[2] Testing Review Laporan Formulas...\n";
$review = Sop08ReviewLaporan::where('report_id', $reportId)->first();
$review->akurasi_data = 'MEMADAI';
$review->konsistensi = 'MEMADAI';
$review->kualitas_analisis = 'MEMADAI';
$review->bukti_pendukung = 'MEMADAI';
$review->tindak_lanjut = 'MEMADAI';
$review->eskalasi = 'MEMADAI';
$review->override = null;
$review->save();

$service->recalculateReviewLaporan($review);
$review->refresh();

echo "    - Skor Review (100% lengkap + 6 Memadai): {$review->skor_review} (Expected: 100)\n";
assert($review->skor_review == 100, "Skor review harus 100");
echo "    - Hasil Otomatis: {$review->hasil_otomatis} (Expected: MEMADAI)\n";
assert($review->hasil_otomatis == 'MEMADAI', "Hasil otomatis harus MEMADAI");
echo "    - Status Review Final: {$review->status_review_final} (Expected: MEMADAI)\n";
assert($review->status_review_final == 'MEMADAI', "Status review final harus MEMADAI");

// Test Perlu Perbaikan
$review->akurasi_data = 'PERLU PERBAIKAN';
$review->save();
$service->recalculateReviewLaporan($review);
$review->refresh();
echo "    - Hasil Otomatis dengan 1 Perlu Perbaikan: {$review->hasil_otomatis} (Expected: KOREKSI MINOR)\n";
assert($review->hasil_otomatis == 'KOREKSI MINOR', "Hasil otomatis harus KOREKSI MINOR");

// Test Override
$review->override = 'KOREKSI SUBSTANSI';
$review->alasan_override = 'Ditemukan bukti baru yang membutuhkan pengujian ulang.';
$review->save();
$service->recalculateReviewLaporan($review);
$review->refresh();
echo "    - Status Review Final dengan Override: {$review->status_review_final} (Expected: KOREKSI SUBSTANSI)\n";
assert($review->status_review_final == 'KOREKSI SUBSTANSI', "Status final harus menggunakan nilai override");

// 3. Test Koreksi & Approval
echo "\n[3] Testing Koreksi & Approval (WORKDAY Calculation)...\n";
$koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->first();
// Set jenis koreksi ADMINISTRATIF/MINOR
$koreksi->tanggal_permintaan = '2026-10-23'; // Jumat
$koreksi->hasil_review = 'KOREKSI MINOR';
$koreksi->save();
$service->recalculateKoreksiApproval($koreksi, 'KOREKSI MINOR');
$koreksi->refresh();

echo "    - Jenis Koreksi untuk KOREKSI MINOR: {$koreksi->jenis_koreksi} (Expected: ADMINISTRATIF/MINOR)\n";
assert($koreksi->jenis_koreksi == 'ADMINISTRATIF/MINOR', "Jenis koreksi harus ADMINISTRATIF/MINOR");
$batasFormatted = \Carbon\Carbon::parse($koreksi->batas_koreksi)->format('Y-m-d');
echo "    - Batas Koreksi (+1 workday dari Jumat 2026-10-23): {$batasFormatted} (Expected: Senin 2026-10-26)\n";
assert($batasFormatted == '2026-10-26', "Batas koreksi harus melompati akhir pekan ke hari Senin");

// Test Approval Final
$koreksi->keputusan_approval = 'APPROVED/FINAL';
$koreksi->approved_by = 'Kadiv SKAI Test';
$koreksi->tanggal_approval = '2026-10-26';
$koreksi->save();
$service->recalculateKoreksiApproval($koreksi);
$koreksi->refresh();
echo "    - Status Final setelah Keputusan APPROVED/FINAL: {$koreksi->status_final} (Expected: APPROVED/FINAL)\n";
assert($koreksi->status_final == 'APPROVED/FINAL', "Status final harus APPROVED/FINAL");

// 4. Test Distribusi & 7 Bobot Quality Score
echo "\n[4] Testing Distribusi & 7 Bobot Quality Score...\n";
$dist = Sop08DistribusiQuality::where('report_id', $reportId)->first();
$review->override = null;
$review->akurasi_data = 'MEMADAI';
$review->save();
$service->recalculateReviewLaporan($review);

$service->recalculateDistribusiQuality($dist, $review);
$dist->refresh();

echo "    - Tepat Waktu (F): {$dist->skor_tepat_waktu} [Bobot 20]\n";
echo "    - Kelengkapan (G): {$dist->skor_kelengkapan} [Bobot 20]\n";
echo "    - Akurasi (H): {$dist->skor_akurasi} [Bobot 15]\n";
echo "    - Analisis (I): {$dist->skor_analisis} [Bobot 15]\n";
echo "    - Bukti (J): {$dist->skor_bukti} [Bobot 10]\n";
echo "    - TL (K): {$dist->skor_tindak_lanjut} [Bobot 10]\n";
echo "    - Eskalasi (L): {$dist->skor_eskalasi} [Bobot 10]\n";
echo "    - Total Skor Dasar: {$dist->skor_dasar} (Expected: 100)\n";
assert($dist->skor_dasar == 100.0, "Skor dasar harus 100");
echo "    - Quality Score: {$dist->quality_score}\n";
echo "    - Kategori Mutu: {$dist->kategori} (Expected: SANGAT BAIK)\n";
assert($dist->kategori == 'SANGAT BAIK', "Kategori harus SANGAT BAIK");

// 5. Test Auto-Sync Balik ke Kalender Laporan
echo "\n[5] Testing Auto-Sync Balik ke Kalender Laporan...\n";
$kalender->refresh();
echo "    - Kalender Status Review: {$kalender->status_review}\n";
echo "    - Kalender Status Final: {$kalender->status_final}\n";
echo "    - Kalender Quality Score: {$kalender->quality_score}\n";
echo "    - Kalender Masuk Konsolidasi: {$kalender->masuk_konsolidasi} (Expected: YA)\n";
assert($kalender->masuk_konsolidasi == 'YA', "Masuk konsolidasi harus YA ketika APPROVED/FINAL dan 100% lengkap");

// 6. Test Matriks Isu Signifikan (Auto-Pull)
echo "\n[6] Testing Matriks Isu Signifikan (Auto-Pull Flag Signifikan = YA)...\n";
// Buat dummy isu di Rekap Isu & TL
$isuDummy = Sop08RekapIsuTl::updateOrCreate(
    ['issue_id' => 'ISU-TEST-SIG-01'],
    [
        'report_id' => $reportId,
        'periode' => $periode,
        'kode_unit' => '999',
        'nama_unit' => 'KC Test Verifikasi',
        'resident_auditor' => 'Auditor Verifikator',
        'sumber_sop' => 'SOP 05',
        'referensi_sumber' => 'KKA-TEST-01',
        'area' => 'Teller Kas',
        'jenis_isu' => 'INDIKASI FRAUD',
        'risk_level' => 'HIGH',
        'uraian_isu' => 'Selisih fisik kas teller material berulang.',
        'sebab_awal' => 'Ketiadaan dual control',
        'dampak_risiko' => 'Potensi kerugian finansial',
        'rekomendasi' => 'Pemeriksaan investigasi fraud',
        'pic' => 'Pemimpin Cabang',
        'status_tl' => 'OPEN',
        'berulang' => 'YA',
        'flag_signifikan' => 'YA',
        'jalur_tindak_lanjut' => 'SOP-RA-09/10',
    ]
);

$pulledCount = $service->pullIsuSignifikan($reportId);
echo "    - Isu Signifikan yang ditarik: {$pulledCount} item\n";
assert($pulledCount >= 1, "Harus menarik minimal 1 isu signifikan");

$sigIsu = Sop08IsuSignifikan::where('issue_id', 'ISU-TEST-SIG-01')->first();
echo "    - Matriks Isu Ditemukan: {$sigIsu->issue_id} | Jenis: {$sigIsu->jenis_isu} | Jalur: {$sigIsu->jalur_sistem}\n";
assert($sigIsu !== null, "Isu signifikan harus tersimpan di tabel sop08_isu_signifikans");

echo "\n>>> ALL 6 FASE 2 SOP 08 VERIFICATION CHECKS PASSED SUCCESSFULLY! <<<\n";
