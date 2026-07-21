<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KertasHasilAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'kka_id',
        'ringkasan_hasil',
        'status_kha',
    ];

    // Relasi ke KKA induknya
    public function kertasKerjaAudit(): BelongsTo
    {
        return $this->belongsTo(KertasKerjaAudit::class, 'kka_id');
    }
}