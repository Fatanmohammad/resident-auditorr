<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpKredit extends Model
{
    protected $table = 'dump_kredit';
    protected $fillable = ['wp_offsite_id', 'kode_unit', 'tanggal_data', 'cif_nasabah', 'no_rekening_kredit', 'no_nasabah', 'no_akad', 'nama_debitur', 'produk_kredit', 'jenis_kredit', 'tanggal_realisasi', 'tanggal_jatuh_tempo', 'jangka_waktu_bulan', 'plafon', 'baki_debet', 'kolektibilitas', 'tunggakan_pokok', 'tunggakan_bunga', 'ao_pengelola', 'total_agunan', 'data_source', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'status_data_quality'];
    protected $casts = ['tanggal_data' => 'date', 'tanggal_realisasi' => 'date', 'tanggal_jatuh_tempo' => 'date', 'jangka_waktu_bulan' => 'integer', 'plafon' => 'decimal:2', 'baki_debet' => 'decimal:2', 'tunggakan_pokok' => 'decimal:2', 'tunggakan_bunga' => 'decimal:2', 'total_agunan' => 'decimal:2'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
