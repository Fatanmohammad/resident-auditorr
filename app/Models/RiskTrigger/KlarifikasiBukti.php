<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Cabang;
use App\Models\Unit;
use App\Models\User;

class KlarifikasiBukti extends Model
{
    use HasFactory;

    protected $table = 'risk_trigger_klarifikasi_buktis';

    protected $fillable = [
        'assignment_scope_id',
        'register_trigger_id',
        'kode_unit',
        'unit_kantor',
        'visit_id',
        'trigger_id',
        'area',
        'isu_exception',
        'pihak_diklarifikasi',
        'tanggal_permintaan',
        'pertanyaan_klarifikasi',
        'jawaban_auditee',
        'bukti_diminta',
        'bukti_diterima',
        'lampiran_file',
        'tanggal_diterima',
        'kecukupan_bukti',
        'analisis_ra',
        'status_klarifikasi',
        'reviewer',
        'catatan_review',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'date',
        'tanggal_diterima' => 'date',
    ];

    protected static function booted()
    {
        static::saved(function ($model) {
            if (!empty($model->visit_id)) {
                $simpulan = SimpulanTl::where('visit_id', $model->visit_id)->first();
                if ($simpulan) {
                    $simpulan->calculateDerivedAttributes();
                    $simpulan->saveQuietly();
                }
            }
        });

        static::deleted(function ($model) {
            if (!empty($model->visit_id)) {
                $simpulan = SimpulanTl::where('visit_id', $model->visit_id)->first();
                if ($simpulan) {
                    $simpulan->calculateDerivedAttributes();
                    $simpulan->saveQuietly();
                }
            }
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
