<?php

namespace App\Models\Offsite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_data', 
        'periode',
        'kode_unit', 
        'nama_unit', 
        'source_sheet', 
        'kategori', 
        'risk_awal',
        'rincian', 
        'nominal_terkait'
    ];
}
