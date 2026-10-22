<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaAtmDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_atm_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'area_kka',
        'objek_lokasi_atm',
        'saldo_buku',
        'kaset1_pecahan', 
        'kaset1_lembar', 
        'kaset1_nilai',
        'kaset2_pecahan', 
        'kaset2_lembar', 
        'kaset2_nilai',
        'kaset3_pecahan', 
        'kaset3_lembar', 
        'kaset3_nilai',
        'kaset4_pecahan', 
        'kaset4_lembar', 
        'kaset4_nilai',
        'hasil_fisik_total',
        'selisih',
        'status_uji',
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