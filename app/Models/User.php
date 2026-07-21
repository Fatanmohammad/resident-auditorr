<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nip',
        'name',
        'email',
        'password',
        'cabang_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Cabang tempat User ditempatkan
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    // Relasi jika user bertindak sebagai RA yang ditugaskan di Audit Plan
    public function auditPlansAsRa(): HasMany
    {
        return $this->hasMany(AuditPlan::class, 'ra_user_id');
    }

    // Relasi jika user bertindak sebagai Auditee di Tindak Lanjut
    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'auditee_user_id');
    }
}