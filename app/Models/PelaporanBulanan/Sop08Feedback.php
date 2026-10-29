<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop08Feedback extends Model
{
    protected $table = 'sop08_feedbacks';

    protected $fillable = [
        'feedback_id',
        'report_id',
        'kode_unit',
        'nama_unit',
        'tanggal',
        'diberikan_kepada',
        'jenis_feedback',
        'uraian_feedback',
        'tindakan_perbaikan',
        'pic',
        'target',
        'status',
        'bukti',
        'tanggal_selesai',
        'verifikasi_reviewer',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'target' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function kalender(): BelongsTo
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function jenisFeedbackList(): array
    {
        return [
            'Kualitas Laporan',
            'Metodologi/Pengujian',
            'Ketepatan Waktu',
            'Kualitas Analisis',
            'Monitoring TL',
            'Apresiasi/Lainnya',
        ];
    }

    public static function statusList(): array
    {
        return [
            'OPEN',
            'IN PROGRESS',
            'MENUNGGU VERIFIKASI',
            'CLOSED',
        ];
    }

    public static function verifikasiReviewerList(): array
    {
        return [
            'BELUM DIVERIFIKASI',
            'MEMADAI / CLOSED',
            'PERLU PERBAIKAN ULANG',
        ];
    }
}
