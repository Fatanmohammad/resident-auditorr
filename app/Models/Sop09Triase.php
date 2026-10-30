<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09Triase extends Model
{
    use HasFactory;

    protected $table = 'sop09_triase_awals';
    protected $guarded = ['id'];

    /**
     * Relasi ke Register Indikasi Fraud
     */
    public function registerIndikasi()
    {
        return $this->belongsTo(Sop09RegisterIndikasi::class, 'indikasi_id', 'indikasi_id');
    }
}