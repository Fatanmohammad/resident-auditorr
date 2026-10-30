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

    /**
     * Mutator Kategori Bukti -> kolom fisik 'jenis_bukti'
     */
    public function setKategoriBuktiAttribute($value)
    {
        $this->attributes['jenis_bukti'] = $value;
    }

    /**
     * Mutator Deskripsi Bukti -> kolom fisik 'deskripsi_relevansi'
     */
    public function setDeskripsiBuktiAttribute($value)
    {
        $this->attributes['deskripsi_relevansi'] = $value;
    }

    /**
     * Mutator Kondisi Bukti -> kolom fisik 'status_integritas'
     */
    public function setKondisiBuktiAttribute($value)
    {
        $this->attributes['status_integritas'] = $value;
    }

    /**
     * Mutator Status Penguasaan -> kolom fisik 'pemegang_saat_ini'
     */
    public function setStatusPenguasaanAttribute($value)
    {
        $this->attributes['pemegang_saat_ini'] = $value;
    }

    /**
     * Mutator Sumber Bukti -> kolom fisik 'sumber_perolehan'
     */
    public function setSumberBuktiAttribute($value)
    {
        $this->attributes['sumber_perolehan'] = $value;
    }

    /**
     * Mutator Petugas Custodian -> kolom fisik 'diperoleh_oleh'
     */
    public function setPetugasCustodianAttribute($value)
    {
        $this->attributes['diperoleh_oleh'] = $value;
    }
}
