<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KertasKerjaAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_plan_id',
        'bidang_audit',
        'sub_bidang',
        'tanggal_pemeriksaan',
        'sample_pemeriksaan',
        'status_kka',
        'catatan_kabag',
    ];

    // Relasi ke Audit Plan induk
    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class);
    }

    // Relasi ke Kertas Hasil Audit (KHA)[cite: 1]
    public function kertasHasilAudit(): HasOne
    {
        return $this->hasOne(KertasHasilAudit::class, 'kka_id');
    }

    // Relasi ke Temuan Audit yang didapat dari KKA ini[cite: 1]
    public function temuanAudits(): HasMany
    {
        return $this->hasMany(TemuanAudit::class, 'kka_id');
    }
}