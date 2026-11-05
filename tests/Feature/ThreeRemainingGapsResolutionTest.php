<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\DailyRegisterChecklist;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Http\Controllers\DailyRegister\PhysicalKkaController;
use App\Services\Offsite\DumpCbsParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class ThreeRemainingGapsResolutionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected User $adminUser;
    protected Cabang $cabang;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-099'], [
            'nama_cabang' => 'Cabang Audit Khusus',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '099'], [
            'unit_name' => 'KC Audit Khusus',
            'unit_type' => 'KC',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_audit_khusus@banksulteng.co.id'], [
            'nip' => '999001',
            'name' => 'RA Audit Khusus',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
            'unit_code' => '099',
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_skai_khusus@banksulteng.co.id'], [
            'nip' => '999002',
            'name' => 'Admin SKAI Khusus',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    // =========================================================================
    // CELAH 1: LOGIKA HARDCODE KEYWORD "TPP" DI DAILY REGISTER
    // =========================================================================

    public function test_daily_register_detects_tpp_keyword_and_updates_checklist(): void
    {
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $this->unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 85000000,
            'risk_awal' => 'Moderate',
            'jenis_exception_awal' => 'Transaksi Tertentu',
            'deskripsi' => 'Pembayaran Payroll TPP Pemda Sulteng Bulan Ini',
        ]);

        $response = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $finding->id));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['has_tpp'], 'Sistem wajib mendeteksi has_tpp = true saat terdapat keyword TPP.');
        $this->assertFalse($data['has_cek'], 'has_cek harus false bila tidak ada keyword cek.');

        // Item No 1 Sub-Area A1 harus diawali dengan "Terdapat TPP: "
        $firstChecklist = collect($data['checklists'])->first(fn($c) => $c['item_no'] == 1 && $c['sub_area_code'] === 'A1');
        $this->assertNotNull($firstChecklist);
        $this->assertStringStartsWith('Terdapat TPP: ', $firstChecklist['item_checklist']);
    }

    public function test_daily_register_detects_both_cek_and_tpp(): void
    {
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $this->unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 120000000,
            'risk_awal' => 'High',
            'jenis_exception_awal' => 'Kliring Cek',
            'deskripsi' => 'Pencairan Cek Kliring Dana TPP Dinas Kesehatan',
        ]);

        $response = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $finding->id));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['has_cek'], 'has_cek harus true');
        $this->assertTrue($data['has_tpp'], 'has_tpp harus true');

        $firstChecklist = collect($data['checklists'])->first(fn($c) => $c['item_no'] == 1 && $c['sub_area_code'] === 'A1');
        $this->assertNotNull($firstChecklist);
        $this->assertStringStartsWith('Terdapat Cek & TPP: ', $firstChecklist['item_checklist']);
    }

    public function test_cbs_description_fallback_reading(): void
    {
        // 1. Finding with only rincian filled
        $f1 = DailyRegisterFinding::create([
            'kode_unit' => $this->unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 25000000,
            'risk_awal' => 'Moderate',
            'rincian' => 'Tunjangan Perbaikan Penghasilan (TPP) Guru',
            'deskripsi' => null,
        ]);

        $this->assertStringContainsString('TPP', $f1->ket_tx);

        $response = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $f1->id));
        $this->assertTrue($response->json('has_tpp'));

        // 2. DumpCbsParser populates deskripsi and rincian and flags TPP
        $tempCsv = tempnam(sys_get_temp_dir(), 'cbs_');
        file_put_contents($tempCsv, "TGL_TX,KET_TX,JUMLAH_TX\n2026-11-05,TRANSFER DANA TPP ASN PROVINSI,150000000\n");

        $parser = new DumpCbsParser();
        $res = $parser->parse($tempCsv, $this->unit->unit_code, true, $this->unit->unit_name);
        @unlink($tempCsv);

        $created = DailyRegisterFinding::where('kode_unit', $this->unit->unit_code)
            ->where('deskripsi', 'like', '%TPP%')
            ->first();
        $this->assertNotNull($created);
        $this->assertEquals('KKA_Teller_Kas', $created->source_sheet);
        $this->assertEquals('Transaksi TPP', $created->jenis_exception_awal);
        $this->assertStringContainsString('TPP', $created->rincian);
    }

    // =========================================================================
    // CELAH 2: TOMBOL TAMBAH MANDIRI PEMERIKSAAN KAS KECIL & KAS / KHASANAH
    // =========================================================================

    public function test_ra_can_add_manual_kas_kecil_examination(): void
    {
        $payload = [
            'kode_unit' => $this->unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'sub_area' => 'A2',
            'nominal_terkait' => 15000000,
            'no_arsip' => 'BA-KK-099-20261105-001',
            'deskripsi' => 'Pemeriksaan Mandiri Kas Kecil Semester II',
        ];

        $response = $this->actingAs($this->raUser)->postJson(route('daily-register.store-mandiri'), $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $findingId = $response->json('finding.id');
        $finding = DailyRegisterFinding::findOrFail($findingId);

        $this->assertEquals('KKA_Teller_Kas', $finding->source_sheet);
        $this->assertEquals(['A2'], $finding->selected_sub_areas);
        $this->assertEquals('BA-KK-099-20261105-001', $finding->no_arsip);
        $this->assertEquals(DailyRegisterFinding::STATUS_BELUM_DIUJI, $finding->review_status);

        // Langsung dapat diuji melalui reviewData
        $revRes = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $findingId));
        $revRes->assertStatus(200);
        $this->assertEquals(['A2'], $revRes->json('finding.selected_sub_areas'));
    }

    public function test_ra_can_add_manual_kas_khasanah_and_print_matriks_even_with_nihil_cbs(): void
    {
        // 1. Tambah Mandiri Kas / Khasanah (A3)
        $payload = [
            'kode_unit' => $this->unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'sub_area' => 'A3',
            'nominal_terkait' => 250000000,
            'no_arsip' => 'BA-KH-099-20261105-001',
            'deskripsi' => 'Pemeriksaan Fisik Kas Khasanah Khusus Mandiri',
        ];

        $res = $this->actingAs($this->raUser)->postJson(route('daily-register.store-mandiri'), $payload);
        $res->assertStatus(200);
        $findingId = $res->json('finding.id');

        // 2. RA menjawab checklist dan menyimpan agar selesai
        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers = $checklists->map(fn($c) => [
            'checklist_id' => $c->id,
            'jawaban' => 'Ya',
            'keterangan' => 'Sesuai fisik opname khasanah'
        ])->toArray();

        $updatePayload = [
            'selected_sub_areas' => ['A3'],
            'bukti_referensi' => 'BA Opname Khasanah & Register Brankas',
            'checklist_answers' => $answers,
            'critical_trigger' => 'Tidak',
        ];

        $upRes = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $findingId), $updatePayload);
        $upRes->assertStatus(200);

        $fUpdated = DailyRegisterFinding::findOrFail($findingId);
        $this->assertEquals(DailyRegisterFinding::STATUS_SELESAI, $fUpdated->review_status);

        // 3. Print Arsip Matriks tetap bisa dicetak meskipun data CBS nihil
        $printRes = $this->actingAs($this->raUser)->get(route('daily-register.arsip-matriks.print', [
            'source_sheet' => 'KKA_Teller_Kas',
            'kode_unit' => $this->unit->unit_code,
        ]));
        $printRes->assertStatus(200);
        $printRes->assertSee('BA-KH-099-20261105-001');
        $printRes->assertSee('Pemeriksaan Fisik Kas Khasanah Khusus Mandiri');
    }

    // =========================================================================
    // CELAH 3: RINCIAN SPESIFIK PADA MODUL OBSERVASI LINGKUNGAN
    // =========================================================================

    public function test_observasi_lingkungan_contains_all_structured_specific_items(): void
    {
        $items = PhysicalKkaController::getDefaultObsItems();
        $this->assertNotEmpty($items);

        $itemAreas = collect($items)->pluck('area')->toArray();

        // 1. Spesifik 4 Titik Alarm
        $this->assertContains('Titik Alarm | Ruang Pimpinan Cabang', $itemAreas);
        $this->assertContains('Titik Alarm | Ruang Wakil Pimpinan Cabang', $itemAreas);
        $this->assertContains('Titik Alarm | Counter Teller', $itemAreas);
        $this->assertContains('Titik Alarm | Meja Customer Service (CS)', $itemAreas);

        // 2. Spesifik 5 Area Terbatas
        $this->assertContains('Area Terbatas | Counter Teller', $itemAreas);
        $this->assertContains('Area Terbatas | Customer Service (CS)', $itemAreas);
        $this->assertContains('Area Terbatas | Ruang Arsip', $itemAreas);
        $this->assertContains('Area Terbatas | Ruang Server', $itemAreas);
        $this->assertContains('Area Terbatas | Khasanah', $itemAreas);

        // 3. Matriks 9 Titik CCTV dengan Backup Minimal 90 Hari (E.1.5)
        $cctvTitik = [
            'Matriks CCTV (E.1.5) | Titik 1: Depan Banking Hall',
            'Matriks CCTV (E.1.5) | Titik 2: Banking Hall',
            'Matriks CCTV (E.1.5) | Titik 3: Gardu ATM',
            'Matriks CCTV (E.1.5) | Titik 4: Gudang Arsip',
            'Matriks CCTV (E.1.5) | Titik 5: Khasanah',
            'Matriks CCTV (E.1.5) | Titik 6: Teller',
            'Matriks CCTV (E.1.5) | Titik 7: CS',
            'Matriks CCTV (E.1.5) | Titik 8: Ruang Server',
            'Matriks CCTV (E.1.5) | Titik 9: Kredit',
        ];

        foreach ($cctvTitik as $t) {
            $this->assertContains($t, $itemAreas);
            $cctvItem = collect($items)->firstWhere('area', $t);
            $this->assertStringContainsString('90 Hari', $cctvItem['kriteria']);
            $this->assertEquals('Wajib Min. 90 Hari', $cctvItem['backup_90_hari']);
        }

        // 4. Mutasi & Buku Mutasi Security serta Absensi Karyawan
        $this->assertContains('Security | Mutasi & Buku Mutasi Security', $itemAreas);
        $this->assertContains('Kepegawaian | Absensi Karyawan', $itemAreas);

        // 5. Section Khusus ATM (E.4.4): Branding Interior Kantor vs Fasilitas ATM
        $this->assertContains('Branding Interior Kantor | Lisplank & Signage Kantor', $itemAreas);
        $this->assertContains('Branding Interior Kantor | Sandblast Kaca Kantor', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Fasilitas UPS ATM', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Fasilitas Stavol / Stabilizer', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Kunci Kombinasi Brankas ATM (Dual Control > 1 Kunci)', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Lolipop (Neon Box) ATM', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Lisplank Fasilitas ATM', $itemAreas);
        $this->assertContains('Fasilitas ATM (E.4.4) | Sandblast & Kaca Bilik ATM', $itemAreas);
    }

    public function test_observasi_lingkungan_page_renders_and_saves_structured_items(): void
    {
        // 1. Akses form KKA Fisik Tab Observasi
        $response = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.index'));
        $response->assertStatus(200);
        $response->assertSee('SPESIFIK 4 TITIK ALARM');
        $response->assertSee('SPESIFIK 5 AREA TERBATAS');
        $response->assertSee('MATRIKS 9 TITIK CCTV (ACUAN E.1.5');
        $response->assertSee('Wajib Min. 90 Hari');
        $response->assertSee('MUTASI &amp; BUKU MUTASI SECURITY', false);
        $response->assertSee('BRANDING INTERIOR KANTOR');
        $response->assertSee('SECTION KHUSUS ATM (ACUAN STANDAR AUDIT E.4.4)');

        // 2. Simpan form Observasi Lingkungan
        $defaultItems = PhysicalKkaController::getDefaultObsItems();
        $obsDetailsPayload = [];
        foreach ($defaultItems as $idx => $item) {
            $obsDetailsPayload[] = [
                'area_observasi' => $item['area'],
                'kriteria_diharapkan' => $item['kriteria'],
                'hasil_observasi' => 'Sesuai',
                'kondisi_aktual' => 'Kondisi operasional fisik baik dan sesuai standar',
                'tanggal_uji' => Carbon::now()->toDateString(),
            ];
        }

        // Tandai 1 item CCTV sebagai Tidak Sesuai (exception)
        $obsDetailsPayload[9]['hasil_observasi'] = 'Tidak Sesuai';
        $obsDetailsPayload[9]['penyebab'] = 'Backup rekaman CCTV hanya tersimpan 30 hari karena harddisk penuh';
        $obsDetailsPayload[9]['impact'] = 4;
        $obsDetailsPayload[9]['likelihood'] = 3;
        $obsDetailsPayload[9]['critical_trigger'] = 'Tidak';

        $submitPayload = [
            'no_arsip' => 'ARSIP-OBS-POSO-001',
            'saldo_kas_olibs' => 0,
            'obs_details' => $obsDetailsPayload,
        ];

        $postRes = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $submitPayload);
        $postRes->assertSessionHas('success');

        $kka = PhysicalKka::where('no_arsip', 'ARSIP-OBS-POSO-001')->first();
        $this->assertNotNull($kka);
        $this->assertEquals(count($defaultItems), $kka->obsDetails->count());

        $cctvException = $kka->obsDetails->firstWhere('area_observasi', 'Matriks CCTV (E.1.5) | Titik 1: Depan Banking Hall');
        $this->assertNotNull($cctvException);
        $this->assertEquals('Exception', $cctvException->status_uji);
        $this->assertEquals(12, $cctvException->risk_score);
        $this->assertEquals('Tinggi', $cctvException->severity_level);

        // 3. Print KKA Observasi Lingkungan
        $printObsRes = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-observasi', $kka->id));
        $printObsRes->assertStatus(200);
        $printObsRes->assertSee('KERTAS KERJA OBSERVASI LINGKUNGAN, PERIMETER KEAMANAN &amp; FASILITAS FISIK', false);
        $printObsRes->assertSee('Wajib Min. 90 Hari');
        $printObsRes->assertSee('Backup rekaman CCTV hanya tersimpan 30 hari karena harddisk penuh');

        // 4. Dossier Print All mencakup Observasi Lingkungan
        $dossierRes = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-all', $kka->id));
        $dossierRes->assertStatus(200);
        $dossierContent = $dossierRes->getContent();
        $this->assertEquals(1, substr_count($dossierContent, '<!DOCTYPE html>'));
        $this->assertEquals(1, substr_count($dossierContent, '</html>'));
        $this->assertStringContainsString('KERTAS KERJA OBSERVASI LINGKUNGAN', $dossierContent);
    }

    public function test_cbs_parser_handles_utf8_bom_and_detects_tpp(): void
    {
        $tempCsv = tempnam(sys_get_temp_dir(), 'bom_');
        // Prepend UTF-8 BOM bytes (\xEF\xBB\xBF) as commonly output by Windows Excel
        $csvContent = "\xEF\xBB\xBF" . "TGL_TX,KET_TX,JUMLAH_TX\n2026-11-05,PAYROLL TUNJANGAN PERBAIKAN PENGHASILAN (TPP) KAB. SIGI,65000000\n";
        file_put_contents($tempCsv, $csvContent);

        $parser = new DumpCbsParser();
        $res = $parser->parse($tempCsv, $this->unit->unit_code, true, $this->unit->unit_name);
        @unlink($tempCsv);

        $this->assertGreaterThan(0, $res['total_moderate'] + $res['total_high']);

        $finding = DailyRegisterFinding::where('kode_unit', $this->unit->unit_code)
            ->where('deskripsi', 'like', '%KAB. SIGI%')
            ->first();

        $this->assertNotNull($finding, 'Finding dengan CSV UTF-8 BOM harus berhasil disimpan.');
        $this->assertEquals('KKA_Teller_Kas', $finding->source_sheet);
        $this->assertEquals('Transaksi TPP', $finding->jenis_exception_awal);
        $this->assertStringContainsString('TPP', $finding->ket_tx);
    }

    public function test_ket_tx_attribute_skips_empty_or_whitespace_strings(): void
    {
        $finding = new DailyRegisterFinding([
            'kode_unit' => $this->unit->unit_code,
            'source_sheet' => 'KKA_Teller_Kas',
            'ket_tx' => '   ',
            'deskripsi' => '',
            'rincian' => 'Pencairan Dana TPP Tenaga Medis RSUD',
            'jenis_exception_awal' => 'Normal'
        ]);

        $this->assertEquals('Pencairan Dana TPP Tenaga Medis RSUD', $finding->ket_tx);
    }

    public function test_daily_register_blade_contains_interactive_tambah_mandiri_elements_and_js(): void
    {
        $response = $this->actingAs($this->raUser)->get(route('daily-register.index'));
        $response->assertStatus(200);

        // Pastikan button dan modal hadir dalam DOM
        $response->assertSee('id="btnTambahMandiriKas"', false);
        $response->assertSee('id="modalTambahMandiriKas"', false);
        $response->assertSee('+ Tambah Pemeriksaan Kas / Kas Kecil');

        // Pastikan variabel JavaScript dideklarasikan tanpa ReferenceError
        $response->assertSee('let currentActiveUnit =', false);
        $response->assertSee('let currentActiveDate =', false);
        $response->assertSee('function openModalTambahMandiri()', false);
        $response->assertSee('function handleMandiriSubAreaChange()', false);
        $response->assertSee('function submitTambahMandiri', false);
    }

    public function test_existing_physical_kka_with_partial_obs_details_merges_all_32_items(): void
    {
        // 1. Buat KKA dengan hanya 1 detail observasi parsial
        $kka = PhysicalKka::create([
            'kode_unit' => $this->unit->unit_code,
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => now()->toDateString(),
            'status_final' => 'Draft',
            'no_arsip' => 'ARSIP-OBS-PARTIAL-001',
        ]);

        PhysicalKkaObsDetail::create([
            'physical_kka_id' => $kka->id,
            'area_observasi' => 'Titik Alarm | Ruang Pimpinan Cabang',
            'kriteria_diharapkan' => 'Panic button pimpinan cabang',
            'hasil_observasi' => 'Sesuai',
            'kondisi_aktual' => 'Tombol tersembunyi responsif',
            'status_uji' => 'Sesuai',
            'is_exception' => false,
            'tanggal_uji' => now()->toDateString(),
        ]);

        // 2. Buka form edit
        $response = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.index', $kka->id));
        $response->assertStatus(200);

        // Seluruh 32 item harus tetap hadir di form view
        $response->assertSee('Titik Alarm | Ruang Pimpinan Cabang');
        $response->assertSee('Tombol tersembunyi responsif'); // Data tersimpan ter-merge
        $response->assertSee('Titik Alarm | Ruang Wakil Pimpinan Cabang'); // Item default lain tetap muncul
        $response->assertSee('Matriks CCTV (E.1.5) | Titik 1: Depan Banking Hall');
        $response->assertSee('Fasilitas ATM (E.4.4) | Fasilitas UPS ATM');

        // 3. Print KKA Observasi juga harus memuat seluruh matriks secara lengkap
        $printRes = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-observasi', $kka->id));
        $printRes->assertStatus(200);
        $printRes->assertSee('Titik Alarm | Ruang Wakil Pimpinan Cabang');
        $printRes->assertSee('Matriks CCTV (E.1.5) | Titik 9: Kredit');
        $printRes->assertSee('Wajib Min. 90 Hari');
    }
}

