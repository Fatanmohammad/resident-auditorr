<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\DailyRegisterChecklist;
use App\Models\DailyRegister\DailyRegisterChecklistAnswer;
use App\Models\RiskTrigger\RegisterTrigger;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DailyRegisterChecklistTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected User $adminUser;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'resident-auditorr',
        ]);

        $cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-003'], [
            'nama_cabang' => 'Cabang Luwuk',
            'tipe' => 'kc',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '003'], [
            'unit_name' => 'KC Luwuk',
            'unit_type' => 'KC',
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_test_luwuk@banksulteng.co.id'], [
            'nip' => '998801',
            'name' => 'RA Luwuk Test',
            'password' => bcrypt('password123'),
            'role' => 'ra',
            'cabang_id' => $cabang->id,
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_test_checker@banksulteng.co.id'], [
            'nip' => '998802',
            'name' => 'Admin Checker Test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'cabang_id' => $cabang->id,
        ]);
    }

    protected function createIsolatedUnit(string $prefix = 'U'): Unit
    {
        $code = $prefix . rand(100, 999);
        return Unit::firstOrCreate(['unit_code' => $code], [
            'unit_name' => 'Unit Test ' . $code,
            'unit_type' => 'KC',
            'cabang_id' => $this->raUser->cabang_id,
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Verifikasi 218 item checklist ter-seed lengkap sesuai 6 Tab KKA
     */
    public function test_218_checklist_items_seeded_across_6_tabs(): void
    {
        $this->artisan('db:seed', ['--class' => 'DailyRegisterChecklistSeeder', '--force' => true]);

        $totalCount = DailyRegisterChecklist::count();
        $this->assertEquals(218, $totalCount, "Total checklist items must be exactly 218");

        // TAB 1: KKA Teller & Kas (30 Item)
        $tab1 = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $this->assertCount(30, $tab1);
        $this->assertCount(10, $tab1->where('sub_area_code', 'A1'));
        $this->assertCount(5, $tab1->where('sub_area_code', 'A2'));
        $this->assertCount(10, $tab1->where('sub_area_code', 'A3'));
        $this->assertCount(5, $tab1->where('sub_area_code', 'A4'));
        $this->assertEquals('Voucher/slip setoran tunai', $tab1->firstWhere('item_no', 1)->item_checklist);
        $this->assertEquals('Prosedur buka-tutup khasanah (jam buka, jam tutup, saksi)', $tab1->firstWhere('item_no', 30)->item_checklist);

        // TAB 2: KKA DPK & Layanan (29 Item)
        $tab2 = DailyRegisterChecklist::where('source_sheet', 'KKA_DPK_Layanan')->get();
        $this->assertCount(29, $tab2);
        $this->assertCount(10, $tab2->where('sub_area_code', 'D1'));
        $this->assertCount(7, $tab2->where('sub_area_code', 'D2'));
        $this->assertCount(5, $tab2->where('sub_area_code', 'D3'));
        $this->assertCount(7, $tab2->where('sub_area_code', 'D4'));
        $this->assertEquals("Status rekening berubah 'Closed' di sistem", $tab2->firstWhere('item_no', 29)->item_checklist);

        // TAB 3: KKA Kredit (99 Item)
        $tab3 = DailyRegisterChecklist::where('source_sheet', 'KKA_Kredit')->get();
        $this->assertCount(99, $tab3);
        $this->assertCount(18, $tab3->where('sub_area_code', 'C1'));
        $this->assertCount(16, $tab3->where('sub_area_code', 'C2'));
        $this->assertCount(16, $tab3->where('sub_area_code', 'C3'));
        $this->assertCount(13, $tab3->where('sub_area_code', 'C4'));
        $this->assertCount(7, $tab3->where('sub_area_code', 'C5'));
        $this->assertCount(7, $tab3->where('sub_area_code', 'C6'));
        $this->assertCount(15, $tab3->where('sub_area_code', 'C7'));
        $this->assertCount(7, $tab3->where('sub_area_code', 'C8'));
        $this->assertEquals('Register klaim asuransi (tanggal ajuan, status, jumlah)', $tab3->firstWhere('item_no', 99)->item_checklist);

        // TAB 4: KKA Biaya & Beban (22 Item)
        $tab4 = DailyRegisterChecklist::where('source_sheet', 'KKA_Biaya_Beban')->get();
        $this->assertCount(22, $tab4);
        $this->assertCount(7, $tab4->where('sub_area_code', 'E1'));
        $this->assertCount(5, $tab4->where('sub_area_code', 'E2'));
        $this->assertCount(6, $tab4->where('sub_area_code', 'E3'));
        $this->assertCount(4, $tab4->where('sub_area_code', 'E4'));

        // TAB 5: KKA Kartu ATM (30 Item)
        $tab5 = DailyRegisterChecklist::where('source_sheet', 'KKA_Kartu_ATM')->get();
        $this->assertCount(30, $tab5);
        $this->assertCount(7, $tab5->where('sub_area_code', 'B1'));
        $this->assertCount(9, $tab5->where('sub_area_code', 'B2'));
        $this->assertCount(8, $tab5->where('sub_area_code', 'B3'));
        $this->assertCount(6, $tab5->where('sub_area_code', 'B4'));

        // TAB 6: KKA Pengaduan (8 Item)
        $tab6 = DailyRegisterChecklist::where('source_sheet', 'KKA_Pengaduan')->get();
        $this->assertCount(8, $tab6);
        $this->assertCount(8, $tab6->where('sub_area_code', 'D5'));
        $this->assertEquals('Bukti penyelesaian (pengembalian dana, koreksi, dll)', $tab6->firstWhere('item_no', 8)->item_checklist);
    }

    /**
     * Test 2: Verifikasi Blade View merender checklist accordion, menyembunyikan simpulan_ra, dan mengunci tanggal
     */
    public function test_blade_view_elements_and_omission_of_simpulan_ra(): void
    {
        $response = $this->actingAs($this->raUser)->get(route('daily-register.kka.index'));
        $response->assertStatus(200);

        // Verifikasi Section 2
        $response->assertSee('2. Hasil Pengujian &amp; Klarifikasi', false);
        $response->assertSee('checklistAccordionContainer');
        $response->assertSee('containerExceptionFields');
        $response->assertSee('display:none');

        // Verifikasi field Simpulan RA SUDAH DIHAPUS dari UI
        $response->assertDontSee('id="simpulan_ra"', false);
        $response->assertDontSee('<textarea class="custom-form-control field-ra" id="simpulan_ra"', false);

        // Verifikasi tanggal_ditemukan readonly
        $response->assertSee('id="tanggal_ditemukan"', false);
        $response->assertSee('readonly', false);
    }

    /**
     * Test 3: Endpoint review-data mengembalikan finding, checklist lengkap, dan recurring_count
     */
    public function test_review_data_endpoint(): void
    {
        $unit = $this->createIsolatedUnit('U03');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 25000000,
            'risk_awal' => 'Moderate',
            'jenis_exception_awal' => 'Selisih Kas',
        ]);

        $response = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $finding->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'finding' => ['id', 'kode_unit', 'source_sheet'],
            'recurring_count',
            'checklists'
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['recurring_count'], "First incident count should be 1");
        $this->assertCount(30, $data['checklists'], "Teller Kas should have 30 checklist items");
    }

    /**
     * Test 4: Kondisi Semua Checklist = Ya (0 Tidak) dan Critical Trigger == Tidak
     * Menghasilkan Skor Dampak = 1, Kemungkinan = 1, Final = 1 (Low), Onsite = Tidak Perlu,
     * serta ketiga field exception dikosongkan.
     */
    public function test_all_ya_results_in_low_risk_and_cleared_exception_fields(): void
    {
        $unit = $this->createIsolatedUnit('U04');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 75000000,
            'risk_awal' => 'Moderate',
            'jenis_exception_ra' => 'Ada Exception Lama',
            'hasil_uji' => 'Fakta Lama',
            'klarifikasi_unit' => 'Klarifikasi Lama',
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers = $checklists->map(fn($c) => ['checklist_id' => $c->id, 'jawaban' => 'Ya', 'keterangan' => ''])->toArray();

        $payload = [
            'bukti_referensi' => 'Ref Doc All Ya',
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            // Coba kirim teks exception, harus dikosongkan oleh backend karena semua Ya
            'jenis_exception_ra' => 'Harus Hilang',
            'hasil_uji' => 'Harus Hilang',
            'klarifikasi_unit' => 'Harus Hilang',
        ];

        $response = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), $payload);
        $response->assertStatus(200);

        $fresh = $finding->fresh();
        $this->assertEquals(1, $fresh->skor_dampak, "Skor Dampak harus 1 jika semua Ya");
        $this->assertEquals(1, $fresh->skor_kemungkinan, "Skor Kemungkinan harus 1 jika semua Ya");
        $this->assertEquals(1, $fresh->skor_final, "Skor Final harus 1 jika semua Ya");
        $this->assertEquals('Low', $fresh->risk_final);
        $this->assertEquals('Tidak Perlu', $fresh->perlu_onsite);
        $this->assertNull($fresh->jenis_exception_ra, "Jenis exception harus null saat semua Ya");
        $this->assertNull($fresh->hasil_uji, "Hasil uji harus null saat semua Ya");
        $this->assertNull($fresh->klarifikasi_unit, "Klarifikasi unit harus null saat semua Ya");
        $this->assertEquals(now()->toDateString(), $fresh->tanggal_ditemukan);
    }

    /**
     * Test 5: Kondisi >= 1 Checklist = Tidak mewajibkan Jenis Exception, Hasil Uji, dan Klarifikasi
     */
    public function test_checklist_with_tidak_requires_exception_fields(): void
    {
        $unit = $this->createIsolatedUnit('U05');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 20000000,
            'risk_awal' => 'Moderate',
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers = $checklists->map(function($c, $idx) {
            return [
                'checklist_id' => $c->id,
                'jawaban' => ($idx === 0) ? 'Tidak' : 'Ya',
                'keterangan' => ($idx === 0) ? 'Slip tidak ada paraf' : ''
            ];
        })->toArray();

        // Kirim tanpa field exception -> Harus gagal validasi 422
        $payloadInvalid = [
            'bukti_referensi' => 'Ref Doc 123',
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'jenis_exception_ra' => '',
            'hasil_uji' => '',
            'klarifikasi_unit' => '',
        ];

        $responseInvalid = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), $payloadInvalid);
        $responseInvalid->assertStatus(422);

        // Kirim lengkap -> Harus sukses 200
        $payloadValid = [
            'bukti_referensi' => 'Ref Doc 123',
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'jenis_exception_ra' => 'Tidak ada paraf otorisasi',
            'hasil_uji' => 'Ditemukan 1 transaksi tanpa paraf pejabat',
            'klarifikasi_unit' => 'Pejabat sedang dinas luar',
        ];

        $responseValid = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), $payloadValid);
        $responseValid->assertStatus(200);

        $fresh = $finding->fresh();
        $this->assertEquals('Tidak ada paraf otorisasi', $fresh->jenis_exception_ra);
        $this->assertEquals('Ditemukan 1 transaksi tanpa paraf pejabat', $fresh->hasil_uji);
        $this->assertEquals('Pejabat sedang dinas luar', $fresh->klarifikasi_unit);
    }

    /**
     * Test 6: Rumus Matriks 5x5: Skor Dampak, Skor Kemungkinan berulang, Skor & Risk Final, dan Perlu Onsite
     */
    public function test_5x5_scoring_matrix_and_recurring_calculation(): void
    {
        $unit6 = $this->createIsolatedUnit('U06');
        // Skenario A: Nominal Rp 15jt (Dampak 2), 1 checklist Tidak (Dampak 1), Max Dampak = 2.
        // Kejadian ke-1 (history 0) -> Kemungkinan = 1.
        // Final = 2 x 1 = 2 (Low), Onsite = Tidak Perlu.
        $findingA = DailyRegisterFinding::create([
            'kode_unit' => $unit6->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 15000000, // Range 10jt - < 50jt -> nomScore = 2
            'risk_awal' => 'Moderate',
        ]);
        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answersA = $checklists->map(fn($c, $idx) => ['checklist_id' => $c->id, 'jawaban' => ($idx === 0) ? 'Tidak' : 'Ya'])->toArray();

        $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $findingA->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answersA,
            'jenis_exception_ra' => 'Exception A',
            'hasil_uji' => 'Uji A',
            'klarifikasi_unit' => 'Klarifikasi A',
        ]);

        $freshA = $findingA->fresh();
        $this->assertEquals(2, $freshA->skor_dampak, "Nominal 15jt menghasilkan dampak 2");
        $this->assertEquals(1, $freshA->skor_kemungkinan, "Kejadian pertama dengan 1 gagal menghasilkan kemungkinan 1");
        $this->assertEquals(2, $freshA->skor_final);
        $this->assertEquals('Low', $freshA->risk_final);
        $this->assertEquals('Tidak Perlu', $freshA->perlu_onsite);

        // Skenario B: Nominal Rp 60jt (Dampak 3), 3 checklist Tidak (Dampak 3), Max Dampak = 3.
        // Karena findingA sudah berstatus exception (risk_final = Low tetapi checklist jawaban Tidak),
        // sekarang buat findingB, findingC sehingga kejadian menjadi berulang ke-3 (history 2).
        $findingB = DailyRegisterFinding::create([
            'kode_unit' => $unit6->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 20000000,
            'risk_awal' => 'Moderate',
            'risk_final' => 'Moderate',
        ]);

        $findingC = DailyRegisterFinding::create([
            'kode_unit' => $unit6->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 60000000, // 50jt - < 100jt -> nomScore = 3
            'risk_awal' => 'Moderate',
        ]);
        $answersC = $checklists->map(fn($c, $idx) => ['checklist_id' => $c->id, 'jawaban' => ($idx < 3) ? 'Tidak' : 'Ya'])->toArray();

        $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $findingC->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answersC,
            'jenis_exception_ra' => 'Exception C',
            'hasil_uji' => 'Uji C',
            'klarifikasi_unit' => 'Klarifikasi C',
        ]);

        $freshC = $findingC->fresh();
        $this->assertEquals(3, $freshC->skor_dampak, "Nominal 60jt atau 3 checklist Tidak menghasilkan dampak 3");
        $this->assertEquals(3, $freshC->skor_kemungkinan, "Kejadian ke-3 menghasilkan kemungkinan 3");
        $this->assertEquals(9, $freshC->skor_final, "3 x 3 = 9");
        $this->assertEquals('Moderate to High', $freshC->risk_final, "Skor 8-9 harus Moderate to High");
        $this->assertEquals('Perlu', $freshC->perlu_onsite, "Moderate to High harus Perlu Onsite");

        // Skenario C: Critical Trigger == Ya
        // Paksa Dampak = 5, Final >= 16 (Critical), Onsite = Perlu
        $findingCrit = DailyRegisterFinding::create([
            'kode_unit' => $unit6->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 5000000,
            'risk_awal' => 'Moderate',
        ]);
        $answersCrit = $checklists->map(fn($c) => ['checklist_id' => $c->id, 'jawaban' => 'Ya'])->toArray();

        $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $findingCrit->id), [
            'critical_trigger' => 'Ya',
            'checklist_answers' => $answersCrit,
            'jenis_exception_ra' => 'Critical Fraud Exception',
            'hasil_uji' => 'Indikasi fraud ditemukan',
            'klarifikasi_unit' => 'Sedang diinvestigasi',
        ]);

        $freshCrit = $findingCrit->fresh();
        $this->assertEquals(5, $freshCrit->skor_dampak);
        $this->assertEquals('Critical', $freshCrit->risk_final);
        $this->assertEquals('Perlu', $freshCrit->perlu_onsite);
    }

    /**
     * Test 7: Sinkronisasi ke SOP 06 Risk Trigger Visit
     */
    public function test_integration_with_risk_trigger_sop06(): void
    {
        $unit7 = $this->createIsolatedUnit('U07');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit7->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Biaya_Beban',
            'nominal_terkait' => 120000000, // 100jt - < 500jt -> Dampak 4
            'risk_awal' => 'Moderate',
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Biaya_Beban')->get();
        $answers = $checklists->map(fn($c, $idx) => ['checklist_id' => $c->id, 'jawaban' => ($idx < 5) ? 'Tidak' : 'Ya'])->toArray();

        $response = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'jenis_exception_ra' => 'Biaya Tanpa Kontrak',
            'hasil_uji' => 'Pengadaan 120jt tanpa persetujuan',
            'klarifikasi_unit' => 'Keterlambatan berkas admin',
        ]);
        $response->assertStatus(200);

        // Verifikasi terdaftar di RegisterTrigger (SOP 06)
        $trigger = RegisterTrigger::where('source_wp_id', "DRF-{$finding->id}")->first();
        $this->assertNotNull($trigger, "Trigger harus otomatis tersinkron ke RegisterTrigger SOP 06");
        $this->assertEquals('Biaya Beban', $trigger->area);
        $this->assertGreaterThanOrEqual(1, $trigger->risk_score_sumber);
        $this->assertEquals($unit7->unit_code, $trigger->kode_unit);
    }

    /**
     * Test 8: Edge Case Baris KKA tanpa nominal (null atau 0):
     * Jumlah checklist Tidak & Critical Trigger sebagai penentu otomatis Skor Dampak minimal 2 saat ada temuan.
     */
    public function test_edge_case_zero_nominal_determines_dampak_minimal_2(): void
    {
        $unit8 = $this->createIsolatedUnit('U08');
        // 1 checklist Tidak tanpa nominal -> Dampak 2 (minimal 2 saat ada temuan)
        $findingZeroNom = DailyRegisterFinding::create([
            'kode_unit' => $unit8->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_DPK_Layanan',
            'nominal_terkait' => null, // Tidak terdapat kolom nominal uang
            'risk_awal' => 'Moderate',
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_DPK_Layanan')->get();
        $answers = $checklists->map(fn($c, $idx) => ['checklist_id' => $c->id, 'jawaban' => ($idx === 0) ? 'Tidak' : 'Ya'])->toArray();

        $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $findingZeroNom->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'jenis_exception_ra' => 'KTP Nasabah Tidak Terverifikasi',
            'hasil_uji' => 'Fotokopi KTP buram dan tidak divalidasi ke Dukcapil',
            'klarifikasi_unit' => 'Sistem Dukcapil sedang offline',
        ]);

        $fresh = $findingZeroNom->fresh();
        $this->assertEquals(2, $fresh->skor_dampak, "Jika nominal kosong/0 dan ada temuan (1 Tidak), Skor Dampak minimal 2");
        $this->assertEquals(1, $fresh->skor_kemungkinan, "Kejadian pertama 1 Tidak menghasilkan kemungkinan 1");
        $this->assertEquals(2, $fresh->skor_final);
        $this->assertEquals('Low', $fresh->risk_final);
        $this->assertEquals('Tidak Perlu', $fresh->perlu_onsite);
    }

    /**
     * Test 9: Edge Case Berulang >= 7 kali menghasilkan Skor Kemungkinan = 5 (Sangat Sering)
     */
    public function test_edge_case_recurring_7_times_gives_kemungkinan_5(): void
    {
        $unit9 = $this->createIsolatedUnit('U09');
        // Buat 6 finding sebelumnya dengan temuan di kode unit dan sheet yang sama
        for ($i = 1; $i <= 6; $i++) {
            DailyRegisterFinding::create([
                'kode_unit' => $unit9->unit_code,
                'tanggal_data' => now()->subDays($i)->toDateString(),
                'source_sheet' => 'KKA_Kartu_ATM',
                'nominal_terkait' => 0,
                'risk_awal' => 'Moderate',
                'risk_final' => 'Moderate',
            ]);
        }

        // Kejadian ke-7
        $finding7 = DailyRegisterFinding::create([
            'kode_unit' => $unit9->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Kartu_ATM',
            'nominal_terkait' => 0,
            'risk_awal' => 'Moderate',
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Kartu_ATM')->get();
        $answers = $checklists->map(fn($c, $idx) => ['checklist_id' => $c->id, 'jawaban' => ($idx === 0) ? 'Tidak' : 'Ya'])->toArray();

        $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding7->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'jenis_exception_ra' => 'CCTV ATM Mati Berulang',
            'hasil_uji' => 'CCTV di ATM unit 003 tidak berfungsi',
            'klarifikasi_unit' => 'Vendor belum datang perbaiki',
        ]);

        $fresh7 = $finding7->fresh();
        $this->assertEquals(5, $fresh7->skor_kemungkinan, "Kejadian ke-7 harus menghasilkan Kemungkinan 5 (Sangat Sering)");
        // Dampak = 2 (tanpa nominal, 1 Tidak), Kemungkinan = 5 -> Skor Final = 10 (High), Onsite = Perlu
        $this->assertEquals(2, $fresh7->skor_dampak);
        $this->assertEquals(10, $fresh7->skor_final);
        $this->assertEquals('High', $fresh7->risk_final);
        $this->assertEquals('Perlu', $fresh7->perlu_onsite);
    }

    /**
     * Test 10: Edge Case Verifikasi 6 Tab KKA Mengembalikan Checklist Lengkap di Endpoint review-data
     */
    public function test_all_6_tabs_return_correct_checklists_in_review_data(): void
    {
        $unit10 = $this->createIsolatedUnit('U10');
        $tabs = [
            'KKA_Teller_Kas' => 30,
            'KKA_DPK_Layanan' => 29,
            'KKA_Kredit' => 99,
            'KKA_Biaya_Beban' => 22,
            'KKA_Kartu_ATM' => 30,
            'KKA_Pengaduan' => 8,
        ];

        foreach ($tabs as $sheetCode => $expectedItemCount) {
            $f = DailyRegisterFinding::create([
                'kode_unit' => $unit10->unit_code,
                'tanggal_data' => now()->toDateString(),
                'source_sheet' => $sheetCode,
                'nominal_terkait' => 1000000,
                'risk_awal' => 'Moderate',
            ]);

            $res = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $f->id));
            $res->assertStatus(200);
            $chkList = $res->json('checklists');
            $this->assertCount($expectedItemCount, $chkList, "Sheet {$sheetCode} harus memiliki {$expectedItemCount} item checklist");
        }
    }

    /**
     * Test 11: Edge Case Tanggal Ditemukan / Diuji Terkunci Otomatis Saat RA Membuka KKA
     * dan Upaya Manipulasi Tanggal Manual Ditolak Validasi Backend
     */
    public function test_tanggal_ditemukan_auto_locked_on_first_open_and_tamper_rejected(): void
    {
        $unit11 = $this->createIsolatedUnit('U11');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit11->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 10000000,
            'risk_awal' => 'Moderate',
            'tanggal_ditemukan' => null, // Awalnya null
        ]);

        // RA pertama kali membuka review modal
        $res = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $finding->id));
        $res->assertStatus(200);

        // Di database harus sudah terkunci dengan tanggal hari ini
        $fresh = $finding->fresh();
        $this->assertEquals(now()->format('Y-m-d'), $fresh->tanggal_ditemukan, "Tanggal ditemukan harus terkunci saat RA membuka KKA");

        // Coba manipulasi tanggal_ditemukan manual dari client ke tanggal lampau (misal: 2024-01-01)
        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers = $checklists->map(fn($c) => ['checklist_id' => $c->id, 'jawaban' => 'Ya'])->toArray();

        $tamperPayload = [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            'tanggal_ditemukan' => '2024-01-01', // Tanggal yang dimanipulasi
        ];

        $responseTamper = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), $tamperPayload);
        $responseTamper->assertStatus(422);
        $responseTamper->assertJsonValidationErrors(['tanggal_ditemukan']);
    }

    /**
     * Test 12: Edge Case Normalisasi Kode Unit (dengan / tanpa prefix BS-) pada Perhitungan Kejadian Berulang
     * dan Verifikasi Temuan yang Telah Bersih (All Ya / Low) Tidak Dihitung Sebagai Exception Berulang
     */
    public function test_recurring_calculation_normalizes_unit_prefix_and_ignores_cleared_low_findings(): void
    {
        $unit12 = $this->createIsolatedUnit('U12');
        $cleanCode = $unit12->unit_code;
        $bsCode = 'BS-' . $cleanCode;

        // 1. Buat finding terdahulu dengan kode BS-U12 yang bernilai Exception (risk_final = High)
        DailyRegisterFinding::create([
            'kode_unit' => $bsCode,
            'tanggal_data' => now()->subDays(2)->toDateString(),
            'source_sheet' => 'KKA_Biaya_Beban',
            'nominal_terkait' => 50000000,
            'risk_awal' => 'Moderate',
            'risk_final' => 'High',
        ]);

        // 2. Buat finding terdahulu lainnya dengan kode U12 tetapi sudah direview bersih (All Ya -> risk_final = Low)
        DailyRegisterFinding::create([
            'kode_unit' => $cleanCode,
            'tanggal_data' => now()->subDay()->toDateString(),
            'source_sheet' => 'KKA_Biaya_Beban',
            'nominal_terkait' => 10000000,
            'risk_awal' => 'Low',
            'risk_final' => 'Low',
        ]);

        // 3. Finding saat ini (kode U12)
        $currentFinding = DailyRegisterFinding::create([
            'kode_unit' => $cleanCode,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Biaya_Beban',
            'nominal_terkait' => 30000000,
            'risk_awal' => 'Moderate',
        ]);

        // Panggil endpoint review-data
        $res = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $currentFinding->id));
        $res->assertStatus(200);

        // Kejadian harus ke-2 (karena finding 1 adalah exception berulang walau beda prefix BS-, dan finding 2 diabaikan karena bersih Low)
        $this->assertEquals(2, $res->json('recurring_count'), "Recurring count harus menormalisasi prefix BS- dan mengabaikan temuan Low yang sudah bersih");
    }

    /**
     * Test 13: DailyRegisterTmpService::cleanupExpired() Tidak Menghapus Finding yang Sudah Di-review
     */
    public function test_daily_register_tmp_cleanup_protects_reviewed_findings(): void
    {
        $unit13 = $this->createIsolatedUnit('U13');
        $reviewedFinding = DailyRegisterFinding::create([
            'kode_unit' => $unit13->unit_code,
            'tanggal_data' => now()->subDays(2)->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 5000000,
            'risk_awal' => 'Low',
            'risk_final' => 'Low',
            'status_review' => 'Approved',
            'tanggal_ditemukan' => now()->subDays(2)->toDateString(),
        ]);

        // Jalankan cleanup
        \App\Services\DailyRegister\DailyRegisterTmpService::cleanupExpired();

        // Finding yang sudah di-review tidak boleh terhapus
        $this->assertDatabaseHas('daily_register_findings', [
            'id' => $reviewedFinding->id,
            'risk_final' => 'Low',
        ]);
    }
}

