<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\RawMetric;
use App\Services\RiskScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RawMetricController extends Controller
{
public function __construct(private RiskScoringService $scoringService) {}

    /**
     * Pastikan user (khususnya role RA) berhak mengakses unit.
     * RA hanya boleh mengakses unit di cabangnya + seluruh anak cabangnya.
     * Operator (kabag_ra/kadiv_skai/admin) boleh mengakses semua unit.
     */
    private function ensureCanAccessUnit(Unit $unit): void
    {
        $allowedCabangIds = Auth::user()->cabangIdYangDapatDiakses();

        // null = operator (akses semua); kosong = RA tanpa cabang (akses none)
        if ($allowedCabangIds === null) {
            return;
        }

        if (!$unit->cabang_id || !in_array($unit->cabang_id, $allowedCabangIds)) {
            abort(403, 'Anda hanya dapat mengakses unit di cabang Anda sendiri.');
        }
    }

/**
     * Halaman daftar unit untuk menginput raw metrics.
     * RA hanya melihat unit pada wilayahnya (cabang sendiri + anak cabang).
     * Halaman ini TIDAK menampilkan skor risiko (hanya untuk input).
     */
    public function index()
    {
        $allowedCabangIds = Auth::user()->cabangIdYangDapatDiakses();

        $query = Unit::withCount('rawMetrics')
            ->where('is_active', true);

        if ($allowedCabangIds !== null) {
            $query->whereIn('units.cabang_id', $allowedCabangIds);
        }

        $period = request('period', date('Y'));
        $units = $query->orderBy('unit_type')->orderBy('unit_name')->get();

        return view('raw-metrics.index', compact('units', 'period'));
    }

    public function create(Unit $unit)
    {
        $this->ensureCanAccessUnit($unit);

        $period  = request('period', date('Y'));
        $existing = RawMetric::where('unit_id', $unit->id)->where('period', $period)->first();
        return view('raw-metrics.form', compact('unit', 'period', 'existing'));
    }

public function store(Request $request, Unit $unit)
    {
        $this->ensureCanAccessUnit($unit);

        $period = $request->integer('period', date('Y'));

        // Aturan validasi dasar (period + semua bidang yang selalu relevan)
        $rules = [
            'period'                      => 'required|integer|min:2020|max:2099',
            'prior_onsite_findings'       => 'required|integer|min:0',
            'significant_findings'        => 'required|integer|min:0',
            'repeat_findings'             => 'required|integer|min:0',
            'offsite_deviation'           => 'required|integer|min:0',
            'offsite_deviation_significant'=> 'required|integer|min:0',
            'offsite_deviation_repeat'    => 'required|integer|min:0',
            'months_since_last_onsite'    => 'required|integer|min:0',
'reversal_correction_txn'     => 'required|integer|min:0',
            'cash_discrepancy'            => 'required|integer|min:0',
            'unusual_cost_journal'        => 'required|integer|min:0',
            'large_risky_cash_txn'        => 'required|integer|min:0',
            'ra_onsite_tl_overdue'        => 'required|integer|min:0',
            'ra_offsite_tl_overdue'       => 'required|integer|min:0',
            'skai_tl_overdue'             => 'required|integer|min:0',
            'regulator_tl_overdue'        => 'required|integer|min:0',
            'kap_tl_overdue'              => 'required|integer|min:0',
            'avg_response_days'           => 'required|numeric|min:0',
            'tl_response_quality'         => 'required|integer|min:0|max:4',
        ];

        // Bidang C (CS/DPK) — tidak relevan untuk Payment Point
        if (!in_array($unit->unit_type, ['Payment Point'])) {
            $rules['dpk_anomaly']          = 'required|integer|min:0';
            $rules['overdue_complaints']   = 'required|integer|min:0';
            $rules['incomplete_cdd_edd']   = 'required|integer|min:0';
        }

        // Bidang D (Kredit) — tidak relevan untuk Payment Point & KCPLK
        if (!in_array($unit->unit_type, ['Payment Point', 'KCPLK'])) {
            $rules['debtors_col_3_5']      = 'required|integer|min:0';
            $rules['npl_ratio']            = 'required|numeric|min:0|max:1';
            $rules['credit_deviation']     = 'required|integer|min:0';
        }

        // Bidang E (TI/ATM) — tidak relevan untuk Payment Point
        if (!in_array($unit->unit_type, ['Payment Point'])) {
            $rules['atm_dispute']          = 'required|integer|min:0';
            $rules['atm_downtime_hours']   = 'required|numeric|min:0';
            $rules['critical_ti_incident'] = 'required|integer|min:0';
            $rules['unusual_user_reset']   = 'required|integer|min:0';
        }

        $validated = $request->validate($rules);

        // Isi otomatis 0 untuk bidang yang tidak relevan
        $validated = array_merge([
            'dpk_anomaly'          => 0,
            'overdue_complaints'   => 0,
            'incomplete_cdd_edd'   => 0,
            'debtors_col_3_5'      => 0,
            'npl_ratio'            => 0,
            'credit_deviation'     => 0,
            'atm_dispute'          => 0,
            'atm_downtime_hours'   => 0,
            'critical_ti_incident' => 0,
            'unusual_user_reset'   => 0,
        ], $validated);

        $validated['unit_id']  = $unit->id;
        $validated['input_by'] = Auth::id();

        RawMetric::updateOrCreate(
            ['unit_id' => $unit->id, 'period' => $period],
            $validated
        );

// Recompute otomatis seluruh chain scoring.
        // Jika recompute gagal, data raw tetap tersimpan dan tidak menggagalkan redirect.
        try {
            $this->scoringService->recompute($unit, $period);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Recompute scoring gagal saat menyimpan raw metrics: ' . $e->getMessage());
        }

// Setelah menyimpan, kembali ke halaman Data Unit (units.index)
        // dengan notifikasi sukses di bagian atas.
        return redirect()->route('units.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }
}
