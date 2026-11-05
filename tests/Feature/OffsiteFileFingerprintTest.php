<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\Offsite\AuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OffsiteFileFingerprintTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected Cabang $cabang;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-098'], [
            'nama_cabang' => 'Cabang Test Fingerprint',
            'tipe' => 'cabang_a',
        ]);

        $this->unit = Unit::firstOrCreate(['unit_code' => '098'], [
            'unit_name' => 'KC Test Fingerprint',
            'unit_type' => 'KC',
            'cabang_id' => $this->cabang->id,
            'is_active' => true,
        ]);

        $this->adminUser = User::firstOrCreate(['email' => 'admin_fingerprint_test@banksulteng.co.id'], [
            'nip' => '998001',
            'name' => 'Admin Test Fingerprint',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * Skenario (a): Upload file CSV baru -> file_hash terisi 64 karakter heksadesimal.
     */
    public function test_scenario_a_upload_new_csv_populates_64_char_sha256_hash(): void
    {
        $csvContent = "TGL,KETERANGAN,NOMINAL,AKUN_GL\n" .
                      "2026-11-01,BIAYA ATK KANTOR CABANG TEST A,500000,51101\n";
        $file = UploadedFile::fake()->createWithContent('dump_biaya_sample_a.csv', $csvContent);
        $expectedHash = hash('sha256', $csvContent);

        $response = $this->actingAs($this->adminUser)->post(route('offsite.upload'), [
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'file_csv' => $file,
            'periode' => '2026-11-01',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $log = AuditLog::where('nama_file', 'dump_biaya_sample_a.csv')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'AuditLog record harus berhasil dibuat.');
        $this->assertNotNull($log->file_hash, 'Kolom file_hash tidak boleh bernilai null.');
        $this->assertSame(64, strlen($log->file_hash), 'Panjang file_hash harus tepat 64 karakter.');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/i', $log->file_hash, 'file_hash harus format heksadesimal 64 karakter.');
        $this->assertSame($expectedHash, $log->file_hash, 'file_hash harus sama persis dengan SHA-256 konten file.');

        // Pastikan file tersimpan di storage dan dapat diverifikasi langsung
        $verifyResult = $log->verifyIntegrity();
        $this->assertSame('verified', $verifyResult['status']);
        $this->assertSame('badge-success', $verifyResult['badge']);
        $this->assertSame('Terverifikasi', $verifyResult['label']);

        $storedPath = $log->getStoredFilePath();
        if ($storedPath && file_exists($storedPath)) {
            @unlink($storedPath);
        }
    }

    /**
     * Skenario (b): Upload file yang sama dua kali -> hash kedua upload sama persis.
     */
    public function test_scenario_b_uploading_same_file_twice_produces_identical_hash(): void
    {
        $csvContent = "TGL,KETERANGAN,NOMINAL,AKUN_GL\n" .
                      "2026-11-01,BIAYA PEMELIHARAAN TEST B,1500000,51102\n";

        // Upload pertama
        $file1 = UploadedFile::fake()->createWithContent('dump_identical_b.csv', $csvContent);
        $this->actingAs($this->adminUser)->post(route('offsite.upload'), [
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'file_csv' => $file1,
            'periode' => '2026-11-01',
        ]);

        $log1 = AuditLog::where('nama_file', 'dump_identical_b.csv')
            ->latest('id')
            ->first();

        // Upload kedua dengan isi yang persis sama
        $file2 = UploadedFile::fake()->createWithContent('dump_identical_b.csv', $csvContent);
        $this->actingAs($this->adminUser)->post(route('offsite.upload'), [
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'file_csv' => $file2,
            'periode' => '2026-11-01',
        ]);

        $log2 = AuditLog::where('nama_file', 'dump_identical_b.csv')
            ->where('id', '!=', $log1->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log1);
        $this->assertNotNull($log2);
        $this->assertSame($log1->file_hash, $log2->file_hash, 'Hash dari dua kali upload file yang sama harus identik.');

        foreach ([$log1, $log2] as $logItem) {
            $path = $logItem->getStoredFilePath();
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Skenario (c): Ubah 1 karakter pada file yang tersimpan di storage, lalu klik Verifikasi -> status "Tidak Cocok / Berubah".
     */
    public function test_scenario_c_altering_one_character_in_stored_file_causes_mismatch_status(): void
    {
        $csvContent = "TGL,KETERANGAN,NOMINAL,AKUN_GL\n" .
                      "2026-11-01,BIAYA SEWA TEST C,2500000,51103\n";

        $file = UploadedFile::fake()->createWithContent('dump_tamper_c.csv', $csvContent);
        $this->actingAs($this->adminUser)->post(route('offsite.upload'), [
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'file_csv' => $file,
            'periode' => '2026-11-01',
        ]);

        $log = AuditLog::where('nama_file', 'dump_tamper_c.csv')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $filePath = $log->getStoredFilePath();
        $this->assertNotNull($filePath, 'File harus ada di storage.');
        $this->assertFileExists($filePath);

        // Verifikasi sebelum diubah: harus "Terverifikasi"
        $verifyBefore = $log->verifyIntegrity();
        $this->assertSame('verified', $verifyBefore['status']);
        $this->assertSame('badge-success', $verifyBefore['badge']);
        $this->assertSame('Terverifikasi', $verifyBefore['label']);

        // Ubah TEPAT 1 KARAKTER pada file di storage (simulasi tamper / modifikasi tidak sah)
        $currentBytes = file_get_contents($filePath);
        // Ganti angka 2 menjadi 9 di baris kedua
        $tamperedBytes = str_replace('2500000', '9500000', $currentBytes);
        file_put_contents($filePath, $tamperedBytes);

        // Panggil endpoint Verifikasi (HTTP request seperti saat tombol Verifikasi diklik)
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('offsite.history.verify', $log->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'mismatch',
            'badge' => 'badge-danger',
            'label' => 'Tidak Cocok / Berubah',
        ]);

        // Bersihkan file test
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Skenario tambahan: Verifikasi record lama yang belum memiliki file_hash menghasilkan "Belum Ada Hash".
     */
    public function test_verification_on_record_without_hash_returns_belum_ada_hash(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'legacy_upload.csv',
            'file_hash' => null, // Data lama sebelum fitur fingerprint
            'status' => 'Berhasil',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('offsite.history.verify', $log->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'no_hash',
            'badge' => 'badge-gray',
            'label' => 'Belum Ada Hash',
        ]);
    }

    /**
     * Skenario tambahan: Verifikasi record yang filenya tidak ditemukan di storage menghasilkan "Belum Ada Hash".
     */
    public function test_verification_when_stored_file_missing_returns_belum_ada_hash(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'missing_file_in_storage.csv',
            'file_hash' => str_repeat('a', 64),
            'status' => 'Berhasil',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('offsite.history.verify', $log->id));

        $response->assertOk();
        $response->assertJson([
            'status' => 'not_found',
            'badge' => 'badge-gray',
            'label' => 'Belum Ada Hash',
        ]);
    }

    /**
     * Verifikasi tampilan riwayat menampilkan kolom Fingerprint dan tanda "—" untuk record tanpa hash.
     */
    public function test_history_view_displays_fingerprint_column_and_verify_button(): void
    {
        $hashSample = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';

        $logWithHash = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'sample_with_hash.csv',
            'file_hash' => $hashSample,
            'status' => 'Berhasil',
        ]);

        $logNoHash = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'sample_no_hash.csv',
            'file_hash' => null,
            'status' => 'Berhasil',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('offsite.history.index'));
        $response->assertOk();

        // Cek header tabel
        $response->assertSee('FINGERPRINT');
        $response->assertSee('VERIFIKASI');

        // Cek tampilan hash pendek: 8 char pertama (e3b0c442) + '...' + 4 char terakhir (b855)
        $shortHash = substr($hashSample, 0, 8) . '...' . substr($hashSample, -4);
        $response->assertSee($shortHash);
        $response->assertSee($hashSample); // Pada atribut title

        // Cek tanda "—" untuk record tanpa hash
        $response->assertSee('—');

        // Cek tombol Verifikasi untuk record
        $response->assertSee(route('offsite.history.verify', $logWithHash->id));
    }

    /**
     * Skenario verifikasi non-AJAX (HTTP regular form/link): Redirect back dengan session flash status badge.
     */
    public function test_verification_via_standard_http_redirects_with_session(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'non_ajax_test.csv',
            'file_hash' => null,
            'status' => 'Berhasil',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('offsite.history.verify', $log->id));

        $response->assertRedirect();
        $response->assertSessionHas('verify_id', $log->id);
        $response->assertSessionHas('verify_badge', 'badge-gray');
        $response->assertSessionHas('verify_label', 'Belum Ada Hash');
    }

    /**
     * Skenario verifikasi oleh RA: ditolak jika bukan unit wilayahnya.
     */
    public function test_ra_cannot_verify_unauthorized_unit(): void
    {
        $otherCabang = Cabang::firstOrCreate(['kode_cabang' => 'BS-999'], [
            'nama_cabang' => 'Cabang Lain',
            'tipe' => 'cabang_b',
        ]);

        $raUser = User::firstOrCreate(['email' => 'ra_other_unit@banksulteng.co.id'], [
            'nip' => '998099',
            'name' => 'RA Unit Lain',
            'password' => bcrypt('password'),
            'role' => 'ra',
            'cabang_id' => $otherCabang->id,
            'unit_code' => '999',
        ]);

        $log = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code, // Unit 098, bukan wilayah RA Unit 999
            'jenis_file' => 'DUMP_04',
            'nama_file' => 'forbidden_test.csv',
            'file_hash' => null,
            'status' => 'Berhasil',
        ]);

        $response = $this->actingAs($raUser)
            ->get(route('offsite.history.verify', $log->id));

        $response->assertStatus(403);
    }

    /**
     * Skenario pencegahan tabrakan file: Record yang filenya terhapus tidak boleh
     * mencocokkan file milik record lain yang kebetulan memiliki nama_file sama.
     */
    public function test_get_stored_file_path_does_not_collide_across_records_with_same_filename(): void
    {
        $sharedName = 'shared_audit_test.csv';

        // Record A: Memiliki hash, tapi file fisiknya TIDAK ada di storage (misal terhapus)
        $logA = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => $sharedName,
            'file_hash' => hash('sha256', "original content of file A\n"),
            'status' => 'Berhasil',
        ]);

        // Record B: Memiliki hash dan file fisiknya ADA di storage
        $logB = AuditLog::create([
            'user_id' => $this->adminUser->id,
            'kode_unit' => $this->unit->unit_code,
            'jenis_file' => 'DUMP_04',
            'nama_file' => $sharedName,
            'file_hash' => hash('sha256', "different content of file B\n"),
            'status' => 'Berhasil',
        ]);

        $fileBPath = Storage::disk('local')->path('offsite_staging/' . $logB->id . '_' . $sharedName);
        Storage::disk('local')->makeDirectory('offsite_staging');
        file_put_contents($fileBPath, "different content of file B\n");

        // Record A harus mengembalikan null untuk getStoredFilePath(), BUKAN file milik Record B!
        $this->assertNull($logA->getStoredFilePath(), 'Record A tidak boleh mencocokkan file milik Record B.');

        // Integritas Record A harus berstatus 'not_found' dengan badge-gray ('Belum Ada Hash')
        $verifyA = $logA->verifyIntegrity();
        $this->assertSame('not_found', $verifyA['status']);
        $this->assertSame('badge-gray', $verifyA['badge']);
        $this->assertSame('Belum Ada Hash', $verifyA['label']);

        // Record B tetap terverifikasi dengan benar
        $verifyB = $logB->verifyIntegrity();
        $this->assertSame('verified', $verifyB['status']);
        $this->assertSame('badge-success', $verifyB['badge']);

        if (file_exists($fileBPath)) {
            @unlink($fileBPath);
        }
    }
}

