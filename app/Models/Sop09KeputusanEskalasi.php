<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09KeputusanEskalasi extends Model
{
    use HasFactory;

    protected $table = 'sop09_keputusan_eskalasis';
    protected $guarded = ['id'];

    /**
     * Relasi ke Register Indikasi Fraud
     */
    public function registerIndikasi()
    {
        return $this->belongsTo(Sop09RegisterIndikasi::class, 'indikasi_id', 'indikasi_id');
    }

    /**
     * Accessor & Mutator untuk fakta_utama (kompatibel dengan arahan_tindakan_segera / catatan_review)
     */
    public function getFaktaUtamaAttribute()
    {
        return $this->attributes['fakta_utama'] ?? ($this->attributes['arahan_tindakan_segera'] ?? '-');
    }

    public function setFaktaUtamaAttribute($value)
    {
        $this->attributes['arahan_tindakan_segera'] = $value;
    }

    /**
     * Accessor & Mutator untuk rekomendasi_tim (kompatibel dengan saran_sistem)
     */
    public function getRekomendasiTimAttribute()
    {
        return $this->attributes['rekomendasi_tim'] ?? ($this->attributes['saran_sistem'] ?? '-');
    }

    public function setRekomendasiTimAttribute($value)
    {
        $this->attributes['saran_sistem'] = $value;
    }

    /**
     * Accessor & Mutator untuk nomor_nota_dinas (kompatibel dengan laporan_insidentil_id)
     */
    public function getNomorNotaDinasAttribute()
    {
        return $this->attributes['nomor_nota_dinas'] ?? ($this->attributes['laporan_insidentil_id'] ?? '-');
    }

    public function setNomorNotaDinasAttribute($value)
    {
        $this->attributes['laporan_insidentil_id'] = $value;
    }

    /**
     * Accessor untuk estimasi kerugian dari relasi indikasi jika tidak ada di tabel eskalasi
     */
    public function getEstimasiKerugianAttribute()
    {
        if (isset($this->attributes['estimasi_kerugian'])) {
            return $this->attributes['estimasi_kerugian'];
        }
        return $this->registerIndikasi->nominal_awal ?? 0;
    }
}
