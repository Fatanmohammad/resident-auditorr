<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08RekapIsuTl extends Model
{
    use HasFactory;

    protected $table = 'sop08_rekap_isu_tls';

    protected $fillable = [
        'issue_id',
        'report_id',
        'periode',
        'kode_unit',
        'nama_unit',
        'resident_auditor',
        'sumber_sop',
        'referensi_sumber',
        'area',
        'jenis_isu',
        'risk_level',
        'uraian_isu',
        'sebab_awal',
        'dampak_risiko',
        'rekomendasi',
        'pic',
        'target_tl',
        'status_tl',
        'aging_overdue',
        'berulang',
        'nilai_dampak',
        'flag_signifikan',
        'jalur_tindak_lanjut',
    ];

    protected $casts = [
        'periode' => 'date',
        'target_tl' => 'date',
        'aging_overdue' => 'integer',
        'nilai_dampak' => 'float',
    ];

    public function kalender()
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function areaList(): array
    {
        return [
            'Teller Kas',
            'DPK Layanan',
            'Kredit',
            'Biaya Beban',
            'Kartu ATM',
            'Pengaduan',
            'Rekon Kartu ATM',
            'Cash Opname',
            'Observasi Lingkungan',
            'Rekon Cek BG',
            'Rekon Barang Cetakan',
            'Aset',
            'Lainnya',
        ];
    }
}
