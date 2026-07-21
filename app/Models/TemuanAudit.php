<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemuanAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'kka_id',
        'judul_temuan',
        'kategori',
        'kondisi',
        'kriteria',
        'sebab',
        'akibat',
        'rekomendasi_ra',
        'target_selesai_tl',
    ];

    // Relasi ke KKA tempat temuan ditemukan
    public function kertasKerjaAudit(): BelongsTo
    {
        return $this->belongsTo(KertasKerjaAudit::class, 'kka_id');
    }

    // Relasi ke Tindak Lanjut oleh Auditee[cite: 1]
    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'temuan_id');
    }
}