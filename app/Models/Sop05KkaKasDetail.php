<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaKasDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_kas_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'objek_pemeriksaan',
        'nilai_sistem',
        'ule_100k', 'ule_50k', 'ule_20k', 'ule_10k', 'ule_5k', 'ule_2k', 'ule_1k',
        'utle_100k', 'utle_50k', 'utle_20k', 'utle_10k', 'utle_5k', 'utle_2k', 'utle_1k',
        'logam_1000', 'logam_500', 'logam_200', 'logam_100',
        'total_ule', 'total_utle', 'total_logam',
        'nilai_fisik',
        'selisih',
        'status_uji',
        'catatan_pemeriksa',
        'exception_aktif',
    ];

    public function assignment()
    {
        return $this->belongsTo(SurpriseAuditAssignment::class, 'surprise_audit_assignment_id');
    }
}