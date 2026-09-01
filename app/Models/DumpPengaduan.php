<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpPengaduan extends Model
{
    protected $table = 'dump_pengaduan';
    protected $fillable = [
        'wp_offsite_id', 'kode_unit', 'tanggal_data', 'no_tiket',
        'no_nasabah', 'no_rekening_nasabah', 'nama_nasabah',
        'tanggal_pengaduan', 'jenis_pengaduan', 'isi_pengaduan',
        'status_pengaduan', 'tanggal_selesai', 'nominal_kerugian',
        'bukti_penyelesaian', 'catatan_tl_cabang',
        'data_source', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'status_data_quality',
    ];
    protected $casts = [
        'tanggal_data'      => 'date',
        'tanggal_pengaduan' => 'date',
        'tanggal_selesai'   => 'date',
        'nominal_kerugian'  => 'decimal:2',
    ];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
