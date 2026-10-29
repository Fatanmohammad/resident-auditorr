<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop08KoreksiApproval extends Model
{
    use HasFactory;

    protected $table = 'sop08_koreksi_approvals';

    protected $fillable = [
        'report_id',
        'hasil_review',
        'jenis_koreksi',
        'catatan_reviewer',
        'tanggal_permintaan',
        'batas_koreksi',
        'tanggal_resubmit',
        'perbaikan_ra',
        'tanggal_review_ulang',
        'keputusan_approval',
        'approved_by',
        'tanggal_approval',
        'versi_final',
        'status_final',
        'hari_terlambat_koreksi',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
        'batas_koreksi' => 'date',
        'tanggal_resubmit' => 'date',
        'tanggal_review_ulang' => 'date',
        'tanggal_approval' => 'date',
        'hari_terlambat_koreksi' => 'integer',
    ];

    public function kalender(): BelongsTo
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function opsiKeputusan(): array
    {
        return [
            'APPROVED/FINAL',
            'REVISION REQUIRED',
            'REJECTED FOR RESUBMISSION',
        ];
    }
}
