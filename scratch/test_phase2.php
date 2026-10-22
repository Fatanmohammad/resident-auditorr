<?php
require 'c:/laragon/www/resident-auditorr/vendor/autoload.php';
$app = require_once 'c:/laragon/www/resident-auditorr/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\ProgramKerjaData;
use App\Models\RiskTrigger\MasterParameter;
use App\Services\RiskTrigger\RiskTriggerService;

echo "========================================================\n";
echo "TEST 1: Master_Parameter 13 Standar Area\n";
echo "========================================================\n";

$allParams = MasterParameter::all();
echo "Total Master Parameters in DB: " . $allParams->count() . "\n";
assert($allParams->count() === 13, "Master Parameter must have 13 standard areas");

$teller = MasterParameter::getForArea('Teller Kas');
assert($teller && $teller->kka_tujuan === 'KKA_Teller_Kas', "Teller Kas maps to KKA_Teller_Kas");
assert($teller->perlu_fisik_default === 'Ya', "Teller Kas perlu fisik Ya");

$pengaduan = MasterParameter::getForArea('Pengaduan');
assert($pengaduan && $pengaduan->kka_tujuan === 'KKA_Pengaduan', "Pengaduan maps to KKA_Pengaduan");
assert($pengaduan->perlu_fisik_default === 'Jika perlu', "Pengaduan perlu fisik Jika perlu");

$cashOpname = MasterParameter::getForArea('Cash Opname');
assert($cashOpname && $cashOpname->kka_tujuan === 'KKA_Fisik', "Cash Opname maps to KKA_Fisik");

echo ">>> TEST 1 PASSED: All 13 Master Parameters verified successfully!\n\n";

echo "========================================================\n";
echo "TEST 2: Assignment Generation & Penomoran Visit ID\n";
echo "========================================================\n";

$admin = User::find(30); // Admin Sistem
$raPoso = User::find(8); // Yan Hamsah (ra, cabang Poso - 003)
$raPalu = User::find(2); // Januar (ra, cabang Palu - 001)

$service = app(RiskTriggerService::class);

// Temukan trigger berstatus RISK TRIGGER VISIT untuk cabang Poso (003)
$triggerPoso = RegisterTrigger::where('kode_unit', '003')
    ->where('keputusan_final', RegisterTrigger::KEPUTUSAN_RISK_TRIGGER_VISIT)
    ->whereDoesntHave('assignmentScope')
    ->first();

if (!$triggerPoso) {
    // Buat manual jika belum ada
    $triggerPoso = $service->createManualTrigger([
        'sumber' => 'Daily Review',
        'source_wp_id' => 'MANUAL-TEST-AS-' . time(),
        'tanggal_trigger' => date('Y-m-d'),
        'kode_unit' => '003',
        'nama_unit' => 'Kantor Cabang Poso',
        'area' => 'Teller Kas',
        'objek_key' => 'Selisih Kas Teller 01',
        'uraian_trigger' => 'Uji penugasan assignment visit teller kas Poso',
        'bukti_awal' => 'Voucher kas dan log sistem',
        'risk_score_sumber' => 15,
        'jumlah_kejadian' => 4,
        'eksposur_nominal' => 25000000,
        'perlu_verifikasi_lapangan' => 'Ya',
        'indikasi_fraud' => 'Tidak',
        'objek_surprise_fisik' => 'Tidak',
    ], $admin);
    $triggerPoso->override_keputusan = 'RISK TRIGGER VISIT';
    $triggerPoso->alasan_override = 'Dibutuhkan visit lapangan segera';
    $triggerPoso->calculateDerivedAttributes();
    $triggerPoso->save();
}

echo "Target Trigger for Assignment: {$triggerPoso->trigger_id} ({$triggerPoso->area} - {$triggerPoso->kode_unit})\n";

// Buat Assignment Scope
$assignmentData = [
    'tanggal_visit' => date('Y-m-d'),
    'ra_pelaksana' => 'Yan Hamsah',
    'reviewer' => 'Admin Sistem',
    'referensi_otorisasi' => 'ST/099/SKAI/X/2026',
    'klasifikasi' => 'Risk Trigger Visit Terarah',
    'batas_scope' => 'Terbatas pada Teller Kas | Periode Uji | Tidak diperluas tanpa persetujuan reviewer',
    'kriteria_selesai' => 'Trigger dinilai terbukti/tidak terbukti; exception diklarifikasi; bukti memadai; keputusan TL, perluasan, atau eskalasi ditetapkan',
    'langkah_tambahan_ra' => 'Lakukan konfirmasi fisik saldo kas pagi hari',
];

$assignment = $service->createAssignmentFromTrigger($triggerPoso, $assignmentData, $admin);

echo "Created Visit ID: {$assignment->visit_id}\n";
assert(str_starts_with($assignment->visit_id, 'RTV-003-'), "Visit ID must start with RTV-003-");
assert($assignment->status_assignment === 'SIAP DILAKSANAKAN', "Status Assignment must be SIAP DILAKSANAKAN");
assert($assignment->perlu_data_unit === 'Ya - permintaan terbatas sesuai scope', "Teller Kas perlu data unit");

// Verifikasi Sinkronisasi Balik pada Register Trigger
$refreshedTrigger = RegisterTrigger::find($triggerPoso->id);
echo "Refreshed Trigger Workflow: {$refreshedTrigger->status_workflow}\n";
assert($refreshedTrigger->status_workflow === 'VISIT DALAM PROSES', "Workflow must change to VISIT DALAM PROSES");

// Verifikasi KPI Ringkasan Control
$statsPoso = $service->getRingkasanControlStats($raPoso);
echo "Ringkasan Control Assignment Siap (Poso): " . $statsPoso['kpi']['assignment_siap'] . "\n";
echo "Ringkasan Control Visit Dalam Proses (Poso): " . $statsPoso['kpi']['visit_dalam_proses'] . "\n";
assert($statsPoso['kpi']['assignment_siap'] >= 1, "Assignment Siap must be >= 1");
assert($statsPoso['kpi']['visit_dalam_proses'] >= 1, "Visit Dalam Proses must be >= 1");

echo ">>> TEST 2 PASSED: Assignment Scope created, Visit ID generated, Workflow synchronized!\n\n";

echo "========================================================\n";
echo "TEST 3: Program Kerja Data Auto-Generation & Sampling Formula\n";
echo "========================================================\n";

$pk = ProgramKerjaData::where('assignment_scope_id', $assignment->id)->first();
assert($pk !== null, "ProgramKerjaData must be auto-generated");
echo "PKA Visit ID: {$pk->visit_id} | Area: {$pk->area} | KKA: {$pk->kka_tujuan}\n";
echo "Sampel Pemicu (J): {$pk->sampel_pemicu}\n";
echo "Sampel Pembanding (K): {$pk->sampel_pembanding}\n";

$expectedPembanding = (int)ceil(sqrt($pk->sampel_pemicu));
assert($pk->sampel_pembanding === max(1, $expectedPembanding), "Sampel Pembanding must equal CEIL(SQRT(J))");

echo "Initial Status Kesiapan: {$pk->status_kesiapan}\n";
assert($pk->status_kesiapan === 'BELUM DIMINTA', "Initial status for area needing unit data must be BELUM DIMINTA");

// Simulasi RA meminta data
$service->updateProgramKerjaData($pk, [
    'tanggal_permintaan' => date('Y-m-d'),
], $raPoso);
$pk->refresh();
echo "Status Kesiapan after Tgl Permintaan: {$pk->status_kesiapan}\n";
assert($pk->status_kesiapan === 'MENUNGGU DATA', "Status must change to MENUNGGU DATA");

// Simulasi RA menerima data
$service->updateProgramKerjaData($pk, [
    'tanggal_diterima' => date('Y-m-d'),
], $raPoso);
$pk->refresh();
echo "Status Kesiapan after Tgl Diterima: {$pk->status_kesiapan}\n";
assert($pk->status_kesiapan === 'SIAP UJI', "Status must change to SIAP UJI");

echo ">>> TEST 3 PASSED: Program Kerja Data sampling formulas and status progression verified!\n\n";

echo "========================================================\n";
echo "TEST 4: RBAC & Branch Scoping (Fase 2)\n";
echo "========================================================\n";

$asPosoCount = AssignmentScope::byBranch($raPoso)->count();
$asPaluCount = AssignmentScope::byBranch($raPalu)->count();
$asAdminCount = AssignmentScope::count();

echo "Assignments visible to RA Poso: {$asPosoCount}\n";
echo "Assignments visible to RA Palu: {$asPaluCount}\n";
echo "Assignments visible to Admin (Global): {$asAdminCount}\n";

assert($asPosoCount >= 1, "RA Poso sees their branch assignment");
assert($asPaluCount === 0, "RA Palu does not see Poso assignments");
assert($asAdminCount >= $asPosoCount, "Admin sees global assignments");

$pkPosoCount = ProgramKerjaData::byBranch($raPoso)->count();
$pkPaluCount = ProgramKerjaData::byBranch($raPalu)->count();
echo "PKA visible to RA Poso: {$pkPosoCount}\n";
echo "PKA visible to RA Palu: {$pkPaluCount}\n";
assert($pkPosoCount >= 1, "RA Poso sees their branch PKA");
assert($pkPaluCount === 0, "RA Palu does not see Poso PKA");

// Simulasi RA update Tanggal Aktual Lapangan
$service->updateAssignmentActualDates($assignment, [
    'mulai_aktual' => date('Y-m-d'),
    'selesai_aktual' => date('Y-m-d'),
], $raPoso);
$assignment->refresh();
echo "Mulai Aktual: {$assignment->mulai_aktual->format('Y-m-d')} | Selesai Aktual: {$assignment->selesai_aktual->format('Y-m-d')}\n";
assert($assignment->mulai_aktual !== null && $assignment->selesai_aktual !== null, "Actual dates successfully updated");

echo ">>> TEST 4 PASSED: RBAC and branch data isolation verified 100%!\n\n";

echo "========================================================\n";
echo "ALL FASE 2 AUTOMATED TESTS PASSED SUCCESSFULLY!\n";
echo "========================================================\n";
