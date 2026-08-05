<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Cabang extends Model
{
    protected $fillable = ['nama_cabang', 'kode_cabang', 'tipe', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Cabang::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Cabang::class, 'parent_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'cabang_id');
    }

    /**
     * Ambil semua ID cabang ini beserta seluruh anak cabangnya (rekursif).
     * Dipakai untuk membatasi akses RA: RA hanya boleh menginput unit milik
     * cabangnya sendiri beserta semua anak cabangnya.
     */
    public static function idsBesertaKeturunannya(int $cabangId): Collection
    {
        $ids = collect([$cabangId]);
        $toProcess = collect([$cabangId]);

        while ($toProcess->isNotEmpty()) {
            $children = static::whereIn('parent_id', $toProcess)->pluck('id');
            $ids = $ids->merge($children);
            $toProcess = $children;
        }

        return $ids->unique()->values();
    }
}
