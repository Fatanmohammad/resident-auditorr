<?php

namespace App\Models\PelaporanBulanan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop08KalenderLaporan extends Model
{
    use HasFactory;

    protected $table = 'sop08_kalender_laporans';

    protected $fillable = [
        'report_id',
        'periode',
        'kode_unit',
        'nama_unit',
        'resident_auditor',
        'unit_ra',
        'wajib_lapor',
        'jatuh_tempo',
        'tanggal_submit',
        'hari_terlambat',
        'ketepatan',
        'kelengkapan',
        'status_review',
        'status_final',
        'quality_score',
        'masuk_konsolidasi',
        'catatan',
    ];

    protected $casts = [
        'periode' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_submit' => 'date',
        'hari_terlambat' => 'integer',
        'kelengkapan' => 'float',
        'quality_score' => 'float',
    ];

    public function checklist()
    {
        return $this->hasOne(Sop08ChecklistLaporan::class, 'report_id', 'report_id');
    }

    public function narasi()
    {
        return $this->hasOne(Sop08NarasiLaporan::class, 'report_id', 'report_id');
    }

    public function rekapKegiatan()
    {
        return $this->hasMany(Sop08RekapKegiatan::class, 'report_id', 'report_id');
    }

    public function rekapIsuTl()
    {
        return $this->hasMany(Sop08RekapIsuTl::class, 'report_id', 'report_id');
    }

    public function masterUnit()
    {
        return $this->belongsTo(Sop08MasterUnit::class, 'kode_unit', 'kode_unit');
    }

    public function review()
    {
        return $this->hasOne(Sop08ReviewLaporan::class, 'report_id', 'report_id');
    }

    public function koreksi()
    {
        return $this->hasOne(Sop08KoreksiApproval::class, 'report_id', 'report_id');
    }

    public function isuSignifikans()
    {
        return $this->hasMany(Sop08IsuSignifikan::class, 'report_id', 'report_id');
    }

    public function distribusi()
    {
        return $this->hasOne(Sop08DistribusiQuality::class, 'report_id', 'report_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Sop08Feedback::class, 'report_id', 'report_id');
    }
}
