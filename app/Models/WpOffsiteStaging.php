<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WpOffsiteStaging extends Model
{
    use HasFactory;

    protected $table = 'wp_offsite_stagings';

    protected $fillable = [
        'wp_offsite_id',
        'cabang_id',
        'domain_type',
        'tgl_transaksi',
        'raw_data',
        'flags',
        'jumlah_flag_risiko',
        'area_review',
        'risk_level',
        'case_id',
        'kka_sheet_tujuan',
        'perlu_kka',
        'perlu_klarifikasi',
        'perlu_eskalasi',
        'processed_at',
        'uploaded_by',
    ];


    protected $casts = [
        'raw_data'          => 'array',
        'flags'             => 'array',
        'perlu_kka'         => 'boolean',
        'perlu_klarifikasi' => 'boolean',
        'perlu_eskalasi'    => 'boolean',
        'tgl_transaksi'     => 'date',
        'processed_at'      => 'datetime',
    ];

    /** Kata kunci (fuzzy, case-insensitive) untuk mencari kolom deskripsi/narasi di raw_data */
    private const KATA_KUNCI_DESKRIPSI = ['uraian', 'keterangan', 'narasi', 'deskripsi', 'ket_tx', 'ket'];

    /** Kata kunci untuk mencari kolom nomor rekening/referensi di raw_data */
    private const KATA_KUNCI_REFERENSI = ['no_rek', 'rekening', 'referensi', 'no_ref', 'account', 'no_arsip'];

    /**
     * Cari value pertama di $data yang key-nya mengandung salah satu dari $kataKunci,
     * tanpa peduli besar/kecil huruf atau garis bawah.
     */
    private static function cariNilaiFuzzy(array $data, array $kataKunci): ?string
    {
        foreach ($data as $key => $value) {
            if (empty($value) || !is_scalar($value)) {
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
     * Ringkasan siap-baca dari raw_data. Dipakai di Blade sebagai
     * {{ $item->formatted_raw_data }} — JANGAN tampilkan $item->raw_data langsung.
     */
    protected function formattedRawData(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $raw = $attributes['raw_data'] ?? null;
                $data = is_string($raw) ? json_decode($raw, true) : $raw;

                if (!is_array($data) || empty($data)) {
                    return '-';
                }

                $uraian = self::cariNilaiFuzzy($data, self::KATA_KUNCI_DESKRIPSI);
                $noRef  = self::cariNilaiFuzzy($data, self::KATA_KUNCI_REFERENSI);

                if ($uraian && $noRef) {
                    return "{$uraian} (Ref: {$noRef})";
                }
                if ($uraian) {
                    return $uraian;
                }
                if ($noRef) {
                    return "Ref: {$noRef}";
                }


                // Fallback terakhir: tidak ada key yang cocok sama sekali.
                // Tampilkan 2-3 pasangan key:value pertama yang ada isinya,
                // supaya tetap informatif (bukan cuma "-") sambil kelihatan
                // datanya belum ter-mapping dengan baik.
                $pasangan = [];
                foreach ($data as $key => $value) {
                    if (empty($value) || !is_scalar($value)) {
                        continue;
                    }
                    $labelRapi = ucwords(str_replace('_', ' ', strtolower((string) $key)));
                    $pasangan[] = "{$labelRapi}: {$value}";
                    if (count($pasangan) >= 3) {
                        break;
                    }
                }

                return $pasangan ? implode(' | ', $pasangan) : '-';
            }
        );
    }
}