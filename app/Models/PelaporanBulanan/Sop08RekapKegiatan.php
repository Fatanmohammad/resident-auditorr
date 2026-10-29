<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08RekapKegiatan extends Model
{
    use HasFactory;

    protected $table = 'sop08_rekap_kegiatans';

    protected $fillable = [
        'source_export_id',
        'report_id',
        'periode',
        'kode_unit',
        'nama_unit',
        'resident_auditor',
        'sumber_sop',
        'jenis_kegiatan',
        'area',
        'rencana',
        'realisasi',
        'populasi',
        'sampel_terpilih',
        'sudah_diuji',
        'exception',
        'finding',
        'high_risk',
        'referensi_wp_kka',
        'status_kegiatan',
        'catatan',
    ];

    protected $casts = [
        'periode' => 'date',
        'rencana' => 'integer',
        'realisasi' => 'integer',
        'populasi' => 'integer',
        'sampel_terpilih' => 'integer',
        'sudah_diuji' => 'integer',
        'exception' => 'integer',
        'finding' => 'integer',
        'high_risk' => 'integer',
    ];

    public function kalender()
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function jenisKegiatanList(): array
    {
        return [
            'Daily Review',
            'Offsite Review',
            'Onsite Visit',
            'Surprise Audit',
            'Risk Trigger Visit',
            'Monitoring Tindak Lanjut',
        ];
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
