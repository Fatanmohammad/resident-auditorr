<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'skor_parameter_kat',
        'skor_tindak_lanjut',
        'skor_akhir',
        'peringkat_ra',
    ];

    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }
}