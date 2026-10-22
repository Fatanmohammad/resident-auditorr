<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaBarangCetakanDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_barang_cetakan_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'jenis_barang_cetakan',
        'catatan_petugas',
        'hasil_fisik',
        'selisih',
        'kondisi_penyimpanan',
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