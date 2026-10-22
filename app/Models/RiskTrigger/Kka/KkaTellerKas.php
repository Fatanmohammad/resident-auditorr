<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkaTellerKas extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_teller_kas';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'no_referensi',
        'tanggal_transaksi',
        'redaksi',
        'd_k',
        'nominal',
        'user_id_transaksi',
        'jenis_transaksi',
        'nama_transaksi',
        'nominal_pasangan',
        'bukti_kas_voucher',
        'selisih',
        'reversal_match',
        'otorisasi_sesuai',
        'analisis_pola',
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
        'tanggal_transaksi' => 'date',
        'nominal' => 'decimal:2',
        'nominal_pasangan' => 'decimal:2',
        'bukti_kas_voucher' => 'decimal:2',
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
        // Rumus Selisih (Kolom O): Jika M dan N terisi => Bukti Kas/Voucher (N) - Nominal Pasangan (M)
        if ($this->bukti_kas_voucher !== null && $this->nominal_pasangan !== null) {
            $this->selisih = floatval($this->bukti_kas_voucher) - floatval($this->nominal_pasangan);
        } else {
            $this->selisih = null;
        }

        // Rumus Exception (Kolom X): Otomatis "Ya" jika (Selisih != "" DAN Selisih != 0) ATAU Reversal Match == "Tidak" ATAU Otorisasi Sesuai == "Tidak"; selain itu "Tidak".
        $hasSelisihError = ($this->selisih !== null && abs(floatval($this->selisih)) > 0.001);
        $reversalNotMatch = ($this->reversal_match === 'Tidak');
        $otorisasiNotSesuai = ($this->otorisasi_sesuai === 'Tidak');

        if ($hasSelisihError || $reversalNotMatch || $otorisasiNotSesuai) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Rumus Status Uji (Kolom W): Jika E, F, G, I, R, U, atau V kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->no_referensi) ||
                         empty($this->tanggal_transaksi) ||
                         empty($this->redaksi) ||
                         $this->nominal === null ||
                         empty($this->analisis_pola) ||
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
