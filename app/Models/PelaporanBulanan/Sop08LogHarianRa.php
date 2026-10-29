<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Sop08LogHarianRa extends Model
{
    use HasFactory;

    protected $table = 'sop08_log_harian_ras';

    protected $fillable = [
        'log_id',
        'tanggal',
        'ra_id',
        'user_id',
        'nama_ra',
        'kode_unit',
        'nama_unit',
        'status_hari',
        'sumber_sop',
        'jenis_aktivitas',
        'area',
        'output_referensi_wp',
        'status_penyelesaian',
        'hasil_review',
        'reviewer',
        'tanggal_review',
        'jumlah_item',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_review' => 'date',
        'jumlah_item' => 'integer',
    ];

    public function masterRa()
    {
        return $this->belongsTo(Sop08MasterRa::class, 'ra_id', 'ra_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function statusHariList(): array
    {
        return [
            'KERJA',
            'CUTI',
            'SAKIT',
            'DINAS LUAR',
            'PELATIHAN',
        ];
    }

    public static function sumberSopList(): array
    {
        return [
            'SOP 03 - Daily Review',
            'SOP 04 - Offsite Review',
            'SOP 05 - Onsite Audit',
            'SOP 06 - Risk Trigger Visit',
            'SOP 07 - Monitoring Tindak Lanjut',
            'SOP 08 - Pelaporan Bulanan',
            'Non-Audit / Administrasi',
        ];
    }

    public static function statusPenyelesaianList(): array
    {
        return [
            'COMPLETED',
            'IN PROGRESS',
            'PENDING',
        ];
    }

    public static function hasilReviewList(): array
    {
        return [
            'BELUM DIREVIEW',
            'MEMADAI',
            'PERLU PERBAIKAN',
            'TIDAK MEMADAI',
        ];
    }

    public static function areaList(): array
    {
        return array_merge(Sop08RekapIsuTl::areaList(), ['Umum / Administrasi']);
    }
}
