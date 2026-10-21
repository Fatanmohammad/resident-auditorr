<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaAsetDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_aset_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'id_tag_aset',
        'jenis_nama_aset',
        'lokasi_register',
        'lokasi_fisik',
        'qty_register',
        'qty_fisik',
        'selisih_qty',
        'kondisi_fisik',
        'label_inventaris',
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