<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurpriseAuditAssignment extends Model
{
    use HasFactory;

    protected $table = 'surprise_audit_assignments';

    protected $fillable = [
        'visit_id',
        'kode_unit',
        'nama_unit',
        'tanggal_rencana',
        'tanggal_mulai_aktual',
        'tanggal_selesai_aktual',
        'resident_auditor',
        'reviewer',
        'dasar_penugasan',
        'nomor_assignment',
        'pemberi_tugas',
        'tujuan',
        'klasifikasi_kerahasiaan',
        'ruang_lingkup_area',
        'status_assignment',
    ];

    protected $casts = [
        'ruang_lingkup_area' => 'array',
        'tanggal_rencana' => 'datetime',
        'tanggal_mulai_aktual' => 'datetime',
        'tanggal_selesai_aktual' => 'datetime',
    ];

    /**
     * Relasi ke rincian KKA Kas (WP-RA-05-C)
     */
    public function kasDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaKasDetail::class, 'surprise_audit_assignment_id');
    }
}