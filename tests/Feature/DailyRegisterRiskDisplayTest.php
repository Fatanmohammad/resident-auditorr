<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\DailyRegister\DailyRegisterFinding;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DailyRegisterRiskDisplayTest extends TestCase
{
    use DatabaseTransactions;

    protected User $raUser;
    protected User $adminUser;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-007'], [
            'nama_cabang' => 'Cabang Palu Barat',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '007'], [
            'unit_name' => 'KC Palu Barat',
            'unit_type' => 'KC',
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);

        $this->raUser = User::firstOrCreate(['email' => 'ra_palu_barat@banksulteng.co.id'], [
            'nip' => '998877',
            'name' => 'RA Palu Barat Test',
            'password' => bcrypt('password123'),
            'role' => 'ra',
            'cabang_id' => $cabang->id,
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_skai_test@banksulteng.co.id'], [
            'nip' => '112233',
            'name' => 'Admin SKAI Test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    /**
     * Test 1: Temuan baru yang belum diuji harus memiliki risk_final = null
     * dan tidak boleh menampilkan 'Low' saat statusnya BELUM_DIUJI.
     */
    public function test_newly_imported_finding_has_null_risk_final_and_displays_initial_risk()
    {
        $finding = DailyRegisterFinding::create([
            'kode_unit'            => '007',
            'tanggal_data'         => now()->toDateString(),
            'source_sheet'         => 'KKA_Teller_Kas',
            'nominal_terkait'      => 500000000,
            'risk_awal'            => 'High',
            'jenis_exception_awal' => 'Nominal Melewati Limit',
            'deskripsi'            => 'Nominal Melewati Limit',
            'review_status'        => DailyRegisterFinding::STATUS_BELUM_DIUJI,
        ]);

        $this->assertNull($finding->risk_final, "risk_final pada temuan baru BELUM_DIUJI harus null");
        $this->assertEquals('High', $finding->risk_awal, "risk_awal harus High sesuai hasil deteksi");
    }

    /**
     * Test 2: Endpoint /daily-register/data melakukan normalisasi otomatis
     * dan mengembalikan temuan belum diuji dengan risk_awal High/Moderate.
     */
    public function test_data_endpoint_cleans_legacy_low_risk_final_for_untested_findings()
    {
        // Simulasikan temuan legacy yang terlanjur menyimpan risk_final = 'Low'
        $legacyFinding = DailyRegisterFinding::create([
            'kode_unit'            => '007',
            'tanggal_data'         => now()->toDateString(),
            'source_sheet'         => 'KKA_Teller_Kas',
            'nominal_terkait'      => 212049350,
            'risk_awal'            => 'High',
            'risk_final'           => 'Low', // terlanjur tersimpan Low
            'jenis_exception_awal' => 'Nominal Melewati Limit',
            'deskripsi'            => 'Nominal Melewati Limit',
            'review_status'        => DailyRegisterFinding::STATUS_BELUM_DIUJI,
        ]);

        // Panggil endpoint data
        $response = $this->actingAs($this->raUser)->getJson(route('daily-register.data', [
            'source_sheet' => 'KKA_Teller_Kas',
            'kode_unit'    => '007',
        ]));

        $response->assertStatus(200);

        // Verifikasi bahwa legacy finding telah dinormalisasi di database
        $fresh = $legacyFinding->fresh();
        $this->assertNull($fresh->risk_final, "risk_final harus dibersihkan menjadi null oleh data endpoint");

        // Dan di response JSON data
        $items = collect($response->json('data.data'));
        $targetItem = $items->firstWhere('id', $legacyFinding->id);
        $this->assertNotNull($targetItem);
        $this->assertEquals('High', $targetItem['risk_awal']);
        $this->assertNull($targetItem['risk_final']);
    }
}

