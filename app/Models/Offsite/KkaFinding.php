<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class KkaFinding extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi secara massal, kecuali 'id'
    protected $guarded = ['id'];

    /**
     * Accessor untuk memastikan user_maker tidak kosong atau bernilai strip (-)
     */
    protected function userMaker(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (empty($value) || $value === '-') ? 'Sistem / Tidak Tercatat' : $value
        );
    }
}