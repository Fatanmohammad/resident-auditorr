<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaAtmMachine;
use App\Models\DailyRegister\PhysicalKkaAtmCassette;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\RegisterKlarifikasi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class Tahap2PhysicalKkaTest extends TestCase
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
            'nama_cabang' => 'Cabang Poso',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '003'], [
            'unit_name' => 'KC Poso',
            'unit_type' => 'KC',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_poso_tahap2@banksulteng.co.id'], [
            'nip' => '888001',
            'name' => 'RA Poso Tahap 2',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
            'unit_code' => '003',
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_skai_tahap2@banksulteng.co.id'], [
            'nip' => '888002',
            'name' => 'Admin SKAI Reviewer',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * Test 1: Halaman KKA Fisik Tahap 2 dapat diakses oleh RA dengan layout baru
     */
    public function test_ra_can_access_physical_kka_tahap2_page(): void
    {
        $response = $this->actingAs($this->raUser)
            ->get(route('daily-register.physical-kka.index'));

        $response->assertStatus(200);
        $response->assertSee('Tahap 2: KKA Fisik &amp; Rekonsiliasi', false);
        $response->assertSee('Cash Opname (Kas Khasanah)');
        $response->assertSee('Cash Opname (Mesin ATM)');
        $response->assertSee('Rekon Kartu ATM');
        $response->assertSee('Rekon Barang Cetakan');
        $response->assertSee('+ Tambah Mesin ATM');
    }

    /**
     * Test 2: Cash Opname Kas Khasanah (3 Kelompok) & Rekonsiliasi Saldo Kas Besar Olibs
     */
    public function test_khasanah_cash_opname_calculation_and_difference(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KKA-003-20261105-001',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 100000000,
            'penjelasan_selisih_kas' => 'Selisih kurang Rp 5.000.000 karena belum droping kas dari khasanah utama.',
            'kas_details' => [
                // ULE (Total: Rp 90.000.000)
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 900, 'nominal' => 90000000],
                // Logam (Total: Rp 3.000.000)
                ['kelompok' => 'Logam', 'objek_persediaan' => 'Uang Logam', 'pecahan' => 'Rp 1.000', 'jumlah_lembar' => 3000, 'nominal' => 3000000],
                // UTLE (Total: Rp 2.000.000)
                ['kelompok' => 'UTLE', 'objek_persediaan' => 'Uang lusuh/tidak layak edar', 'pecahan' => 'Rp 50.000', 'jumlah_lembar' => 40, 'nominal' => 2000000],
            ],
            // Total Fisik: 90jt + 3jt + 2jt = 95.000.000. Saldo Olibs = 100.000.000 => Selisih: -5.000.000 (Kurang)
        ];

        $response = $this->actingAs($this->raUser)
            ->post(route('daily-register.physical-kka.submit'), $payload);

        $response->assertSessionHas('success');

        $kka = PhysicalKka::where('kode_unit', '003')->latest('id')->first();
        $this->assertNotNull($kka);
        $this->assertEquals(95000000, (float)$kka->saldo_fisik_khasanah);
        $this->assertEquals(100000000, (float)$kka->saldo_kas_olibs);
        $this->assertEquals(-5000000, (float)$kka->selisih_kas_khasanah);
        $this->assertEquals('Final', $kka->status_final);

        // Harus masuk ke RegisterKlarifikasi karena selisih != 0
        $klarifikasi = RegisterKlarifikasi::where('kode_unit', '003')->where('source_type', PhysicalKkaCashDetail::class)->first();
        $this->assertNotNull($klarifikasi);
    }

    /**
     * Test 3: Cash Opname Mesin ATM & Kaset (Model Card per Mesin ATM)
     */
    public function test_atm_cards_and_cassettes_dynamic_saving_and_difference(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KKA-003-20261105-002',
            'saldo_kas_olibs' => 0,
            'atm_machines' => [
                [
                    'terminal_id' => '00128',
                    'lokasi' => 'ATM KC Poso Museum',
                    'merk' => 'DBL',
                    'denominasi' => 100000,
                    'tanggal_pengisian_terakhir' => Carbon::now()->toDateString(),
                    'saldo_cbs' => 200000000,
                    'keterangan_selisih' => null,
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                        ['nama_kaset' => 'Cassette 2', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                        ['nama_kaset' => 'Cassette 3', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                        ['nama_kaset' => 'Cassette 4', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                        ['nama_kaset' => 'Kaset Reject', 'denominasi' => 100000, 'jumlah_lembar' => 0, 'total_nominal' => 0],
                    ],
                ],
                [
                    'terminal_id' => '00139',
                    'lokasi' => 'ATM Gallery Swalayan Smart',
                    'merk' => 'CRM',
                    'denominasi' => 50000,
                    'tanggal_pengisian_terakhir' => Carbon::now()->toDateString(),
                    'saldo_cbs' => 100000000,
                    'keterangan_selisih' => 'Selisih kurang Rp 10.000.000 karena penarikan offline pending settlement.',
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 50000, 'jumlah_lembar' => 600, 'total_nominal' => 30000000],
                        ['nama_kaset' => 'Cassette 2', 'denominasi' => 50000, 'jumlah_lembar' => 600, 'total_nominal' => 30000000],
                        ['nama_kaset' => 'Cassette 3', 'denominasi' => 50000, 'jumlah_lembar' => 600, 'total_nominal' => 30000000],
                        ['nama_kaset' => 'Kaset Cadangan', 'denominasi' => 50000, 'jumlah_lembar' => 0, 'total_nominal' => 0],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->raUser)
            ->post(route('daily-register.physical-kka.submit'), $payload);

        $kka = PhysicalKka::where('kode_unit', '003')->latest('id')->first();
        $this->assertCount(2, $kka->atmMachines);

        // Mesin 1: Klop 200.000.000
        $atm1 = $kka->atmMachines->where('terminal_id', '00128')->first();
        $this->assertEquals(200000000, (float)$atm1->total_fisik);
        $this->assertEquals(0, (float)$atm1->selisih);
        $this->assertEquals('Selesai Diuji', $atm1->status_uji);
        $this->assertFalse((bool)$atm1->is_exception);
        $this->assertCount(5, $atm1->cassettes);

        // Mesin 2: Selisih kurang 10.000.000
        $atm2 = $kka->atmMachines->where('terminal_id', '00139')->first();
        $this->assertEquals(90000000, (float)$atm2->total_fisik);
        $this->assertEquals(10000000, (float)$atm2->selisih); // CBS (100jt) - Fisik (90jt)
        $this->assertTrue((bool)$atm2->is_exception);
        $this->assertCount(4, $atm2->cassettes);

        // Verifikasi exception mesin 2 tercatat di RegisterKlarifikasi
        $klarifAtm = RegisterKlarifikasi::where('source_id', $atm2->id)->where('source_type', PhysicalKkaAtmMachine::class)->first();
        $this->assertNotNull($klarifAtm);
    }

    /**
     * Test 4: Rekonsiliasi Kartu ATM (Format Tegas Section A & Section B — Acuan E.4.3)
     */
    public function test_kartu_atm_section_a_and_section_b_saving_and_verification(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KKA-003-20261105-003',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'rekon_kartu_penyebab_selisih' => 'Terdapat selisih 10 kartu Silver rusak belum dibuatkan Berita Acara Pemusnahan.',
            // Section A: Stok Matrix
            'kartu_atm_stock' => [
                [
                    'jenis_kartu' => 'Kartu Silver',
                    'harga_modal' => 42180,
                    'qty_fisik_kosong' => 490,
                    'qty_fisik_rusak' => 10,
                    'saldo_buku_qty' => 500,
                    'saldo_buku_nominal' => 21090000, // 500 * 42.180
                    'pin_mailer_qty' => 490,
                ],
                [
                    'jenis_kartu' => 'Kartu Gold',
                    'harga_modal' => 68820,
                    'qty_fisik_kosong' => 200,
                    'qty_fisik_rusak' => 0,
                    'saldo_buku_qty' => 200,
                    'saldo_buku_nominal' => 13764000, // 200 * 68.820
                    'pin_mailer_qty' => 200,
                ],
                [
                    'jenis_kartu' => 'Kartu Platinum',
                    'harga_modal' => 68820,
                    'qty_fisik_kosong' => 100,
                    'qty_fisik_rusak' => 0,
                    'saldo_buku_qty' => 100,
                    'saldo_buku_nominal' => 6882000,
                    'pin_mailer_qty' => 100,
                ],
            ],
            // Section B: 8 Item Kontrol Pengelolaan (Checklist Murni Ya / Tidak)
            'kartu_atm_controls' => [
                ['jenis_kartu' => 'Formulir permohonan kartu ATM (asli, tanda tangan nasabah) lengkap.', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Lengkap'],
                ['jenis_kartu' => 'Register kartu ATM keluar/masuk (stock opname) tertib dan mutakhir.', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Mutakhir'],
                ['jenis_kartu' => 'Otorisasi pengaktifan kartu ATM sesuai kewenangan.', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Sesuai'],
                ['jenis_kartu' => 'Tanda terima penyerahan kartu ATM oleh nasabah terdokumentasi.', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Ada'],
                ['jenis_kartu' => 'Penyimpanan & penyerahan PIN Mailer dilakukan terpisah dari fisik kartu ATM (dual control).', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Dual control aman'],
                ['jenis_kartu' => 'Kartu ATM rusak / expired / tertelan dibuatkan Berita Acara Pemusnahan.', 'jawaban_kontrol' => 'Tidak', 'catatan_kontrol' => 'Belum dibuatkan BA Pemusnahan untuk 10 kartu rusak'],
                ['jenis_kartu' => 'Penyimpanan stok kartu ATM kosong (blank) berada di lemari terkunci / brankas aman (dual control).', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Brankas terkunci'],
                ['jenis_kartu' => 'Register kartu ATM yang diblokir / ditutup tercatat lengkap.', 'jawaban_kontrol' => 'Ya', 'catatan_kontrol' => 'Lengkap'],
            ],
        ];

        $this->actingAs($this->raUser)
            ->post(route('daily-register.physical-kka.submit'), $payload);

        $kka = PhysicalKka::where('kode_unit', '003')->latest('id')->first();
        $this->assertNotNull($kka);

        // Section A verification: Silver memiliki selisih nominal persediaan
        $silver = $kka->kartuAtmDetails->where('jenis_kartu', 'Kartu Silver')->first();
        $this->assertNotNull($silver);
        $this->assertEquals(490, $silver->qty_fisik_kosong);
        $this->assertEquals(10, $silver->qty_fisik_rusak);
        $this->assertEquals(490 * 42180, (float)$silver->nominal_fisik);
        $this->assertEquals((490 * 42180) - 21090000, (float)$silver->selisih_persediaan);
        $this->assertTrue((bool)$silver->is_exception);

        // Section B verification: Item 6 berjawaban 'Tidak' harus exception
        $ctrl6 = $kka->kartuAtmDetails->where('sub_area', 'Kontrol')->where('jenis_kartu', 'Kartu ATM rusak / expired / tertelan dibuatkan Berita Acara Pemusnahan.')->first();
        $this->assertNotNull($ctrl6);
        $this->assertEquals('Tidak', $ctrl6->jawaban_kontrol);
        $this->assertTrue((bool)$ctrl6->is_exception);

        // Exception dari kontrol 'Tidak' harus masuk ke RegisterKlarifikasi
        $klarifCtrl = RegisterKlarifikasi::where('source_id', $ctrl6->id)->where('source_type', PhysicalKkaKartuAtmDetail::class)->first();
        $this->assertNotNull($klarifCtrl);
    }

    /**
     * Test 5: Rekonsiliasi Barang Cetakan (Merged Kriteria & 4 Kontrol Ya/Tidak)
     */
    public function test_barang_cetakan_merged_kriteria_and_checklist_controls(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KKA-003-20261105-004',
            'barang_cetakan_details' => [
                [
                    'kategori' => 'Formulir',
                    'nama_barang' => 'Formulir / Dokumen Pembukaan ATM',
                    'kriteria' => 'Formulir Pembukaan ATM — Kosong / Belum Digunakan',
                    'satuan' => 'Bh',
                    'stok_sistem' => 200,
                    'hasil_fisik' => 200,
                    'penyebab' => null,
                ],
                [
                    'kategori' => 'Formulir',
                    'nama_barang' => 'Formulir / Dokumen Pembukaan ATM',
                    'kriteria' => 'Formulir Pembukaan ATM — Rusak / Batal',
                    'satuan' => 'Bh',
                    'stok_sistem' => 0,
                    'hasil_fisik' => 5,
                    'penyebab' => 'Terdapat 5 lembar formulir rusak saat printer macet.',
                ],
                [
                    'kategori' => 'Bilyet Deposito',
                    'nama_barang' => 'Bilyet Deposito',
                    'kriteria' => 'Bilyet Deposito — Kosong / Belum Digunakan',
                    'satuan' => 'Bh',
                    'stok_sistem' => 300,
                    'hasil_fisik' => 300,
                    'penyebab' => null,
                ],
            ],
            'barang_cetakan_controls' => [
                ['butir' => 'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.', 'jawaban' => 'Ya', 'catatan' => 'Tertib'],
                ['butir' => 'Penyimpanan stok barang cetakan berada di lemari terkunci (dual control).', 'jawaban' => 'Ya', 'catatan' => 'Terkunci'],
                ['butir' => 'Berita acara pemusnahan warkat/barang cetakan rusak atau dibatalkan terdokumentasi.', 'jawaban' => 'Ya', 'catatan' => 'Lengkap'],
                ['butir' => 'Kesesuaian nomor seri warkat berurutan (tidak ada nomor seri hilang/melompat).', 'jawaban' => 'Ya', 'catatan' => 'Berurutan'],
            ],
        ];

        $this->actingAs($this->raUser)
            ->post(route('daily-register.physical-kka.submit'), $payload);

        $kka = PhysicalKka::where('kode_unit', '003')->latest('id')->first();
        $this->assertNotNull($kka);

        // Cek Pembukaan ATM Kosong
        $bctAtmKosong = $kka->barangCetakanDetails->where('kriteria', 'Formulir Pembukaan ATM — Kosong / Belum Digunakan')->first();
        $this->assertNotNull($bctAtmKosong);
        $this->assertEquals(0, (float)$bctAtmKosong->selisih);
        $this->assertEquals('SESUAI', $bctAtmKosong->status_uji);

        // Cek Pembukaan ATM Rusak (Selisih +5 lembar)
        $bctAtmRusak = $kka->barangCetakanDetails->where('kriteria', 'Formulir Pembukaan ATM — Rusak / Batal')->first();
        $this->assertNotNull($bctAtmRusak);
        $this->assertEquals(5, (float)$bctAtmRusak->selisih);
        $this->assertTrue((bool)$bctAtmRusak->is_exception);

        // 4 Kontrol tersimpan
        $controls = $kka->barangCetakanDetails->where('kategori', 'Kontrol');
        $this->assertCount(4, $controls);
    }

    /**
     * Test 6: Output Arsip Siap Cetak (Print / PDF) Langsung Terbuka Tanpa Tertahan Review
     */
    public function test_print_routes_accessible_immediately_after_submit(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-KKA-003-TEST-PRINT',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 50000000,
            'saldo_fisik_khasanah' => 50000000,
        ]);

        // Print BA Kas Khasanah
        $resKas = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-kas', $kka->id));
        $resKas->assertStatus(200);
        $resKas->assertSee('BERITA ACARA PEMERIKSAAN KAS KHASANAH (OLIBS)');
        $resKas->assertSee('ARSIP-KKA-003-TEST-PRINT');

        // Print BA Cash ATM
        $resAtm = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-ba-atm', $kka->id));
        $resAtm->assertStatus(200);
        $resAtm->assertSee('BERITA ACARA CASH OPNAME KAS MESIN ATM &amp; KASSETTE', false);

        // Print KKA Kartu ATM
        $resKartu = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-kartu', $kka->id));
        $resKartu->assertStatus(200);
        $resKartu->assertSee('KERTAS KERJA REKONSILIASI PERSEDIAAN KARTU ATM (FORMAT RESMI E.4.3)');
        $resKartu->assertSee('SECTION A');
        $resKartu->assertSee('SECTION B');

        // Print KKA Cetakan
        $resBct = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-kka-cetakan', $kka->id));
        $resBct->assertStatus(200);
        $resBct->assertSee('REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA &amp; PENGUJIAN KONTROL', false);

        // Print All Dossier
        $resAll = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-all', $kka->id));
        $resAll->assertStatus(200);
        $resAll->assertSee('Dossier Berkas Lengkap Tahap 2: KKA Fisik &amp; Rekonsiliasi Bank Sulteng', false);
    }

    /**
     * Test 7: Resubmission does not create duplicate or orphaned RegisterKlarifikasi records
     */
    public function test_resubmission_does_not_create_duplicate_or_orphaned_klarifikasi(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KKA-003-RESUBMIT',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 100000000,
            'penjelasan_selisih_kas' => 'Selisih awal Rp 5.000.000',
            'kas_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 950, 'nominal' => 95000000],
            ],
            'atm_machines' => [
                [
                    'terminal_id' => '00999',
                    'lokasi' => 'ATM KC Test',
                    'saldo_cbs' => 50000000,
                    'keterangan_selisih' => 'Selisih ATM Rp 10.000.000',
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 400, 'total_nominal' => 40000000],
                    ],
                ],
            ],
        ];

        // First submit
        $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payload);
        
        $kka = PhysicalKka::where('no_arsip', 'ARSIP-KKA-003-RESUBMIT')->first();
        $this->assertNotNull($kka);
        
        $countKlarifFirst = RegisterKlarifikasi::where('kode_unit', '003')->count();
        $this->assertGreaterThan(0, $countKlarifFirst);

        // Second submit (update)
        $payload['penjelasan_selisih_kas'] = 'Selisih revisi setelah opname ulang';
        $payload['atm_machines'][0]['keterangan_selisih'] = 'Selisih ATM revisi setelah opname ulang';
        $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payload);

        // Count should NOT double!
        $countKlarifSecond = RegisterKlarifikasi::where('kode_unit', '003')->count();
        $this->assertEquals($countKlarifFirst, $countKlarifSecond);

        // And no orphaned klarifikasi pointing to non-existent IDs
        $allKlarif = RegisterKlarifikasi::where('kode_unit', '003')->get();
        foreach ($allKlarif as $kl) {
            $modelClass = $kl->source_type;
            if ($modelClass && class_exists($modelClass)) {
                $this->assertNotNull($modelClass::find($kl->source_id), "Found orphaned klarifikasi pointing to deleted {$modelClass} ID {$kl->source_id}");
            }
        }
    }

    /**
     * Test 8: Explanation is required when discrepancy exists (server-side validation)
     */
    public function test_explanation_is_required_when_discrepancy_exists(): void
    {
        // Khasanah discrepancy without explanation
        $payloadKhasanah = [
            'no_arsip' => 'ARSIP-VAL-001',
            'saldo_kas_olibs' => 100000000,
            'penjelasan_selisih_kas' => '', // Empty!
            'kas_details' => [
                ['kelompok' => 'ULE', 'objek_persediaan' => 'Uang Kertas Layak Edar', 'pecahan' => 'Rp 100.000', 'jumlah_lembar' => 900, 'nominal' => 90000000],
            ],
        ];

        $res1 = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadKhasanah);
        $res1->assertSessionHasErrors(['penjelasan_selisih_kas']);

        // ATM discrepancy without keterangan_selisih
        $payloadAtm = [
            'no_arsip' => 'ARSIP-VAL-002',
            'saldo_kas_olibs' => 0,
            'atm_machines' => [
                [
                    'terminal_id' => '00111',
                    'lokasi' => 'ATM KC Test',
                    'saldo_cbs' => 100000000,
                    'keterangan_selisih' => '', // Empty!
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                    ],
                ],
            ],
        ];

        $res2 = $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payloadAtm);
        $res2->assertSessionHasErrors(['atm_machines.0.keterangan_selisih']);
    }

    /**
     * Test 9: ATM machine status_uji reflects cassettes testing
     */
    public function test_atm_machine_status_uji_reflects_cassettes_testing(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-STATUS-001',
            'atm_machines' => [
                // Machine 1 with cassettes filled
                [
                    'terminal_id' => '00555',
                    'lokasi' => 'ATM Active',
                    'saldo_cbs' => 50000000,
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                    ],
                ],
                // Machine 2 without cassettes filled (zero values)
                [
                    'terminal_id' => '00666',
                    'lokasi' => 'ATM Untested',
                    'saldo_cbs' => 0,
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 0, 'total_nominal' => 0],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payload);

        $kka = PhysicalKka::where('no_arsip', 'ARSIP-STATUS-001')->first();
        $this->assertNotNull($kka);

        $atm1 = $kka->atmMachines->where('terminal_id', '00555')->first();
        $this->assertEquals('Selesai Diuji', $atm1->status_uji);

        $atm2 = $kka->atmMachines->where('terminal_id', '00666')->first();
        $this->assertEquals('Belum Diuji', $atm2->status_uji);
    }

    /**
     * Test 10: Dossier print all contains all sections cleanly without nested HTML documents
     */
    public function test_dossier_print_all_contains_all_sections_cleanly(): void
    {
        $kka = PhysicalKka::create([
            'kode_unit' => '003',
            'user_id' => $this->raUser->id,
            'tanggal_pemeriksaan' => Carbon::now()->toDateString(),
            'status_final' => 'Final',
            'no_arsip' => 'ARSIP-DOSSIER-CLEAN',
            'posisi_tanggal_rekon' => Carbon::now()->toDateString(),
            'saldo_kas_olibs' => 50000000,
            'saldo_fisik_khasanah' => 50000000,
        ]);

        $res = $this->actingAs($this->raUser)->get(route('daily-register.physical-kka.print-all', $kka->id));
        $res->assertStatus(200);

        $content = $res->getContent();
        // Should have exactly ONE <!DOCTYPE html>
        $this->assertEquals(1, substr_count($content, '<!DOCTYPE html>'));
        // Should have exactly ONE </html>
        $this->assertEquals(1, substr_count($content, '</html>'));
        // Should have all 4 sections
        $this->assertStringContainsString('BERITA ACARA PEMERIKSAAN KAS KHASANAH (OLIBS)', $content);
        $this->assertStringContainsString('BERITA ACARA CASH OPNAME KAS MESIN ATM', $content);
        $this->assertStringContainsString('KERTAS KERJA REKONSILIASI PERSEDIAAN KARTU ATM', $content);
        $this->assertStringContainsString('REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA', $content);
    }

    /**
     * Test 11: Klarifikasi displays ATM machine exception properly
     */
    public function test_klarifikasi_displays_atm_machine_exception_properly(): void
    {
        $payload = [
            'no_arsip' => 'ARSIP-KLARIF-ATM',
            'atm_machines' => [
                [
                    'terminal_id' => '00888',
                    'lokasi' => 'ATM KC Sentral Klarifikasi',
                    'saldo_cbs' => 80000000,
                    'keterangan_selisih' => 'Selisih ATM Rp 30.000.000 akibat uang tersangkut pada kaset 2',
                    'cassettes' => [
                        ['nama_kaset' => 'Cassette 1', 'denominasi' => 100000, 'jumlah_lembar' => 500, 'total_nominal' => 50000000],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->raUser)->post(route('daily-register.physical-kka.submit'), $payload);

        $res = $this->actingAs($this->raUser)->get(route('daily-register.klarifikasi.index'));
        $res->assertStatus(200);
        $res->assertSee('Cash Opname ATM');
        $res->assertSee('00888');
        $res->assertSee('ATM KC Sentral Klarifikasi');
        $res->assertSee('Selisih ATM Rp 30.000.000 akibat uang tersangkut pada kaset 2');
    }
}
