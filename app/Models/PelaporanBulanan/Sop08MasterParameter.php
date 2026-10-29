<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08MasterParameter extends Model
{
    use HasFactory;

    protected $table = 'sop08_master_parameters';

    protected $fillable = [
        'kode_sop',
        'versi_wp',
        'tanggal_cutoff',
        'periode_dashboard',
        'hari_kerja_jatuh_tempo',
        'reviewer_default',
        'unit_ra_default',
        'target_quality_score',
        'bobot_tepat_waktu',
        'bobot_kelengkapan',
        'bobot_akurasi',
        'bobot_analisis',
        'bobot_bukti',
        'bobot_tindak_lanjut',
        'bobot_eskalasi',
        'ambang_high_attention',
        'bobot_konsistensi',
        'bobot_penyelesaian',
        'bobot_kualitas',
        'ambang_baik_konsisten',
        'ambang_memadai',
        'ambang_perlu_peningkatan',
    ];

    protected $casts = [
        'tanggal_cutoff' => 'date',
        'periode_dashboard' => 'date',
        'target_quality_score' => 'float',
        'ambang_high_attention' => 'float',
        'ambang_baik_konsisten' => 'float',
        'ambang_memadai' => 'float',
        'ambang_perlu_peningkatan' => 'float',
    ];

    public static function getActive(): self
    {
        return static::first() ?? static::create([
            'kode_sop' => 'SOP-RA-08',
            'versi_wp' => 'v1',
            'tanggal_cutoff' => '2026-10-14',
            'periode_dashboard' => '2026-09-01',
            'hari_kerja_jatuh_tempo' => 5,
        ]);
    }
}
