<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaLingkunganDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_lingkungan_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'kategori',
        'area_perangkat',
        'kriteria_unik',
        'hasil_observasi_ra',
        'hasil_uji_playback',
        'kesimpulan_uji',
        'sebab',
        'dampak',
        'referensi_bukti',
        'validasi',
        'exception_aktif',
    ];

    public function assignment()
    {
        return $this->belongsTo(SurpriseAuditAssignment::class, 'surprise_audit_assignment_id');
    }
}