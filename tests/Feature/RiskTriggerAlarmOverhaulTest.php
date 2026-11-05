<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Services\RiskTrigger\RiskTriggerService;
use App\Services\PelaporanBulanan\Sop08Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RiskTriggerAlarmOverhaulTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $raUser;
    protected Cabang $cabang;
    protected Unit $unit;
    protected RiskTriggerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(RiskTriggerService::class);

        $this->cabang = Cabang::firstOrCreate(['kode_cabang' => 'TEST-01'], [
            'nama_cabang' => 'Cabang Uji Alarm',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '999'], [
            'unit_name' => 'KC Uji Alarm',
            'unit_type' => 'KC',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_alarm_final@banksulteng.co.id'], [
            'nip' => '999001',
            'name' => 'Admin Reviewer Test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_alarm_final@banksulteng.co.id'], [
            'nip' => '999002',
            'name' => 'RA Pelaksana Test',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
            'unit_code' => '999',
        ]);
    }

    /**
     * Point 1: Ambang batas alarm pemicu diubah menjadi > 10 temuan (minimal 11 temuan per Cabang).
     * 10 temuan = alarm belum aktif.
     * 11 temuan = alarm otomatis aktif.
     */
    public function test_alarm_triggers_only_when_accumulation_exceeds_10()
    {
        // 1. Buat 10 temuan di unit '999'
        for ($i = 1; $i <= 10; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-TEST-10-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-WP-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek Selisih Kas {$i}",
                'uraian_trigger' => "Selisih kas hari ke-{$i}",
                'bukti_awal' => 'Kuitansi & Buku Kas',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 20000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => false,
                'keputusan_otomatis' => 'VISIT SEGERA',
                'keputusan_final' => 'VISIT SEGERA',
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $this->service->recalculateDuplicatesAndAlarms();

        // 10 temuan: alarm belum aktif
        $activeAlarms10 = $this->service->getActiveAlarms();
        $alarmForUnit10 = collect($activeAlarms10)->firstWhere('kode_unit', '999');
        $this->assertNull($alarmForUnit10, 'Alarm tidak boleh menyala untuk 10 temuan');
        $this->assertEquals(0, $this->service->getActiveAlarmsCount());

        // 2. Tambah temuan ke-11 (> 10)
        RegisterTrigger::create([
            'trigger_id' => 'TRG-TEST-11-11',
            'sumber' => 'Offsite',
            'source_wp_id' => 'OFF-WP-11',
            'tanggal_trigger' => now(),
            'kode_unit' => '999',
            'nama_unit' => 'KC Uji Alarm',
            'area' => 'Kredit',
            'objek_key' => 'Objek Kredit Macet 11',
            'uraian_trigger' => 'Penyimpangan restrukturisasi kredit ke-11',
            'bukti_awal' => 'Perjanjian Kredit & SK Pemutus',
            'jumlah_kejadian' => 1,
            'eksposur_nominal' => 50000000,
            'risk_score_sumber' => 6,
            'risk_score_reassessment' => 6,
            'risk_level_reassessment' => 'SEDANG',
            'kelengkapan_data' => 'VALID',
            'status_duplikat' => 'UNIK',
            'alarm_akumulasi' => false,
            'keputusan_otomatis' => 'VISIT SEGERA',
            'keputusan_final' => 'VISIT SEGERA',
            'status_workflow' => 'TERCATAT',
        ]);

        $this->service->recalculateDuplicatesAndAlarms();

        // 11 temuan: alarm aktif
        $activeAlarms11 = $this->service->getActiveAlarms();
        $alarmForUnit11 = collect($activeAlarms11)->firstWhere('kode_unit', '999');
        $this->assertNotNull($alarmForUnit11, 'Alarm wajib aktif ketika akumulasi > 10 temuan (11 temuan)');
        $this->assertEquals(11, $alarmForUnit11['count']);
        $this->assertEquals('Teller Kas', $alarmForUnit11['area']); // Area terbanyak (10 Teller Kas vs 1 Kredit)
        $this->assertStringContainsString('Teller Kas (10)', $alarmForUnit11['sebaran_area']);
        $this->assertStringContainsString('Kredit (1)', $alarmForUnit11['sebaran_area']);
        $this->assertEquals(1, $this->service->getActiveAlarmsCount());
    }

    /**
     * Point 2: Notifikasi Lonceng Navbar Admin & Alert Banner Dashboard Admin.
     */
    public function test_admin_receives_bell_notification_and_dashboard_alert_banner()
    {
        // Siapkan 12 temuan di cabang 999
        for ($i = 1; $i <= 12; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-BELL-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-BELL-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'DPK Layanan',
                'objek_key' => "Objek DPK {$i}",
                'uraian_trigger' => "Temuan DPK {$i}",
                'bukti_awal' => 'Formulir Pembukaan Rekening',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 15000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $this->service->recalculateDuplicatesAndAlarms();

        // 1. Dashboard Admin memuat alert banner mencolok & link ke risk-trigger.index
        $dashResponse = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('ALARM RISK TRIGGER (SOP 06)');
        $dashResponse->assertSee('> 10 Temuan');
        $dashResponse->assertSee('KC Uji Alarm');
        $dashResponse->assertSee('12 Temuan');
        $dashResponse->assertSee(route('risk-trigger.index'));

        // 2. Bell Notification Navbar Admin memuat teks alarm sesuai spesifikasi
        $dashResponse->assertSee('⚠️ Risk Trigger: Cabang KC Uji Alarm memiliki 12 temuan (> 10). Segera informasikan untuk pendalaman lebih lanjut.');

        // 3. RA TIDAK menerima notifikasi Risk Trigger pada navbar
        $raDashResponse = $this->actingAs($this->raUser)->get(route('dashboard'));
        $raDashResponse->assertStatus(200);
        $raDashResponse->assertDontSee('⚠️ Risk Trigger: Cabang KC Uji Alarm memiliki 12 temuan (> 10)');
        $raDashResponse->assertDontSee('ALARM RISK TRIGGER (SOP 06)');
    }

    /**
     * Point 1: Sidebar Navigasi Admin menampilkan 1 menu tunggal Risk Trigger dengan badge merah,
     * dan disembunyikan total untuk role RA.
     */
    public function test_sidebar_displays_single_menu_for_admin_with_badge_and_hidden_for_ra()
    {
        // Buat 11 temuan agar ada alarm aktif
        for ($i = 1; $i <= 11; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-SIDE-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-SIDE-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek {$i}",
                'uraian_trigger' => "Temuan kas {$i}",
                'bukti_awal' => 'Kuitansi',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 10000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        // 1. Tampilan Admin: Menu tunggal Risk Trigger dengan badge 1
        $adminView = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $adminView->assertStatus(200);
        $adminView->assertSee('Risk Trigger');
        $adminView->assertSee('badge bg-danger rounded-pill', false);
        $adminView->assertSee(route('risk-trigger.index'));

        // Tidak ada sub-menu bertingkat lama di sidebar
        $adminView->assertDontSee('Ringkasan Control');
        $adminView->assertDontSee('Assignment & Scope');
        $adminView->assertDontSee('Klarifikasi & Simpulan TL');

        // 2. Tampilan RA: Menu Risk Trigger TIDAK ADA sama sekali di sidebar
        $raView = $this->actingAs($this->raUser)->get(route('dashboard'));
        $raView->assertStatus(200);
        $raView->assertDontSee('Risk Trigger');
        $raView->assertDontSee(route('risk-trigger.index'));
    }

    /**
     * Point 1: Akses Role Non-Admin (RA dll) dibatasi tegas 403 Forbidden.
     */
    public function test_non_admin_cannot_access_risk_trigger_403_forbidden()
    {
        // RA mencoba mengakses halaman tunggal Risk Trigger
        $responseIndex = $this->actingAs($this->raUser)->get(route('risk-trigger.index'));
        $responseIndex->assertStatus(403);

        // RA mencoba mengakses sub-menu lama Risk Trigger
        $responseControl = $this->actingAs($this->raUser)->get(route('risk-trigger.control'));
        $responseControl->assertStatus(403);

        $responseAssignment = $this->actingAs($this->raUser)->get(route('risk-trigger.assignment'));
        $responseAssignment->assertStatus(403);

        $responseKka = $this->actingAs($this->raUser)->get(route('risk-trigger.kka.index'));
        $responseKka->assertStatus(403);

        $responseSimpulan = $this->actingAs($this->raUser)->get(route('risk-trigger.simpulan-tl.index'));
        $responseSimpulan->assertStatus(403);
    }

    /**
     * Point 3: Halaman Tunggal Risk Trigger untuk Admin memuat KPI Ringkas,
     * Tabel Daftar Alarm Cabang, Modal Rincian Temuan, dan Tombol Tandai Sudah Diinfokan.
     */
    public function test_admin_single_page_risk_trigger_view_kpi_table_and_detail_modal()
    {
        // Buat 13 temuan di KC Uji Alarm
        for ($i = 1; $i <= 13; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-PAGE-{$i}",
                'sumber' => $i % 2 === 0 ? 'Daily Review' : 'Offsite',
                'source_wp_id' => "WP-PAGE-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => $i <= 8 ? 'Teller Kas' : 'Kredit',
                'objek_key' => "Objek Temuan {$i}",
                'uraian_trigger' => "Deskripsi anomali temuan ke-{$i}",
                'bukti_awal' => "Bukti Pendukung {$i}",
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 15000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $this->service->recalculateDuplicatesAndAlarms();

        $pageResponse = $this->actingAs($this->adminUser)->get(route('risk-trigger.index'));
        $pageResponse->assertStatus(200);

        // 1. KPI Ringkas di atas
        $pageResponse->assertSee('Total Cabang Terkena Trigger (> 10)');
        $pageResponse->assertSee('Belum Diinfokan / Baru');
        $pageResponse->assertSee('Sudah Diinfokan');

        // 2. Tabel Alarm Cabang
        $pageResponse->assertSee('Daftar Alarm Cabang (> 10 Temuan)');
        $pageResponse->assertSee('KC Uji Alarm');
        $pageResponse->assertSee('13 Temuan');
        $pageResponse->assertSee('Perlu Diinfokan');
        $pageResponse->assertSee('Teller Kas (8), Kredit (5)');

        // 3. Tombol Aksi
        $pageResponse->assertSee('Lihat Rincian');
        $pageResponse->assertSee('Tandai Diinfokan');

        // 4. Modal Read-only Rincian Temuan
        $pageResponse->assertSee('Rincian Temuan Cabang: KC Uji Alarm');
        $pageResponse->assertSee('Deskripsi anomali temuan ke-1');
        $pageResponse->assertSee('Deskripsi anomali temuan ke-13');
    }

    /**
     * Point 3: Tindak Lanjut "Tandai Sudah Diinfokan" langsung mengubah status alarm cabang
     * menjadi hijau tanpa form KKA / penugasan panjang.
     */
    public function test_admin_mark_as_informed_updates_status_to_sudah_diinfokan()
    {
        // Buat 11 temuan
        for ($i = 1; $i <= 11; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-INF-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-INF-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Biaya Beban',
                'objek_key' => "Objek Biaya {$i}",
                'uraian_trigger' => "Selisih biaya operasional {$i}",
                'bukti_awal' => 'Voucher Pembayaran',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 5000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        // Tandai Sudah Diinfokan
        $postResponse = $this->actingAs($this->adminUser)->post(route('risk-trigger.inform', '999'), [
            'catatan' => 'Telah diinformasikan kepada Pak Adam untuk penugasan audit lapangan',
        ]);
        $postResponse->assertRedirect(route('risk-trigger.index'));
        $postResponse->assertSessionHas('success');

        // Cek database
        $triggers = RegisterTrigger::where('kode_unit', '999')->get();
        foreach ($triggers as $t) {
            $this->assertEquals('TELAH DITINDAKLANJUTI', $t->status_tindak_lanjut_alarm);
            $this->assertEquals('DALAM PANTAUAN ALARM', $t->status_workflow);
            $this->assertNotNull($t->waktu_tindak_lanjut_alarm);
        }

        // Cek halaman index menampilkan badge Sudah Diinfokan (hijau)
        $viewAfter = $this->actingAs($this->adminUser)->get(route('risk-trigger.index'));
        $viewAfter->assertStatus(200);
        $viewAfter->assertSee('Sudah Diinfokan');
        $viewAfter->assertSee('Terinfokan');

        // KPI Sudah Diinfokan bernilai 1, Belum Diinfokan bernilai 0
        $kpi = $this->service->getAlarmKpiStats();
        $this->assertEquals(1, $kpi['total_cabang']);
        $this->assertEquals(0, $kpi['belum_diinfokan']);
        $this->assertEquals(1, $kpi['sudah_diinfokan']);
    }

    /**
     * Point 4: Pembersihan Route & Fallback Redirects untuk Sub-Menu Lama.
     * Memastikan tidak ada dead link atau error 404/500 saat rute lama diakses Admin.
     */
    public function test_old_sub_menus_redirect_safely_to_index_with_no_dead_links()
    {
        $oldRoutes = [
            route('risk-trigger.control'),
            route('risk-trigger.register'),
            route('risk-trigger.create'),
            route('risk-trigger.assignment'),
            route('risk-trigger.assignment.create'),
            route('risk-trigger.kka.index'),
            route('risk-trigger.simpulan-tl.index'),
        ];

        foreach ($oldRoutes as $url) {
            $response = $this->actingAs($this->adminUser)->get($url);
            $response->assertRedirect(route('risk-trigger.index'));
        }
    }

    /**
     * Point 4: Integrasi Modul Pelaporan Bulanan (SOP 08) berjalan aman tanpa error.
     */
    public function test_sop08_pelaporan_bulanan_remains_functional_with_risk_trigger()
    {
        $sop08Service = app(Sop08Service::class);
        $periodeDate = now()->toDateString();

        // 1. Ambil hasil utama temuan kunci dari SOP 08 (terintegrasi dengan Risk Trigger)
        $temuanKunci = $sop08Service->getHasilUtamaTemuanKunci($periodeDate, '999');
        $this->assertIsArray($temuanKunci);

        // 2. Ambil status kepatuhan KKA
        $compliance = $sop08Service->getKkaComplianceStatus('999', $periodeDate);
        $this->assertIsArray($compliance);

        // 3. Pastikan halaman matriks konsolidasi cabang berjalan sukses (200 OK)
        $resKonsolidasi = $this->actingAs($this->adminUser)->get(route('pelaporan-bulanan.konsolidasi'));
        $resKonsolidasi->assertStatus(200);
    }

    /**
     * Uji Bug Fix 1: markBranchAsInformed tanpa menyertakan argumen actor tidak melempar Fatal Error.
     */
    public function test_mark_branch_as_informed_without_actor_does_not_throw_fatal_error()
    {
        for ($i = 1; $i <= 11; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-NOACT-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-NOACT-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek Kas {$i}",
                'uraian_trigger' => "Selisih kas {$i}",
                'bukti_awal' => 'Kuitansi',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 1000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        // Jalankan tanpa auth dan tanpa actor
        $affected = $this->service->markBranchAsInformed('999');
        $this->assertGreaterThan(0, $affected);

        $t = RegisterTrigger::where('kode_unit', '999')->first();
        $this->assertEquals('TELAH DITINDAKLANJUTI', $t->status_tindak_lanjut_alarm);
        $this->assertEquals('Admin SKAI', $t->ditindaklanjuti_oleh);
    }

    /**
     * Uji Bug Fix 2: Alert Banner Dashboard & Lonceng Navbar disembunyikan ketika alarm cabang sudah ditandai selesai/diteruskan.
     * Mengimplementasikan arahan tegas: "selama cabang tersebut masih berstatus Active Trigger belum ditandai selesai/diteruskan."
     */
    public function test_dashboard_alert_banner_and_bell_disappear_when_all_branches_are_marked_informed()
    {
        // 1. Buat 12 temuan aktif di cabang 999
        for ($i = 1; $i <= 12; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-BANNER-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-BANNER-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'DPK Layanan',
                'objek_key' => "Objek DPK {$i}",
                'uraian_trigger' => "Temuan DPK {$i}",
                'bukti_awal' => 'Formulir Pembukaan',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 2000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $this->service->recalculateDuplicatesAndAlarms();

        // Sebelum ditandai: Alert banner muncul di Dashboard
        $dashBefore = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashBefore->assertStatus(200);
        $dashBefore->assertSee('ALARM RISK TRIGGER (SOP 06)');
        $dashBefore->assertSee('KC Uji Alarm');
        $dashBefore->assertSee('⚠️ Risk Trigger: Cabang KC Uji Alarm memiliki 12 temuan (> 10)');

        // Tandai Sudah Diinfokan
        $this->actingAs($this->adminUser)->post(route('risk-trigger.inform', '999'), [
            'catatan' => 'Sudah diinfokan ke pimpinan',
        ]);

        // Setelah ditandai: Alert banner TIDAK LAGI muncul di Dashboard
        $dashAfter = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashAfter->assertStatus(200);
        $dashAfter->assertDontSee('ALARM RISK TRIGGER (SOP 06)');
        $dashAfter->assertDontSee('⚠️ Risk Trigger: Cabang KC Uji Alarm memiliki 12 temuan (> 10)');

        // Namun pada halaman tunggal Risk Trigger, cabang tetap tercatat dengan status Sudah Diinfokan
        $indexAfter = $this->actingAs($this->adminUser)->get(route('risk-trigger.index'));
        $indexAfter->assertStatus(200);
        $indexAfter->assertSee('KC Uji Alarm');
        $indexAfter->assertSee('Sudah Diinfokan');
    }

    /**
     * Uji Bug Fix 3: SOP 08 Checklist Kepatuhan KKA tidak mem-flag MERAH cabang yang temuannya <= 10,
     * dan otomatis HIJAU jika alarm > 10 telah ditandai Sudah Diinfokan oleh Admin.
     */
    public function test_sop08_compliance_status_for_sub_threshold_and_informed_branches()
    {
        $sop08Service = app(Sop08Service::class);
        $periodeDate = now()->toDateString();

        // Skenario A: Cabang dengan 3 temuan (<= 10 temuan, bukan alarm)
        for ($i = 1; $i <= 3; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-SOP08-SUB-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-SUB-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek Sub {$i}",
                'uraian_trigger' => "Temuan minor {$i}",
                'bukti_awal' => 'Kuitansi',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 500000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => false,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $complianceSub = $sop08Service->getKkaComplianceStatus('999', $periodeDate);
        $rtItemSub = $complianceSub['details']['Risk Trigger SOP 06'] ?? null;
        $this->assertNotNull($rtItemSub);
        $this->assertEquals('HIJAU', $rtItemSub['status_kepatuhan'], 'Cabang dengan temuan <= 10 harus berstatus HIJAU (bukan alarm)');
        $this->assertEquals(0, $rtItemSub['populasi']);

        // Skenario B: Tambah temuan hingga 11 (> 10 temuan, alarm aktif belum diinfokan)
        for ($i = 4; $i <= 11; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-SOP08-ALARM-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-ALARM-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek Alarm {$i}",
                'uraian_trigger' => "Temuan alarm {$i}",
                'bukti_awal' => 'Kuitansi',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 500000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        $complianceAlarm = $sop08Service->getKkaComplianceStatus('999', $periodeDate);
        $rtItemAlarm = $complianceAlarm['details']['Risk Trigger SOP 06'] ?? null;
        $this->assertNotNull($rtItemAlarm);
        $this->assertEquals('MERAH', $rtItemAlarm['status_kepatuhan'], 'Alarm > 10 yang belum diinfokan harus berstatus MERAH');
        $this->assertEquals(1, $rtItemAlarm['item_tertunggak']);

        // Skenario C: Admin menandai alarm Sudah Diinfokan
        $this->service->markBranchAsInformed('999');

        $complianceInformed = $sop08Service->getKkaComplianceStatus('999', $periodeDate);
        $rtItemInformed = $complianceInformed['details']['Risk Trigger SOP 06'] ?? null;
        $this->assertNotNull($rtItemInformed);
        $this->assertEquals('HIJAU', $rtItemInformed['status_kepatuhan'], 'Alarm yang sudah diinfokan Admin harus berubah menjadi HIJAU');
        $this->assertEquals(0, $rtItemInformed['item_tertunggak']);
    }

    /**
     * Uji Bug Fix 4: recalculateDuplicatesAndAlarms mempertahankan status workflow DALAM PANTAUAN ALARM.
     */
    public function test_recalculate_preserves_dalam_pantauan_alarm_workflow_status()
    {
        for ($i = 1; $i <= 11; $i++) {
            RegisterTrigger::create([
                'trigger_id' => "TRG-PRESERVE-{$i}",
                'sumber' => 'Daily Review',
                'source_wp_id' => "DR-PRESERVE-{$i}",
                'tanggal_trigger' => now(),
                'kode_unit' => '999',
                'nama_unit' => 'KC Uji Alarm',
                'area' => 'Teller Kas',
                'objek_key' => "Objek {$i}",
                'uraian_trigger' => "Temuan kas {$i}",
                'bukti_awal' => 'Kuitansi',
                'jumlah_kejadian' => 1,
                'eksposur_nominal' => 1000000,
                'risk_score_sumber' => 6,
                'risk_score_reassessment' => 6,
                'risk_level_reassessment' => 'SEDANG',
                'kelengkapan_data' => 'VALID',
                'status_duplikat' => 'UNIK',
                'alarm_akumulasi' => true,
                'status_workflow' => 'TERCATAT',
            ]);
        }

        // Tandai Sudah Diinfokan
        $this->service->markBranchAsInformed('999');

        // Jalankan kalkulasi ulang formula turunan
        $this->service->recalculateDuplicatesAndAlarms();

        $triggers = RegisterTrigger::where('kode_unit', '999')->get();
        foreach ($triggers as $t) {
            $this->assertEquals('DALAM PANTAUAN ALARM', $t->status_workflow);
            $this->assertEquals('TELAH DITINDAKLANJUTI', $t->status_tindak_lanjut_alarm);
        }
    }
}
