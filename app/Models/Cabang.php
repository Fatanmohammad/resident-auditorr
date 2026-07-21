<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cabang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_cabang',
        'kode_cabang',
        'tipe',
        'parent_id',
    ];

    // Relasi ke Anak Cabang (Hirarki Struktur Cabang)
    public function anakCabang(): HasMany
    {
        return $this->hasMany(Cabang::class, 'parent_id');
    }

    // Relasi ke Induk Cabang / KCU
    public function parentCabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'parent_id');
    }

    // Relasi ke User / Pegawai yang ada di cabang ini
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relasi ke Audit Plan cabang ini
    public function auditPlans(): HasMany
    {
        return $this->hasMany(AuditPlan::class);
    }
}