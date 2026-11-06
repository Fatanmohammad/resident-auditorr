<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09FraudTriageChecklist extends Model
{
    use HasFactory;

    protected $table = 'sop09_fraud_triage_checklists';

    protected $guarded = ['id'];

    protected $casts = [
        'checklist_answers'    => 'array',
        'tanggal_pemeriksaan' => 'date',
        'ada_critical_trigger' => 'boolean',
        'total_item'          => 'integer',
        'dijawab_ya'          => 'integer',
        'dijawab_tidak'       => 'integer',
        'belum_dijawab'       => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

