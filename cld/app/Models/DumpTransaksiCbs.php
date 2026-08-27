<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DumpTransaksiCbs extends Model
{
    protected $table = 'dump_transaksi_cbs';
    protected $fillable = ['wp_offsite_id', 'kode_unit', 'tanggal_data', 'no_referensi', 'kode_transaksi', 'nama_transaksi', 'user_maker', 'nama_user', 'nominal', 'd_k', 'deskripsi_narasi', 'data_source', 'flag_reversal', 'flag_koreksi_override', 'flag_selisih_kas', 'flag_tunai_besar', 'flag_biaya_jurnal', 'flag_internal_account', 'flag_pencairan_kredit', 'flag_rutin_whitelist', 'risk_level', 'area_review', 'kka_sheet_tujuan', 'case_id', 'jumlah_flag_risiko', 'status_data_quality', 'catatan_rule'];
    protected $casts = ['tanggal_data' => 'date', 'nominal' => 'decimal:2', 'flag_reversal' => 'boolean', 'flag_koreksi_override' => 'boolean', 'flag_selisih_kas' => 'boolean', 'flag_tunai_besar' => 'boolean', 'flag_biaya_jurnal' => 'boolean', 'flag_internal_account' => 'boolean', 'flag_pencairan_kredit' => 'boolean', 'flag_rutin_whitelist' => 'boolean'];
    public function wpOffsite() { return $this->belongsTo(WpOffsite::class); }
}
