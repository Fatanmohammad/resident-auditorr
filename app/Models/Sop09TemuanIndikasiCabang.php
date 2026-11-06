<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop09TemuanIndikasiCabang extends Model
{
    use HasFactory;

    protected $table = 'sop09_temuan_indikasi_cabang';

    protected $guarded = ['id'];

    /**
     * Hitung total temuan per baris cabang
     */
    public function getTotalTemuanAttribute(): int
    {
        return (int) (
            $this->reversal_teller +
            $this->selisih_kas_teller +
            $this->selisih_kas_khasanah +
            $this->kas_kecil_melebihi_limit +
            $this->kas_kecil_belum_selesai +
            $this->dual_control_kunci_dilanggar +
            $this->trx_rekening_pasif +
            $this->trx_rekening_titipan +
            $this->trx_tidak_wajar_pegawai +
            $this->trx_luar_jam_operasional +
            $this->setoran_non_tunai_pegawai +
            $this->sharing_user_id +
            $this->skimming_atm +
            $this->selisih_kas_crm_atm +
            $this->restrukturisasi_berulang +
            $this->pencairan_tanpa_dokumen +
            $this->cuti_wajib_belum_diambil +
            $this->lonjakan_pengaduan_sla
        );
    }
}
