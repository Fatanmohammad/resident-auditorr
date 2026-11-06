<?php

namespace Tests\Feature;

use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\User;
use App\Services\DailyRegister\AutoRiskScoringService;
use App\Services\Offsite\DumpCbsParser;
use App\Services\Offsite\DumpParserHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AutoRiskScoringOverhaulTest extends TestCase
{
    use DatabaseTransactions;

    protected $raUser;

    protected function setUp(): void
    {
        parent::setUp();

        DailyRegisterFinding::where('kode_unit', '001')->orWhere('no_arsip', 'like', 'ARSIP-%')->delete();
        PhysicalKka::where('kode_unit', '001')->delete();

        $cabang = \App\Models\Cabang::firstOrCreate(['kode_cabang' => '001'], [
            'nama_cabang' => 'KC Palu',
            'tipe' => 'cabang_a',
        ]);

        $unit = \App\Models\Unit::firstOrCreate(['unit_code' => '001'], [
            'unit_name' => 'KC Palu',
            'unit_type' => 'KC',
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_overhaul_tester@banksulteng.co.id'], [
            'nip' => '888777',
            'name' => 'RA Overhaul Tester',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'unit_code' => '001',
            'cabang_id' => $cabang->id,
        ]);
        $this->raUser->cabang_id = $cabang->id;
        $this->raUser->save();
    }

    /**
     * Test 1: Parser CBS menangani format TIME_STAMP unik DD/MM/YYYY HH:MM:SS:UUUUUU,
     * filter Periode Tanggal, dan memetakan NO_ARSIP serta KET_TX ke database.
     */
    public function test_cbs_parser_handles_unique_timestamp_format_and_maps_no_arsip_and_ket_tx(): void
    {
        // Verifikasi helper parsing timestamp terlebih dahulu
        $sample1 = '13/08/2026 08:19:50:307034';
        $sample2 = '13/08/2026 17:03:07:78203';
        $sample3 = '13/08/2026 10:13:38:93456';
        $sampleDiff = '14/08/2026 09:00:00:12345';

        $this->assertEquals('2026-08-13', DumpParserHelper::parseTimestampToDate($sample1));
        $this->assertEquals('2026-08-13', DumpParserHelper::parseTimestampToDate($sample2));
        $this->assertEquals('2026-08-13', DumpParserHelper::parseTimestampToDate($sample3));
        $this->assertEquals('2026-08-14', DumpParserHelper::parseTimestampToDate($sampleDiff));

        // Buat CSV dummy dengan header TIME_STAMP, NO_ARSIP, KET_TX, NOMINAL, dll.
        $csvContent = implode("\n", [
            'TIME_STAMP,NO_ARSIP,KET_TX,NO_REK,NOMINAL,USER_ID,KODE_CABANG',
            '13/08/2026 08:19:50:307034,ARSIP-20260813-001,Setoran Tunai Teller 01,111222333,15000000,TLR01,001',
            '13/08/2026 17:03:07:78203,ARSIP-20260813-002,Penarikan Tunai Kas Khasanah,444555666,35000000,TLR02,001',
            '14/08/2026 09:00:00:12345,ARSIP-20260814-003,Transaksi Hari Lain Diluar Filter,777888999,50000000,TLR03,001',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'dump_cbs_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $parser = app(DumpCbsParser::class);

        // Filter hanya untuk periode 2026-08-13 s.d. 2026-08-13
        $results = $parser->parse($tempFile, '001', true, 'KC Palu', 'August 2026', '2026-08-13', '2026-08-13');

        @unlink($tempFile);

        // Hanya 2 baris dari 2026-08-13 yang boleh lolos dan masuk ke daily_register_findings, baris 2026-08-14 wajib terfilter
        $findings = DailyRegisterFinding::where('kode_unit', '001')
            ->whereIn('no_arsip', ['ARSIP-20260813-001', 'ARSIP-20260813-002'])
            ->get();
        $this->assertCount(2, $findings);

        // Baris 1
        $f1 = $findings->firstWhere('no_arsip', 'ARSIP-20260813-001');
        $this->assertNotNull($f1);
        $this->assertEquals('2026-08-13', substr((string)$f1->tanggal_data, 0, 10));
        $this->assertEquals('ARSIP-20260813-001', $f1->no_arsip);
        $this->assertEquals('Setoran Tunai Teller 01', $f1->deskripsi);
        $this->assertEquals('Setoran Tunai Teller 01', $f1->ket_tx);

        // Baris 2
        $f2 = $findings->firstWhere('no_arsip', 'ARSIP-20260813-002');
        $this->assertNotNull($f2);
        $this->assertEquals('2026-08-13', substr((string)$f2->tanggal_data, 0, 10));
        $this->assertEquals('ARSIP-20260813-002', $f2->no_arsip);
        $this->assertEquals('Penarikan Tunai Kas Khasanah', $f2->deskripsi);
        $this->assertEquals('Penarikan Tunai Kas Khasanah', $f2->ket_tx);

        // Baris hari lain (2026-08-14) tidak boleh tersimpan
        $this->assertNull(DailyRegisterFinding::where('no_arsip', 'ARSIP-20260814-003')->first());
    }

    /**
     * Test 2: Upload CSV via HTTP Request dengan Filter Tanggal Mulai & Tanggal Selesai
     * dan verifikasi penyimpanan ke database tabel daily_register_findings.
     */
    public function test_upload_csv_with_date_range_filter_stores_data_properly(): void
    {
        $csvContent = implode("\n", [
            'TIME_STAMP,NO_ARSIP,KET_TX,NO_REK,NOMINAL,USER_ID,KODE_CABANG',
            '13/08/2026 08:19:50:307034,ARSIP-UPLOAD-01,Pembayaran Biaya Operasional,10001,4500000,ADM01,001',
            '13/08/2026 17:03:07:78203,ARSIP-UPLOAD-02,Setoran Bilyet Deposito,10002,30000000,CS01,001',
            '15/08/2026 11:22:33:44455,ARSIP-UPLOAD-03,Transaksi Lintas Hari Diabaikan,10003,50000000,CS02,001',
        ]);

        $file = UploadedFile::fake()->createWithContent('dump_cbs_sample.csv', $csvContent);

        $response = $this->actingAs($this->raUser)->post(route('daily-register.upload.store'), [
            'kode_unit' => '001',
            'jenis_file' => 'DUMP_01',
            'periode' => 'August 2026',
            'tanggal_mulai' => '2026-08-13',
            'tanggal_selesai' => '2026-08-13',
            'file_csv' => $file,
        ]);

        $response->assertSessionHas('success');

        // Pastikan hanya 2 record 2026-08-13 yang tersimpan di DB
        $stored = DailyRegisterFinding::where('kode_unit', '001')
            ->whereIn('no_arsip', ['ARSIP-UPLOAD-01', 'ARSIP-UPLOAD-02'])
            ->get();

        $this->assertCount(2, $stored);

        $f1 = $stored->firstWhere('no_arsip', 'ARSIP-UPLOAD-01');
        $this->assertNotNull($f1);
        $this->assertEquals('2026-08-13', substr((string)$f1->tanggal_data, 0, 10));
        $this->assertEquals('Pembayaran Biaya Operasional', $f1->deskripsi);

        $f2 = $stored->firstWhere('no_arsip', 'ARSIP-UPLOAD-02');
        $this->assertNotNull($f2);
        $this->assertEquals('2026-08-13', substr((string)$f2->tanggal_data, 0, 10));
        $this->assertEquals('Setoran Bilyet Deposito', $f2->deskripsi);

        // Pastikan baris tanggal 15 tidak tersimpan
        $this->assertNull(DailyRegisterFinding::where('no_arsip', 'ARSIP-UPLOAD-03')->first());
    }

    /**
     * Test 3: AutoRiskScoringService kalkulasi otomatis Impact (Skala 1 - 5).
     */
    public function test_auto_risk_scoring_service_impact_calculation(): void
    {
        $service = app(AutoRiskScoringService::class);

        // 1. Nihil temuan => Impact 1
        $this->assertEquals(1, $service->calculateImpact(0, 0, false, false, false, false));

        // 2. Dampak administratif ringan / < Rp 5.000.000 atau 1 item non-kritikal => Impact 2
        $this->assertEquals(2, $service->calculateImpact(3000000, 1, false, false, false, true));

        // 3. Dampak sedang (Rp 5.000.000 s.d. < Rp 25.000.000 atau 2-3 item tidak sesuai) => Impact 3
        $this->assertEquals(3, $service->calculateImpact(15000000, 2, false, false, false, true));

        // 4. Dampak besar (Rp 25.000.000 s.d. < Rp 100.000.000 atau selisih warkat/kartu atau > 3 item gagal) => Impact 4
        $this->assertEquals(4, $service->calculateImpact(50000000, 1, false, false, false, true));
        $this->assertEquals(4, $service->calculateImpact(2000000, 1, false, true, false, true)); // warkat diff

        // 5. Dampak sangat material (>= Rp 100.000.000, selisih kas/ATM signifikan, security breach) => Impact 5
        $this->assertEquals(5, $service->calculateImpact(150000000, 1, false, false, false, true));
        $this->assertEquals(5, $service->calculateImpact(1000000, 1, true, false, false, true)); // security breach (CCTV/dual control)
        $this->assertEquals(5, $service->calculateImpact(30000000, 1, false, false, true, true)); // selisih kas/ATM >= 25M
    }

    /**
     * Test 4: AutoRiskScoringService kalkulasi otomatis Likelihood dari histori database cabang
     * (Temuan Baru -> Berulang 1x -> Temuan Berulang -> Sering Berulang -> Sangat Sering Berulang).
     */
    public function test_auto_risk_scoring_service_likelihood_calculation_and_escalation(): void
    {
        $service = app(AutoRiskScoringService::class);

        // 0 histori => Likelihood 1 (Temuan Baru)
        $lik0 = $service->calculateLikelihoodFromHistoryCount(0, true);
        $this->assertEquals(1, $lik0['likelihood']);
        $this->assertEquals('Temuan Baru', $lik0['badge']);
        $this->assertEquals('Temuan pertama kali', $lik0['note']);

        // 1 histori => Likelihood 2 (Berulang 1x)
        $lik1 = $service->calculateLikelihoodFromHistoryCount(1, true);
        $this->assertEquals(2, $lik1['likelihood']);
        $this->assertEquals('Berulang 1x', $lik1['badge']);
        $this->assertStringContainsString('Berulang 1x', $lik1['note']);

        // 2-3 histori => Likelihood 3 (Temuan Berulang)
        $lik2 = $service->calculateLikelihoodFromHistoryCount(2, true);
        $this->assertEquals(3, $lik2['likelihood']);
        $this->assertEquals('Temuan Berulang', $lik2['badge']);

        $lik3 = $service->calculateLikelihoodFromHistoryCount(3, true);
        $this->assertEquals(3, $lik3['likelihood']);

        // 4-5 histori => Likelihood 4 (Sering Berulang)
        $lik4 = $service->calculateLikelihoodFromHistoryCount(4, true);
        $this->assertEquals(4, $lik4['likelihood']);
        $this->assertEquals('Sering Berulang', $lik4['badge']);

        // >= 6 histori => Likelihood 5 (Sangat Sering Berulang)
        $lik6 = $service->calculateLikelihoodFromHistoryCount(6, true);
        $this->assertEquals(5, $lik6['likelihood']);
        $this->assertEquals('Sangat Sering Berulang', $lik6['badge']);

        // Nihil temuan => Likelihood 1 (Nihil Temuan)
        $likNihil = $service->calculateLikelihoodFromHistoryCount(5, false);
        $this->assertEquals(1, $likNihil['likelihood']);
        $this->assertEquals('Nihil Temuan', $likNihil['note']);
    }

    /**
     * Test 5: Otomatisasi Critical, Risk Score, dan Risk Level.
     * Jika Critical = Ya dan Risk Score < 16, skor otomatis naik ke minimal 16 (High).
     */
    public function test_critical_risk_score_elevation_and_level_mapping(): void
    {
        $service = app(AutoRiskScoringService::class);

        // Kasus 1: Nihil temuan
        $riskNihil = $service->determineRisk(1, 1, false, false);
        $this->assertEquals(1, $riskNihil['score']);
        $this->assertEquals('Low', $riskNihil['level']);

        // Kasus 2: Temuan non-critical, impact 2, likelihood 2 => score 4 => Low to Moderate
        $riskLtm = $service->determineRisk(2, 2, false, true);
        $this->assertEquals(4, $riskLtm['score']);
        $this->assertEquals('Low to Moderate', $riskLtm['level']);

        // Kasus 3: Temuan non-critical, impact 3, likelihood 3 => score 9 => Moderate
        $riskMod = $service->determineRisk(3, 3, false, true);
        $this->assertEquals(9, $riskMod['score']);
        $this->assertEquals('Moderate', $riskMod['level']);

        // Kasus 4: Critical = Ya (misal selisih kas fisik != 0), impact 2, likelihood 1 => perkalian mentah 2x1=2
        // Namun karena Critical = Ya, risk score wajib dielevasi minimal ke 16 (High)
        $riskCrit = $service->determineRisk(2, 1, true, true);
        $this->assertGreaterThanOrEqual(16, $riskCrit['score']);
        $this->assertEquals('High', $riskCrit['level']);
    }

    /**
     * Test 6: Endpoint AJAX Preview Risiko Real-Time (/daily-register/auto-risk/preview).
     */
    public function test_ajax_realtime_risk_preview_endpoint(): void
    {
        // 1. Preview Kas Khasanah dengan selisih Rp 10.000.000 (Impact 3, Critical Ya karena selisih kas)
        $response1 = $this->actingAs($this->raUser)->postJson(route('daily-register.auto-risk.preview'), [
            'type' => 'physical',
            'tab' => 'kas',
            'unit_code' => '001',
            'selisih_kas_khasanah' => -10000000,
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'success' => true,
            'data' => [
                'has_finding' => true,
                'impact' => 3,
                'is_critical' => true,
                'critical_label' => 'Ya',
                'risk_level' => 'High', // di-elevasi karena Critical Ya
            ]
        ]);
        $this->assertGreaterThanOrEqual(16, $response1->json('data.risk_score'));

        // 2. Preview Nihil Temuan
        $response2 = $this->actingAs($this->raUser)->postJson(route('daily-register.auto-risk.preview'), [
            'type' => 'physical',
            'tab' => 'kas',
            'unit_code' => '001',
            'selisih_kas_khasanah' => 0,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'data' => [
                'has_finding' => false,
                'impact' => 1,
                'likelihood' => 1,
                'is_critical' => false,
                'risk_score' => 1,
                'risk_level' => 'Low',
            ]
        ]);
    }

    /**
     * Test 7: Simpan KKA Fisik dengan Blok Evaluasi Gabungan & validasi required penjelasan selisih.
     */
    public function test_physical_kka_combined_evaluation_block_and_submission(): void
    {
        // Kasus Gagal: Selisih kas ada tapi penyebab & keterangan kosong
        $payloadFail = [
            'sub_tab' => 'kas',
            'no_arsip' => 'ARSIP-EVAL-001',
            'saldo_kas_olibs' => 50000000,
            'kas_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 400, 'nominal' => 40000000],
            ],
            'penyebab_keterangan' => '',
            'kas_penyebab_keterangan' => '',
            'penjelasan_selisih_kas' => '',
        ];

        $resFail = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadFail);
        $resFail->assertSessionHasErrors(['kas_penyebab_keterangan']);

        // Kasus Berhasil: Dengan penyebab keterangan terisi
        $payloadSuccess = [
            'sub_tab' => 'kas',
            'no_arsip' => 'ARSIP-EVAL-001',
            'saldo_kas_olibs' => 50000000,
            'kas_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 400, 'nominal' => 40000000],
            ],
            'kas_penyebab_keterangan' => 'Selisih kurang Rp 10.000.000 sedang dalam proses rekonsiliasi kas cabang.',
        ];

        $resSuccess = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadSuccess);
        $resSuccess->assertSessionHas('success');

        $kka = PhysicalKka::where('no_arsip', 'ARSIP-EVAL-001')->first();
        $this->assertNotNull($kka);
        $this->assertEquals(-10000000, (float)$kka->selisih_kas_khasanah);
        $this->assertEquals(3, (int)$kka->kas_impact);
        $this->assertTrue((bool)$kka->kas_is_critical);
        $this->assertGreaterThanOrEqual(16, (int)$kka->kas_risk_score);
        $this->assertEquals('High', $kka->kas_risk_level);
        $this->assertEquals('Selisih kurang Rp 10.000.000 sedang dalam proses rekonsiliasi kas cabang.', $kka->kas_penyebab_keterangan);
    }

    /**
     * Test 8: Print View dan Berkas Lengkap Dossier memuat Blok Evaluasi Terpadu.
     */
    public function test_print_views_render_unified_evaluation_summary_block(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '001',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-PRINT-001',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 10000000,
            'saldo_fisik_khasanah' => 10000000,
            'selisih_kas_khasanah' => 0,
            'kas_impact' => 1,
            'kas_likelihood' => 1,
            'kas_is_critical' => false,
            'kas_risk_score' => 1,
            'kas_risk_level' => 'Low',
            'kas_penyebab_keterangan' => 'Nihil / Seluruh fisik kas khasanah sesuai.',
            'atm_impact' => 1,
            'atm_likelihood' => 1,
            'atm_is_critical' => false,
            'atm_risk_score' => 1,
            'atm_risk_level' => 'Low',
            'atm_penyebab_keterangan' => 'Nihil / Fisik ATM sesuai.',
            'kartu_impact' => 1,
            'kartu_likelihood' => 1,
            'kartu_is_critical' => false,
            'kartu_risk_score' => 1,
            'kartu_risk_level' => 'Low',
            'kartu_penyebab_keterangan' => 'Nihil / Kartu ATM sesuai.',
        ]);

        // 1. BA Kas Khasanah Print View
        $resKas = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-kas', $kka->id));
        $resKas->assertStatus(200);
        $resKas->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KAS KHASANAH', false);
        $resKas->assertSee('Impact (1-5)');
        $resKas->assertSee('Likelihood (1-5)');

        // 2. BA Cash Opname Mesin ATM Print View
        $resAtm = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-atm', $kka->id));
        $resAtm->assertStatus(200);
        $resAtm->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO CASH OPNAME ATM', false);

        // 3. KKA Kartu ATM Print View
        $resKartu = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-kartu', $kka->id));
        $resKartu->assertStatus(200);
        $resKartu->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA KARTU ATM', false);

        // 4. Dossier Print All
        $resAll = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-all', $kka->id));
        $resAll->assertStatus(200);
        $resAll->assertSee('Dossier Berkas Lengkap Tahap 2: KKA Fisik &amp; Rekonsiliasi Bank Sulteng', false);
    }

    /**
     * Test 9: Verifikasi Requirement 5.2: Input temuan pertama kali vs input temuan berulang di cabang yang sama
     * pada KKA Daily Register -> nilai Likelihood, Impact, Critical, Risk Score, dan Risk Level terhitung otomatis.
     */
    public function test_daily_register_first_time_vs_repeat_finding_auto_risk_scoring(): void
    {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DailyRegisterChecklistSeeder', '--force' => true]);

        // Temuan 1: Pertama kali di cabang 001 (history = 0), nominal Rp 3.000.000, 1 checklist item Tidak
        $f1 = DailyRegisterFinding::create([
            'kode_unit' => '001',
            'tanggal_data' => Carbon::now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 3000000,
            'risk_awal' => 'Moderate',
            'no_arsip' => 'ARSIP-DR-001',
        ]);

        $checklists = \App\Models\DailyRegister\DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')->get();
        $answers1 = $checklists->map(fn($c, $idx) => [
            'checklist_id' => $c->id,
            'jawaban' => ($idx === 0) ? 'Tidak' : 'Ya',
            'keterangan' => ($idx === 0) ? 'Slip teller tidak lengkap' : 'Sesuai'
        ])->toArray();

        $res1 = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $f1->id), [
            'use_auto_risk' => true,
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers1,
            'jenis_exception_ra' => 'Slip Teller Tidak Lengkap',
            'hasil_uji' => 'Ditemukan slip tanpa verifikasi',
            'klarifikasi_unit' => 'Telah ditegur supervisor',
            'no_arsip' => 'ARSIP-DR-001',
        ]);
        $res1->assertStatus(200);

        $f1Fresh = $f1->fresh();
        // Impact: 2 (< Rp 5jt / 1 item)
        $this->assertEquals(2, $f1Fresh->skor_dampak);
        // Likelihood: 1 (Temuan Baru / 0 history sebelumnya)
        $this->assertEquals(1, $f1Fresh->skor_kemungkinan);
        $this->assertEquals(2, $f1Fresh->skor_final); // 2 * 1
        $this->assertEquals('Low', $f1Fresh->risk_final);

        // Temuan 2: Temuan berulang ke-2 di cabang 001 (history = 1), nominal Rp 15.000.000, 2 checklist items Tidak
        $f2 = DailyRegisterFinding::create([
            'kode_unit' => '001',
            'tanggal_data' => Carbon::now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 15000000,
            'risk_awal' => 'Moderate',
            'no_arsip' => 'ARSIP-DR-002',
        ]);

        $answers2 = $checklists->map(fn($c, $idx) => [
            'checklist_id' => $c->id,
            'jawaban' => ($idx < 2) ? 'Tidak' : 'Ya',
            'keterangan' => ($idx < 2) ? 'Tidak sesuai' : 'Sesuai'
        ])->toArray();

        $res2 = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $f2->id), [
            'use_auto_risk' => true,
            'critical_trigger' => 'Tidak',
            'checklist_answers' => $answers2,
            'jenis_exception_ra' => 'Kekurangan Validasi Teller',
            'hasil_uji' => 'Dua transaksi tanpa tanda tangan',
            'klarifikasi_unit' => 'Sedang dilengkapi',
            'no_arsip' => 'ARSIP-DR-002',
        ]);
        $res2->assertStatus(200);

        $f2Fresh = $f2->fresh();
        // Impact: 3 (Rp 15jt / 2 item tidak sesuai)
        $this->assertEquals(3, $f2Fresh->skor_dampak);
        // Likelihood: 2 (Berulang 1x karena ada 1 temuan sebelumnya di cabang 001)
        $this->assertEquals(2, $f2Fresh->skor_kemungkinan);
        $this->assertEquals(6, $f2Fresh->skor_final); // 3 * 2
        $this->assertEquals('Low to Moderate', $f2Fresh->risk_final);

        // Temuan 3: Temuan berulang ke-3 di cabang 001 (history = 2), Critical Trigger Ya
        $f3 = DailyRegisterFinding::create([
            'kode_unit' => '001',
            'tanggal_data' => Carbon::now()->toDateString(),
            'source_sheet' => 'KKA_Teller_Kas',
            'nominal_terkait' => 5000000,
            'risk_awal' => 'Moderate',
            'no_arsip' => 'ARSIP-DR-003',
        ]);

        $res3 = $this->actingAs($this->raUser)->postJson(route('daily-register.update.ra', $f3->id), [
            'use_auto_risk' => true,
            'critical_trigger' => 'Ya',
            'checklist_answers' => $answers1,
            'jenis_exception_ra' => 'Selisih Kas Khasanah Signifikan',
            'hasil_uji' => 'Selisih kas fisik di teller',
            'klarifikasi_unit' => 'Dalam proses rekonsiliasi',
            'no_arsip' => 'ARSIP-DR-003',
        ]);
        $res3->assertStatus(200);

        $f3Fresh = $f3->fresh();
        // Impact: 5 (karena Critical Trigger Ya)
        $this->assertEquals(5, $f3Fresh->skor_dampak);
        // Likelihood: 3 (Temuan Berulang karena ada 2 histori sebelumnya: f1 & f2)
        $this->assertEquals(3, $f3Fresh->skor_kemungkinan);
        $this->assertGreaterThanOrEqual(16, $f3Fresh->skor_final);
        $this->assertEquals('High', $f3Fresh->risk_final);
    }

    /**
     * Test 10: Verifikasi KKA Fisik rekonsiliasi berulang di cabang yang sama dan autoritatif backend scoring.
     */
    public function test_physical_kka_recurrence_and_authoritative_scoring(): void
    {
        // 1. KKA Fisik Pertama dari minggu lalu dengan selisih kas khasanah
        $kka1 = PhysicalKka::create([
            'kode_unit' => '001',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->subWeeks(1)->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KAS-RECURRING-1',
            'posisi_tanggal_rekon' => Carbon::now()->subWeeks(1)->toDateString(),
            'saldo_kas_olibs' => 100000000,
            'saldo_fisik_khasanah' => 90000000,
            'selisih_kas_khasanah' => -10000000,
            'kas_impact' => 3,
            'kas_likelihood' => 1,
            'kas_is_critical' => true,
            'kas_risk_score' => 16,
            'kas_risk_level' => 'High',
            'kas_penyebab_keterangan' => 'Selisih kas minggu lalu.',
        ]);

        // 2. Submit KKA Fisik Kedua minggu ini pada cabang yang sama (unit 001) dengan selisih kas -> Likelihood harus naik ke 2 (Berulang 1x)
        $payload2 = [
            'sub_tab' => 'kas',
            'no_arsip' => 'ARSIP-KAS-RECURRING-2',
            'saldo_kas_olibs' => 50000000,
            'kas_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 450, 'nominal' => 45000000],
            ],
            'kas_penyebab_keterangan' => 'Selisih kas berulang kedua kali.',
            // Test bahwa backend menimpa input tampered dari user
            'kas_impact' => 1,
            'kas_likelihood' => 1,
            'kas_risk_score' => 1,
            'kas_risk_level' => 'Low',
        ];

        $res2 = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payload2);
        $res2->assertSessionHas('success');

        $kka2 = PhysicalKka::where('no_arsip', 'ARSIP-KAS-RECURRING-2')->first();
        $this->assertNotNull($kka2);
        $this->assertEquals(2, (int)$kka2->kas_likelihood, "Likelihood harus naik menjadi 2 (Berulang 1x) karena sudah pernah terjadi di cabang 001");
        $this->assertEquals('High', $kka2->kas_risk_level);
        $this->assertTrue((bool)$kka2->kas_is_critical);
        $this->assertGreaterThanOrEqual(16, (int)$kka2->kas_risk_score);
    }
}
