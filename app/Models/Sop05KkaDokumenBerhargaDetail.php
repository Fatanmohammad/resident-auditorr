<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaDokumenBerhargaDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_dokumen_berharga_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'jenis_dokumen',
        'persediaan_sistem_qty',
        'catatan_petugas_qty',
        'hasil_fisik_qty',
        'harga_per_lembar',
        'nilai_sistem',
        'nilai_fisik',
        'selisih_sistem_catatan',
        'selisih_sistem_fisik',
        'selisih_catatan_fisik',
        'selisih_nilai',
        'range_nomor_seri',
        'nomor_hilang_lompat',
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