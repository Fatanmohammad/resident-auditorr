<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop08DistribusiQuality extends Model
{
    use HasFactory;

    protected $table = 'sop08_distribusi_qualities';

    protected $fillable = [
        'report_id',
        'tanggal_distribusi',
        'penerima',
        'media_arsip',
        'versi',
        'skor_tepat_waktu',
        'skor_kelengkapan',
        'skor_akurasi',
        'skor_analisis',
        'skor_bukti',
        'skor_tindak_lanjut',
        'skor_eskalasi',
        'skor_dasar',
        'penyesuaian_reviewer',
        'quality_score',
        'kategori',
        'catatan_kpi',
        'status_arsip',
    ];

    protected $casts = [
        'tanggal_distribusi' => 'date',
        'skor_tepat_waktu' => 'decimal:2',
        'skor_kelengkapan' => 'decimal:2',
        'skor_akurasi' => 'decimal:2',
        'skor_analisis' => 'decimal:2',
        'skor_bukti' => 'decimal:2',
        'skor_tindak_lanjut' => 'decimal:2',
        'skor_eskalasi' => 'decimal:2',
        'skor_dasar' => 'decimal:2',
        'penyesuaian_reviewer' => 'decimal:2',
        'quality_score' => 'decimal:2',
    ];

    public function kalender(): BelongsTo
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function opsiStatusArsip(): array
    {
        return [
            'TERARSIP LENGKAP',
            'BELUM DIARSIP',
            'DALAM PROSES',
        ];
    }
}
