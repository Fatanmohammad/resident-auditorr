<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaAtm extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_atms';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'objek_jenis_kartu_atm',
        'no_referensi',
        'qty_buku',
        'qty_fisik',
        'qty_pin_mailer',
        'harga_satuan',
        'nilai_buku',
        'nilai_fisik',
        'selisih_qty',
        'selisih_nilai',
        'custody_sesuai',
        'akses_log_sesuai',
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
        'qty_buku' => 'decimal:2',
        'qty_fisik' => 'decimal:2',
        'qty_pin_mailer' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
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

        // Nilai Buku (Kolom K): Qty Buku (G) * Harga Satuan (J)
        if ($this->qty_buku !== null) {
            $this->nilai_buku = floatval($this->qty_buku) * $harga;
        } else {
            $this->nilai_buku = null;
        }

        // Nilai Fisik (Kolom L): Qty Fisik (H) * Harga Satuan (J)
        if ($this->qty_fisik !== null) {
            $this->nilai_fisik = floatval($this->qty_fisik) * $harga;
        } else {
            $this->nilai_fisik = null;
        }

        // Selisih Qty (Kolom M): Qty Fisik (H) - Qty Buku (G)
        if ($this->qty_fisik !== null && $this->qty_buku !== null) {
            $this->selisih_qty = floatval($this->qty_fisik) - floatval($this->qty_buku);
        } else {
            $this->selisih_qty = null;
        }

        // Selisih Nilai (Kolom N): Nilai Fisik (L) - Nilai Buku (K)
        if ($this->nilai_fisik !== null && $this->nilai_buku !== null) {
            $this->selisih_nilai = floatval($this->nilai_fisik) - floatval($this->nilai_buku);
        } else {
            $this->selisih_nilai = null;
        }

        // Exception (Kolom W): Otomatis "Ya" jika (Selisih Qty != "" DAN Selisih Qty != 0) ATAU (Selisih Nilai != "" DAN Selisih Nilai != 0) ATAU Custody Sesuai == "Tidak" ATAU Akses/Log Sesuai == "Tidak" ATAU (Qty PIN Mailer != "" DAN Qty Fisik != Qty PIN Mailer); selain itu "Tidak".
        $hasQtySelisih = ($this->selisih_qty !== null && abs(floatval($this->selisih_qty)) > 0.001);
        $hasNilaiSelisih = ($this->selisih_nilai !== null && abs(floatval($this->selisih_nilai)) > 0.001);
        $custodyNot = ($this->custody_sesuai === 'Tidak');
        $aksesNot = ($this->akses_log_sesuai === 'Tidak');
        $pinMailerNotMatch = ($this->qty_pin_mailer !== null && $this->qty_fisik !== null && abs(floatval($this->qty_fisik) - floatval($this->qty_pin_mailer)) > 0.001);

        if ($hasQtySelisih || $hasNilaiSelisih || $custodyNot || $aksesNot || $pinMailerNotMatch) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom V): Jika E, G, H, Q, T, atau U kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->objek_jenis_kartu_atm) ||
                         $this->qty_buku === null ||
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
