<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaDpkLayanan extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_dpk_layanans';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'no_cif_rekening',
        'produk_layanan',
        'nama_nasabah',
        'risk_nasabah',
        'pep_high_risk',
        'dokumen_wajib',
        'dokumen_tersedia',
        'kelengkapan_persen',
        'kyc_terakhir',
        'otorisasi_sesuai',
        'profil_transaksi_sesuai',
        'analisis_kyc_risiko',
        'sebab',
        'dampak',
        'referensi_bukti',
        'simpulan',
        'status_uji',
        'exception',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dokumen_wajib' => 'integer',
        'dokumen_tersedia' => 'integer',
        'kelengkapan_persen' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        // Kelengkapan % (Kolom L): Jika J > 0 dan K terisi => Dokumen Tersedia (K) / Dokumen Wajib (J) * 100
        if (!empty($this->dokumen_wajib) && intval($this->dokumen_wajib) > 0 && $this->dokumen_tersedia !== null) {
            $this->kelengkapan_persen = round((floatval($this->dokumen_tersedia) / floatval($this->dokumen_wajib)) * 100, 2);
        } else {
            $this->kelengkapan_persen = null;
        }

        // Exception (Kolom V): Otomatis "Ya" jika (Kelengkapan % < 100%) ATAU Otorisasi Sesuai == "Tidak" ATAU Profil/Transaksi Sesuai == "Tidak" ATAU PEP/High Risk == "Ya"; selain itu "Tidak".
        $kurangLengkap = ($this->kelengkapan_persen !== null && floatval($this->kelengkapan_persen) < 100.0);
        $otorisasiNotSesuai = ($this->otorisasi_sesuai === 'Tidak');
        $profilNotSesuai = ($this->profil_transaksi_sesuai === 'Tidak');
        $pepHighRisk = ($this->pep_high_risk === 'Ya');

        if ($kurangLengkap || $otorisasiNotSesuai || $profilNotSesuai || $pepHighRisk) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom U): Jika E, F, J, K, P, S, atau T kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->no_cif_rekening) ||
                         empty($this->produk_layanan) ||
                         $this->dokumen_wajib === null ||
                         $this->dokumen_tersedia === null ||
                         empty($this->analisis_kyc_risiko) ||
                         empty($this->referensi_bukti) ||
                         empty($this->simpulan);

        if ($emptyRequired) {
            $this->status_uji = 'BELUM LENGKAP';
        } elseif ($this->exception === 'Ya') {
            $this->status_uji = 'EXCEPTION';
        } else {
            $this->status_uji = 'SESUAI';
        }
    }
}
