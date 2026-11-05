<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaAtmMachine;
use App\Models\DailyRegister\PhysicalKkaAtmCassette;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Services\Offsite\DumpParserHelper;
use App\Services\Offsite\DumpCbsParser;
use App\Services\Offsite\DumpDpkParser;
use App\Services\Offsite\DumpKreditParser;
use App\Services\Offsite\DumpBiayaParser;
use App\Services\Offsite\DumpPengaduanParser;
use App\Services\Offsite\DumpKartuAtmParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class SixTasksVerificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected User $adminUser;
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

        $this->raUser = User::firstOrCreate(['email' => 'ra_poso_six_tasks@banksulteng.co.id'], [
            'nip' => '999001',
            'name' => 'RA Poso Verifier',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
            'unit_code' => '003',
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_six_tasks@banksulteng.co.id'], [
            'nip' => '999002',
            'name' => 'Admin SKAI Verifier',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * TASK 1: Verifikasi DumpParserHelper dan ke-6 Dump Parser menarik otomatis NO_ARSIP dan KET_TX ke database
     */
    public function test_task_1_csv_parsers_auto_populate_no_arsip_and_ket_tx(): void
    {
        $today = date('d/m/Y');

        // 1. CBS Parser (Teller & Kas)
        $csvCbs = "TANGGAL,KETERANGAN,NOMINAL,NO_ARSIP,KET_TX\n"
            . "{$today},TARIK TUNAI KAS BESAR REVERSAL,150000000,ARSIP-CBS-001,Penarikan Tunai Kas Khasanah oleh Teller 01\n";
        $fileCbs = UploadedFile::fake()->createWithContent('cbs.csv', $csvCbs);
        (new DumpCbsParser())->parse($fileCbs, '003', true);

        $findingCbs = DailyRegisterFinding::where('no_arsip', 'ARSIP-CBS-001')->first();
        $this->assertNotNull($findingCbs, 'CBS finding with auto-mapped no_arsip should exist');
        $this->assertEquals('Penarikan Tunai Kas Khasanah oleh Teller 01', $findingCbs->deskripsi);

        // 2. DPK Parser (DPK & Layanan) with fallback header NO_VOUCHER & KETERANGAN_TX
        $csvDpk = "TGL_TX_AKHIR,NO_REK,NAMA_SINGKAT,STSDESC,SALDO_AKHIR,NO_VOUCHER,KETERANGAN_TX\n"
            . "{$today},1020304050,NASABAH DORMANT,DORMANT,25000000,VCH-DPK-888,Aktivasi Buku Tabungan Dormant dan Setor Tunai\n";
        $fileDpk = UploadedFile::fake()->createWithContent('dpk.csv', $csvDpk);
        (new DumpDpkParser())->parse($fileDpk, '003', true);

        $findingDpk = DailyRegisterFinding::where('no_arsip', 'VCH-DPK-888')->first();
        $this->assertNotNull($findingDpk, 'DPK finding with fallback voucher no_arsip should exist');
        $this->assertEquals('Aktivasi Buku Tabungan Dormant dan Setor Tunai', $findingDpk->deskripsi);

        // 3. Kredit Parser
        $csvKredit = "TANGGAL,NO_REKENING,NAMA_DEBITUR,PLAFON,KOLEKTIBILITAS,NO ARSIP,KET_TRANSAKSI\n"
            . "{$today},3030303030,DEBITUR KONSUMTIF JUMBO,750000000,1,ARSIP-KREDIT-777,Pencairan Kredit Modal Kerja Tahap 1\n";
        $fileKredit = UploadedFile::fake()->createWithContent('kredit.csv', $csvKredit);
        (new DumpKreditParser())->parse($fileKredit, '003', true);

        $findingKredit = DailyRegisterFinding::where('no_arsip', 'ARSIP-KREDIT-777')->first();
        $this->assertNotNull($findingKredit, 'Kredit finding should have mapped NO ARSIP');
        $this->assertEquals('Pencairan Kredit Modal Kerja Tahap 1', $findingKredit->deskripsi);

        // 4. Biaya Parser
        $csvBiaya = "TANGGAL,NAMA_AKUN,NOMINAL,ARSIP,KETERANGAN\n"
            . "{$today},BIAYA PROMOSI CABANG,45000000,ARSIP-BIAYA-555,Pembayaran Banner Branding HUT Bank Sulteng\n";
        $fileBiaya = UploadedFile::fake()->createWithContent('biaya.csv', $csvBiaya);
        (new DumpBiayaParser())->parse($fileBiaya, '003', true);

        $findingBiaya = DailyRegisterFinding::where('no_arsip', 'ARSIP-BIAYA-555')->first();
        $this->assertNotNull($findingBiaya, 'Biaya finding should have mapped ARSIP');
        $this->assertEquals('Pembayaran Banner Branding HUT Bank Sulteng', $findingBiaya->deskripsi);

        // 5. Pengaduan Parser
        $csvPengaduan = "TANGGAL_PENGADUAN,NO_TIKET,NAMA_NASABAH,JENIS_PENGADUAN,SLA_HARI,NO_REF,KET_TX\n"
            . "{$today},TKT-001,NASABAH ATM MACET,KARTU TERTELAN,20,REF-ADU-444,Kartu tertelan di ATM KC belum diproses 20 hari\n";
        $filePengaduan = UploadedFile::fake()->createWithContent('pengaduan.csv', $csvPengaduan);
        (new DumpPengaduanParser())->parse($filePengaduan, '003', true);

        $findingPengaduan = DailyRegisterFinding::where('no_arsip', 'REF-ADU-444')->first();
        $this->assertNotNull($findingPengaduan, 'Pengaduan finding should have mapped NO_REF');
        $this->assertEquals('Kartu tertelan di ATM KC belum diproses 20 hari', $findingPengaduan->deskripsi);

        // 6. Kartu ATM Parser
        $csvKartu = "TANGGAL,NO_KARTU,JENIS_KARTU,STATUS,USER_ID,KETERANGAN,NO_ARSIP,KET_TX\n"
            . "{$today},60110001,ATM GOLD,BLOCKED PIN 3X,USRAT01,KARTU TERTELAN / RUSAK,ARSIP-ATM-333,Nasabah salah input PIN 3 kali di ATM\n";
        $fileKartu = UploadedFile::fake()->createWithContent('kartu.csv', $csvKartu);
        (new DumpKartuAtmParser())->parse($fileKartu, '003', true);

        $findingKartu = DailyRegisterFinding::where('no_arsip', 'ARSIP-ATM-333')->first();
        $this->assertNotNull($findingKartu, 'Kartu ATM finding should have mapped NO_ARSIP');
        $this->assertEquals('Nasabah salah input PIN 3 kali di ATM', $findingKartu->deskripsi);
    }

    /**
     * TASK 1 (Lanjutan): Verifikasi backfill / sync ringan di KkaDailyRegisterController::data()
     */
    public function test_task_1_existing_records_with_empty_deskripsi_get_backfilled(): void
    {
        $finding = DailyRegisterFinding::create([
            'kode_unit' => '003',
            'nama_unit' => 'KC Poso',
            'source_sheet' => 'KKA_Teller_Kas',
            'tanggal_data' => Carbon::now()->toDateString(),
            'nominal_terkait' => 1000000,
            'risk_awal' => 'Moderate',
            'jenis_exception_awal' => 'Selisih Kas',
            'no_arsip' => 'ARSIP-BACKFILL-001',
            'deskripsi' => null, // empty
            'rincian' => 'Rincian transaksi awal kas kecil teller',
        ]);

        $response = $this->actingAs($this->raUser)
            ->get(route('daily-register.data', ['source_sheet' => 'KKA_Teller_Kas']));

        $response->assertStatus(200);
        $items = $response->json('data.data') ?? $response->json('data');
        $found = collect($items)->firstWhere('id', $finding->id);

        $this->assertNotNull($found, 'Finding should be returned in list');
        $this->assertEquals('Rincian transaksi awal kas kecil teller', $found['deskripsi']);
    }

    /**
     * TASK 2: Banner Alarm Risk Trigger (SOP 06) di Dashboard berstruktur dan bergaya identik dengan Banner Fraud (SOP 09)
     */
    public function test_task_2_sop06_banner_matches_sop09_fraud_banner_structure(): void
    {
        // Buat 12 temuan Risk Trigger (> 10) untuk memicu ambang batas alarm
        for ($i = 1; $i <= 12; $i++) {
            RegisterTrigger::create([
                'trigger_id' => 'TRIG-003-' . $i,
                'kode_unit' => '003',
                'nama_unit' => 'Kantor Cabang Poso',
                'area' => RegisterTrigger::AREA_TELLER_KAS,
                'sumber' => 'Daily Review',
                'deskripsi' => "Temuan trigger {$i}",
                'tanggal_trigger' => Carbon::now()->toDateString(),
                'status_workflow' => 'PERLU KLARIFIKASI',
                'is_informed_to_leader' => false,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertStatus(200);

        // Memastikan judul tebal merah dan ikon peringatan seragam
        $response->assertSee('ALARM RISK TRIGGER (SOP 06):', false);
        $response->assertSee('Terdeteksi 1 Cabang Melampaui Ambang Batas (&gt; 10 Temuan)', false);
        $response->assertSee('Kantor Cabang Poso (12 Temuan - Area Teller Kas)', false);
        $response->assertSee('bi-exclamation-triangle-fill', false);

        // Memastikan tombol pil rapi "Lihat Risk Trigger →"
        $response->assertSee('Lihat Risk Trigger &rarr;', false);
        $response->assertSee(route('risk-trigger.index'), false);
    }

    /**
     * TASK 3: Tampilan Kartu Daily Register SOP 02 di Dashboard Utama bergaya Onsite (SOP 04) & Angka Belum Dikerjakan Real-Time
     */
    public function test_task_3_daily_register_kpi_card_and_unfinished_count(): void
    {
        // Setup data belum dikerjakan
        DailyRegisterFinding::create([
            'kode_unit' => '003',
            'nama_unit' => 'KC Poso',
            'source_sheet' => 'KKA_Teller_Kas',
            'tanggal_data' => Carbon::now()->toDateString(),
            'nominal_terkait' => 500000,
            'risk_awal' => 'Moderate',
            'jenis_exception_awal' => 'Keterlambatan Setoran',
            'status_penyelesaian' => 'Menunggu Klarifikasi',
            'deskripsi' => 'Pengujian Belum Dikerjakan 1',
        ]);

        // Cek Dashboard Utama
        $response = $this->actingAs($this->raUser)->get(route('dashboard'));
        $response->assertStatus(200);

        // Memastikan kartu SOP 02 Daily Register tampil dengan format "X Belum Dikerjakan"
        $response->assertSee('SOP 02: Daily Register', false);
        $response->assertSee('Belum Dikerjakan', false);
        $response->assertSee('Selesai', false);

        // Cek Halaman Daily Register
        $responseDaily = $this->actingAs($this->raUser)->get(route('daily-register.index'));
        $responseDaily->assertStatus(200);
        $responseDaily->assertSee('BELUM DIKERJAKAN', false);
        $responseDaily->assertSee('statBelumDikerjakan', false);
    }

    /**
     * TASK 4: Submit penyimpanan ke-5 Form KKA Fisik satu per satu tanpa error validasi tersembunyi
     */
    public function test_task_4_submit_all_five_physical_kka_forms_individually(): void
    {
        $noArsipGlobal = 'ARSIP-KKA-003-INDIVIDUAL-' . date('Ymd');
        $tglRekon = Carbon::now()->toDateString();

        // 1. Submit Form Tab 1: Kas Khasanah (sub_tab = 'kas')
        $payloadKas = [
            'sub_tab' => 'kas',
            'no_arsip' => $noArsipGlobal,
            'posisi_tanggal_rekon' => $tglRekon,
            'saldo_kas_olibs' => 50000000,
            'penjelasan_selisih_kas' => '',
            'cash_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 500, 'nominal' => 50000000],
            ],
        ];
        $resKas = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadKas);
        $resKas->assertSessionHasNoErrors();
        $resKas->assertStatus(302);

        $kka = PhysicalKka::where('no_arsip', $noArsipGlobal)->first();
        $this->assertNotNull($kka, 'Physical KKA created from Kas form');
        $this->assertEquals(50000000, $kka->saldo_kas_olibs);
        $this->assertCount(1, $kka->cashDetails);

        // 2. Submit Form Tab 2: Mesin ATM (sub_tab = 'atm')
        $payloadAtm = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'atm',
            'no_arsip' => $noArsipGlobal,
            'posisi_tanggal_rekon' => $tglRekon,
            'atm_machines' => [
                [
                    'terminal_id' => '00128',
                    'lokasi' => 'Galeri ATM KC Poso',
                    'merk' => 'DBL',
                    'denominasi' => '100000',
                    'tanggal_pengisian_terakhir' => $tglRekon,
                    'saldo_cbs' => 100000000,
                    'keterangan_selisih' => '',
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                        ['nama_kaset' => 'Cassette 2', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                    ],
                ],
            ],
        ];
        $resAtm = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadAtm);
        $resAtm->assertSessionHasNoErrors();
        $resAtm->assertStatus(302);

        $kka->refresh();
        $this->assertCount(1, $kka->cashDetails, 'Cash details must NOT be wiped when ATM tab is submitted');
        $this->assertCount(1, $kka->atmMachines);
        $this->assertCount(2, $kka->atmMachines->first()->cassettes);

        // 3. Submit Form Tab 3: Observasi Lingkungan (sub_tab = 'observasi')
        $payloadObs = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'observasi',
            'no_arsip' => $noArsipGlobal,
            'posisi_tanggal_rekon' => $tglRekon,
            'obs_details' => [
                [
                    'area_observasi' => 'Titik Alarm Panic Button',
                    'kriteria_diharapkan' => 'Berfungsi normal dan terkoneksi ke vendor keamanan',
                    'hasil_observasi' => 'Sesuai',
                    'kondisi_aktual' => 'Tombol alarm diuji dan sirine aktif normal',
                    'tanggal_uji' => $tglRekon,
                ],
            ],
        ];
        $resObs = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadObs);
        $resObs->assertSessionHasNoErrors();
        $resObs->assertStatus(302);

        $kka->refresh();
        $this->assertCount(1, $kka->cashDetails, 'Kas details must remain intact');
        $this->assertCount(1, $kka->atmMachines, 'ATM details must remain intact');
        $this->assertCount(1, $kka->obsDetails);

        // 4. Submit Form Tab 4: Rekon Kartu ATM (sub_tab = 'kartu')
        $payloadKartu = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'kartu',
            'no_arsip' => $noArsipGlobal,
            'posisi_tanggal_rekon' => $tglRekon,
            'rekon_kartu_penyebab_selisih' => '',
            'kartu_atm_stock' => [
                [
                    'jenis_kartu' => 'Kartu Silver',
                    'keterangan_lokasi' => 'Brankas Khasanah',
                    'qty_fisik_kosong' => 100,
                    'qty_fisik_rusak' => 0,
                    'harga_modal' => 42180,
                    'saldo_buku_qty' => 100,
                    'saldo_buku_nominal' => 4218000,
                    'pin_mailer_qty' => 100,
                ],
            ],
            'kartu_atm_controls' => [
                [
                    'jenis_kartu' => 'Penyimpanan kartu ATM di brankas terkunci',
                    'jawaban_kontrol' => 'Ya',
                    'catatan_kontrol' => 'Terkunci dual control',
                ],
            ],
        ];
        $resKartu = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadKartu);
        $resKartu->assertSessionHasNoErrors();
        $resKartu->assertStatus(302);

        $kka->refresh();
        $this->assertCount(1, $kka->cashDetails, 'Kas details must remain intact');
        $this->assertCount(1, $kka->atmMachines, 'ATM details must remain intact');
        $this->assertCount(1, $kka->obsDetails, 'Obs details must remain intact');
        $this->assertCount(2, $kka->kartuAtmDetails); // 1 stock row + 1 control row

        // 5. Submit Form Tab 5: Rekon Barang Cetakan (sub_tab = 'cetakan')
        $payloadCetakan = [
            'physical_kka_id' => $kka->id,
            'sub_tab' => 'cetakan',
            'no_arsip' => $noArsipGlobal,
            'posisi_tanggal_rekon' => $tglRekon,
            'barang_cetakan_details' => [
                [
                    'kategori' => 'Buku Tabungan',
                    'nama_barang' => 'Buku Tabungan Simpeda',
                    'kriteria' => 'Buku Tabungan Simpeda Kosong',
                    'satuan' => 'Bh',
                    'stok_sistem' => 200,
                    'hasil_fisik' => 200,
                    'penyebab' => '',
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
        $resCetakan = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadCetakan);
        $resCetakan->assertSessionHasNoErrors();
        $resCetakan->assertStatus(302);

        $kka->refresh();
        $this->assertCount(1, $kka->cashDetails, 'All tabs preserved');
        $this->assertCount(1, $kka->atmMachines, 'All tabs preserved');
        $this->assertCount(1, $kka->obsDetails, 'All tabs preserved');
        $this->assertCount(2, $kka->kartuAtmDetails, 'All tabs preserved');
        $this->assertCount(2, $kka->barangCetakanDetails, 'All tabs preserved');
    }

    /**
     * TASK 5: Endpoint Download PDF & Excel berfungsi 100% untuk seluruh 5 KKA Fisik dan Rekap All
     */
    public function test_task_5_download_endpoints_return_200_valid_files(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-DOWNLOAD-TEST',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 50000000,
            'kas_pelaksana_nama' => 'RA Tester',
            'kas_pelaksana_jabatan' => 'Resident Auditor',
            'kas_saksi_nama' => 'Supervisor Kas',
            'kas_saksi_jabatan' => 'Head Teller',
        ]);

        PhysicalKkaCashDetail::create([
            'physical_kka_id' => $kka->id,
            'sub_area' => 'Kas Khasanah',
            'kelompok' => 'ULE',
            'objek_persediaan' => 'Uang Kertas Layak Edar',
            'pecahan' => 'Rp 100.000',
            'jumlah_lembar' => 500,
            'nominal' => 50000000,
        ]);

        $atm = PhysicalKkaAtmMachine::create([
            'physical_kka_id' => $kka->id,
            'terminal_id' => '00128',
            'lokasi' => 'Galeri ATM KC Poso',
            'merk' => 'DBL',
            'saldo_cbs' => 50000000,
            'total_fisik' => 50000000,
            'selisih' => 0,
            'status_uji' => 'Sesuai',
        ]);
        $atm->cassettes()->create([
            'nama_kaset' => 'Cassette 1',
            'denominasi' => 100000,
            'jumlah_lembar' => 500,
            'total_nominal' => 50000000,
        ]);

        PhysicalKkaObsDetail::create([
            'physical_kka_id' => $kka->id,
            'area_observasi' => 'Panic Button',
            'kriteria_diharapkan' => 'Aktif',
            'hasil_observasi' => 'Sesuai',
            'kondisi_aktual' => 'Normal',
            'status_uji' => 'Sesuai',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        PhysicalKkaKartuAtmDetail::create([
            'physical_kka_id' => $kka->id,
            'procedure_id' => 'PROC-KRT-01',
            'sub_area' => 'Persediaan Kartu ATM',
            'jenis_kartu' => 'Kartu Silver',
            'qty_fisik_kosong' => 50,
            'saldo_buku_qty' => 50,
            'status_uji' => 'SESUAI',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        PhysicalKkaBarangCetakanDetail::create([
            'physical_kka_id' => $kka->id,
            'procedure_id' => 'PROC-BCT-01',
            'kategori' => 'Buku Tabungan',
            'nama_barang' => 'Simpeda',
            'kriteria' => 'Buku Tabungan Simpeda',
            'stok_sistem' => 100,
            'hasil_fisik' => 100,
            'selisih' => 0,
            'status_uji' => 'SESUAI',
            'tanggal_uji' => Carbon::now()->toDateString(),
        ]);

        // 1. Uji PDF untuk ke-5 tab KKA Fisik & Rekap All
        $pdfTabs = ['kas', 'atm', 'observasi', 'kartu', 'cetakan', 'all'];
        foreach ($pdfTabs as $type) {
            $resPdf = $this->actingAs($this->raUser)
                ->get(route('daily-register.physical-kka.download-pdf', ['id' => $kka->id, 'type' => $type]));

            $resPdf->assertStatus(200);
            $this->assertEquals('application/pdf', $resPdf->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF-', $resPdf->getContent(), "File content must be a valid PDF format for {$type}");
        }

        // Uji named route aliases PDF
        $resPdfKasAlias = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.download-pdf-kas', $kka->id));
        $resPdfKasAlias->assertStatus(200);
        $resPdfAtmAlias = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.download-pdf-atm', $kka->id));
        $resPdfAtmAlias->assertStatus(200);

        // 2. Uji Excel untuk ke-5 tab KKA Fisik & Rekap All
        $excelTabs = ['kas', 'atm', 'observasi', 'kartu', 'cetakan', 'all'];
        foreach ($excelTabs as $type) {
            $resExcel = $this->actingAs($this->raUser)
                ->get(route('daily-register.physical-kka.export-excel', ['id' => $kka->id, 'type' => $type]));

            $resExcel->assertStatus(200);
            $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $resExcel->headers->get('content-type'));
            ob_start();
            $resExcel->sendContent();
            $excelContent = ob_get_clean();
            $this->assertGreaterThan(1000, strlen($excelContent), "Excel binary size must be valid (>1000 bytes) for {$type}");
        }

        // Uji named route aliases Excel
        $resExcelKasAlias = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.export-excel-kas', $kka->id));
        $resExcelKasAlias->assertStatus(200);
        $resExcelAtmAlias = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.export-excel-atm', $kka->id));
        $resExcelAtmAlias->assertStatus(200);
    }

    /**
     * TASK 4 & 5 (Lanjutan): Validasi bahwa Cetak BA & KKA HTML tetap berfungsi penuh
     */
    public function test_print_html_views_render_proper_bank_sulteng_headers(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-PRINT-TEST',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 50000000,
        ]);

        $resKas = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-kas', $kka->id));
        $resKas->assertStatus(200);
        $resKas->assertSee('BERITA ACARA PEMERIKSAAN KAS KHASANAH (OLIBS)');
        $resKas->assertSee('ARSIP-KKA-003-PRINT-TEST');

        $resAtm = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-atm', $kka->id));
        $resAtm->assertStatus(200);
        $resAtm->assertSee('BERITA ACARA CASH OPNAME KAS MESIN ATM &amp; KASSETTE', false);

        $resObs = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-observasi', $kka->id));
        $resObs->assertStatus(200);
        $resObs->assertSee('KKA Observasi Lingkungan &amp; Matriks CCTV', false);

        $resKartu = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-kartu', $kka->id));
        $resKartu->assertStatus(200);
        $resKartu->assertSee('KERTAS KERJA REKONSILIASI PERSEDIAAN KARTU ATM (FORMAT RESMI E.4.3)');

        $resCetakan = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-cetakan', $kka->id));
        $resCetakan->assertStatus(200);
        $resCetakan->assertSee('REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA &amp; PENGUJIAN KONTROL', false);
    }

    /**
     * REGRESSION TEST: Verifikasi bahwa impor CSV Offsite (isDailyRegister = false) tidak error kolom 'no_arsip' pada tabel kka_findings
     */
    public function test_offsite_csv_import_does_not_fail_on_kka_findings_table(): void
    {
        $today = date('d/m/Y');

        // 1. Offsite Kredit Parser
        $csvKredit = "TANGGAL,NO_REKENING,NAMA_DEBITUR,PLAFON,KOLEKTIBILITAS,NO_ARSIP,KET_TX\n"
            . "{$today},9988776655,DEBITUR OFFSITE,800000000,1,ARSIP-OFFSITE-01,Pencairan Kredit Offsite\n";
        $fileKredit = UploadedFile::fake()->createWithContent('kredit_offsite.csv', $csvKredit);
        $resKredit = (new DumpKreditParser())->parse($fileKredit, '003', false);
        $this->assertIsArray($resKredit);

        // 2. Offsite Biaya Parser
        $csvBiaya = "TANGGAL,NAMA_AKUN,NOMINAL,NO_ARSIP,KET_TX\n"
            . "{$today},BIAYA PEMELIHARAAN GEDUNG,30000000,ARSIP-OFFSITE-02,Renovasi Fasad KC Offsite\n";
        $fileBiaya = UploadedFile::fake()->createWithContent('biaya_offsite.csv', $csvBiaya);
        $resBiaya = (new DumpBiayaParser())->parse($fileBiaya, '003', false);
        $this->assertIsArray($resBiaya);

        // 3. Offsite Pengaduan Parser
        $csvPengaduan = "TANGGAL_PENGADUAN,NO_TIKET,NAMA_NASABAH,JENIS_PENGADUAN,SLA_HARI,NO_ARSIP,KET_TX\n"
            . "{$today},TKT-OFF-01,NASABAH OFFSITE,KARTU RUSAK,40,ARSIP-OFFSITE-03,Pengaduan Offsite Melebihi SLA\n";
        $filePengaduan = UploadedFile::fake()->createWithContent('pengaduan_offsite.csv', $csvPengaduan);
        $resPengaduan = (new DumpPengaduanParser())->parse($filePengaduan, '003', false);
        $this->assertIsArray($resPengaduan);

        // 4. Offsite CBS Parser
        $csvCbs = "TANGGAL,KETERANGAN,NOMINAL,NO_ARSIP,KET_TX\n"
            . "{$today},TARIK TUNAI KAS BESAR REVERSAL,150000000,ARSIP-OFF-04,Penarikan Tunai Kas Khasanah Offsite\n";
        $fileCbs = UploadedFile::fake()->createWithContent('cbs_offsite.csv', $csvCbs);
        $resCbs = (new DumpCbsParser())->parse($fileCbs, '003', false);
        $this->assertIsArray($resCbs);

        // 5. Offsite DPK Parser
        $csvDpk = "TGL_TX_AKHIR,NO_REK,NAMA_SINGKAT,STSDESC,SALDO_AKHIR,NO_VOUCHER,KETERANGAN_TX\n"
            . "{$today},1020304050,NASABAH DORMANT,DORMANT,25000000,VCH-OFF-05,Aktivasi Buku Tabungan Offsite\n";
        $fileDpk = UploadedFile::fake()->createWithContent('dpk_offsite.csv', $csvDpk);
        $resDpk = (new DumpDpkParser())->parse($fileDpk, '003', false);
        $this->assertIsArray($resDpk);

        // 6. Offsite Kartu ATM Parser
        $csvKartu = "TANGGAL,NO_KARTU,JENIS_KARTU,STATUS,USER_ID,KETERANGAN,NO_ARSIP,KET_TX\n"
            . "{$today},60110001,ATM GOLD,BLOCKED PIN 3X,USRAT01,KARTU TERTELAN / RUSAK,ARSIP-OFF-06,Nasabah salah PIN Offsite\n";
        $fileKartu = UploadedFile::fake()->createWithContent('kartu_offsite.csv', $csvKartu);
        $resKartu = (new DumpKartuAtmParser())->parse($fileKartu, '003', false);
        $this->assertIsArray($resKartu);
    }

    /**
     * EDGE CASE TEST: Verifikasi variasi header ekstrim dan fuzzy matching
     */
    public function test_dump_parser_helper_handles_extreme_and_fuzzy_headers(): void
    {
        $headers = ['TANGGAL', 'NO. ARSIP (VOUCHER/REF)', 'KETERANGAN TRANSAKSI', 'TOTAL NOMINAL'];

        $idxArsip = DumpParserHelper::findNoArsipIndex($headers);
        $this->assertEquals(1, $idxArsip, 'Should detect fuzzy header NO. ARSIP (VOUCHER/REF)');

        $idxKet = DumpParserHelper::findKetTxIndex($headers);
        $this->assertEquals(2, $idxKet, 'Should detect KETERANGAN TRANSAKSI');
    }

    /**
     * UI ROBUSTNESS TEST: Memastikan tidak ada input yang memiliki atribut HTML5 required di form KKA Fisik
     */
    public function test_physical_kka_rendered_html_has_no_required_inputs(): void
    {
        $response = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.index'));
        $response->assertStatus(200);

        $html = $response->getContent();
        // Regex untuk mencari tag input atau select dengan atribut required
        $hasRequiredInput = preg_match('/<(?:input|select)[^>]*\srequired[\s=>]/i', $html);
        $this->assertEquals(0, $hasRequiredInput, 'Physical KKA blade should not contain HTML5 required attributes on input/select elements');
    }
}
