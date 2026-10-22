<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaKredit extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_kredits';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'no_kredit_debitur',
        'nama_debitur',
        'produk',
        'plafond',
        'outstanding',
        'nilai_agunan',
        'coverage_agunan',
        'nilai_asuransi',
        'coverage_asuransi',
        'persetujuan_sesuai',
        'pencairan_sesuai',
        'dokumen_lengkap',
        'covenant_deviasi',
        'analisis_kredit',
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
        'plafond' => 'decimal:2',
        'outstanding' => 'decimal:2',
        'nilai_agunan' => 'decimal:2',
        'coverage_agunan' => 'decimal:4',
        'nilai_asuransi' => 'decimal:2',
        'coverage_asuransi' => 'decimal:4',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        // Coverage Agunan (Kolom K): Jika Outstanding (I) > 0 dan Nilai Agunan (J) terisi => J / I
        if ($this->outstanding !== null && floatval($this->outstanding) > 0 && $this->nilai_agunan !== null) {
            $this->coverage_agunan = round(floatval($this->nilai_agunan) / floatval($this->outstanding), 4);
        } else {
            $this->coverage_agunan = null;
        }

        // Coverage Asuransi (Kolom M): Jika Outstanding (I) > 0 dan Nilai Asuransi (L) terisi => L / I
        if ($this->outstanding !== null && floatval($this->outstanding) > 0 && $this->nilai_asuransi !== null) {
            $this->coverage_asuransi = round(floatval($this->nilai_asuransi) / floatval($this->outstanding), 4);
        } else {
            $this->coverage_asuransi = null;
        }

        // Exception (Kolom X): Otomatis "Ya" jika Persetujuan Sesuai == "Tidak" ATAU Pencairan Sesuai == "Tidak" ATAU Dokumen Lengkap == "Tidak" ATAU (Coverage Agunan != "" DAN Coverage Agunan < 1) ATAU (Coverage Asuransi != "" DAN Coverage Asuransi < 1); selain itu "Tidak".
        $persetujuanNot = ($this->persetujuan_sesuai === 'Tidak');
        $pencairanNot = ($this->pencairan_sesuai === 'Tidak');
        $dokumenNot = ($this->dokumen_lengkap === 'Tidak');
        $underAgunan = ($this->coverage_agunan !== null && floatval($this->coverage_agunan) < 1.0);
        $underAsuransi = ($this->coverage_asuransi !== null && floatval($this->coverage_asuransi) < 1.0);

        if ($persetujuanNot || $pencairanNot || $dokumenNot || $underAgunan || $underAsuransi) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom W): Jika E, H, I, R, U, atau V kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->no_kredit_debitur) ||
                         $this->plafond === null ||
                         $this->outstanding === null ||
                         empty($this->analisis_kredit) ||
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
