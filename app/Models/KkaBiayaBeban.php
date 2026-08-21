<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KkaBiayaBeban extends Model
{
    use SoftDeletes;
    protected $table = 'kka_biaya_beban';
    protected $primaryKey = 'kka_id';
    protected $fillable = ['wp_offsite_id', 'staging_id', 'kka_number', 'area_review', 'tanggal_data', 'kode_unit', 'nama_unit', 'ra_id', 'nama_ra', 'source_sheet', 'object_id', 'case_id', 'data_code', 'deskripsi_narasi', 'nominal', 'user_maker', 'risk_awal', 'exception_awal', 'jenis_exception_awal', 'sampel_low', 'catatan_rule', 'tujuan_uji', 'kriteria', 'prosedur_uji', 'bukti_referensi', 'hasil_uji', 'jenis_exception_ra', 'dampak', 'kemungkinan', 'critical_trigger', 'klarifikasi_awal', 'klarifikasi_unit', 'status_klarifikasi', 'perluasan_sampel', 'perlu_onsite', 'keputusan_onsite', 'keputusan_eskalasi', 'simpulan_ra', 'tanggal_ditemukan', 'status_review', 'skor_risiko', 'kategori_risiko_final', 'eskalasi_awal', 'rekomendasi_eskalasi', 'catatan_reviewer', 'reviewer_id'];
    protected $casts = ['tanggal_data' => 'date', 'nominal' => 'decimal:2', 'sampel_low' => 'boolean', 'critical_trigger' => 'boolean', 'perluasan_sampel' => 'boolean', 'perlu_onsite' => 'boolean', 'eskalasi_awal' => 'boolean', 'rekomendasi_eskalasi' => 'boolean', 'tanggal_ditemukan' => 'date'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
    public function staging() { return $this->belongsTo(StagingOffsite::class); }
    public function ra() { return $this->belongsTo(User::class, 'ra_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
}
