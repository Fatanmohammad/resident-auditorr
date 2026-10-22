<?php

namespace App\Models\Onsite;

use Illuminate\Database\Eloquent\Model;

class OnsiteRekonKartuAtmItem extends Model
{
    protected $guarded = ['id'];
    protected $table   = 'onsite_rekon_kartu_atm_items';

    public function rekon() { return $this->belongsTo(OnsiteRekonKartuAtm::class, 'rekon_id'); }

    public function hitungRisk(): string
    {
        $score = ($this->impact ?? 1) * ($this->likelihood ?? 1);
        if ($score >= 20) return 'Critical';
        if ($score >= 12) return 'High';
        if ($score >= 6)  return 'Moderate';
        return 'Low';
    }
}
