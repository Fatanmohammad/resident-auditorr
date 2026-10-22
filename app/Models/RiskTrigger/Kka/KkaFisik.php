<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaFisik extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_fisiks';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'jenis_objek',
        'identitas_lokasi_seri',
        'qty_sistem',
        'qty_catatan',
        'qty_fisik',
        'harga_satuan',
        'nilai_sistem',
        'nilai_fisik',
        'selisih_qty',
        'selisih_nilai',
        'kondisi_custody',
        'analisis_rekonsiliasi',
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
        'qty_sistem' => 'decimal:2',
        'qty_catatan' => 'decimal:2',
        'qty_fisik' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'nilai_sistem' => 'decimal:2',
        'nilai_fisik' => 'decimal:2',
        'selisih_qty' => 'decimal:2',
        'selisih_nilai' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        $harga = ($this->harga_satuan !== null) ? floatval($this->harga_satuan) : 0;

        // Nilai Sistem (Kolom K): Qty Sistem (G) * Harga Satuan (J)
        if ($this->qty_sistem !== null) {
            $this->nilai_sistem = floatval($this->qty_sistem) * $harga;
        } else {
            $this->nilai_sistem = null;
        }

        // Nilai Fisik (Kolom L): Qty Fisik (I) * Harga Satuan (J)
        if ($this->qty_fisik !== null) {
            $this->nilai_fisik = floatval($this->qty_fisik) * $harga;
        } else {
            $this->nilai_fisik = null;
        }

        // Selisih Qty (Kolom M): Qty Fisik (I) - Qty Sistem (G)
        if ($this->qty_fisik !== null && $this->qty_sistem !== null) {
            $this->selisih_qty = floatval($this->qty_fisik) - floatval($this->qty_sistem);
        } else {
            $this->selisih_qty = null;
        }

        // Selisih Nilai (Kolom N): Nilai Fisik (L) - Nilai Sistem (K)
        if ($this->nilai_fisik !== null && $this->nilai_sistem !== null) {
            $this->selisih_nilai = floatval($this->nilai_fisik) - floatval($this->nilai_sistem);
        } else {
            $this->selisih_nilai = null;
        }

        // Exception (Kolom V): Otomatis "Ya" jika (Selisih Qty != "" DAN Selisih Qty != 0) ATAU (Selisih Nilai != "" DAN Selisih Nilai != 0) ATAU (Qty Catatan != "" DAN Qty Catatan != Qty Fisik) ATAU Kondisi/Custody == "Tidak Sesuai"; selain itu "Tidak".
        $hasQtySelisih = ($this->selisih_qty !== null && abs(floatval($this->selisih_qty)) > 0.001);
        $hasNilaiSelisih = ($this->selisih_nilai !== null && abs(floatval($this->selisih_nilai)) > 0.001);
        $catatanNotMatch = ($this->qty_catatan !== null && $this->qty_fisik !== null && abs(floatval($this->qty_catatan) - floatval($this->qty_fisik)) > 0.001);
        $custodyNot = ($this->kondisi_custody === 'Tidak Sesuai');

        if ($hasQtySelisih || $hasNilaiSelisih || $catatanNotMatch || $custodyNot) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom U): Jika E, F, G, I, P, S, atau T kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->jenis_objek) ||
                         empty($this->identitas_lokasi_seri) ||
                         $this->qty_sistem === null ||
                         $this->qty_fisik === null ||
                         empty($this->analisis_rekonsiliasi) ||
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
