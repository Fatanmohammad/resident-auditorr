<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyRegisterFinding extends Model
{
    public const STATUS_BELUM_DIUJI = 'BELUM_DIUJI';
    public const STATUS_MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';
    public const STATUS_PERLU_REVISI = 'PERLU_REVISI';
    public const STATUS_SELESAI = 'SELESAI';

    protected $guarded = ['id'];

    protected $casts = [
        'reviewed_at'        => 'datetime',
        'submitted_at'       => 'datetime',
        'selected_sub_areas' => 'array',
    ];

    public function checklistAnswers(): HasMany
    {
        return $this->hasMany(DailyRegisterChecklistAnswer::class, 'daily_register_finding_id');
    }

    public function getReviewStatusAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        if (($this->status_review ?? '') === 'Approved') {
            return self::STATUS_SELESAI;
        }
        if (($this->status_review ?? '') === 'Revisi') {
            return self::STATUS_PERLU_REVISI;
        }
        if (!empty($this->submitted_at)) {
            return self::STATUS_MENUNGGU_KONFIRMASI;
        }

        return self::STATUS_BELUM_DIUJI;
    }

    public function getReviewerNoteAttribute($value): ?string
    {
        return $value ?: $this->catatan_reviewer;
    }

    public function getKetTxAttribute(): ?string
    {
        foreach (['ket_tx', 'deskripsi', 'rincian', 'jenis_exception_awal'] as $col) {
            $val = $this->attributes[$col] ?? null;
            if ($val !== null && trim((string)$val) !== '') {
                return $val;
            }
        }
        return null;
    }

    public function isCompleted(): bool
    {
        return $this->review_status === self::STATUS_SELESAI || ($this->status_review ?? '') === 'Approved';
    }
}
