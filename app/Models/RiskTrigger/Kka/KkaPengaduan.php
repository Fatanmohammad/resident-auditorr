<?php

namespace App\Models\RiskTrigger\Kka;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KkaPengaduan extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_kka_pengaduans';

    protected $fillable = [
        'assignment_scope_id',
        'kode_unit',
        'visit_id',
        'trigger_id',
        'unit_kantor',
        'area',
        'complaint_id',
        'tanggal_terima',
        'kategori',
        'uraian_pengaduan',
        'sla_hari',
        'tanggal_selesai',
        'aging_hari',
        'status_penyelesaian',
        'bukti_penyelesaian',
        'berulang',
        'akar_masalah',
        'dampak_nasabah',
        'analisis_sla',
        'referensi_bukti',
        'simpulan',
        'status_uji',
        'exception',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_selesai' => 'date',
        'sla_hari' => 'integer',
        'aging_hari' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerived();
        });
    }

    public function calculateDerived()
    {
        // Aging Hari (Kolom K): Jika Tanggal Terima (F) terisi:
        // jika Tanggal Selesai (J) kosong maka Hari_Ini - Tanggal_Terima
        // jika Tanggal Selesai (J) terisi maka Tanggal_Selesai - Tanggal_Terima (dalam jumlah hari)
        if (!empty($this->tanggal_terima)) {
            $terima = Carbon::parse($this->tanggal_terima)->startOfDay();
            if (!empty($this->tanggal_selesai)) {
                $selesai = Carbon::parse($this->tanggal_selesai)->startOfDay();
                $this->aging_hari = (int) $terima->diffInDays($selesai, false);
            } else {
                $today = Carbon::today()->startOfDay();
                $this->aging_hari = (int) $terima->diffInDays($today, false);
            }
            if ($this->aging_hari < 0) {
                $this->aging_hari = 0;
            }
        } else {
            $this->aging_hari = null;
        }

        // Exception (Kolom U): Otomatis "Ya" jika (Aging Hari != "" DAN SLA Hari != "" DAN Aging Hari > SLA Hari) ATAU Status Penyelesaian != "Selesai" ATAU Bukti Penyelesaian == "Tidak" ATAU Berulang? == "Ya"; selain itu "Tidak".
        $overSla = ($this->aging_hari !== null && $this->sla_hari !== null && intval($this->aging_hari) > intval($this->sla_hari));
        $statusNotSelesai = (!empty($this->status_penyelesaian) && $this->status_penyelesaian !== 'Selesai');
        $buktiNot = ($this->bukti_penyelesaian === 'Tidak');
        $berulangYa = ($this->berulang === 'Ya');

        if ($overSla || $statusNotSelesai || $buktiNot || $berulangYa) {
            $this->exception = 'Ya';
        } else {
            $this->exception = 'Tidak';
        }

        // Status Uji (Kolom T): Jika E, F, H, I, Q, R, atau S kosong => "BELUM LENGKAP"; jika Exception == "Ya" => "EXCEPTION"; selain itu => "SESUAI".
        $emptyRequired = empty($this->complaint_id) ||
                         empty($this->tanggal_terima) ||
                         empty($this->uraian_pengaduan) ||
                         $this->sla_hari === null ||
                         empty($this->analisis_sla) ||
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
