<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\ProgramKerjaData;

class RiskTriggerKkaInduk extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_induks';

    protected $fillable = [
        'assignment_scope_id',
        'register_trigger_id',
        'visit_id',
        'trigger_id',
        'kode_unit',
        'unit_kantor',
        'area',
        'risk_level',
        'pertanyaan_audit',
        'kondisi_awal',
        'kriteria',
        'prosedur_utama',
        'ringkasan_bukti',
        'hasil_analisis',
        'trigger_terbukti',
        'perlu_perluasan',
        'alasan_perluasan',
        'kka_area',
        'exception_area',
        'simpulan_ra',
        'ra_pelaksana',
        'reviewer',
        'status_review',
    ];

    protected $casts = [
        'exception_area' => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssignmentScope::class, 'assignment_scope_id', 'id');
    }

    public function trigger()
    {
        return $this->belongsTo(RegisterTrigger::class, 'register_trigger_id', 'id');
    }

    public function programKerja()
    {
        return $this->hasOne(ProgramKerjaData::class, 'visit_id', 'visit_id');
    }
}
