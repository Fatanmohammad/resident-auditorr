<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUnitScope;

class StagingOffsite extends Model
{
    use HasUnitScope;
    
    protected $table = 'staging_offsite';
    protected $fillable = ['wp_offsite_id', 'tanggal_data', 'kode_unit', 'nama_unit', 'jenis_unit', 'ra_id', 'nama_ra', 'source_table', 'source_record_id', 'object_id', 'case_id', 'data_code', 'area_review', 'deskripsi_narasi', 'nominal', 'user_maker', 'risk_level', 'exception_awal', 'jenis_exception_awal', 'kka_sheet_tujuan', 'sampel_low', 'masuk_kka_final', 'alasan_tidak_masuk_kka', 'status_data_quality', 'catatan_validasi'];
    protected $casts = ['tanggal_data' => 'date', 'nominal' => 'decimal:2', 'exception_awal' => 'boolean', 'sampel_low' => 'boolean', 'masuk_kka_final' => 'boolean'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
    public function ra() { return $this->belongsTo(User::class, 'ra_id'); }
}
