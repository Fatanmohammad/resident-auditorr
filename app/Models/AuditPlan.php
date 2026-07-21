<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuditPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabang_id',
        'ra_user_id',
        'tahun_periode',
        'jadwal_mulai',
        'jadwal_selesai',
        'status_approval',
        'catatan_revisi',
    ];

    // Relasi ke Cabang target audit
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    // Relasi ke User RA yang ditugaskan[cite: 1]
    public function raUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ra_user_id');
    }

    // Relasi ke Kertas Kerja Audit (KKA)[cite: 1]
    public function kertasKerjaAudits(): HasMany
    {
        return $this->hasMany(KertasKerjaAudit::class);
    }

    // Relasi ke Monitoring Audit[cite: 1]
    public function monitoringAudits(): HasMany
    {
        return $this->hasMany(MonitoringAudit::class);
    }

    // Relasi ke Scoring Akhir Audit[cite: 1]
    public function scoringAudit(): HasOne
    {
        return $this->hasOne(ScoringAudit::class);
    }

    // Relasi ke Laporan Akhir Audit[cite: 1]
    public function laporanAudit(): HasOne
    {
        return $this->hasOne(LaporanAudit::class);
    }
}