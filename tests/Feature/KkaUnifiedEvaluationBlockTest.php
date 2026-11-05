<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class KkaUnifiedEvaluationBlockTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected Cabang $cabang;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-003'], [
            'nama_cabang' => 'Kantor Cabang Poso',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '003'], [
            'unit_name' => 'KC Poso',
            'unit_type' => 'KC',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_eval_block_tester@banksulteng.co.id'], [
            'nip' => '999888',
            'name' => 'RA Eval Tester',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
            'unit_code' => '003',
        ]);
    }

    /**
     * 1. Per-row table test: The 7 evaluation columns must NOT exist in the <thead> or <tbody> for:
     * - Observasi Lingkungan (tab 3)
     * - Stok Cek / BG (tab 4)
     * - Barang Cetakan Berharga (tab 6)
     */
    public function test_physical_kka_rendered_tables_have_no_per_row_evaluation_columns_for_obs_stok_cetakan(): void
    {
        $response = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.index'));
        $response->assertStatus(200);

        $html = $response->getContent();

        // 1. Observasi Lingkungan (#obs)
        $this->assertStringContainsString('id="obs"', $html);
        $this->assertStringContainsString('Area Observasi', $html);
        $this->assertStringNotContainsString('obs_details[0][impact]', $html);
        $this->assertStringNotContainsString('obs_details[0][likelihood]', $html);
        $this->assertStringNotContainsString('obs_details[0][critical_trigger]', $html);
        $this->assertStringNotContainsString('obs_details[0][penyebab]', $html);
        $this->assertStringNotContainsString('obs_details[0][tanggal_uji]', $html);

        // 2. Stok Cek & BG (#stok)
        $this->assertStringContainsString('id="stok"', $html);
        $this->assertStringNotContainsString('stok_details[0][impact]', $html);
        $this->assertStringNotContainsString('stok_details[0][likelihood]', $html);
        $this->assertStringNotContainsString('stok_details[0][critical_trigger]', $html);
        $this->assertStringNotContainsString('stok_details[0][penyebab]', $html);
        $this->assertStringNotContainsString('stok_details[0][tanggal_uji]', $html);

        // 3. Barang Cetakan (#cetakan)
        $this->assertStringContainsString('id="cetakan"', $html);
        $this->assertStringNotContainsString('barang_cetakan_details[0][penyebab]', $html);
        $this->assertStringNotContainsString('barang_cetakan_details[0][impact]', $html);
        $this->assertStringNotContainsString('barang_cetakan_details[0][likelihood]', $html);
    }

    /**
     * 2. Bottom Evaluation Panel test: All 3 forms must contain 1 unified evaluation card before submit button
     */
    public function test_physical_kka_rendered_forms_contain_unified_evaluation_panel_for_obs_stok_cetakan(): void
    {
        $response = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.index'));
        $response->assertStatus(200);

        $html = $response->getContent();

        // Check evaluation panels
        $this->assertStringContainsString('Evaluasi Keseluruhan Observasi Lingkungan', $html);
        $this->assertStringContainsString('Evaluasi Keseluruhan Stok Cek &amp; Bilyet Giro', $html);
        $this->assertStringContainsString('Evaluasi Keseluruhan Barang Cetakan Berharga', $html);

        // Check single date, penyebab textarea, impact/likelihood/critical/score/level inputs
        $this->assertStringContainsString('name="penyebab_keterangan"', $html);
        $this->assertStringContainsString('name="tgl_uji"', $html);
        $this->assertStringContainsString('name="impact"', $html);
        $this->assertStringContainsString('name="likelihood"', $html);
        $this->assertStringContainsString('name="is_critical"', $html);
        $this->assertStringContainsString('name="risk_score"', $html);
        $this->assertStringContainsString('name="risk_level"', $html);
    }

    /**
     * 3. Observasi Lingkungan: Submit without exception (nihil) & with exception (validation + adjust risk)
     */
    public function test_observasi_lingkungan_submit_without_exception_and_with_exception(): void
    {
        $noArsip = 'ARSIP-KKA-003-OBS-' . date('YmdHis');
        $tglUji = Carbon::now()->toDateString();

        // 3a. Submit without exception (all 'Sesuai') -> penyebab optional, risk low
        $payloadClean = [
            'sub_tab' => 'observasi',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'obs_tgl_uji' => $tglUji,
            'obs_penyebab_keterangan' => '',
            'obs_impact' => 1,
            'obs_likelihood' => 1,
            'obs_is_critical' => 'Tidak',
            'obs_risk_score' => 1,
            'obs_risk_level' => 'Low',
            'obs_details' => [
                [
                    'area_observasi' => 'Pintu Khasanah',
                    'kriteria_diharapkan' => 'Kombinasi brankas dan kunci dual control',
                    'hasil_observasi' => 'Sesuai',
                    'kondisi_aktual' => 'Kunci dual control dipegang petugas terpisah',
                ],
            ],
        ];

        $resClean = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadClean);
        $resClean->assertSessionHasNoErrors();
        $resClean->assertStatus(302);

        $kka = PhysicalKka::where('no_arsip', $noArsip)->first();
        $this->assertNotNull($kka);
        $this->assertEquals('Low', $kka->obs_risk_level);
        $this->assertEquals(1, $kka->obs_risk_score);
        $this->assertCount(1, $kka->obsDetails);

        // 3b. Submit with exception ('Tidak Sesuai') but EMPTY penyebab_keterangan -> MUST FAIL validation
        $payloadWithExceptionEmptyPenyebab = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'observasi',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'obs_tgl_uji' => $tglUji,
            'obs_penyebab_keterangan' => '',
            'obs_impact' => 3,
            'obs_likelihood' => 2,
            'obs_is_critical' => 'Tidak',
            'obs_details' => [
                [
                    'area_observasi' => 'CCTV Khasanah',
                    'kriteria_diharapkan' => 'CCTV berfungsi 24 jam dan merekam minimal 30 hari',
                    'hasil_observasi' => 'Tidak Sesuai',
                    'kondisi_aktual' => 'Kamera mati sejak 2 hari lalu',
                ],
            ],
        ];

        $resFail = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadWithExceptionEmptyPenyebab);
        $resFail->assertSessionHasErrors('penyebab_keterangan');

        // 3c. Submit with exception AND filled penyebab_keterangan -> MUST SUCCEED
        $payloadWithExceptionValid = $payloadWithExceptionEmptyPenyebab;
        $payloadWithExceptionValid['obs_penyebab_keterangan'] = 'Power supply CCTV rusak dan sedang dalam proses pengadaan perbaikan.';
        $payloadWithExceptionValid['obs_risk_score'] = 6;
        $payloadWithExceptionValid['obs_risk_level'] = 'Low to Moderate';

        $resValid = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadWithExceptionValid);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertStatus(302);

        $kka->refresh();
        $this->assertEquals('Power supply CCTV rusak dan sedang dalam proses pengadaan perbaikan.', $kka->obs_penyebab_keterangan);
        $this->assertEquals(6, $kka->obs_risk_score);
        $this->assertEquals('Low to Moderate', $kka->obs_risk_level);
    }

    /**
     * 4. Stok Cek / BG: Submit without exception & with selisih exception
     */
    public function test_stok_cek_bg_submit_without_exception_and_with_exception(): void
    {
        $noArsip = 'ARSIP-KKA-003-STOK-' . date('YmdHis');
        $tglUji = Carbon::now()->toDateString();

        // 4a. Submit without exception (selisih 0, kontrol Ya)
        $payloadClean = [
            'sub_tab' => 'stok',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'stok_tgl_uji' => $tglUji,
            'stok_penyebab_keterangan' => '',
            'stok_impact' => 1,
            'stok_likelihood' => 1,
            'stok_is_critical' => 'Tidak',
            'stok_risk_score' => 1,
            'stok_risk_level' => 'Low',
            'stok_details' => [
                [
                    'sub_area' => 'Cek',
                    'kondisi_objek' => 'Buku cek kosong/belum digunakan',
                    'saldo_sistem' => 50,
                    'hasil_fisik' => 50,
                    'status_uji' => 'Sesuai',
                ],
                [
                    'sub_area' => 'Kontrol',
                    'kondisi_objek' => 'Kartu kontrol harian',
                    'saldo_sistem' => 0,
                    'hasil_fisik' => 0,
                    'status_uji' => 'Ya',
                ],
            ],
        ];

        $resClean = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadClean);
        $resClean->assertSessionHasNoErrors();
        $resClean->assertStatus(302);

        $kka = PhysicalKka::where('no_arsip', $noArsip)->first();
        $this->assertNotNull($kka);
        $this->assertEquals('Low', $kka->stok_risk_level);
        $this->assertCount(2, $kka->stokDetails);

        // 4b. Submit with selisih != 0 but EMPTY penyebab_keterangan -> MUST FAIL validation
        $payloadSelisihEmpty = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'stok',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'stok_tgl_uji' => $tglUji,
            'stok_penyebab_keterangan' => '',
            'stok_impact' => 4,
            'stok_likelihood' => 3,
            'stok_is_critical' => 'Tidak',
            'stok_details' => [
                [
                    'sub_area' => 'Cek',
                    'kondisi_objek' => 'Buku cek kosong/belum digunakan',
                    'saldo_sistem' => 50,
                    'hasil_fisik' => 48, // Selisih -2
                    'status_uji' => 'Exception',
                ],
            ],
        ];

        $resFail = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadSelisihEmpty);
        $resFail->assertSessionHasErrors('penyebab_keterangan');

        // 4c. Submit with selisih AND filled penyebab_keterangan -> MUST SUCCEED
        $payloadSelisihValid = $payloadSelisihEmpty;
        $payloadSelisihValid['stok_penyebab_keterangan'] = '2 buku cek sedang diserahkan ke nasabah tetapi belum selesai di-update di sistem.';
        $payloadSelisihValid['stok_risk_score'] = 12;
        $payloadSelisihValid['stok_risk_level'] = 'Moderate';

        $resValid = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadSelisihValid);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertStatus(302);

        $kka->refresh();
        $this->assertEquals('2 buku cek sedang diserahkan ke nasabah tetapi belum selesai di-update di sistem.', $kka->stok_penyebab_keterangan);
        $this->assertEquals(12, $kka->stok_risk_score);
        $this->assertEquals('Moderate', $kka->stok_risk_level);

        // 4d. Submit with Kontrol answered 'Tidak' but EMPTY penyebab_keterangan -> MUST FAIL validation
        $payloadKontrolEmpty = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'stok',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'stok_tgl_uji' => $tglUji,
            'stok_penyebab_keterangan' => '',
            'stok_impact' => 3,
            'stok_likelihood' => 2,
            'stok_is_critical' => 'Tidak',
            'stok_details' => [
                [
                    'sub_area' => 'Cek',
                    'kondisi_objek' => 'Buku cek kosong/belum digunakan',
                    'saldo_sistem' => 50,
                    'hasil_fisik' => 50,
                    'status_uji' => 'Sesuai',
                ],
                [
                    'sub_area' => 'Kontrol',
                    'kondisi_objek' => 'Kartu kontrol harian',
                    'saldo_sistem' => 0,
                    'hasil_fisik' => 0,
                    'status_uji' => 'Tidak',
                ],
            ],
        ];

        $resCtrlFail = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadKontrolEmpty);
        $resCtrlFail->assertSessionHasErrors('penyebab_keterangan');

        // 4e. Submit with Kontrol answered 'Tidak' AND filled penyebab_keterangan -> MUST SUCCEED and preserve status_uji = 'Tidak'
        $payloadKontrolValid = $payloadKontrolEmpty;
        $payloadKontrolValid['stok_penyebab_keterangan'] = 'Kartu kontrol harian belum ditandatangani oleh pejabat yang berwenang.';
        $payloadKontrolValid['stok_risk_score'] = 6;
        $payloadKontrolValid['stok_risk_level'] = 'Low to Moderate';

        $resCtrlValid = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadKontrolValid);
        $resCtrlValid->assertSessionHasNoErrors();
        $resCtrlValid->assertStatus(302);

        $kka->refresh();
        $this->assertEquals('Kartu kontrol harian belum ditandatangani oleh pejabat yang berwenang.', $kka->stok_penyebab_keterangan);
        $this->assertEquals(6, $kka->stok_risk_score);
        $this->assertEquals('Low to Moderate', $kka->stok_risk_level);
        $ctrlDetail = $kka->stokDetails->firstWhere('sub_area', 'Kontrol');
        $this->assertNotNull($ctrlDetail);
        $this->assertEquals('Tidak', $ctrlDetail->status_uji);
        $this->assertTrue((bool)$ctrlDetail->is_exception);
    }

    /**
     * 5. Rekonsiliasi Barang Cetakan: Submit without exception & with exception
     */
    public function test_barang_cetakan_submit_without_exception_and_with_exception(): void
    {
        $noArsip = 'ARSIP-KKA-003-BCT-' . date('YmdHis');
        $tglUji = Carbon::now()->toDateString();

        // 5a. Submit clean
        $payloadClean = [
            'sub_tab' => 'cetakan',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'cetakan_tgl_uji' => $tglUji,
            'cetakan_penyebab_keterangan' => '',
            'cetakan_impact' => 1,
            'cetakan_likelihood' => 1,
            'cetakan_is_critical' => 'Tidak',
            'cetakan_risk_score' => 1,
            'cetakan_risk_level' => 'Low',
            'barang_cetakan_details' => [
                [
                    'kategori' => 'Buku Tabungan',
                    'nama_barang' => 'Buku Tabungan Simpeda',
                    'kriteria' => 'Buku Tabungan Simpeda Kosong',
                    'satuan' => 'Bh',
                    'stok_sistem' => 100,
                    'hasil_fisik' => 100,
                ],
            ],
            'barang_cetakan_controls' => [
                [
                    'butir' => 'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.',
                    'jawaban' => 'Ya',
                    'catatan' => 'Tertib',
                ],
            ],
        ];

        $resClean = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadClean);
        $resClean->assertSessionHasNoErrors();
        $resClean->assertStatus(302);

        $kka = PhysicalKka::where('no_arsip', $noArsip)->first();
        $this->assertNotNull($kka);
        $this->assertEquals('Low', $kka->cetakan_risk_level);
        $this->assertCount(2, $kka->barangCetakanDetails);

        // 5b. Submit with control answer 'Tidak' but empty penyebab -> MUST FAIL
        $payloadControlFail = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'cetakan',
            'no_arsip' => $noArsip,
            'posisi_tanggal_rekon' => $tglUji,
            'cetakan_tgl_uji' => $tglUji,
            'cetakan_penyebab_keterangan' => '',
            'barang_cetakan_details' => [
                [
                    'kategori' => 'Buku Tabungan',
                    'nama_barang' => 'Buku Tabungan Simpeda',
                    'kriteria' => 'Buku Tabungan Simpeda Kosong',
                    'satuan' => 'Bh',
                    'stok_sistem' => 100,
                    'hasil_fisik' => 100,
                ],
            ],
            'barang_cetakan_controls' => [
                [
                    'butir' => 'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.',
                    'jawaban' => 'Tidak',
                    'catatan' => 'Register belum diparaf',
                ],
            ],
        ];

        $resFail = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadControlFail);
        $resFail->assertSessionHasErrors('penyebab_keterangan');

        // 5c. Submit with control answer 'Tidak' AND filled penyebab -> MUST SUCCEED
        $payloadControlValid = $payloadControlFail;
        $payloadControlValid['cetakan_penyebab_keterangan'] = 'Register belum diparaf oleh pejabat berwenang selama 2 hari kerja.';
        $payloadControlValid['cetakan_impact'] = 3;
        $payloadControlValid['cetakan_likelihood'] = 2;
        $payloadControlValid['cetakan_risk_score'] = 6;
        $payloadControlValid['cetakan_risk_level'] = 'Low to Moderate';

        $resValid = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadControlValid);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertStatus(302);

        $kka->refresh();
        $this->assertEquals('Register belum diparaf oleh pejabat berwenang selama 2 hari kerja.', $kka->cetakan_penyebab_keterangan);
        $this->assertEquals('Low to Moderate', $kka->cetakan_risk_level);
    }

    /**
     * 6. Print Views: Verify all 3 print views render clean table and bottom evaluation card
     */
    public function test_print_views_render_clean_table_and_evaluation_block_for_all_three_forms(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-PRINT-' . date('YmdHis'),
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'obs_tgl_uji' => Carbon::now()->toDateString(),
            'obs_penyebab_keterangan' => 'Keterangan Observasi Terpadu',
            'obs_impact' => 2,
            'obs_likelihood' => 2,
            'obs_risk_score' => 4,
            'obs_risk_level' => 'Low to Moderate',
            'stok_tgl_uji' => Carbon::now()->toDateString(),
            'stok_penyebab_keterangan' => 'Keterangan Stok Cek BG Terpadu',
            'stok_impact' => 3,
            'stok_likelihood' => 2,
            'stok_risk_score' => 6,
            'stok_risk_level' => 'Low to Moderate',
            'cetakan_tgl_uji' => Carbon::now()->toDateString(),
            'cetakan_penyebab_keterangan' => 'Keterangan Barang Cetakan Terpadu',
            'cetakan_impact' => 1,
            'cetakan_likelihood' => 1,
            'cetakan_risk_score' => 1,
            'cetakan_risk_level' => 'Low',
        ]);

        PhysicalKkaObsDetail::create([
            'physical_kka_id' => $kka->id,
            'area_observasi' => 'Pintu Khasanah',
            'kriteria_diharapkan' => 'Dual control',
            'hasil_observasi' => 'Sesuai',
            'status_uji' => 'Sesuai',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        PhysicalKkaStokDetail::create([
            'physical_kka_id' => $kka->id,
            'sub_area' => 'Cek',
            'kondisi_objek' => 'Buku cek kosong',
            'saldo_sistem' => 20,
            'hasil_fisik' => 20,
            'selisih' => 0,
            'status_uji' => 'Sesuai',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        PhysicalKkaBarangCetakanDetail::create([
            'physical_kka_id' => $kka->id,
            'procedure_id' => 'FLD-WK-003-TEST-001',
            'kategori' => 'Buku Tabungan',
            'nama_barang' => 'Simpeda',
            'kriteria' => 'Buku Tabungan',
            'satuan' => 'Bh',
            'stok_sistem' => 50,
            'hasil_fisik' => 50,
            'selisih' => 0,
            'status_uji' => 'SESUAI',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        // 6a. Print Observasi
        $resObs = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-observasi', $kka->id));
        $resObs->assertStatus(200);
        $resObs->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA', false);
        $resObs->assertSee('Keterangan Observasi Terpadu');

        // 6b. Print Stok Cek / BG
        $resStok = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-stok', $kka->id));
        $resStok->assertStatus(200);
        $resStok->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA', false);
        $resStok->assertSee('Keterangan Stok Cek BG Terpadu');

        // 6c. Print Barang Cetakan
        $resCetakan = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-cetakan', $kka->id));
        $resCetakan->assertStatus(200);
        $resCetakan->assertSee('RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA', false);
        $resCetakan->assertSee('Keterangan Barang Cetakan Terpadu');

        // 6d. Print All
        $resAll = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-all', $kka->id));
        $resAll->assertStatus(200);
    }

    /**
     * 7. PDF and Excel Exports: Verify download works cleanly without error
     */
    public function test_pdf_and_excel_exports_succeed_for_all_three_forms(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-EXPORT-' . date('YmdHis'),
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'obs_tgl_uji' => Carbon::now()->toDateString(),
            'obs_penyebab_keterangan' => 'Observasi aman',
            'obs_impact' => 1,
            'obs_likelihood' => 1,
            'obs_risk_score' => 1,
            'obs_risk_level' => 'Low',
            'stok_tgl_uji' => Carbon::now()->toDateString(),
            'stok_penyebab_keterangan' => 'Stok aman',
            'stok_impact' => 1,
            'stok_likelihood' => 1,
            'stok_risk_score' => 1,
            'stok_risk_level' => 'Low',
            'cetakan_tgl_uji' => Carbon::now()->toDateString(),
            'cetakan_penyebab_keterangan' => 'Cetakan aman',
            'cetakan_impact' => 1,
            'cetakan_likelihood' => 1,
            'cetakan_risk_score' => 1,
            'cetakan_risk_level' => 'Low',
        ]);

        // PDF Downloads
        $pdfObs = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.download-pdf', ['id' => $kka->id, 'type' => 'obs']));
        $pdfObs->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfObs->headers->get('content-type'));

        $pdfStok = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.download-pdf-stok', $kka->id));
        $pdfStok->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfStok->headers->get('content-type'));

        $pdfCetakan = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.download-pdf', ['id' => $kka->id, 'type' => 'cetakan']));
        $pdfCetakan->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfCetakan->headers->get('content-type'));

        // Excel Downloads
        $xlsObs = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.export-excel', ['id' => $kka->id, 'type' => 'obs']));
        $xlsObs->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $xlsObs->headers->get('content-type'));

        $xlsStok = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.export-excel-stok', $kka->id));
        $xlsStok->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $xlsStok->headers->get('content-type'));

        $xlsCetakan = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.export-excel', ['id' => $kka->id, 'type' => 'cetakan']));
        $xlsCetakan->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml', $xlsCetakan->headers->get('content-type'));
    }
}
