<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterHarian extends Model
{
    protected $table = 'register_harian';
    protected $fillable = ['wp_offsite_id', 'tanggal_data', 'target_review_h1', 'kode_unit', 'nama_unit', 'ra_id', 'nama_ra', 'area_review', 'populasi_eligible', 'sampel_low', 'kka_final', 'exception', 'perlu_klarifikasi', 'perlu_eskalasi', 'risiko_tertinggi', 'hasil_awal', 'kka_sheet_tujuan', 'tanggal_aktual_review', 'status_review', 'catatan_ra'];
    protected $casts = ['tanggal_data' => 'date', 'target_review_h1' => 'date', 'tanggal_aktual_review' => 'date', 'populasi_eligible' => 'integer', 'sampel_low' => 'integer', 'kka_final' => 'integer', 'exception' => 'integer', 'perlu_klarifikasi' => 'integer', 'perlu_eskalasi' => 'integer'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
    public function ra() { return $this->belongsTo(User::class, 'ra_id'); }
}
