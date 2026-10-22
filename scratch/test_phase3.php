<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\ProgramKerjaData;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;
use App\Models\RiskTrigger\Kka\KkaTellerKas;
use App\Models\RiskTrigger\Kka\KkaDpkLayanan;
use App\Models\RiskTrigger\Kka\KkaKredit;
use App\Models\RiskTrigger\Kka\KkaBiayaBeban;
use App\Models\RiskTrigger\Kka\KkaAtm;
use App\Models\RiskTrigger\Kka\KkaPengaduan;
use App\Models\RiskTrigger\Kka\KkaFisik;
use App\Models\RiskTrigger\Kka\KkaObservasi;
use App\Models\User;
use App\Services\RiskTrigger\RiskTriggerService;

echo "=== MEMULAI TEST VERIFIKASI FASE 3: KKA RISK TRIGGER & 8 KKA AREA ===\n\n";

$service = app(RiskTriggerService::class);

// 1. Verifikasi KKA Induk dari Assignment yang sudah ada
$assignments = AssignmentScope::all();
echo "[1] Memeriksa KKA Induk untuk " . $assignments->count() . " assignment existing...\n";
foreach ($assignments as $a) {
    $kka = $service->ensureKkaInduk($a);
    echo "  -> Visit ID: {$kka->visit_id} | Area: {$kka->area} | Pertanyaan Audit: {$kka->pertanyaan_audit}\n";
    assert($kka->pertanyaan_audit === "Apakah risk trigger pada {$kka->area} terbukti, berulang, material, atau memerlukan tindak lanjut/perluasan?");
}
echo "  [OK] Seluruh KKA Induk valid sesuai rumus 20 kolom Excel.\n\n";

// Ambil assignment pertama untuk pengujian sampel
$targetAssignment = $assignments->first();
$visitId = $targetAssignment->visit_id;
echo "[2] Menyiapkan Test Baris Sampel untuk Visit ID: {$visitId} (Unit: {$targetAssignment->kode_unit})\n";

// Bersihkan data sampel uji sebelumnya jika ada
KkaTellerKas::where('visit_id', $visitId)->delete();
KkaDpkLayanan::where('visit_id', $visitId)->delete();
KkaKredit::where('visit_id', $visitId)->delete();
KkaBiayaBeban::where('visit_id', $visitId)->delete();
KkaAtm::where('visit_id', $visitId)->delete();
KkaPengaduan::where('visit_id', $visitId)->delete();
KkaFisik::where('visit_id', $visitId)->delete();
KkaObservasi::where('visit_id', $visitId)->delete();

$service->recalculateExceptionArea($visitId);
$kka = RiskTriggerKkaInduk::where('visit_id', $visitId)->first();
echo "  -> Initial Exception Area: {$kka->exception_area} (Harus 0)\n";
assert($kka->exception_area === 0);

// Mock User Admin & RA
$adminUser = User::where('role', 'admin')->first() ?: new User(['name' => 'Admin Test', 'role' => 'admin']);
$adminUser->id = $adminUser->id ?: 1;

// 2.A Test KKA_Teller_Kas
echo "\n[2.A] Test KKA_Teller_Kas (24 Kolom)...\n";
$teller = $service->storeAreaRow('KKA_Teller_Kas', $visitId, [
    'no_referensi' => 'TK-001',
    'tanggal_transaksi' => '2026-10-20',
    'redaksi' => 'Uji Selisih Kas Teller',
    'd_k' => 'D',
    'nominal' => 5000000,
    'user_id_transaksi' => 'TLR01',
    'jenis_transaksi' => 'Tarik Tunai',
    'nama_transaksi' => 'Penarikan Kas',
    'nominal_pasangan' => 5000000,
    'bukti_kas_voucher' => 4500000, // Selisih 4.500.000 - 5.000.000 = -500.000 -> EXCEPTION
    'reversal_match' => 'Ya',
    'otorisasi_sesuai' => 'Ya',
    'analisis_pola' => 'Voucher fisik kurang Rp 500.000 dibanding pembukuan',
    'referensi_bukti' => 'Voucher Kas No. 01',
    'simpulan' => 'Terdapat selisih kas fisik',
], $adminUser);

echo "  -> Selisih: {$teller->selisih} (Expected: -500000.00)\n";
echo "  -> Exception: {$teller->exception} (Expected: Ya)\n";
echo "  -> Status Uji: {$teller->status_uji} (Expected: EXCEPTION)\n";
assert(floatval($teller->selisih) == -500000);
assert($teller->exception === 'Ya');
assert($teller->status_uji === 'EXCEPTION');

// 2.B Test KKA_DPK_Layanan
echo "\n[2.B] Test KKA_DPK_Layanan (22 Kolom)...\n";
$dpk = $service->storeAreaRow('KKA_DPK_Layanan', $visitId, [
    'no_cif_rekening' => 'CIF-9901',
    'produk_layanan' => 'Tabungan Bisnis',
    'nama_nasabah' => 'PT Mandiri Sukses',
    'risk_nasabah' => 'High',
    'pep_high_risk' => 'Ya', // PEP Ya -> EXCEPTION
    'dokumen_wajib' => 10,
    'dokumen_tersedia' => 8, // 8 / 10 = 80% -> EXCEPTION
    'otorisasi_sesuai' => 'Ya',
    'profil_transaksi_sesuai' => 'Ya',
    'analisis_kyc_risiko' => 'Dokumen akta belum diperbarui',
    'referensi_bukti' => 'Berkas CIF 9901',
    'simpulan' => 'Dokumen belum lengkap',
], $adminUser);

echo "  -> Kelengkapan %: {$dpk->kelengkapan_persen}% (Expected: 80.00%)\n";
echo "  -> Exception: {$dpk->exception} (Expected: Ya)\n";
echo "  -> Status Uji: {$dpk->status_uji} (Expected: EXCEPTION)\n";
assert(floatval($dpk->kelengkapan_persen) == 80.00);
assert($dpk->exception === 'Ya');

// 2.C Test KKA_Kredit
echo "\n[2.C] Test KKA_Kredit (24 Kolom)...\n";
$kredit = $service->storeAreaRow('KKA_Kredit', $visitId, [
    'no_kredit_debitur' => 'KRD-8812',
    'nama_debitur' => 'Budi Santoso',
    'produk' => 'Kredit Modal Kerja',
    'plafond' => 1000000000,
    'outstanding' => 800000000,
    'nilai_agunan' => 600000000, // 600jt / 800jt = 0.75 (< 1.0) -> Under-collateral Exception!
    'nilai_asuransi' => 800000000, // 800jt / 800jt = 1.0
    'persetujuan_sesuai' => 'Ya',
    'pencairan_sesuai' => 'Ya',
    'dokumen_lengkap' => 'Ya',
    'analisis_kredit' => 'Nilai pengikatan agunan di bawah baki debet',
    'referensi_bukti' => 'Laporan Appraisal 2026',
    'simpulan' => 'Coverage agunan 75% di bawah batas 100%',
], $adminUser);

echo "  -> Coverage Agunan: " . ($kredit->coverage_agunan * 100) . "% (Expected: 75.00%)\n";
echo "  -> Exception: {$kredit->exception} (Expected: Ya)\n";
assert(floatval($kredit->coverage_agunan) == 0.75);
assert($kredit->exception === 'Ya');

// 2.D Test KKA_Biaya_Beban
echo "\n[2.D] Test KKA_Biaya_Beban (24 Kolom)...\n";
$biaya = $service->storeAreaRow('KKA_Biaya_Beban', $visitId, [
    'gl_rekening' => '512.01',
    'no_voucher' => 'VCR-771',
    'tanggal' => '2026-10-18',
    'redaksi' => 'Biaya Promosi Cabang',
    'nominal_buku' => 15000000,
    'nominal_invoice' => 12000000,
    'nominal_disetujui' => 12000000, // Selisih 15jt - 12jt = 3jt -> EXCEPTION
    'anggaran_tersedia' => 'Ya',
    'otorisasi_sesuai' => 'Ya',
    'pajak_sesuai' => 'Ya',
    'barang_jasa_diterima' => 'Ya',
    'transaksi_berulang' => 'Tidak',
    'analisis_kewajaran' => 'Pembukuan melebihi nilai invoice yang disetujui',
    'referensi_bukti' => 'Invoice & Voucher 771',
    'simpulan' => 'Terdapat selisih Rp 3.000.000',
], $adminUser);

echo "  -> Selisih Biaya: {$biaya->selisih} (Expected: 3000000.00)\n";
echo "  -> Exception: {$biaya->exception} (Expected: Ya)\n";
assert(floatval($biaya->selisih) == 3000000);
assert($biaya->exception === 'Ya');

// 2.E Test KKA_ATM
echo "\n[2.E] Test KKA_ATM (23 Kolom)...\n";
$atm = $service->storeAreaRow('KKA_ATM', $visitId, [
    'objek_jenis_kartu_atm' => 'Kartu ATM Instan GPN',
    'no_referensi' => 'ATM-BATCH-01',
    'qty_buku' => 100,
    'qty_fisik' => 95, // Selisih Qty 95 - 100 = -5 -> EXCEPTION
    'qty_pin_mailer' => 100, // Fisik 95 != PIN 100 -> EXCEPTION
    'harga_satuan' => 15000,
    'custody_sesuai' => 'Ya',
    'akses_log_sesuai' => 'Ya',
    'analisis_rekonsiliasi' => 'Fisik kartu kurang 5 pcs dari catatan',
    'referensi_bukti' => 'BA Opname Kartu ATM',
    'simpulan' => 'Terdapat selisih 5 fisik kartu',
], $adminUser);

echo "  -> Selisih Qty: {$atm->selisih_qty} (Expected: -5)\n";
echo "  -> Selisih Nilai: {$atm->selisih_nilai} (Expected: -75000)\n";
echo "  -> Exception: {$atm->exception} (Expected: Ya)\n";
assert($atm->selisih_qty == -5);
assert($atm->selisih_nilai == -75000);
assert($atm->exception === 'Ya');

// 2.F Test KKA_Pengaduan
echo "\n[2.F] Test KKA_Pengaduan (21 Kolom)...\n";
$pengaduan = $service->storeAreaRow('KKA_Pengaduan', $visitId, [
    'complaint_id' => 'CMP-001',
    'tanggal_terima' => '2026-10-01',
    'kategori' => 'ATM Tertelan',
    'uraian_pengaduan' => 'Uang tidak keluar tapi saldo terpotong',
    'sla_hari' => 5,
    'tanggal_selesai' => '2026-10-15', // Aging: 14 hari > SLA 5 hari -> EXCEPTION
    'status_penyelesaian' => 'Selesai',
    'bukti_penyelesaian' => 'Ya',
    'berulang' => 'Tidak',
    'analisis_sla' => 'Penyelesaian melebihi SLA 5 hari kerja',
    'referensi_bukti' => 'Tiket Helpdesk 001',
    'simpulan' => 'Penyelesaian terlambat 9 hari kerja',
], $adminUser);

echo "  -> Aging Hari: {$pengaduan->aging_hari} hari (Expected: 14)\n";
echo "  -> Exception: {$pengaduan->exception} (Expected: Ya)\n";
assert($pengaduan->aging_hari == 14);
assert($pengaduan->exception === 'Ya');

// 2.G Test KKA_Fisik
echo "\n[2.G] Test KKA_Fisik (22 Kolom)...\n";
$fisik = $service->storeAreaRow('KKA_Fisik', $visitId, [
    'jenis_objek' => 'Fisik Uang Kas Khazanah',
    'identitas_lokasi_seri' => 'Brankas Utama KC',
    'qty_sistem' => 100000000,
    'qty_catatan' => 100000000,
    'qty_fisik' => 100000000, // Cocok -> Tidak Exception
    'harga_satuan' => 1,
    'kondisi_custody' => 'Sesuai',
    'analisis_rekonsiliasi' => 'Fisik kas khazanah cocok sempurna dengan GL',
    'referensi_bukti' => 'BA Cash Opname 20261021',
    'simpulan' => 'Kas fisik sesuai pembukuan',
], $adminUser);

echo "  -> Selisih Fisik: {$fisik->selisih_qty} (Expected: 0)\n";
echo "  -> Exception: {$fisik->exception} (Expected: Tidak)\n";
echo "  -> Status Uji: {$fisik->status_uji} (Expected: SESUAI)\n";
assert($fisik->selisih_qty == 0);
assert($fisik->exception === 'Tidak');
assert($fisik->status_uji === 'SESUAI');

// 2.H Test KKA_Observasi
echo "\n[2.H] Test KKA_Observasi (18 Kolom)...\n";
$obs = $service->storeAreaRow('KKA_Observasi', $visitId, [
    'kategori' => 'CCTV & Keamanan',
    'titik_kontrol_lokasi' => 'CCTV Ruang Khazanah',
    'kriteria_standar' => 'Kamera playback aktif minimal 60 hari',
    'kondisi_hasil_observasi' => 'Kamera mati, tidak merekam selama 3 hari terakhir',
    'uji_fungsi_playback' => 'Tidak Berfungsi', // Tidak Berfungsi -> EXCEPTION
    'akses_kebersihan_aman' => 'Tidak Sesuai',
    'rating_1_5' => 1, // Rating <= 2 -> EXCEPTION
    'analisis_gap' => 'Blind spot rekaman khazanah selama 3 hari kerja',
    'referensi_bukti_foto' => 'Foto Monitor DVR 01',
    'simpulan' => 'Kamera rusak, berisiko tinggi',
], $adminUser);

echo "  -> Exception: {$obs->exception} (Expected: Ya)\n";
echo "  -> Status Uji: {$obs->status_uji} (Expected: EXCEPTION)\n";
assert($obs->exception === 'Ya');
assert($obs->status_uji === 'EXCEPTION');

// 3. Verifikasi Real-Time Exception Area pada KKA Induk
echo "\n[3] Memeriksa Real-Time Exception Area (Kolom P) pada KKA Induk...\n";
$kka->refresh();
echo "  -> Total Exception Area: {$kka->exception_area}\n";
// Exception yang Ya: Teller (1) + DPK (1) + Kredit (1) + Biaya (1) + ATM (1) + Pengaduan (1) + Observasi (1) = 7
assert($kka->exception_area === 7);
echo "  [OK] COUNTIFS lintas 8 KKA Area bernilai 7 (Sesuai 100%!).\n";

// 4. Test RBAC Status Review Lock
echo "\n[4] Memeriksa Aturan RBAC & Penguncian Status Review...\n";
// Update oleh Admin menjadi FINAL
$service->updateKkaInduk($kka, [
    'status_review' => 'FINAL',
    'trigger_terbukti' => 'Ya',
    'perlu_perluasan' => 'Ya',
    'alasan_perluasan' => 'Berdasarkan temuan di 7 area pengujian lapangan',
    'simpulan_ra' => 'Risiko terbukti material dan perlu audit perluasan reguler',
], $adminUser);

$kka->refresh();
assert($kka->status_review === 'FINAL');
echo "  -> Status Review berhasil diubah menjadi FINAL oleh Admin.\n";

// Cek hak akses RA saat FINAL
$raUser = User::where('role', 'ra')->first() 
       ?: new User(['name' => 'RA Test', 'role' => 'ra', 'cabang_id' => 1]);

$canModifyRa = $service->canModifyKka($kka, $raUser);
echo "  -> Hak modifikasi RA saat status FINAL: " . ($canModifyRa ? 'BISA (SALAH)' : 'TERKUNCI (BENAR)') . "\n";
assert($canModifyRa === false);

echo "\n=== SEMUA 4 PENGUJIAN FASE 3 BERHASIL 100% SUKSES! ===\n";
