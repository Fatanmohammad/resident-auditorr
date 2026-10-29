<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop08ReviewLaporan extends Model
{
    use HasFactory;

    protected $table = 'sop08_review_laporans';

    protected $fillable = [
        'report_id',
        'reviewer',
        'tanggal_review',
        'jumlah_kegiatan',
        'jumlah_isu',
        'high_risk',
        'tl_overdue',
        'persen_kelengkapan',
        'akurasi_data',
        'konsistensi',
        'kualitas_analisis',
        'bukti_pendukung',
        'tindak_lanjut',
        'eskalasi',
        'skor_review',
        'hasil_otomatis',
        'override',
        'alasan_override',
        'status_review_final',
    ];

    protected $casts = [
        'tanggal_review' => 'date',
        'persen_kelengkapan' => 'decimal:2',
        'skor_review' => 'integer',
        'jumlah_kegiatan' => 'integer',
        'jumlah_isu' => 'integer',
        'high_risk' => 'integer',
        'tl_overdue' => 'integer',
    ];

    public function kalender(): BelongsTo
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function opsiPenilaian(): array
    {
        return [
            'MEMADAI',
            'PERLU PERBAIKAN',
            'TIDAK MEMADAI',
        ];
    }

    public static function opsiOverride(): array
    {
        return [
            'MEMADAI',
            'KOREKSI MINOR',
            'KOREKSI SUBSTANSI',
            'PERLU ESKALASI',
            'REJECTED FOR RESUBMISSION',
        ];
    }
}
