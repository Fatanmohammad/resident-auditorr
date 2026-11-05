<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;

class RiskTriggerCovenant extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_covenants';

    protected $guarded = ['id'];

    public function kkaInduk()
    {
        return $this->belongsTo(RiskTriggerKkaInduk::class, 'risk_trigger_kka_induk_id');
    }
}
