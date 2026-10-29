<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sop08IsuSignifikan extends Model
{
    use HasFactory;

    protected $table = 'sop08_isu_signifikans';

    protected $fillable = [
        'issue_id',
        'report_id',
        'periode',
        'nama_unit',
        'area',
        'jenis_isu',
        'risk_level',
        'uraian_isu',
        'status_tl',
        'aging_overdue',
        'berulang',
        'jalur_sistem',
        'keputusan_reviewer',
        'tujuan_eskalasi',
        'tanggal_eskalasi',
        'referensi_bukti',
        'arahan_tindak_lanjut',
    ];

    protected $casts = [
        'periode' => 'date',
        'tanggal_eskalasi' => 'date',
        'aging_overdue' => 'integer',
    ];

    public function kalender(): BelongsTo
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public function rekapIsu(): BelongsTo
    {
        return $this->belongsTo(Sop08RekapIsuTl::class, 'issue_id', 'issue_id');
    }

    public static function opsiKeputusan(): array
    {
        return [
            'Eskalasi SOP-RA-10 (Komite Audit)',
            'Pemeriksaan Fraud SOP-RA-09 (Investigasi)',
            'Monitoring Ketat SOP-RA-07',
            'Pantau Bulanan',
        ];
    }

    public static function opsiTujuan(): array
    {
        return [
            'Kadiv SKAI',
            'Direksi',
            'Komite Audit',
            'Pemimpin Cabang',
            'Bagian Investigasi Fraud',
        ];
    }
}
