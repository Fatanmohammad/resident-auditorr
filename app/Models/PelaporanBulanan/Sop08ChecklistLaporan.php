<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08ChecklistLaporan extends Model
{
    use HasFactory;

    protected $table = 'sop08_checklist_laporans';

    protected $fillable = [
        'report_id',
        'komp_identitas_cutoff',
        'komp_ringkasan_eksekutif',
        'komp_rekap_kegiatan',
        'komp_coverage_sampel',
        'komp_exception_temuan',
        'komp_tindak_lanjut',
        'komp_fraud_insidentil',
        'komp_kendala',
        'komp_rencana_berikut',
        'komp_referensi_wp',
        'komp_lampiran_bukti',
        'komp_konsistensi_angka',
        'persen_lengkap',
        'jumlah_gap',
        'status_kesiapan',
        'catatan',
    ];

    protected $casts = [
        'persen_lengkap' => 'float',
        'jumlah_gap' => 'integer',
    ];

    public function kalender()
    {
        return $this->belongsTo(Sop08KalenderLaporan::class, 'report_id', 'report_id');
    }

    public static function komponenList(): array
    {
        return [
            'komp_identitas_cutoff' => 'Identitas & Cut-off',
            'komp_ringkasan_eksekutif' => 'Ringkasan Eksekutif',
            'komp_rekap_kegiatan' => 'Rekap Kegiatan',
            'komp_coverage_sampel' => 'Coverage & Sampel',
            'komp_exception_temuan' => 'Exception/Temuan',
            'komp_tindak_lanjut' => 'Tindak Lanjut',
            'komp_fraud_insidentil' => 'Fraud/Insidentil',
            'komp_kendala' => 'Kendala',
            'komp_rencana_berikut' => 'Rencana Bulan Berikut',
            'komp_referensi_wp' => 'Referensi WP/KKA',
            'komp_lampiran_bukti' => 'Lampiran Bukti',
            'komp_konsistensi_angka' => 'Konsistensi Angka/Status',
        ];
    }
}
