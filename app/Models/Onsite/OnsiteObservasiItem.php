<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteObservasiItem extends Model
{
    protected $table = 'onsite_observasi_items';

    protected $fillable = [
        'onsite_observasi_id', 'procedure_id', 'area', 'objek', 'kriteria',
        'tipe', 'atm_slot', 'urut',
        'kondisi_aktual', 'hasil_observasi', 'uraian_ketidaksesuaian',
        'penyebab', 'dampak', 'impact', 'likelihood',
        'referensi_bukti', 'risk_score', 'risk_level',
        'status_review', 'catatan_reviewer',
    ];

    public function observasi()
    {
        return $this->belongsTo(OnsiteObservasi::class, 'onsite_observasi_id');
    }

    // Hitung risk score & level otomatis
    public function hitungRisk(): void
    {
        if ($this->impact && $this->likelihood) {
            $score = $this->impact * $this->likelihood;
            $this->risk_score = $score;
            $this->risk_level = match(true) {
                $score >= 20 => 'Critical',
                $score >= 12 => 'High',
                $score >= 6  => 'Moderate',
                default      => 'Low',
            };
        }
    }
}
