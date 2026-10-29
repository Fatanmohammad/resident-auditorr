<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08NarasiLaporan extends Model
{
    use HasFactory;

    protected $table = 'sop08_narasi_laporans';

    protected $fillable = [
        'report_id',
        'ringkasan_eksekutif',
        'hasil_utama',
        'analisis_risiko_tren',
        'kendala',
        'rencana_bulan_berikut',
        'arahan_diperlukan',
    ];

    public function kalender()
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }
}
