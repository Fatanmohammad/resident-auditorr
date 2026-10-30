<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09ChainOfCustody extends Model
{
    use HasFactory;

    protected $table = 'sop09_bukti_custodies';
    protected $guarded = ['id'];

    /**
     * Relasi ke Register Indikasi Fraud
     */
    public function registerIndikasi()
    {
        return $this->belongsTo(Sop09RegisterIndikasi::class, 'indikasi_id', 'indikasi_id');
    }

    /**
     * Accessor untuk Kategori Bukti (kompatibilitas dengan jenis_bukti)
     */
    public function getKategoriBuktiAttribute()
    {
        return $this->attributes['kategori_bukti'] ?? ($this->attributes['jenis_bukti'] ?? '-');
    }

    /**
     * Accessor untuk Deskripsi Bukti (kompatibilitas dengan deskripsi_relevansi)
     */
    public function getDeskripsiBuktiAttribute()
    {
        return $this->attributes['deskripsi_bukti'] ?? ($this->attributes['deskripsi_relevansi'] ?? '-');
    }

    /**
     * Accessor untuk Kondisi Integritas Bukti (kompatibilitas dengan status_integritas)
     */
    public function getKondisiBuktiAttribute()
    {
        return $this->attributes['kondisi_bukti'] ?? ($this->attributes['status_integritas'] ?? 'UTUH');
    }

    /**
     * Accessor untuk Status Penguasaan (kompatibilitas dengan pemegang_saat_ini)
     */
    public function getStatusPenguasaanAttribute()
    {
        return $this->attributes['status_penguasaan'] ?? ($this->attributes['pemegang_saat_ini'] ?? 'BRANKAS_SKAI');
    }

    /**
     * Accessor untuk Sumber Bukti (kompatibilitas dengan sumber_perolehan)
     */
    public function getSumberBuktiAttribute()
    {
        return $this->attributes['sumber_bukti'] ?? ($this->attributes['sumber_perolehan'] ?? '-');
    }

    /**
     * Accessor untuk Petugas Custodian (kompatibilitas dengan diperoleh_oleh)
     */
    public function getPetugasCustodianAttribute()
    {
        return $this->attributes['petugas_custodian'] ?? ($this->attributes['diperoleh_oleh'] ?? '-');
    }
}
