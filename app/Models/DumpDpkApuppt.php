<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpDpkApuppt extends Model
{
    protected $table = 'dump_dpk_apuppt';
    protected $fillable = ['wp_offsite_id', 'kode_unit', 'tanggal_data', 'produk', 'cif_nasabah', 'no_rekening', 'nama_nasabah', 'gol_pemilik', 'tanggal_buka', 'jatuh_tempo', 'saldo_akhir', 'status_rekening', 'data_source', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'status_data_quality'];
    protected $casts = ['tanggal_data' => 'date', 'tanggal_buka' => 'date', 'jatuh_tempo' => 'date', 'saldo_akhir' => 'decimal:2'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
