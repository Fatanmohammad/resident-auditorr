<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
