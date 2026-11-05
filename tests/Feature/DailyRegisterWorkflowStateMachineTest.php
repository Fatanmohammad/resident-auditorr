<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\DailyRegisterChecklist;
use App\Models\DailyRegister\DailyRegisterChecklistAnswer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DailyRegisterWorkflowStateMachineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected User $adminUser;
    protected User $raOtherCabangUser;
    protected Cabang $cabang;
    protected Cabang $otherCabang;
    protected Unit $unit;
    protected Unit $otherUnit;

    protected function setUp(): void
    {
        parent::setUp();


        $this->cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-001'], [
            'nama_cabang' => 'Cabang Utama Palu',
            'tipe' => 'kcu',
        ]);

        $this->otherCabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-999'], [
            'nama_cabang' => 'Cabang Lain Isolasi',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '001'], [
            'unit_name' => 'KCU Palu',
            'unit_type' => 'KCU',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->otherUnit = Unit::firstOrCreate(['unit_code' => '999'], [
            'unit_name' => 'KC Isolasi',
            'unit_type' => 'KC',
            'cabang_id' => $this->otherCabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_palu_test@banksulteng.co.id'], [
            'nip' => '998803',
            'name' => 'RA Palu Tester',
            'password' => bcrypt('password123'),
            'role' => 'ra',
            'cabang_id' => $this->cabang->id,
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_checker_wf@banksulteng.co.id'], [
            'nip' => '998804',
            'name' => 'Admin Reviewer Tester',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'cabang_id' => $this->cabang->id,
        ]);

        $this->raOtherCabangUser = User::firstOrCreate(['email' => 'ra_other_cabang@banksulteng.co.id'], [
            'nip' => '998805',
            'name' => 'RA Other Cabang Tester',
            'password' => bcrypt('password123'),
            'role' => 'ra',
            'cabang_id' => $this->otherCabang->id,
        ]);
    }

    /**
     * Helper membuat finding testing
     */
    protected function createFinding(array $attributes = []): DailyRegisterFinding
    {
        return DailyRegisterFinding::create(array_merge([
            'kode_unit'            => $this->unit->unit_code,
            'source_sheet'         => 'KKA_Teller_Kas',
            'offsite_id'           => 'TEST-' . uniqid(),
            'tanggal_data'         => now()->format('Y-m-d'),
            'nominal_terkait'      => 5000000,
            'deskripsi'            => 'Testing workflow state machine item',
            'risk_awal'            => 'Moderate',
            'review_status'        => DailyRegisterFinding::STATUS_BELUM_DIUJI,
            'status_review'        => 'Belum Direview',
        ], $attributes));
    }

    /**
     * Test 1: Item Baru berstatus BELUM_DIUJI secara default
     */
    public function test_new_finding_defaults_to_belum_diuji(): void
    {
        $finding = $this->createFinding();

        $this->assertEquals(DailyRegisterFinding::STATUS_BELUM_DIUJI, $finding->review_status);
        $this->assertNull($finding->submitted_at);
        $this->assertNull($finding->reviewed_by);
        $this->assertNull($finding->reviewed_at);
    }

    /**
     * Test 2: RA Submit Checklist -> Langsung tersimpan final ke SELESAI (Arsip) tanpa tunggu konfirmasi Admin
     */
    public function test_ra_submission_directly_transitions_to_selesai_tanpa_tunggu_admin(): void
    {
        $finding = $this->createFinding();

        $response = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding->id}/ra", [
                'no_arsip'          => 'ARSIP-2026-001',
                'bukti_referensi'   => 'Slip No. 001/TEST',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'checklist_answers' => json_encode([]),
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Review KKA berhasil disimpan langsung ke Arsip Riwayat Selesai.'
        ]);

        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_SELESAI, $finding->review_status);
        $this->assertNotNull($finding->submitted_at);
        $this->assertEquals('Approved', $finding->status_review);
        $this->assertEquals('ARSIP-2026-001', $finding->no_arsip);
    }

    /**
     * Test 3: RBAC Ketat: RA dilarang keras menembak endpoint reviewDecision milik Admin (403 Forbidden)
     */
    public function test_ra_forbidden_from_calling_admin_review_decision_endpoint(): void
    {
        $finding = $this->createFinding([
            'review_status' => DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI,
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding->id}/review-decision", [
                'decision'         => 'APPROVE',
                'catatan_reviewer' => 'RA mencoba approve sendiri',
            ]);

        $response->assertStatus(403);
        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI, $finding->review_status);
    }

    /**
     * Test 4: Admin Minta Revisi wajib mengisi catatan_reviewer (Validasi 422 jika kosong)
     */
    public function test_admin_reject_requires_catatan_reviewer(): void
    {
        $finding = $this->createFinding([
            'review_status' => DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI,
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/daily-register/{$finding->id}/review-decision", [
                'decision'         => 'REVISI',
                'catatan_reviewer' => '   ', // Kosong / spasi saja
            ]);

        $response->assertStatus(422);

        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI, $finding->review_status);
    }

    /**
     * Test 5: Admin Minta Revisi (dengan catatan) -> Status otomatis berubah ke PERLU_REVISI
     */
    public function test_admin_reject_with_note_automatically_transitions_to_perlu_revisi(): void
    {
        $finding = $this->createFinding([
            'review_status' => DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI,
            'submitted_at'  => now(),
        ]);

        $note = 'Mohon lampirkan bukti fisik memo mutasi kas cabang dan lengkapi klarifikasi.';
        $response = $this->actingAs($this->adminUser)
            ->postJson("/daily-register/{$finding->id}/review-decision", [
                'decision'         => 'REVISI',
                'catatan_reviewer' => $note,
            ]);

        $response->assertStatus(200);

        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_PERLU_REVISI, $finding->review_status);
        $this->assertEquals('Revisi', $finding->status_review);
        $this->assertEquals($note, $finding->reviewer_note);
        $this->assertEquals($note, $finding->catatan_reviewer);
        $this->assertEquals($this->adminUser->name, $finding->reviewed_by);
        $this->assertNotNull($finding->reviewed_at);
    }

    /**
     * Test 6: RA Simpan Perbaikan -> Status langsung final SELESAI (Approved)
     */
    public function test_ra_resubmit_perbaikan_transitions_to_selesai(): void
    {
        $finding = $this->createFinding([
            'review_status'    => DailyRegisterFinding::STATUS_PERLU_REVISI,
            'status_review'    => 'Revisi',
            'reviewer_note'    => 'Perlu bukti memo kas',
            'catatan_reviewer' => 'Perlu bukti memo kas',
            'reviewed_by'      => $this->adminUser->name,
            'reviewed_at'      => now()->subHour(),
        ]);

        $response = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding->id}/ra", [
                'no_arsip'          => 'ARSIP-REV-001',
                'bukti_referensi'   => 'Memo No. 456/REV/2026 Terlampir',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'checklist_answers' => json_encode([]),
            ]);

        $response->assertStatus(200);

        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_SELESAI, $finding->review_status);
        $this->assertNotNull($finding->submitted_at);
        $this->assertEquals('Approved', $finding->status_review);
        // Catatan revisi sebelumnya tetap tersimpan sebagai riwayat
        $this->assertEquals('Perlu bukti memo kas', $finding->reviewer_note);
    }

    /**
     * Test 7: Admin Klik Konfirmasi & Selesaikan (APPROVE) -> Status otomatis berubah ke SELESAI
     */
    public function test_admin_approve_automatically_transitions_to_selesai(): void
    {
        $finding = $this->createFinding([
            'review_status' => DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI,
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/daily-register/{$finding->id}/review-decision", [
                'decision'         => 'APPROVE',
                'catatan_reviewer' => 'Telah diverifikasi sesuai SOP',
            ]);

        $response->assertStatus(200);

        $finding->refresh();
        $this->assertEquals(DailyRegisterFinding::STATUS_SELESAI, $finding->review_status);
        $this->assertEquals('Approved', $finding->status_review);
        $this->assertEquals($this->adminUser->name, $finding->reviewed_by);
        $this->assertNotNull($finding->reviewed_at);
    }

    /**
     * Test 8: RA dilarang mengedit KKA yang berstatus SELESAI (Read-Only 403 Forbidden)
     */
    public function test_ra_cannot_edit_finding_when_already_selesai(): void
    {
        $finding = $this->createFinding([
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
            'reviewed_by'   => $this->adminUser->name,
            'reviewed_at'   => now(),
        ]);

        $response = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding->id}/ra", [
                'bukti_referensi'   => 'Coba edit data yang sudah selesai',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'checklist_answers' => json_encode([]),
            ]);

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'status' => 'error',
        ]);
    }

    /**
     * Test 9: RA dilarang mengedit KKA milik Cabang Lain (403 Forbidden)
     */
    public function test_ra_cannot_edit_finding_belonging_to_another_cabang(): void
    {
        $otherFinding = $this->createFinding([
            'kode_unit' => $this->otherUnit->unit_code,
        ]);

        $response = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$otherFinding->id}/ra", [
                'bukti_referensi'   => 'RA Palu coba edit unit 999',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => now()->format('Y-m-d'),
                'checklist_answers' => json_encode([]),
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test 10: Endpoint /daily-register/data menghasilkan KPI selesai_review, tab_statuses, dan smart sorting
     */
    public function test_daily_register_data_returns_tab_statuses_and_smart_sorting(): void
    {
        // Buat 4 finding dengan 4 status berbeda di tab KKA_Pengaduan
        $fBelum = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
            'review_status' => DailyRegisterFinding::STATUS_BELUM_DIUJI,
            'status_review' => 'Belum Direview',
        ]);

        $fMenunggu = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
            'review_status' => DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI,
            'status_review' => 'Belum Direview',
            'submitted_at'  => now(),
        ]);

        $fRevisi = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
            'review_status' => DailyRegisterFinding::STATUS_PERLU_REVISI,
            'status_review' => 'Revisi',
            'reviewer_note' => 'Perbaiki lampiran',
        ]);

        $fSelesai = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
            'reviewed_by'   => $this->adminUser->name,
            'reviewed_at'   => now(),
        ]);

        // Cek pemanggilan data oleh RA
        $responseRa = $this->actingAs($this->raUser)
            ->getJson("/daily-register/data?source_sheet=KKA_Pengaduan&kode_unit={$this->unit->unit_code}");

        $responseRa->assertStatus(200);
        $summaryRa = $responseRa->json('summary');

        // Pastikan selesai_review bertambah
        $this->assertGreaterThanOrEqual(1, $summaryRa['selesai_review']);

        // Pastikan tab_statuses KKA_Pengaduan mengembalikan rincian status
        $this->assertArrayHasKey('KKA_Pengaduan', $summaryRa['tab_statuses']);
        $tabPengaduan = $summaryRa['tab_statuses']['KKA_Pengaduan'];
        $this->assertGreaterThanOrEqual(1, $tabPengaduan['perlu_revisi']);
        $this->assertGreaterThanOrEqual(1, $tabPengaduan['menunggu_konfirmasi']);
        $this->assertGreaterThanOrEqual(1, $tabPengaduan['selesai']);

        // Verifikasi Smart Sorting untuk RA: PERLU_REVISI harus berada di urutan atas sebelum MENUNGGU_KONFIRMASI dan SELESAI
        $itemsRa = $responseRa->json('data.data');
        $itemIdsRa = array_column($itemsRa, 'id');
        $revisiIndex = array_search($fRevisi->id, $itemIdsRa);
        $menungguIndex = array_search($fMenunggu->id, $itemIdsRa);
        $selesaiIndex = array_search($fSelesai->id, $itemIdsRa);

        $this->assertNotFalse($revisiIndex);
        $this->assertNotFalse($menungguIndex);
        $this->assertNotFalse($selesaiIndex);
        $this->assertLessThan($menungguIndex, $revisiIndex, "Untuk RA, item PERLU_REVISI harus lebih atas daripada MENUNGGU_KONFIRMASI");
        $this->assertLessThan($selesaiIndex, $revisiIndex, "Untuk RA, item PERLU_REVISI harus lebih atas daripada SELESAI");

        // Cek pemanggilan data oleh Admin
        $responseAdmin = $this->actingAs($this->adminUser)
            ->getJson("/daily-register/data?source_sheet=KKA_Pengaduan&kode_unit={$this->unit->unit_code}");

        $responseAdmin->assertStatus(200);
        $itemsAdmin = $responseAdmin->json('data.data');
        $itemIdsAdmin = array_column($itemsAdmin, 'id');
        $adminMenungguIndex = array_search($fMenunggu->id, $itemIdsAdmin);
        $adminRevisiIndex = array_search($fRevisi->id, $itemIdsAdmin);
        $adminSelesaiIndex = array_search($fSelesai->id, $itemIdsAdmin);

        $this->assertLessThan($adminRevisiIndex, $adminMenungguIndex, "Untuk Admin, item MENUNGGU_KONFIRMASI harus lebih atas daripada PERLU_REVISI");
        $this->assertLessThan($adminSelesaiIndex, $adminMenungguIndex, "Untuk Admin, item MENUNGGU_KONFIRMASI harus lebih atas daripada SELESAI");
    }

    /**
     * Test 11: Tab tuntas 100% (is_all_selesai) bernilai true saat seluruh item di tab tersebut SELESAI
     */
    public function test_tab_completion_is_all_selesai_flag(): void
    {
        // Buat finding spesifik di tab KKA_Kartu_ATM
        $finding = $this->createFinding([
            'source_sheet'  => 'KKA_Kartu_ATM',
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/daily-register/data?source_sheet=KKA_Kartu_ATM&kode_unit={$this->unit->unit_code}");

        $response->assertStatus(200);
        $tabAtm = $response->json('summary.tab_statuses.KKA_Kartu_ATM');

        if ($tabAtm['total'] === $tabAtm['selesai'] && $tabAtm['total'] > 0) {
            $this->assertTrue($tabAtm['is_all_selesai'], "Tab harus berstatus is_all_selesai = true jika 100% selesai");
        } else {
            $this->assertFalse($tabAtm['is_all_selesai']);
        }
    }

    /**
     * Test 12: Print & Export Excel Arsip Matriks endpoint
     */
    public function test_arsip_matriks_print_and_export_endpoints(): void
    {
        $finding = $this->createFinding([
            'source_sheet'  => 'KKA_Teller',
            'review_status' => DailyRegisterFinding::STATUS_SELESAI,
            'status_review' => 'Approved',
            'no_arsip'      => 'ARSIP-PRINT-01',
        ]);

        $responsePrint = $this->actingAs($this->raUser)
            ->get("/daily-register/arsip-matriks/print?source_sheet=KKA_Teller&kode_unit={$this->unit->unit_code}");

        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('ARSIP MATRIKS REGISTER HARIAN');
        $responsePrint->assertSee('ARSIP-PRINT-01');
        $responsePrint->assertSee('Cetak / Simpan PDF');

        $responseExport = $this->actingAs($this->raUser)
            ->get("/daily-register/arsip-matriks/export-excel?source_sheet=KKA_Teller&kode_unit={$this->unit->unit_code}");

        $responseExport->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $responseExport->headers->get('content-type'));
        $responseExport->assertDontSee('Cetak / Simpan PDF');
    }

    /**
     * Test 13: KKA Pengaduan kalkulasi NETWORKDAYS dan DITOLAK bila REJECT
     */
    public function test_pengaduan_networkdays_and_ditolak(): void
    {
        $finding = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
        ]);

        // Submit pengaduan dengan status REJECT -> lama_hari_kerja = DITOLAK
        $responseReject = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding->id}/ra", [
                'no_arsip'          => 'PENGADUAN-01',
                'status_keluhan'    => 'REJECT',
                'tgl_keluhan'       => '2026-11-02',
                'tgl_buku'          => '2026-11-04',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => '2026-11-04',
                'checklist_answers' => json_encode([]),
            ]);

        $responseReject->assertStatus(200);
        $finding->refresh();
        $this->assertEquals('DITOLAK', $finding->lama_hari_kerja);

        // Submit pengaduan dengan status Selesai -> lama_hari_kerja dihitung hari kerja (2026-11-02 Sen s.d. 2026-11-04 Rab = 3 hari kerja)
        $finding2 = $this->createFinding([
            'source_sheet'  => 'KKA_Pengaduan',
        ]);
        $responseOk = $this->actingAs($this->raUser)
            ->postJson("/daily-register/{$finding2->id}/ra", [
                'no_arsip'          => 'PENGADUAN-02',
                'status_keluhan'    => 'Selesai',
                'tgl_keluhan'       => '2026-11-02',
                'tgl_buku'          => '2026-11-04',
                'critical_trigger'  => 'Tidak',
                'perlu_onsite'      => 'Tidak Perlu',
                'tanggal_ditemukan' => '2026-11-04',
                'checklist_answers' => json_encode([]),
            ]);

        $responseOk->assertStatus(200);
        $finding2->refresh();
        $this->assertEquals('3', $finding2->lama_hari_kerja);
    }

    /**
     * Test 14: Backlog count mendeteksi transaksi belum selesai dari tanggal sebelumnya
     */
    public function test_backlog_detection(): void
    {
        $pastDate = now()->subDays(2)->format('Y-m-d');
        $this->createFinding([
            'source_sheet'  => 'KKA_Teller',
            'tanggal_data'  => $pastDate,
            'review_status' => DailyRegisterFinding::STATUS_BELUM_DIUJI,
        ]);

        $response = $this->actingAs($this->raUser)
            ->getJson("/daily-register/data?source_sheet=KKA_Teller&kode_unit={$this->unit->unit_code}");

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('summary.backlog_count'));
    }

    protected function tearDown(): void
    {
        DailyRegisterChecklistAnswer::whereNotIn('daily_register_finding_id', DailyRegisterFinding::where('id', '<=', 26)->pluck('id'))->delete();
        DailyRegisterFinding::where('id', '>=', 27)->delete();
        \App\Models\RiskTrigger\RegisterTrigger::where('sumber', 'Daily Review')
            ->where(function($q) {
                $q->where('kode_unit', 'like', 'U%')
                  ->orWhere('kode_unit', '999')
                  ->orWhereNotIn('source_wp_id', DailyRegisterFinding::pluck('id')->map(fn($id) => 'DRF-' . $id));
            })->delete();
        Cabang::where('kode_cabang', 'BS-999')->delete();
        Unit::where('unit_code', '999')->delete();
        User::whereIn('email', [
            'ra_palu_test@banksulteng.co.id',
            'admin_review_test@banksulteng.co.id',
            'ra_isolasi_test@banksulteng.co.id',
        ])->delete();

        parent::tearDown();
    }
}
