<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;

class SimpulanTl extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_simpulan_tls';

    protected $fillable = [
        'assignment_scope_id',
        'register_trigger_id',
        'kode_unit',
        'unit_kantor',
        'visit_id',
        'trigger_id',
        'area',
        'risk_level_awal',
        'trigger_terbukti',
        'jumlah_exception',
        'status_klarifikasi',
        'simpulan_akhir',
        'klasifikasi_hasil',
        'perlu_perluasan',
        'scope_perluasan',
        'tindak_lanjut_awal',
        'pic_unit',
        'due_date',
        'level_eskalasi',
        'tujuan_eskalasi',
        'laporan',
        'referensi_kka_bukti',
        'ra_pelaksana',
        'reviewer',
        'status_review',
        'status_closure',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'jumlah_exception' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->calculateDerivedAttributes();
        });
    }

    public function assignment()
    {
        return $this->belongsTo(AssignmentScope::class, 'assignment_scope_id', 'id');
    }

    public function trigger()
    {
        return $this->belongsTo(RegisterTrigger::class, 'register_trigger_id', 'id');
    }

    public function kkaInduk()
    {
        return $this->hasOne(RiskTriggerKkaInduk::class, 'visit_id', 'visit_id');
    }

    public function klarifikasiList()
    {
        return $this->hasMany(KlarifikasiBukti::class, 'visit_id', 'visit_id');
    }

    /**
     * Hitung Status Klarifikasi dan Status Closure sesuai rumus Excel Sel W6 & H6
     */
    public function calculateDerivedAttributes(): void
    {
        // 1. Status Klarifikasi (Kolom H):
        // Jika tidak ada baris klarifikasi => 'TIDAK ADA'
        // Jika ada minimal 1 baris yang Status != 'Closed' => 'MASIH OPEN'
        // Jika ada baris dan seluruhnya 'Closed' => 'SELESAI'
        if (!empty($this->visit_id)) {
            $totalKlarifikasi = KlarifikasiBukti::where('visit_id', $this->visit_id)->count();
            if ($totalKlarifikasi === 0) {
                $this->status_klarifikasi = 'TIDAK ADA';
            } else {
                $openCount = KlarifikasiBukti::where('visit_id', $this->visit_id)
                    ->where('status_klarifikasi', '!=', 'Closed')
                    ->count();
                $this->status_klarifikasi = ($openCount > 0) ? 'MASIH OPEN' : 'SELESAI';
            }
        }

        // 2. Status Closure (Kolom W):
        // Rumus Excel Sel W6:
        // =IF(OR(I6="",J6="",V6<>"FINAL"),"BELUM DITUTUP",IF(AND(OR(J6="Temuan",J6="Perlu Perluasan",J6="Eskalasi"),OR(M6="",N6="",O6="")),"TL BELUM LENGKAP",IF(H6="MASIH OPEN","KLARIFIKASI BELUM SELESAI","CLOSED/DITERUSKAN")))
        $simpulanKosong = empty($this->simpulan_akhir);
        $klasifikasiKosong = empty($this->klasifikasi_hasil);
        $notFinal = ($this->status_review !== 'FINAL');

        if ($simpulanKosong || $klasifikasiKosong || $notFinal) {
            $this->status_closure = 'BELUM DITUTUP';
        } elseif (in_array($this->klasifikasi_hasil, ['Temuan', 'Perlu Perluasan', 'Eskalasi']) && 
                 (empty($this->tindak_lanjut_awal) || empty($this->pic_unit) || empty($this->due_date))) {
            $this->status_closure = 'TL BELUM LENGKAP';
        } elseif ($this->status_klarifikasi === 'MASIH OPEN') {
            $this->status_closure = 'KLARIFIKASI BELUM SELESAI';
        } else {
            $this->status_closure = 'CLOSED/DITERUSKAN';
        }
    }

    /**
     * Scope akses per cabang untuk akun RA
     */
    public function scopeByBranch($query, ?\App\Models\User $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query;
        }

        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];

            $allowedUnits = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
            $allowedCabangCodes = \App\Models\Cabang::whereIn('id', $allowedCabangIds)->pluck('kode_cabang')->toArray();

            $targetCodes = array_unique(array_filter(array_merge($allowedUnits, $allowedCabangCodes)));
            $expanded = [];
            foreach ($targetCodes as $code) {
                $clean = str_replace('BS-', '', (string)$code);
                $expanded[] = $code;
                $expanded[] = $clean;
                $expanded[] = 'BS-' . $clean;
                if (is_numeric($clean)) {
                    $expanded[] = str_pad($clean, 3, '0', STR_PAD_LEFT);
                }
            }

            return $query->whereIn('kode_unit', array_values(array_unique(array_filter($expanded))));
        }

        return $query;
    }
}
