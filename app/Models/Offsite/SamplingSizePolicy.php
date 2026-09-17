<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Model;

class SamplingSizePolicy extends Model
{
    protected $fillable = [
        'min_populasi', 'max_populasi', 'min_sampel', 'sample_all', 'keterangan',
    ];

    protected $casts = [
        'sample_all' => 'boolean',
    ];

    /**
     * Cari aturan ukuran sampel yang cocok untuk jumlah populasi tertentu.
     */
    public static function untukPopulasi(int $jumlahPopulasi): ?self
    {
        return self::where('min_populasi', '<=', $jumlahPopulasi)
            ->where(function ($q) use ($jumlahPopulasi) {
                $q->whereNull('max_populasi')
                  ->orWhere('max_populasi', '>=', $jumlahPopulasi);
            })
            ->first();
    }
}