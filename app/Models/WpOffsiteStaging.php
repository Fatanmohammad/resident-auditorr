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
        'raw_data'      => 'array',
        'flags'         => 'array',
        'perlu_kka'     => 'boolean',
        'perlu_klarifikasi' => 'boolean',
        'perlu_eskalasi' => 'boolean',
        'tgl_transaksi' => 'date',
        'processed_at'  => 'datetime',
    ];

    /**
     * Accessor Otomatis (Gunakan $staging->formatted_raw_data jika ingin menampilkan teks ringkas di Blade View)
     */
    protected function formattedRawData(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $raw = $attributes['raw_data'] ?? null;
                $data = is_string($raw) ? json_decode($raw, true) : $raw;

                if (is_array($data)) {
                    $uraian = $data['URAIAN'] ?? $data['uraian'] ?? $data['DESKRIPSI'] ?? $data['deskripsi'] ?? '';
                    $noRek  = $data['NO_REK'] ?? $data['no_rek'] ?? $data['NO_REKENING'] ?? $data['no_rekening'] ?? '';

                    if ($uraian && $noRek) {
                        return "{$uraian} (No. Rek: {$noRek})";
                    } elseif ($uraian) {
                        return $uraian;
                    } elseif ($noRek) {
                        return "No. Rek: {$noRek}";
                    }
                }

                return '-';
            }
        );
    }
}