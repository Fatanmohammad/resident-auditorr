<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaObservasi extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_observasis';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'kategori',
        'titik_kontrol_lokasi',
        'kriteria_standar',
        'kondisi_hasil_observasi',
        'uji_fungsi_playback',
        'akses_kebersihan_aman',
        'rating_1_5',
        'analisis_gap',
        'sebab',
        'dampak',
        'referensi_bukti_foto',
        'simpulan',
        'status_uji',
        'exception',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rating_1_5' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        // Exception (Kolom R): Otomatis "Ya" jika Uji Fungsi/Playback == "Tidak Berfungsi" ATAU Akses/Kebersihan/Aman == "Tidak Sesuai" ATAU (Rating 1-5 != "" DAN Rating <= 2); selain itu "Tidak".
        $ujiNot = ($this->uji_fungsi_playback === 'Tidak Berfungsi');
        $aksesNot = ($this->akses_kebersihan_aman === 'Tidak Sesuai');
        $lowRating = ($this->rating_1_5 !== null && intval($this->rating_1_5) <= 2);

        if ($ujiNot || $aksesNot || $lowRating) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom Q): Jika E, F, G, H, L, O, atau P kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->kategori) ||
                         empty($this->titik_kontrol_lokasi) ||
                         empty($this->kriteria_standar) ||
                         empty($this->kondisi_hasil_observasi) ||
                         empty($this->analisis_gap) ||
                         empty($this->referensi_bukti_foto) ||
                         empty($this->simpulan);

        if ($emptyRequired) {
            $this->status_uji = 'BELUM LENGKAP';
        } elseif ($this->exception === 'Ya') {
            $this->status_uji = 'EXCEPTION';
        } else {
            $this->status_uji = 'SESUAI';
        }
    }
}
