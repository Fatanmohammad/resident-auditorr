<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaBiayaBeban extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_biaya_bebans';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'gl_rekening',
        'no_voucher',
        'tanggal',
        'redaksi',
        'nominal_buku',
        'nominal_invoice',
        'nominal_disetujui',
        'selisih',
        'anggaran_tersedia',
        'otorisasi_sesuai',
        'pajak_sesuai',
        'barang_jasa_diterima',
        'transaksi_berulang',
        'analisis_kewajaran',
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
        'tanggal' => 'date',
        'nominal_buku' => 'decimal:2',
        'nominal_invoice' => 'decimal:2',
        'nominal_disetujui' => 'decimal:2',
        'selisih' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        // Selisih (Kolom L): Jika I dan K terisi => Nominal Buku (I) - Nominal Disetujui (K)
        if ($this->nominal_buku !== null && $this->nominal_disetujui !== null) {
            $this->selisih = floatval($this->nominal_buku) - floatval($this->nominal_disetujui);
        } else {
            $this->selisih = null;
        }

        // Exception (Kolom X): Otomatis "Ya" jika (Selisih != "" DAN Selisih != 0) ATAU Anggaran Tersedia == "Tidak" ATAU Otorisasi Sesuai == "Tidak" ATAU Pajak Sesuai == "Tidak" ATAU Barang/Jasa Diterima == "Tidak" ATAU Transaksi Berulang == "Ya"; selain itu "Tidak".
        $hasSelisihError = ($this->selisih !== null && abs(floatval($this->selisih)) > 0.001);
        $anggaranNot = ($this->anggaran_tersedia === 'Tidak');
        $otorisasiNot = ($this->otorisasi_sesuai === 'Tidak');
        $pajakNot = ($this->pajak_sesuai === 'Tidak');
        $barangNot = ($this->barang_jasa_diterima === 'Tidak');
        $berulangYa = ($this->transaksi_berulang === 'Ya');

        if ($hasSelisihError || $anggaranNot || $otorisasiNot || $pajakNot || $barangNot || $berulangYa) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom W): Jika E, F, G, I, R, U, atau V kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->gl_rekening) ||
                         empty($this->no_voucher) ||
                         empty($this->tanggal) ||
                         $this->nominal_buku === null ||
                         empty($this->analisis_kewajaran) ||
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
