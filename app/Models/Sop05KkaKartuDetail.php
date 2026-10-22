<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop05KkaKartuDetail extends Model
{
    use HasFactory;

    protected $table = 'sop05_kka_kartu_details';

    protected $fillable = [
        'surprise_audit_assignment_id',
        'lokasi_penyimpanan',
        'jenis_kartu',
        'saldo_persediaan_buku',
        'harga_per_kartu',
        'jumlah_fisik_kartu',
        'jumlah_pin_mailer',
        'qty_menurut_buku',
        'nilai_fisik_kartu',
        'selisih_qty_buku_fisik',
        'selisih_pin_fisik',
        'selisih_nilai',
        'status_rekonsiliasi',
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