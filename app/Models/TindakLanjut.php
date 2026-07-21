<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $fillable = [
        'temuan_id',
        'auditee_user_id',
        'respon_auditee',
        'bukti_lampiran_path',
        'status_tl',
        'catatan_verifikasi_ra',
    ];

    // Relasi ke Temuan Audit induk
    public function temuanAudit(): BelongsTo
    {
        return $this->belongsTo(TemuanAudit::class, 'temuan_id');
    }

    // Relasi ke User Auditee yang merespon[cite: 1]
    public function auditeeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditee_user_id');
    }
}