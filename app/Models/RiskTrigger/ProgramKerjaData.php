<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;

class ProgramKerjaData extends Model
{
    use HasFactory;

    protected $table = 'program_kerja_data';

    protected $guarded = ['id'];

    protected $casts = [
        'cutoff_data' => 'date',
        'tanggal_permintaan' => 'date',
        'tanggal_diterima' => 'date',
        'sampel_pemicu' => 'integer',
        'sampel_pembanding' => 'integer',
    ];

    public function assignmentScope()
    {
        return $this->belongsTo(AssignmentScope::class, 'assignment_scope_id');
    }

    public function registerTrigger()
    {
        return $this->belongsTo(RegisterTrigger::class, 'register_trigger_id');
    }

    /**
     * Hitung formula status kesiapan persis Excel Program_Kerja_Data Kolom Q
     * Formula:
     * IF(OR(J6="",M6="",P6=""), "BELUM SIAP",
     *   IF(PerluDataUnit="Ya - permintaan terbatas sesuai scope",
     *     IF(N6="", "BELUM DIMINTA", IF(O6="", "MENUNGGU DATA", "SIAP UJI")),
     *     "SIAP UJI"
     *   )
     * )
     */
    public function calculateStatusKesiapan(?string $perluDataUnit = null): void
    {
        if ($perluDataUnit === null && $this->assignmentScope) {
            $perluDataUnit = $this->assignmentScope->perlu_data_unit;
        }

        // Jika perluDataUnit belum didapat, default berdasarkan Area
        if ($perluDataUnit === null) {
            $fisikAreas = ['Cash Opname', 'Observasi Lingkungan', 'Rekon Cek BG', 'Rekon Barang Cetakan'];
            $perluDataUnit = in_array($this->area, $fisikAreas)
                ? 'Tidak - data sistem/fisik diisi RA'
                : 'Ya - permintaan terbatas sesuai scope';
        }

        if (empty($this->sampel_pemicu) || empty($this->cutoff_data) || empty($this->referensi_bukti_awal)) {
            $this->status_kesiapan = 'BELUM SIAP';
        } elseif ($perluDataUnit === 'Ya - permintaan terbatas sesuai scope') {
            if (empty($this->tanggal_permintaan)) {
                $this->status_kesiapan = 'BELUM DIMINTA';
            } elseif (empty($this->tanggal_diterima)) {
                $this->status_kesiapan = 'MENUNGGU DATA';
            } else {
                $this->status_kesiapan = 'SIAP UJI';
            }
        } else {
            $this->status_kesiapan = 'SIAP UJI';
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
