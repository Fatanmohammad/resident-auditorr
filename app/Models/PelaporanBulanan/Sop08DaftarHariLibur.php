<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08DaftarHariLibur extends Model
{
    use HasFactory;

    protected $table = 'sop08_daftar_hari_liburs';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'tahun',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tahun' => 'integer',
    ];
}
