<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\PelaporanBulanan\Sop08MasterParameter;
use App\Models\PelaporanBulanan\Sop08MasterUnit;
use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Models\PelaporanBulanan\Sop08ChecklistLaporan;
use App\Models\PelaporanBulanan\Sop08RekapKegiatan;
use App\Models\PelaporanBulanan\Sop08RekapIsuTl;
use App\Models\PelaporanBulanan\Sop08NarasiLaporan;
use App\Models\PelaporanBulanan\Sop08KkaBranchStatus;
use App\Services\PelaporanBulanan\Sop08Service;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class PelaporanBulananOverhaulTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $raUser;
    protected User $raOtherUser;
    protected Sop08Service $service;
    protected string $periode = '2026-11';
    protected string $periodeDate = '2026-11-01';
    protected string $reportId;
    protected string $otherReportId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(Sop08Service::class);

        // Ensure active master parameter exists
        Sop08MasterParameter::firstOrCreate(['id' => 1], [
            'periode_dashboard' => $this->periodeDate,
            'tahun_dashboard' => 2026,
            'target_tepat_waktu_pct' => 100,
            'target_kepatuhan_kegiatan_pct' => 100,
            'max_isu_high_risk' => 0,
            'max_overdue_tl' => 0,
            'target_quality_score' => 90,
            'cut_off_default' => 5,
        ]);

        // Setup Cabang
        $cabangPalu = Cabang::firstOrCreate(['kode_cabang' => '001'], [
            'nama_cabang' => 'Kantor Cabang Utama Palu',
            'tipe' => 'kcu',
        ]);

        $cabangPoso = Cabang::firstOrCreate(['kode_cabang' => '002'], [
            'nama_cabang' => 'Kantor Cabang Poso',
            'tipe' => 'cabang_a',
        ]);

        // Setup master unit kerja
        $masterPalu = Sop08MasterUnit::updateOrCreate(['kode_unit' => '001'], [
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'assigned_ra' => 'RA Palu Test',
            'unit_ra' => 'KC Palu',
            'jenis_unit' => 'KC',
            'cabang_id' => $cabangPalu->id,
            'is_active' => true,
        ]);

        $masterPoso = Sop08MasterUnit::updateOrCreate(['kode_unit' => '002'], [
            'nama_unit' => 'Kantor Cabang Poso',
            'assigned_ra' => 'RA Poso Test',
            'unit_ra' => 'KC Poso',
            'jenis_unit' => 'KC',
            'cabang_id' => $cabangPoso->id,
            'is_active' => true,
        ]);

        // Setup Users
        $this->adminUser = User::updateOrCreate(['email' => 'admin_sop08_test@banksulteng.co.id'], [
            'nip' => 'ADM008',
            'name' => 'Admin SKAI Test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->raUser = User::updateOrCreate(['email' => 'ra_palu_test@banksulteng.co.id'], [
            'nip' => 'RA001',
            'name' => 'RA Palu User',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $cabangPalu->id,
        ]);

        $this->raOtherUser = User::updateOrCreate(['email' => 'ra_poso_test@banksulteng.co.id'], [
            'nip' => 'RA002',
            'name' => 'RA Poso User',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $cabangPoso->id,
        ]);

        $masterPalu->update(['user_ra_id' => $this->raUser->id]);
        $masterPoso->update(['user_ra_id' => $this->raOtherUser->id]);

        // Setup Kalender Laporan
        $this->reportId = 'REP-2026-11-001';
        $kalender = Sop08KalenderLaporan::firstOrCreate(['report_id' => $this->reportId], [
            'periode' => $this->periodeDate,
            'kode_unit' => '001',
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'resident_auditor' => 'RA Palu User',
            'unit_ra' => 'KC Palu',
            'wajib_lapor' => 'YA',
            'jatuh_tempo' => '2026-11-05',
            'status_final' => 'DRAFT',
            'status_review' => 'MENUNGGU REVIEW',
            'kelengkapan' => 100,
        ]);

        $this->otherReportId = 'REP-2026-11-002';
        Sop08KalenderLaporan::firstOrCreate(['report_id' => $this->otherReportId], [
            'periode' => $this->periodeDate,
            'kode_unit' => '002',
            'nama_unit' => 'Kantor Cabang Poso',
            'resident_auditor' => 'RA Poso User',
            'unit_ra' => 'KC Poso',
            'wajib_lapor' => 'YA',
            'jatuh_tempo' => '2026-11-05',
            'status_final' => 'DRAFT',
            'status_review' => 'MENUNGGU REVIEW',
            'kelengkapan' => 50,
        ]);

        Sop08ChecklistLaporan::firstOrCreate(['report_id' => $this->reportId], [
            'kode_unit' => '001',
            'periode' => $this->periodeDate,
            'status_kesiapan' => 'SIAP REVIEW',
            'persen_lengkap' => 100,
            'jumlah_gap' => 0,
        ]);

        Sop08NarasiLaporan::firstOrCreate(['report_id' => $this->reportId], [
            'kode_unit' => '001',
            'periode' => $this->periodeDate,
            'ringkasan_eksekutif' => 'Ringkasan eksekutif awal.',
        ]);
    }

    /**
     * Requirement 1: Zero manual input for RA (POST/PUT mutations return 403 Forbidden).
     */
    public function test_ra_cannot_manually_submit_checklist_kegiatan_isu_or_narasi()
    {
        // 1. Checklist mutation by RA returns 403
        $response = $this->actingAs($this->raUser)
            ->post("/pelaporan-bulanan/checklist/{$this->reportId}", [
                'komp_ringkasan_eksekutif' => 'LENGKAP',
            ]);
        $response->assertStatus(403);

        // 2. Rekap kegiatan manual store by RA returns 403
        $response = $this->actingAs($this->raUser)
            ->post("/pelaporan-bulanan/rekap-kegiatan/{$this->reportId}/store", [
                'jenis_kegiatan' => '1. Pemeriksaan Kas & ATM',
                'area' => 'Operasional',
                'sumber_sop' => 'SOP-01',
            ]);
        $response->assertStatus(403);

        // 3. Rekap isu manual store by RA returns 403
        $response = $this->actingAs($this->raUser)
            ->post("/pelaporan-bulanan/rekap-isu-tl/{$this->reportId}/store", [
                'uraian_isu' => 'Manual issue',
                'area' => 'Operasional',
            ]);
        $response->assertStatus(403);

        // 4. Narasi manual update by RA returns 403
        $response = $this->actingAs($this->raUser)
            ->post("/pelaporan-bulanan/narasi/{$this->reportId}", [
                'ringkasan_eksekutif' => 'Manual narasi update',
            ]);
        $response->assertStatus(403);

        // 5. WYSIWYG update by RA returns 403
        $response = $this->actingAs($this->raUser)
            ->post("/pelaporan-bulanan/wysiwyg/{$this->reportId}", [
                'catatan_eksekutif' => 'Admin only note',
            ]);
        $response->assertStatus(403);
    }

    /**
     * Requirement 1: RA has Mode Preview for their own branch, 403 for other branch.
     */
    public function test_ra_preview_mode_and_branch_isolation()
    {
        // RA views own branch preview -> 200 OK with preview indicator
        $response = $this->actingAs($this->raUser)
            ->get("/pelaporan-bulanan/laporan-bulanan/{$this->reportId}");
        $response->assertStatus(200);
        $response->assertSee('Mode Preview (Read-Only)');

        // RA tries to view another branch report -> 403 Forbidden
        $response = $this->actingAs($this->raUser)
            ->get("/pelaporan-bulanan/laporan-bulanan/{$this->otherReportId}");
        $response->assertStatus(403);

        // RA accesses konsolidasi -> restricted to their assigned branch
        $response = $this->actingAs($this->raUser)
            ->get("/pelaporan-bulanan/konsolidasi?periode={$this->periode}");
        $response->assertStatus(200);
        $response->assertSee('Mode Preview (Read-Only)');
        $response->assertSee('Kantor Cabang Utama Palu');

        // RA tries to access another branch konsolidasi -> 403 Forbidden
        $response = $this->actingAs($this->raUser)
            ->get("/pelaporan-bulanan/konsolidasi?periode={$this->periode}&kode_unit=002");
        $response->assertStatus(403);
    }

    /**
     * Requirement 1: KKA Compliance Indicator Logic (🟢 HIJAU vs 🔴 MERAH), recording empty/0 data.
     */
    public function test_kka_compliance_logic_green_vs_red_including_zero_nihil_recording()
    {
        // Ensure status record exists for area with 0 population
        Sop08KkaBranchStatus::updateOrCreate(
            ['kode_unit' => '001', 'periode' => $this->periodeDate, 'area' => 'Cash Opname & Kas Fisik'],
            [
                'populasi' => 0,
                'item_selesai' => 0,
                'is_nihil' => true,
                'is_confirmed_nihil' => false,
                'status_kepatuhan' => 'MERAH',
            ]
        );

        // Before confirmation, 0 population with unconfirmed status -> MERAH
        $statusBefore = $this->service->getKkaComplianceStatus('001', $this->periodeDate);
        $this->assertEquals('MERAH', $statusBefore['details']['Cash Opname & Kas Fisik']['status_kepatuhan']);

        // RA confirms nihil via endpoint
        $confirmResponse = $this->actingAs($this->raUser)
            ->post('/pelaporan-bulanan/kka/confirm-nihil', [
                'kode_unit' => '001',
                'periode' => $this->periodeDate,
                'area' => 'Cash Opname & Kas Fisik',
                'catatan' => 'Konfirmasi tidak ada transaksi kas abnormal bulan ini.',
            ]);
        $confirmResponse->assertRedirect();
        $confirmResponse->assertSessionHas('success');

        // Verify status in DB is now HIJAU
        $statusAfter = $this->service->getKkaComplianceStatus('001', $this->periodeDate);
        $this->assertEquals('HIJAU', $statusAfter['details']['Cash Opname & Kas Fisik']['status_kepatuhan']);
        $this->assertTrue($statusAfter['details']['Cash Opname & Kas Fisik']['is_confirmed_nihil']);

        // Case with population > 0 and items incomplete -> MERAH
        Sop08KkaBranchStatus::updateOrCreate(
            ['kode_unit' => '001', 'periode' => $this->periodeDate, 'area' => 'KKA Daily Register'],
            [
                'populasi' => 10,
                'item_selesai' => 8,
                'is_nihil' => false,
                'is_confirmed_nihil' => false,
                'status_kepatuhan' => 'MERAH',
            ]
        );
        $statusIncomplete = $this->service->getKkaComplianceStatus('001', $this->periodeDate);
        $this->assertEquals('MERAH', $statusIncomplete['details']['KKA Daily Register']['status_kepatuhan']);

        // Case with population > 0 and all completed -> HIJAU
        Sop08KkaBranchStatus::updateOrCreate(
            ['kode_unit' => '001', 'periode' => $this->periodeDate, 'area' => 'KKA Daily Register'],
            [
                'populasi' => 10,
                'item_selesai' => 10,
                'is_nihil' => false,
                'is_confirmed_nihil' => false,
                'status_kepatuhan' => 'HIJAU',
            ]
        );
        $statusComplete = $this->service->getKkaComplianceStatus('001', $this->periodeDate);
        $this->assertEquals('HIJAU', $statusComplete['details']['KKA Daily Register']['status_kepatuhan']);
    }

    /**
     * Requirement 2: Removal of feedback & consolidation of legacy routes into unified views.
     */
    public function test_feedback_and_legacy_routes_redirect_to_unified_views()
    {
        // Feedback routes redirect to konsolidasi
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/feedback');
        $response->assertRedirect(route('pelaporan-bulanan.konsolidasi'));

        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/feedback/create');
        $response->assertRedirect(route('pelaporan-bulanan.konsolidasi'));

        // Rekap kegiatan redirects to log-harian
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/rekap-kegiatan');
        $response->assertRedirect(route('pelaporan-bulanan.log-harian'));

        // Rekap isu redirects to monitoring tindak lanjut
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/rekap-isu-tl');
        $response->assertRedirect(route('monitoring.index'));

        // Review laporan redirects to konsolidasi
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/review-laporan');
        $response->assertRedirect(route('pelaporan-bulanan.konsolidasi'));

        // Koreksi approval redirects to konsolidasi with approval filter
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/koreksi-approval');
        $response->assertRedirect(route('pelaporan-bulanan.konsolidasi', ['status_approval' => 'MENUNGGU_APPROVAL']));

        // Isu signifikan redirects to konsolidasi with isu filter
        $response = $this->actingAs($this->adminUser)->get('/pelaporan-bulanan/isu-signifikan');
        $response->assertRedirect(route('pelaporan-bulanan.konsolidasi', ['isu_signifikan' => 'YA']));
    }

    /**
     * Requirement 2 & 3: Monthly Matrix Table for all branches (7 columns, all branches appear even if 0).
     */
    public function test_monthly_branch_matrix_displays_all_branches_and_7_columns()
    {
        $matrix = $this->service->getMonthlyBranchMatrix($this->periodeDate, null, []);

        // Assert all master units are present
        $unitCodes = collect($matrix)->pluck('kode_unit')->toArray();
        $this->assertContains('001', $unitCodes);
        $this->assertContains('002', $unitCodes);

        $row1 = collect($matrix)->firstWhere('kode_unit', '001');
        $this->assertArrayHasKey('kode_unit', $row1);
        $this->assertArrayHasKey('nama_unit', $row1);
        $this->assertArrayHasKey('kepatuhan_status', $row1);
        $this->assertArrayHasKey('reversal_count', $row1);
        $this->assertArrayHasKey('selisih_kas_count', $row1);
        $this->assertArrayHasKey('limit_biaya_count', $row1);
        $this->assertArrayHasKey('mod_to_high_count', $row1);
        $this->assertArrayHasKey('tl_active_count', $row1);
        $this->assertArrayHasKey('tl_overdue_count', $row1);

        // Test rendering on consolidation page
        $response = $this->actingAs($this->adminUser)
            ->get('/pelaporan-bulanan/konsolidasi?periode=' . $this->periode);
        $response->assertStatus(200);
        $response->assertSee('Tabel Matriks Konsolidasi Bulanan Seluruh Cabang');
        $response->assertSee('Kantor Cabang Utama Palu');
        $response->assertSee('Kantor Cabang Poso');
    }

    /**
     * Requirement 2: Admin Smart Filters (Kepatuhan, Approval, Isu Signifikan).
     */
    public function test_admin_smart_filters_on_consolidation_matrix()
    {
        // Seed branch 001 as HIJAU
        $areas = ['KKA Daily Register', 'Cash Opname & Kas Fisik', 'KKA Fisik & Observasi', 'Offsite Review', 'Risk Trigger SOP 06'];
        foreach ($areas as $area) {
            Sop08KkaBranchStatus::updateOrCreate(
                ['kode_unit' => '001', 'periode' => $this->periodeDate, 'area' => $area],
                [
                    'populasi' => 0,
                    'item_selesai' => 0,
                    'is_nihil' => true,
                    'is_confirmed_nihil' => true,
                    'status_kepatuhan' => 'HIJAU',
                ]
            );
        }

        // Seed branch 002 as MERAH (unconfirmed nihil)
        foreach ($areas as $area) {
            Sop08KkaBranchStatus::updateOrCreate(
                ['kode_unit' => '002', 'periode' => $this->periodeDate, 'area' => $area],
                [
                    'populasi' => 5,
                    'item_selesai' => 2,
                    'is_nihil' => false,
                    'is_confirmed_nihil' => false,
                    'status_kepatuhan' => 'MERAH',
                ]
            );
        }

        // Filter by kepatuhan = HIJAU
        $filteredHijau = $this->service->getMonthlyBranchMatrix($this->periodeDate, null, ['kepatuhan' => 'HIJAU']);
        $this->assertNotEmpty($filteredHijau);
        $this->assertEquals(['001'], collect($filteredHijau)->pluck('kode_unit')->values()->toArray());
        foreach ($filteredHijau as $r) {
            $this->assertEquals('HIJAU', $r['kepatuhan_status']);
        }

        // Filter by kepatuhan = MERAH
        $filteredMerah = $this->service->getMonthlyBranchMatrix($this->periodeDate, null, ['kepatuhan' => 'MERAH']);
        $this->assertNotEmpty($filteredMerah);
        $this->assertEquals(['002'], collect($filteredMerah)->pluck('kode_unit')->values()->toArray());
        foreach ($filteredMerah as $r) {
            $this->assertEquals('MERAH', $r['kepatuhan_status']);
        }

        // Filter via HTTP Request
        $response = $this->actingAs($this->adminUser)
            ->get('/pelaporan-bulanan/konsolidasi?periode=' . $this->periode . '&kepatuhan=MERAH&isu_signifikan=YA');
        $response->assertStatus(200);
    }

    /**
     * Requirement 4: Hasil Utama & Temuan Kunci (auto-pulled Moderate-to-High & High with Temuan Berulang badge).
     */
    public function test_hasil_utama_temuan_kunci_auto_pulls_with_repeat_finding_badge()
    {
        // Create 2 findings in same branch & area to trigger "Temuan Berulang"
        Sop08RekapIsuTl::create([
            'report_id' => $this->reportId,
            'issue_id' => 'ISU-TEST-001',
            'kode_unit' => '001',
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'periode' => $this->periodeDate,
            'sumber_sop' => 'SOP-01',
            'referensi_sumber' => 'KKA-KAS-01',
            'area' => 'Operasional Kas & Khazanah',
            'jenis_isu' => 'FINDING',
            'risk_level' => 'HIGH',
            'uraian_isu' => 'Selisih fisik kas melebihi limit toleransi operasional.',
            'nilai_dampak' => 25000000,
            'rekomendasi' => 'Lakukan rekonsiliasi harian dan investigasi selisih kas.',
            'pic' => 'Pemimpin Cabang & Supervisor Kas',
            'target_tl' => '2026-11-20',
            'status_tl' => 'OPEN',
            'berulang' => 'YA',
            'flag_signifikan' => 'YA',
            'jalur_tindak_lanjut' => 'SOP 07 (Register TL)',
        ]);

        Sop08RekapIsuTl::create([
            'report_id' => $this->reportId,
            'issue_id' => 'ISU-TEST-002',
            'kode_unit' => '001',
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'periode' => $this->periodeDate,
            'sumber_sop' => 'SOP-01',
            'referensi_sumber' => 'KKA-KAS-02',
            'area' => 'Operasional Kas & Khazanah',
            'jenis_isu' => 'FINDING',
            'risk_level' => 'HIGH',
            'uraian_isu' => 'Kunci ganda khazanah dipegang oleh satu petugas.',
            'nilai_dampak' => 0,
            'rekomendasi' => 'Patuhi prinsip four-eyes dan dual custody.',
            'pic' => 'Supervisor Kas',
            'target_tl' => '2026-11-15',
            'status_tl' => 'IN PROGRESS',
            'berulang' => 'TIDAK',
            'flag_signifikan' => 'YA',
            'jalur_tindak_lanjut' => 'SOP 07 (Register TL)',
        ]);

        // Low risk issue (should NOT be included in Hasil Utama)
        Sop08RekapIsuTl::create([
            'report_id' => $this->reportId,
            'issue_id' => 'ISU-TEST-003',
            'kode_unit' => '001',
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'periode' => $this->periodeDate,
            'sumber_sop' => 'SOP-01',
            'referensi_sumber' => 'KKA-ADM-01',
            'area' => 'Administrasi',
            'jenis_isu' => 'EXCEPTION',
            'risk_level' => 'LOW',
            'uraian_isu' => 'Stempel register belum terisi tanggal.',
            'status_tl' => 'CLOSED',
        ]);

        // RegisterTrigger finding (SOP 06)
        if (class_exists(\App\Models\RiskTrigger\RegisterTrigger::class)) {
            \App\Models\RiskTrigger\RegisterTrigger::create([
                'trigger_id' => 'RT-20261105-0001',
                'sumber' => 'Daily Review',
                'tanggal_trigger' => '2026-11-05',
                'kode_unit' => '001',
                'nama_unit' => 'Kantor Cabang Utama Palu',
                'area' => 'Teller Kas',
                'uraian_trigger' => 'Penyimpangan limit teller tanpa otorisasi dual custody.',
                'eksposur_nominal' => 45000000,
                'risk_level_reassessment' => 'HIGH',
                'status_duplikat' => 'DUPLIKAT - REVIEW',
                'assigned_ra' => 'RA Palu User',
                'sla_deadline' => '2026-11-12',
            ]);
        }

        $findings = $this->service->getHasilUtamaTemuanKunci($this->periodeDate, '001');

        $this->assertCount(3, $findings);

        $highFinding = collect($findings)->firstWhere('id', 'ISU-TEST-001');
        $this->assertNotNull($highFinding);
        $this->assertEquals('HIGH', $highFinding['risk_level']);
        $this->assertEquals('Rp 25.000.000', $highFinding['nominal_formatted']);
        $this->assertTrue($highFinding['is_temuan_berulang']);
        $this->assertEquals('Pemimpin Cabang & Supervisor Kas', $highFinding['pic']);

        $rtFinding = collect($findings)->firstWhere('id', 'RT-20261105-0001');
        $this->assertNotNull($rtFinding);
        $this->assertEquals('HIGH', $rtFinding['risk_level']);
        $this->assertEquals('Rp 45.000.000', $rtFinding['nominal_formatted']);
        $this->assertTrue($rtFinding['is_temuan_berulang']);
        $this->assertEquals('Penyimpangan limit teller tanpa otorisasi dual custody.', $rtFinding['uraian']);

        // Check view rendering
        $response = $this->actingAs($this->adminUser)
            ->get("/pelaporan-bulanan/konsolidasi?periode={$this->periode}&kode_unit=001");
        $response->assertStatus(200);
        $response->assertSee('TEMUAN BERULANG');
        $response->assertSee('Rp 25.000.000');
        $response->assertSee('Rp 45.000.000');
    }

    /**
     * Requirement 5: Rich WYSIWYG Editor for Admin, backend XSS sanitation, and RA 403 prevention.
     */
    public function test_wysiwyg_admin_save_and_xss_sanitization()
    {
        $dirtyHtml = '<h3>Catatan Eksekutif Admin</h3><p>Evaluasi <b>kepatuhan</b> cabang berjalan <i>optimal</i>.</p><table border="1"><tr><td>Item</td><td>Status</td></tr></table><script>alert("xss")</script><img src="x" onerror="alert(1)">';

        $cleanPlan = '<p>Fokus bulan depan adalah pengawasan <strong>ATM</strong> dan <u>limit kredit</u>.</p>';

        $response = $this->actingAs($this->adminUser)
            ->post("/pelaporan-bulanan/wysiwyg/{$this->reportId}", [
                'catatan_eksekutif' => $dirtyHtml,
                'rencana_bulan_depan' => $cleanPlan,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $narasi = Sop08NarasiLaporan::where('report_id', $this->reportId)->first();
        $this->assertNotNull($narasi);

        // Assert script and onerror are stripped
        $this->assertStringNotContainsString('<script>', $narasi->catatan_eksekutif);
        $this->assertStringNotContainsString('onerror', $narasi->catatan_eksekutif);

        // Assert safe formatting and table tags are preserved
        $this->assertStringContainsString('<h3>Catatan Eksekutif Admin</h3>', $narasi->catatan_eksekutif);
        $this->assertStringContainsString('<b>kepatuhan</b>', $narasi->catatan_eksekutif);
        $this->assertStringContainsString('<i>optimal</i>', $narasi->catatan_eksekutif);
        $this->assertStringContainsString('<table', $narasi->catatan_eksekutif);
        $this->assertStringContainsString('<td>Item</td>', $narasi->catatan_eksekutif);

        $this->assertStringContainsString('<strong>ATM</strong>', $narasi->rencana_bulan_depan);
    }

    /**
     * Requirement 5: Signature (TTD) blocks completely removed from Laporan Bulanan and Print.
     */
    public function test_signature_blocks_completely_removed()
    {
        // 1. Laporan Bulanan screen
        $response = $this->actingAs($this->adminUser)
            ->get("/pelaporan-bulanan/laporan-bulanan/{$this->reportId}");
        $response->assertStatus(200);
        $response->assertDontSee('Disusun Oleh,');
        $response->assertDontSee('Diketahui & Disetujui Oleh,');
        $response->assertDontSee('Siti (Kabag RA Korwas)');

        // 2. Konsolidasi Print view
        $printResponse = $this->actingAs($this->adminUser)
            ->get("/pelaporan-bulanan/konsolidasi/print?periode={$this->periode}");
        $printResponse->assertStatus(200);
        $printResponse->assertDontSee('Disusun Oleh,');
        $printResponse->assertDontSee('Diketahui & Disetujui Oleh,');
    }

    /**
     * Requirement 6: Print and Excel Export HTTP 200 and proper formatting.
     */
    public function test_print_and_excel_export_endpoints()
    {
        // Create an activity with date > 12 to test date parsing and array keys
        \App\Models\PelaporanBulanan\Sop08LogHarianRa::create([
            'log_id' => 'LOG-20261125-001',
            'tanggal' => '2026-11-25',
            'ra_id' => 'RA-001',
            'nama_ra' => 'RA Palu User',
            'kode_unit' => '001',
            'nama_unit' => 'Kantor Cabang Utama Palu',
            'status_hari' => 'KERJA',
            'sumber_sop' => 'SOP 03 - Daily Review',
            'jenis_aktivitas' => 'Pemeriksaan Register Fisik Kas Khazanah',
            'area' => 'Cash Opname',
            'output_referensi_wp' => 'WP-PHYS-001',
            'status_penyelesaian' => 'COMPLETED',
            'hasil_review' => 'MEMADAI',
        ]);

        // Print route
        $printResponse = $this->actingAs($this->adminUser)
            ->get("/pelaporan-bulanan/konsolidasi/print?periode={$this->periode}");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH');
        $printResponse->assertSee('TABEL MATRIKS KONSOLIDASI BULANAN SELURUH CABANG');

        // Excel export route
        $excelResponse = $this->actingAs($this->adminUser)
            ->get("/pelaporan-bulanan/konsolidasi/export-excel?periode={$this->periode}");
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $excelResponse->assertHeader('Content-Disposition', 'attachment; filename="Konsolidasi_Bulanan_SOP08_2026_11.xls"');
        $this->assertStringContainsString('PT. BANK SULTENG - SATUAN KERJA AUDIT INTERN (SKAI)', $excelResponse->getContent());
        $this->assertStringContainsString('TABEL MATRIKS KONSOLIDASI BULANAN SELURUH CABANG', $excelResponse->getContent());
    }
}
