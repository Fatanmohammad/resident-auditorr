<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\ProgramKerjaData;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;
use App\Models\RiskTrigger\Kka\KkaKredit;
use App\Models\RiskTrigger\SimpulanTl;
use App\Models\RiskTrigger\KlarifikasiBukti;
use App\Models\User;
use App\Services\RiskTrigger\RiskTriggerService;

echo "=== MEMULAI TEST VERIFIKASI FASE 4 (SOP 06) ===\n\n";

$service = app(RiskTriggerService::class);
$adminUser = User::where('role', 'admin')->first() ?? User::first();
$raUser = User::where('role', 'ra')->first();

echo "Admin User: {$adminUser->name} ({$adminUser->role})\n";
if ($raUser) {
    echo "RA User: {$raUser->name} ({$raUser->role}, Cabang: {$raUser->cabang_id})\n";
}

// 1. Test syncAllSimpulanTl
echo "\n--- 1. Test Sinkronisasi Seluruh Simpulan_TL ---\n";
$synced = $service->syncAllSimpulanTl();
echo "Simpulan_TL disinkronkan / dibuat: {$synced}\n";
$totalSimpulan = SimpulanTl::count();
echo "Total baris di Simpulan_TL saat ini: {$totalSimpulan}\n";
assert($totalSimpulan > 0, "Harus ada minimal 1 baris di Simpulan_TL");

// Ambil 1 record SimpulanTl untuk pengujian mendalam
$simTl = SimpulanTl::first();
$visitId = $simTl->visit_id;
echo "Menguji Visit ID: {$visitId}\n";

// 2. Test Pull Exceptions dari 8 KKA Area
echo "\n--- 2. Test Tarik Cepat Exception KKA ---\n";
// Pastikan ada sampel exception di KKA Kredit untuk visit_id ini
$kkaKredit = KkaKredit::where('visit_id', $visitId)->first();
if (!$kkaKredit) {
    $kkaKredit = new KkaKredit();
    $kkaKredit->assignment_scope_id = $simTl->assignment_scope_id;
    $kkaKredit->visit_id = $visitId;
    $kkaKredit->kode_unit = $simTl->kode_unit;
    $kkaKredit->nomor_rekening = 'TEST-KREDIT-001';
    $kkaKredit->nama_debitur = 'Debitur Uji Coba Fase 4';
    $kkaKredit->plafon = 500000000;
    $kkaKredit->baki_debet = 450000000;
    $kkaKredit->kolektibilitas = '3 - Kurang Lancar';
    $kkaKredit->exception = 'Ya';
    $kkaKredit->catatan_pemeriksaan = 'Ditemukan restrukturisasi kredit tanpa persetujuan komite';
    $kkaKredit->created_by = $adminUser->id;
    $kkaKredit->save();
} else {
    $kkaKredit->exception = 'Ya';
    $kkaKredit->save();
}

$pulled = $service->pullExceptionsToKlarifikasi($visitId, $adminUser);
echo "Jumlah exception baru yang berhasil ditarik: {$pulled}\n";

$klarList = KlarifikasiBukti::where('visit_id', $visitId)->get();
echo "Total baris klarifikasi untuk {$visitId}: {$klarList->count()}\n";
assert($klarList->count() > 0, "Harus ada baris klarifikasi setelah pull exception");

// 3. Test Formula Status Klarifikasi (Kolom H)
echo "\n--- 3. Test Formula Status Klarifikasi (Kolom H) ---\n";
// Pastikan ada baris yang Open
$firstKlar = KlarifikasiBukti::where('visit_id', $visitId)->first();
$firstKlar->status_klarifikasi = 'Open';
$firstKlar->save();

$simTl->refresh();
echo "Status Klarifikasi saat masih ada yang Open: '{$simTl->status_klarifikasi}'\n";
assert($simTl->status_klarifikasi === 'MASIH OPEN', "Harus MASIH OPEN jika ada baris != Closed");

// Set seluruh klarifikasi menjadi Closed
KlarifikasiBukti::where('visit_id', $visitId)->update(['status_klarifikasi' => 'Closed']);
$simTl->refresh();
$simTl->calculateDerivedAttributes();
echo "Status Klarifikasi setelah seluruhnya Closed: '{$simTl->status_klarifikasi}'\n";
assert($simTl->status_klarifikasi === 'SELESAI', "Harus SELESAI jika seluruh baris Closed");

// 4. Test Formula Status Closure (Kolom W)
echo "\n--- 4. Test Formula Status Closure (Kolom W - Sel W6) ---\n";
// Case 4A: DRAFT (Belum FINAL)
$simTl->status_review = 'DRAFT';
$simTl->simpulan_akhir = 'Telah dilakukan pengujian sampling kredit.';
$simTl->klasifikasi_hasil = 'Temuan';
$simTl->calculateDerivedAttributes();
echo "Case 4A (Review DRAFT): '{$simTl->status_closure}'\n";
assert($simTl->status_closure === 'BELUM DITUTUP', "Harus BELUM DITUTUP jika status_review != FINAL");

// Case 4B: FINAL tapi TL Belum Lengkap
$simTl->status_review = 'FINAL';
$simTl->tindak_lanjut_awal = null; // Kosongkan TL
$simTl->calculateDerivedAttributes();
echo "Case 4B (FINAL tapi TL kosong): '{$simTl->status_closure}'\n";
assert($simTl->status_closure === 'TL BELUM LENGKAP', "Harus TL BELUM LENGKAP jika Temuan tapi TL kosong");

// Case 4C: FINAL, TL Lengkap, tapi Klarifikasi MASIH OPEN (ubah 1 row di DB menjadi Open)
$simTl->tindak_lanjut_awal = 'Akan dilakukan review dokumen jaminan';
$simTl->pic_unit = 'Pemimpin Cabang';
$simTl->due_date = '2026-11-30';
$firstKlar = KlarifikasiBukti::where('visit_id', $visitId)->first();
$firstKlar->status_klarifikasi = 'Open';
$firstKlar->save();

$simTl->calculateDerivedAttributes();
echo "Case 4C (FINAL, TL lengkap, tapi Klarifikasi OPEN): '{$simTl->status_closure}'\n";
assert($simTl->status_closure === 'KLARIFIKASI BELUM SELESAI', "Harus KLARIFIKASI BELUM SELESAI");

// Case 4D: FINAL, TL Lengkap, Klarifikasi SELESAI (ubah kembali row di DB menjadi Closed)
$firstKlar->status_klarifikasi = 'Closed';
$firstKlar->save();

$simTl->calculateDerivedAttributes();
echo "Case 4D (Seluruh syarat terpenuhi): '{$simTl->status_closure}'\n";
assert($simTl->status_closure === 'CLOSED/DITERUSKAN', "Harus CLOSED/DITERUSKAN jika semua syarat terpenuhi");
$simTl->save();

// 5. Test Sinkronisasi ke RegisterTrigger Workflow
echo "\n--- 5. Test Sinkronisasi Balik ke Register_Trigger Workflow ---\n";
$trigger = RegisterTrigger::where('trigger_id', $simTl->trigger_id)->first();
if ($trigger) {
    echo "Status Workflow RegisterTrigger: '{$trigger->status_workflow}'\n";
    assert($trigger->status_workflow === 'SELESAI/DALAM TL', "Status Workflow harus SELESAI/DALAM TL");
}

// 6. Test Ringkasan Control KPI B14, B15, B16 & Matrix Area
echo "\n--- 6. Test Sinkronisasi ke Ringkasan Control Dashboard ---\n";
$stats = $service->getRingkasanControlStats($adminUser);
echo "KPI Simpulan Tersedia (B14): {$stats['kpi']['simpulan_tersedia']}\n";
echo "KPI Masih Open / Belum Ditutup (B15): {$stats['kpi']['masih_open']}\n";
echo "KPI Overdue SLA (B16): {$stats['kpi']['overdue_sla']}\n";

$matrixKredit = collect($stats['matrix_area'])->firstWhere('area', 'Kredit');
echo "Matrix Kredit Exception Visit Count: " . ($matrixKredit['total_exception'] ?? 0) . "\n";

echo "\n=== SELURUH TEST VERIFIKASI FASE 4 BERHASIL 100% ===\n";
