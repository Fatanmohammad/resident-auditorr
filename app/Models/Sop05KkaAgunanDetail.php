<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaAgunanDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_agunan_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'cif_debitur',
        'no_kredit',
        'jenis_agunan',
        'dokumen_menurut_register',
        'bentuk_disyaratkan',
        'dokumen_ditemukan',
        'bentuk_ditemukan',
        'identitas_sesuai',
        'nomor_dokumen',
        'tanggal_kedaluwarsa',
        'hari_ke_kedaluwarsa',
        'akses_custodian',
        'register_mutakhir',
        'metode_pemilihan',
        'sebab',
        'dampak',
        'referensi_bukti',
        'status_uji',
        'exception_aktif',
    ];

    public function assignment()
    {
        return $this->belongsTo(SurpriseAuditAssignment::class, 'surprise_audit_assignment_id');
    }
}