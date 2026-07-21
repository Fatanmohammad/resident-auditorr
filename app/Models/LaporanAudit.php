<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'nomor_laporan',
        'file_pdf_path',
        'status_approval_laporan',
    ];

    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }
}