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


        $cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-003'], [
            'nama_cabang' => 'Cabang Luwuk',
            'tipe' => 'cabang_a',
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
     * Test 1: Verifikasi 189 item checklist ter-seed lengkap sesuai 6 Tab KKA (Pemisahan Teller, Kas Kecil, Khasanah dalam KKA_Teller_Kas)
     */
    public function test_189_checklist_items_seeded_across_6_tabs(): void
    {
        $this->artisan('db:seed', ['--class' => 'DailyRegisterChecklistSeeder', '--force' => true]);

        $totalCount = DailyRegisterChecklist::count();
        $this->assertEquals(189, $totalCount, "Total checklist items must be exactly 189");

        // TAB 1: KKA Teller & Kas (13 Item: A1 Transaksi Teller (6 item), A2 Kas Kecil (5 item), A3 Kas / Khasanah (2 item))
        $tabTellerKas = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $this->assertCount(13, $tabTellerKas);
        $this->assertCount(6, $tabTellerKas->where('sub_area_code', 'A1'));
        $this->assertCount(5, $tabTellerKas->where('sub_area_code', 'A2'));
        $this->assertCount(2, $tabTellerKas->where('sub_area_code', 'A3'));
        $this->assertEquals('Voucher slip setoran / slip penarikan / pemindahbukuan (nota debet/kredit)', $tabTellerKas->firstWhere('item_no', 1)->item_checklist);

        // TAB 2: KKA DPK & Layanan (29 Item)
        $tabDpk = DailyRegisterChecklist::where('source_sheet', 'KKA_DPK_Layanan')->get();
        $this->assertCount(29, $tabDpk);

        // TAB 3: KKA Kredit (83 Item: PNS/ASN 20 + FLPP 41 + Konstruksi 22)
        $tabKredit = DailyRegisterChecklist::where('source_sheet', 'KKA_Kredit')->get();
        $this->assertCount(83, $tabKredit);
        $this->assertCount(20, $tabKredit->where('sub_area_code', 'C1'));
        $this->assertCount(41, $tabKredit->where('sub_area_code', 'C2'));
        $this->assertCount(22, $tabKredit->where('sub_area_code', 'C3'));

        // TAB 4: KKA Biaya & Beban (25 Item)
        $tabBiaya = DailyRegisterChecklist::where('source_sheet', 'KKA_Biaya_Beban')->get();
        $this->assertCount(25, $tabBiaya);

        // TAB 5: KKA Kartu ATM (30 Item)
        $tabAtm = DailyRegisterChecklist::where('source_sheet', 'KKA_Kartu_ATM')->get();
        $this->assertCount(30, $tabAtm);

        // TAB 6: KKA Pengaduan (9 Item sesuai acuan E.4.5 KELUHAN NASABAH.xls)
        $tabPengaduan = DailyRegisterChecklist::where('source_sheet', 'KKA_Pengaduan')->get();
        $this->assertCount(9, $tabPengaduan);
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
            'deskripsi' => 'Transaksi Kliring Cek BG',
        ]);

        $response = $this->actingAs($this->raUser)->get(route('daily-register.review-data', $finding->id));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'finding' => ['id', 'kode_unit', 'source_sheet'],
            'recurring_count',
            'checklists',
            'has_cek'
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['recurring_count'], "First incident count should be 1");
        $this->assertCount(13, $data['checklists'], "Teller & Kas should have 13 checklist items across A1, A2, and A3");
        $this->assertTrue($data['has_cek']);
        $this->assertStringContainsString('Terdapat Cek', $data['checklists'][0]['item_checklist']);
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
            'KKA_Teller_Kas' => 13,
            'KKA_Teller' => 13, // Backward compatibility alias
            'KKA_Kas_Kecil' => 13, // Legacy alias mapped to KKA_Teller_Kas
            'KKA_Kas_Khasanah' => 13, // Legacy alias mapped to KKA_Teller_Kas
            'KKA_DPK_Layanan' => 29,
            'KKA_Kredit' => 83,
            'KKA_Biaya_Beban' => 25,
            'KKA_Kartu_ATM' => 30,
            'KKA_Pengaduan' => 9,
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

    /**
     * Test 14: Aturan Backlog Kehadiran RA: Jika RA cuti/absen beberapa hari,
     * transaksi low-risk dari tanggal-tanggal tersebut tetap wajib muncul saat getTodayData() dipanggil tanpa filter tanggal.
     */
    public function test_backlog_presence_during_ra_absence_via_tmp_service(): void
    {
        $unitCode = '003';
        $pastDate1 = now()->subDays(2)->format('Y-m-d');
        $pastDate2 = now()->subDay()->format('Y-m-d');
        $todayDate = now()->format('Y-m-d');

        // Simpan low-risk record di file tmp untuk tanggal lampau saat RA absen
        \App\Services\DailyRegister\DailyRegisterTmpService::storeLowRisk($unitCode, [
            ['kode_unit' => $unitCode, 'tanggal_data' => $pastDate1, 'rincian' => 'Backlog Absen H-2']
        ], $pastDate1);

        \App\Services\DailyRegister\DailyRegisterTmpService::storeLowRisk($unitCode, [
            ['kode_unit' => $unitCode, 'tanggal_data' => $pastDate2, 'rincian' => 'Backlog Absen H-1']
        ], $pastDate2);

        \App\Services\DailyRegister\DailyRegisterTmpService::storeLowRisk($unitCode, [
            ['kode_unit' => $unitCode, 'tanggal_data' => $todayDate, 'rincian' => 'Transaksi Hari Ini']
        ], $todayDate);

        // RA masuk kembali dan membuka modul tanpa filter tanggal spesifik -> Seluruh backlog harus muncul
        $allActive = \App\Services\DailyRegister\DailyRegisterTmpService::getTodayData([$unitCode], $unitCode, null);

        $this->assertGreaterThanOrEqual(3, $allActive->count());
        $rincianList = $allActive->pluck('rincian')->toArray();
        $this->assertContains('Backlog Absen H-2', $rincianList);
        $this->assertContains('Backlog Absen H-1', $rincianList);
        $this->assertContains('Transaksi Hari Ini', $rincianList);

        // Jika RA memfilter tanggal tertentu secara eksplisit, hanya tampil tanggal tersebut
        $filtered = \App\Services\DailyRegister\DailyRegisterTmpService::getTodayData([$unitCode], $unitCode, $pastDate1);
        $this->assertTrue($filtered->every(fn($item) => $item['tanggal_data'] === $pastDate1));

        // Bersihkan file tmp testing
        $dir = \App\Services\DailyRegister\DailyRegisterTmpService::getDirectoryPath();
        foreach ([$pastDate1, $pastDate2, $todayDate] as $d) {
            $f = $dir . DIRECTORY_SEPARATOR . "low_risk_{$unitCode}_{$d}.json";
            if (\Illuminate\Support\Facades\File::exists($f)) {
                \Illuminate\Support\Facades\File::delete($f);
            }
        }
    }

    /**
     * Test 15: Pemisahan KKA: CBS Parser Mengklasifikasikan Kas Kecil dan Kas / Khasanah ke KKA_Teller_Kas dengan selected_sub_areas
     */
    public function test_cbs_parser_classifies_kas_kecil_and_khasanah(): void
    {
        $cbsParser = app(\App\Services\Offsite\DumpCbsParser::class);

        // Buat file CSV simulasi di memori/tmp
        $csvContent = "TANGGAL,KETERANGAN,NOMINAL\n" .
                      now()->format('d/m/Y') . ",PENGISIAN KAS KECIL OPERASIONAL,55000000\n" .
                      now()->format('d/m/Y') . ",MUTASI FISIK BRANKAS KHASANAH,85000000\n" .
                      now()->format('d/m/Y') . ",SETORAN TUNAI TELLER 01,60000000\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tmpFile, $csvContent);

        $result = $cbsParser->parse($tmpFile, $this->unit->unit_code, true, $this->unit->unit_name);
        if (file_exists($tmpFile)) unlink($tmpFile);

        $this->assertIsArray($result);

        // Cek database daily_register_findings untuk unit ini
        $findingsKasKecil = DailyRegisterFinding::where('kode_unit', $this->unit->unit_code)
            ->where('source_sheet', 'KKA_Teller_Kas')
            ->whereJsonContains('selected_sub_areas', 'A2')
            ->first();
        $this->assertNotNull($findingsKasKecil, "KKA Kas Kecil harus terpetakan ke KKA_Teller_Kas dengan selected_sub_areas A2");

        $findingsKhasanah = DailyRegisterFinding::where('kode_unit', $this->unit->unit_code)
            ->where('source_sheet', 'KKA_Teller_Kas')
            ->whereJsonContains('selected_sub_areas', 'A3')
            ->first();
        $this->assertNotNull($findingsKhasanah, "KKA Kas / Khasanah harus terpetakan ke KKA_Teller_Kas dengan selected_sub_areas A3");

        $findingsTeller = DailyRegisterFinding::where('kode_unit', $this->unit->unit_code)
            ->where('source_sheet', 'KKA_Teller_Kas')
            ->whereJsonContains('selected_sub_areas', 'A1')
            ->first();
        $this->assertNotNull($findingsTeller, "Transaksi Teller harus terpetakan ke KKA_Teller_Kas dengan selected_sub_areas A1");
    }

    /**
     * Test 16: Auto-generate No. Arsip saat disimpan final jika tidak diisi
     */
    public function test_auto_generate_no_arsip_when_omitted(): void
    {
        $unit16 = $this->createIsolatedUnit('U16');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit16->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 10000000,
            'risk_awal' => 'Moderate',
            'no_arsip' => null,
        ]);

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers = $checklists->map(fn($c) => ['checklist_id' => $c->id, 'jawaban' => 'Ya'])->toArray();

        $response = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), [
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers,
            // no_arsip sengaja dikosongkan
            'no_arsip' => '',
        ]);

        $response->assertStatus(200);
        $fresh = $finding->fresh();
        $this->assertNotNull($fresh->no_arsip);
        $this->assertStringStartsWith('ARSIP-', $fresh->no_arsip);
    }

    /**
     * Test 17: Mekanisme Checkbox [✓] Sub-Area: Validasi minimal 1 grup dicentang,
     * dan hanya item dalam sub-area tercentang yang dievaluasi untuk penilaian & disimpan.
     */
    public function test_sub_area_checkbox_selection_and_validation(): void
    {
        $unit17 = $this->createIsolatedUnit('U17');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit17->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 15000000,
            'risk_awal' => 'Moderate',
        ]);

        $checklistsA1 = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->where('sub_area_code', 'A1')->get();
        $checklistsA2 = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->where('sub_area_code', 'A2')->get();

        // Kasus A: Kirim selected_sub_areas kosong -> Validasi gagal 422
        $responseEmptySubAreas = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), [
            'selected_sub_areas' => [],
            'checklist_answers' => [
                ['checklist_id' => $checklistsA1->first()->id, 'jawaban' => 'Ya']
            ],
        ]);
        $responseEmptySubAreas->assertStatus(422);

        // Kasus B: Centang hanya sub-area A1. Semua item di A1 dijawab Ya.
        // Jika ada jawaban 'Tidak' di sub-area A2 (yang tidak dicentang), jawaban A2 diabaikan,
        // tidak mewajibkan exception fields, dan tidak menaikkan risk_final!
        $answers = [];
        foreach ($checklistsA1 as $c) {
            $answers[] = ['checklist_id' => $c->id, 'jawaban' => 'Ya'];
        }
        foreach ($checklistsA2 as $c) {
            $answers[] = ['checklist_id' => $c->id, 'jawaban' => 'Tidak', 'keterangan' => 'Harus diabaikan'];
        }

        $payload = [
            'selected_sub_areas' => ['A1'],
            'checklist_answers' => $answers,
            'critical_trigger' => 'Tidak',
        ];

        $responseSuccess = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), $payload);
        $responseSuccess->assertStatus(200);

        $fresh = $finding->fresh();
        $this->assertEquals(['A1'], $fresh->selected_sub_areas);
        $this->assertEquals('Low', $fresh->risk_final, "Jawaban 'Tidak' di grup A2 yang tidak dicentang tidak boleh memicu status exception");
        $this->assertEquals(1, $fresh->skor_final);

        // Pastikan hanya jawaban dari sub-area A1 yang tersimpan di daily_register_checklist_answers
        $savedAnswers = DailyRegisterChecklistAnswer::where('daily_register_finding_id', $finding->id)->get();
        $savedChecklistIds = $savedAnswers->pluck('checklist_id')->toArray();
        foreach ($checklistsA2 as $c2) {
            $this->assertNotContains($c2->id, $savedChecklistIds, "Item A2 tidak boleh tersimpan karena sub-area A2 tidak dicentang");
        }
        foreach ($checklistsA1 as $c1) {
            $this->assertContains($c1->id, $savedChecklistIds, "Item A1 harus tersimpan karena sub-area A1 dicentang");
        }
    }

    /**
     * Test 18: Deselected sub-area membersihkan jawaban lama dari database
     * sehingga temuan 'Tidak' dari sub-area yang dinonaktifkan tidak tertinggal.
     */
    public function test_deselected_sub_area_cleans_up_old_answers_and_does_not_persist_orphaned_tidak(): void
    {
        $unit18 = $this->createIsolatedUnit('U18');
        $finding = DailyRegisterFinding::create([
            'kode_unit' => $unit18->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 20000000,
            'risk_awal' => 'Moderate',
        ]);

        $checklistsA1 = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->where('sub_area_code', 'A1')->get();
        $checklistsA2 = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->where('sub_area_code', 'A2')->get();

        // 1. Simpan pertama kali dengan mencentang A1 dan A2, di mana A2 memiliki 1 'Tidak'
        $initialAnswers = [];
        foreach ($checklistsA1 as $c) {
            $initialAnswers[] = ['checklist_id' => $c->id, 'jawaban' => 'Ya'];
        }
        foreach ($checklistsA2 as $c) {
            $initialAnswers[] = ['checklist_id' => $c->id, 'jawaban' => 'Tidak', 'keterangan' => 'Temuan A2'];
        }

        $res1 = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), [
            'selected_sub_areas' => ['A1', 'A2'],
            'checklist_answers' => $initialAnswers,
            'critical_trigger' => 'Tidak',
            'jenis_exception_ra' => 'Exception Kas Kecil',
            'hasil_uji' => 'Pengeluaran tanpa otorisasi',
            'klarifikasi_unit' => 'Akan diselesaikan',
        ]);
        $res1->assertStatus(200);

        // Pastikan jawaban A2 tersimpan
        $countA2Saved = DailyRegisterChecklistAnswer::where('daily_register_finding_id', $finding->id)
            ->whereIn('checklist_id', $checklistsA2->pluck('id'))
            ->count();
        $this->assertEquals($checklistsA2->count(), $countA2Saved);

        // Simulasi Reviewer meminta perbaikan (Status PERLU_REVISI), membuka kunci form bagi RA
        $finding->review_status = DailyRegisterFinding::STATUS_PERLU_REVISI;
        $finding->status_review = 'Revisi';
        $finding->save();

        // 2. Sekarang RA mengedit: melepas centang A2 (hanya mencentang A1 dengan seluruh Ya)
        $updatedAnswers = [];
        foreach ($checklistsA1 as $c) {
            $updatedAnswers[] = ['checklist_id' => $c->id, 'jawaban' => 'Ya'];
        }

        $res2 = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $finding->id), [
            'selected_sub_areas' => ['A1'],
            'checklist_answers' => $updatedAnswers,
            'critical_trigger' => 'Tidak',
        ]);
        $res2->assertStatus(200);

        $fresh = $finding->fresh();
        $this->assertEquals(['A1'], $fresh->selected_sub_areas);
        $this->assertEquals('Low', $fresh->risk_final);
        $this->assertNull($fresh->jenis_exception_ra);

        // Verifikasi bahwa seluruh jawaban lama di A2 TELAH DIHAPUS bersih
        $countA2After = DailyRegisterChecklistAnswer::where('daily_register_finding_id', $finding->id)
            ->whereIn('checklist_id', $checklistsA2->pluck('id'))
            ->count();
        $this->assertEquals(0, $countA2After, "Jawaban checklist untuk sub-area yang dilepas/dinonaktifkan harus dihapus bersih dari database");
    }

    /**
     * Test 19: Endpoint Cetak Arsip Matriks (Print/PDF) dan Export Excel
     * Memverifikasi pemisahan tabel arsip per sub-area yang dicentang.
     */
    public function test_print_arsip_matriks_and_export_excel_landscape_view(): void
    {
        $unit19 = $this->createIsolatedUnit('U19');
        $findingTeller = DailyRegisterFinding::create([
            'kode_unit' => $unit19->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 15000000,
            'risk_awal' => 'Moderate',
            'selected_sub_areas' => ['A1'],
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
            'no_arsip' => 'ARSIP-TELLER-001',
        ]);

        $findingKasKecil = DailyRegisterFinding::create([
            'kode_unit' => $unit19->unit_code,
            'tanggal_data' => now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 2500000,
            'risk_awal' => 'Moderate',
            'selected_sub_areas' => ['A2'],
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
            'no_arsip' => 'ARSIP-KECIL-002',
        ]);

        // 1. Panggil Print View tanpa filter sub_area (harus merender seluruh sub-area terpisah)
        $printResponse = $this->actingAs($this->raUser)->get(route('daily-register.arsip-matriks.print', [
            'source_sheet' => 'KKA_Teller_Kas',
            'kode_unit' => $unit19->unit_code,
        ]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Sub-Area: Transaksi Teller', false);
        $printResponse->assertSee('Sub-Area: Kas Kecil', false);
        $printResponse->assertSee('ARSIP-TELLER-001');
        $printResponse->assertSee('ARSIP-KECIL-002');
        $printResponse->assertSee('MATRIKS CHECKLIST PENGUJIAN', false);

        // 2. Panggil Print View dengan filter sub_area=A2 (Kas Kecil saja)
        $printFiltered = $this->actingAs($this->raUser)->get(route('daily-register.arsip-matriks.print', [
            'source_sheet' => 'KKA_Teller_Kas',
            'kode_unit' => $unit19->unit_code,
            'sub_area' => 'A2',
        ]));
        $printFiltered->assertStatus(200);
        $printFiltered->assertSee('Sub-Area: Kas Kecil', false);
        $printFiltered->assertDontSee('Sub-Area: Transaksi Teller', false);
        $printFiltered->assertSee('ARSIP-KECIL-002');

        // 3. Panggil Export Excel
        $excelResponse = $this->actingAs($this->raUser)->get(route('daily-register.arsip-matriks.export-excel', [
            'source_sheet' => 'KKA_Teller_Kas',
            'kode_unit' => $unit19->unit_code,
        ]));
        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $excelResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Arsip_Matriks_KKA_Teller_Kas', $excelResponse->headers->get('Content-Disposition'));
    }

    protected function tearDown(): void
    {
        DailyRegisterChecklistAnswer::whereNotIn('daily_register_finding_id', DailyRegisterFinding::where('id', '<=', 26)->pluck('id'))->delete();
        DailyRegisterFinding::where('id', '>=', 27)->delete();
        RegisterTrigger::where('sumber', 'Daily Review')
            ->where(function($q) {
                $q->where('kode_unit', 'like', 'U%')
                  ->orWhere('kode_unit', '999')
                  ->orWhereNotIn('source_wp_id', DailyRegisterFinding::pluck('id')->map(fn($id) => 'DRF-' . $id));
            })->delete();
        Unit::where('unit_code', 'like', 'U%')->orWhere('unit_code', '999')->delete();
        Cabang::where('kode_cabang', 'BS-999')->delete();
        User::whereIn('email', [
            'ra_test_luwuk@banksulteng.co.id',
            'admin_test_checker@banksulteng.co.id',
        ])->delete();

        parent::tearDown();
    }
}

