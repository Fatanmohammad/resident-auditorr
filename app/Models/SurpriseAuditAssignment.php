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
     * Accessor agar pemanggilan $item->status mengembalikan status_assignment
     */
    public function getStatusAttribute()
    {
        return $this->attributes['status_assignment'] ?? 'SIAP DILAKSANAKAN';
    }

    /**
     * Mutator agar $item->status = '...' juga mengisi status_assignment
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status_assignment'] = $value;
    }

    /**
     * Relasi ke rincian KKA Kas Khasanah & Teller (WP-RA-05-C)
     */
    public function kasDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaKasDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Kas ATM (WP-RA-05-D)
     */
    public function atmDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaAtmDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Kartu ATM & PIN (WP-RA-05-E)
     */
    public function kartuDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaKartuDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Persediaan Barang Cetakan (WP-RA-05-F)
     */
    public function barangCetakanDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaBarangCetakanDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Dokumen Berharga (WP-RA-05-G)
     */
    public function dokumenBerhargaDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaDokumenBerhargaDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Dokumen Agunan (WP-RA-05-H)
     */
    public function agunanDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaAgunanDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Inventarisasi Aset (WP-RA-05-I)
     */
    public function asetDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaAsetDetail::class, 'surprise_audit_assignment_id');
    }

    /**
     * Relasi ke rincian KKA Lingkungan dan CCTV (WP-RA-05-J)
     */
    public function lingkunganDetails()
    {
        return $this->hasMany(\App\Models\Sop05KkaLingkunganDetail::class, 'surprise_audit_assignment_id');
    }
}
