<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpPengaduan extends Model
{
    protected $table = 'dump_pengaduan';
    protected $fillable = ['wp_offsite_id', 'kode_unit', 'tanggal_data', 'no_tiket', 'tanggal_pengaduan', 'jenis_pengaduan', 'isi_pengaduan', 'status_pengaduan', 'data_source', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'status_data_quality'];
    protected $casts = ['tanggal_data' => 'date', 'tanggal_pengaduan' => 'date'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
