<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use Carbon\Carbon;

class AssignmentScope extends Model
{
    use HasFactory;

    protected $table = 'assignment_scopes';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_visit' => 'date',
        'mulai_aktual' => 'date',
        'selesai_aktual' => 'date',
    ];

    public function registerTrigger()
    {
        return $this->belongsTo(RegisterTrigger::class, 'register_trigger_id');
    }

    public function programKerjaData()
    {
        return $this->hasOne(ProgramKerjaData::class, 'assignment_scope_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helper to generate sequential Visit ID: RTV-{KodeUnit3Digit}-{YYYYMMDD}-{001}
     */
    public static function generateNextVisitId($kodeUnit, $tanggalVisit = null): string
    {
        $cleanUnit = str_replace('BS-', '', (string)$kodeUnit);
        if (is_numeric($cleanUnit)) {
            $unitStr = str_pad($cleanUnit, 3, '0', STR_PAD_LEFT);
        } else {
            $unitStr = substr($cleanUnit, 0, 3);
        }

        $dateObj = $tanggalVisit ? Carbon::parse($tanggalVisit) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "RTV-{$unitStr}-{$dateStr}-";

        $lastRecord = static::where('visit_id', 'like', "{$prefix}%")
            ->orderBy('visit_id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastSeq = (int) substr($lastRecord->visit_id, -3);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung formula turunan otomatis persis Excel Assignment_Scope (Kolom S, T, U, V)
     */
    public function calculateDerivedAttributes(): void
    {
        // Kolom V: Perlu Data Unit
        $fisikAreas = [
            'Cash Opname',
            'Observasi Lingkungan',
            'Rekon Cek BG',
            'Rekon Barang Cetakan',
        ];
        if (in_array($this->area, $fisikAreas)) {
            $this->perlu_data_unit = 'Tidak - data sistem/fisik diisi RA';
        } else {
            $this->perlu_data_unit = 'Ya - permintaan terbatas sesuai scope';
        }

        // Kolom T: Batas Scope (default jika belum diisi)
        if (empty($this->batas_scope)) {
            $this->batas_scope = "Terbatas pada {$this->area} | {$this->periode_populasi} | Tidak diperluas tanpa persetujuan reviewer";
        }

        // Kolom U: Kriteria Selesai (default jika belum diisi)
        if (empty($this->kriteria_selesai)) {
            $this->kriteria_selesai = "Trigger dinilai terbukti/tidak terbukti; exception diklarifikasi; bukti memadai; keputusan TL, perluasan, atau eskalasi ditetapkan";
        }

        // Default Klasifikasi
        if (empty($this->klasifikasi)) {
            $this->klasifikasi = 'Risk Trigger Visit Terarah';
        }

        // Kolom S: Status Assignment
        // Jika Keputusan Final != RISK TRIGGER VISIT -> BUKAN KEPUTUSAN VISIT
        // Jika Tanggal Visit, RA Pelaksana, Reviewer, Referensi Otorisasi, Klasifikasi, Batas Scope, Kriteria Selesai kosong -> BELUM LENGKAP
        // Jika seluruhnya terisi -> SIAP DILAKSANAKAN
        if ($this->keputusan_final !== 'RISK TRIGGER VISIT') {
            $this->status_assignment = 'BUKAN KEPUTUSAN VISIT';
        } elseif (
            empty($this->tanggal_visit) ||
            empty($this->ra_pelaksana) ||
            empty($this->reviewer) ||
            empty($this->referensi_otorisasi) ||
            empty($this->klasifikasi) ||
            empty($this->batas_scope) ||
            empty($this->kriteria_selesai)
        ) {
            $this->status_assignment = 'BELUM LENGKAP';
        } else {
            $this->status_assignment = 'SIAP DILAKSANAKAN';
        }
    }

    /**
     * Scope akses per cabang untuk akun RA
     */
    public function scopeByBranch($query, ?User $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query;
        }

        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];

            $allowedUnits = Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
            $allowedCabangCodes = Cabang::whereIn('id', $allowedCabangIds)->pluck('kode_cabang')->toArray();

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
