<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUnitScope;

class StagingOffsite extends Model
{
    use HasUnitScope;
    
    protected $table = 'staging_offsite';

    protected $fillable = [
        'wp_offsite_id', 
        'tanggal_data', 
        'kode_unit', 
        'nama_unit', 
        'jenis_unit', 
        'ra_id', 
        'nama_ra', 
        'source_table', 
        'source_record_id', 
        'object_id', 
        'case_id', 
        'data_code', 
        'area_review', 
        'deskripsi_narasi', 
        'nominal', 
        'user_maker', 
        'risk_level', 
        'exception_awal', 
        'jenis_exception_awal', 
        'kka_sheet_tujuan', 
        'sampel_low', 
        'masuk_kka_final', 
        'alasan_tidak_masuk_kka', 
        'status_data_quality', 
        'catatan_validasi',
        // --- Kolom Tambahan Hasil Scan Detector ---
        'flags',
        'jumlah_flag_risiko',
        'perlu_kka',
        'perlu_klarifikasi',
        'perlu_eskalasi',
        'processed_at',
    ];

    protected $casts = [
        'tanggal_data'      => 'date', 
        'nominal'           => 'decimal:2', 
        'exception_awal'    => 'boolean', 
        'sampel_low'        => 'boolean', 
        'masuk_kka_final'   => 'boolean',
        'perlu_kka'         => 'boolean',
        'perlu_klarifikasi' => 'boolean',
        'perlu_eskalasi'    => 'boolean',
        'deskripsi_narasi'  => 'array', // Pastikan di-cast ke array untuk JSON
        'flags'             => 'array', // Pastikan di-cast ke array untuk JSON
        'processed_at'      => 'datetime',
    ];

    /** Kata kunci (fuzzy, case-insensitive) untuk cari kolom deskripsi/narasi di raw data */
    private const KATA_KUNCI_DESKRIPSI = [
        'uraian', 'keterangan', 'narasi', 'deskripsi', 'ket_tx', 'ket',
        'nm_ledger', 'nama_transaksi', 'glnmbi', 'nm_gl',
    ];

    /** Kata kunci untuk cari kolom nomor rekening/referensi/kode di raw data */
    private const KATA_KUNCI_REFERENSI = [
        'no_rek', 'rekening', 'referensi', 'no_ref', 'account', 'no_arsip',
        'no_ledger', 'glnbrbi',
    ];

    /**
     * Cari value pertama di $data yang key-nya mengandung salah satu dari $kataKunci.
     */
    private static function cariNilaiFuzzy(array $data, array $kataKunci): ?string
    {
        foreach ($data as $key => $value) {
            if ($value === null || $value === '' || !is_scalar($value)) {
                continue;
            }
            $keyNormal = strtolower(str_replace([' ', '-'], '_', (string) $key));
            foreach ($kataKunci as $kk) {
                if (str_contains($keyNormal, $kk)) {
                    return (string) $value;
                }
            }
        }
        return null;
    }

    /**
     * Ringkasan siap-baca dari deskripsi_narasi. Dipakai di Blade sebagai
     * {{ $row->ringkasan_narasi }} — JANGAN tampilkan deskripsi_narasi mentah.
     * TIDAK PERNAH mengembalikan JSON mentah, walau tidak ada key yang cocok.
     */
    public function getRingkasanNarasiAttribute(): string
    {
        $data = $this->deskripsi_narasi;

        if (!is_array($data) || empty($data)) {
            return '-';
        }

        $uraian = self::cariNilaiFuzzy($data, self::KATA_KUNCI_DESKRIPSI);
        $noRef  = self::cariNilaiFuzzy($data, self::KATA_KUNCI_REFERENSI);

        if ($uraian && $noRef) {
            return "{$uraian} ({$noRef})";
        }
        if ($uraian) {
            return $uraian;
        }
        if ($noRef) {
            return "Ref: {$noRef}";
        }

        // Fallback aman: pasangan key:value pertama yang ada isinya,
        // TIDAK PERNAH dump JSON mentah utuh.
        $pasangan = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === '' || !is_scalar($value)) {
                continue;
            }
            $labelRapi = ucwords(str_replace('_', ' ', strtolower((string) $key)));
            $pasangan[] = "{$labelRapi}: {$value}";
            if (count($pasangan) >= 2) {
                break;
            }
        }

        return $pasangan ? implode(' | ', $pasangan) : '-';
    }

    /**
     * Nomor rekening/referensi saja (dipakai terpisah kalau perlu ditampilkan
     * di kolom sendiri, bukan digabung dengan uraian).
     */
    public function getNoReferensiRingkasAttribute(): ?string
    {
        $data = $this->deskripsi_narasi;
        if (!is_array($data)) {
            return null;
        }
        return self::cariNilaiFuzzy($data, self::KATA_KUNCI_REFERENSI);
    }

    public function wpOffsite() 
    { 
        return $this->belongsTo(WpOffsite::class); 
    }

    public function ra() 
    { 
        return $this->belongsTo(User::class, 'ra_id'); 
    }
}