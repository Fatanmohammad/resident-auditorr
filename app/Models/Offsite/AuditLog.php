<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use Illuminate\Support\Facades\Storage;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'kode_unit', 
        'periode',
        'jenis_file', 
        'nama_file', 
        'file_hash',
        'status', 
        'total_low', 
        'total_moderate', 
        'total_high', 
        'pesan_error'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cari path file fisik di storage yang sesuai dengan record audit log ini.
     */
    public function getStoredFilePath(): ?string
    {
        if (empty($this->nama_file)) {
            return null;
        }

        $candidateNames = [];
        if ($this->id) {
            $candidateNames[] = $this->id . '_' . $this->nama_file;
        }
        if ($this->created_at) {
            $candidateNames[] = $this->created_at->timestamp . '_' . $this->nama_file;
        }

        $directories = [
            Storage::disk('local')->path('offsite_staging'),
            storage_path('app/private/offsite_staging'),
            storage_path('app/offsite_staging'),
        ];

        foreach ($candidateNames as $filename) {
            foreach ($directories as $dir) {
                $target = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($target)) {
                    return $target;
                }
            }
        }

        return null;
    }

    /**
     * Hitung hash file fisik di storage saat ini secara streaming.
     */
    public function calculateStoredFileHash(): ?string
    {
        $path = $this->getStoredFilePath();
        if (!$path || !file_exists($path)) {
            return null;
        }

        $hash = @hash_file('sha256', $path);
        return ($hash !== false) ? strtolower($hash) : null;
    }

    /**
     * Verifikasi integritas file di storage terhadap file_hash yang tercatat di database.
     * Menggunakan fungsi bawaan hash_equals() untuk keamanan komparasi.
     *
     * Return array:
     * - status: 'verified' | 'mismatch' | 'no_hash' | 'not_found'
     * - badge: 'badge-success' | 'badge-danger' | 'badge-gray'
     * - label: 'Terverifikasi' | 'Tidak Cocok / Berubah' | 'Belum Ada Hash'
     */
    public function verifyIntegrity(): array
    {
        if (empty($this->file_hash)) {
            return [
                'status' => 'no_hash',
                'badge' => 'badge-gray',
                'label' => 'Belum Ada Hash',
                'message' => 'Record riwayat upload ini tidak memiliki fingerprint file_hash.',
                'database_hash' => null,
                'current_hash' => null,
            ];
        }

        $path = $this->getStoredFilePath();
        if (!$path || !file_exists($path)) {
            return [
                'status' => 'not_found',
                'badge' => 'badge-gray',
                'label' => 'Belum Ada Hash',
                'message' => 'File fisik tidak ditemukan di storage.',
                'database_hash' => $this->file_hash,
                'current_hash' => null,
            ];
        }

        $currentHash = @hash_file('sha256', $path);
        if ($currentHash === false) {
            return [
                'status' => 'read_error',
                'badge' => 'badge-gray',
                'label' => 'Belum Ada Hash',
                'message' => 'Gagal membaca atau menghitung hash file dari storage.',
                'database_hash' => $this->file_hash,
                'current_hash' => null,
            ];
        }

        $currentHash = strtolower($currentHash);
        $expectedHash = strtolower($this->file_hash);

        if (hash_equals($expectedHash, $currentHash)) {
            return [
                'status' => 'verified',
                'badge' => 'badge-success',
                'label' => 'Terverifikasi',
                'message' => 'Integritas file valid (SHA-256 cocok sempurna).',
                'database_hash' => $this->file_hash,
                'current_hash' => $currentHash,
            ];
        }

        return [
            'status' => 'mismatch',
            'badge' => 'badge-danger',
            'label' => 'Tidak Cocok / Berubah',
            'message' => 'Peringatan integritas: Isi file di storage telah berubah atau tidak cocok dengan hash saat upload.',
            'database_hash' => $this->file_hash,
            'current_hash' => $currentHash,
        ];
    }
}
