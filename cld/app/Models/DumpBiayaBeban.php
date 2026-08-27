<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpBiayaBeban extends Model
{
    protected $table = 'dump_biaya_beban';
    protected $fillable = ['wp_offsite_id', 'kode_unit', 'tanggal_data', 'no_rekening', 'no_arsip', 'kode_transaksi', 'keterangan_transaksi', 'd_k', 'nominal', 'user_input', 'time_stamp', 'auto_system_flag', 'data_source', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'status_data_quality'];
    protected $casts = ['tanggal_data' => 'date', 'nominal' => 'decimal:2', 'time_stamp' => 'datetime', 'auto_system_flag' => 'boolean'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
