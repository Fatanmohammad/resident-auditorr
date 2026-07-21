<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'jenis_monitoring',
        'total_temuan',
        'total_tl_selesai',
        'total_tl_pending',
        'catatan_monitoring',
    ];

    // Relasi ke Audit Plan
    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }
}