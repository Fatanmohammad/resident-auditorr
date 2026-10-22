<?php

namespace App\Http\Controllers\Offsite;

use App\Http\Controllers\Controller;
use App\Models\Offsite\KkaFinding;
use App\Http\Requests\Offsite\UpdateKkaRaRequest;
use App\Http\Requests\Offsite\UpdateKkaAdminRequest;
use Illuminate\Http\Request;

class KkaController extends Controller
{
    /**
     * HANYA untuk menampilkan halaman UI (Blade)
     */
    public function index()
    {
        return view('offsite.kka.index');
    }

    /**
     * HANYA untuk mengambil data JSON (dipanggil oleh Axios/AJAX di Javascript)
     * Automatic Filter berdasarkan Wewenang Cabang
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        $query = KkaFinding::query();

        // 1. Filter Keamanan Wilayah RA vs Admin
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            // Dapatkan unit_code dari cabang id yang diizinkan
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code');
            $query->whereIn('kode_unit', $unitCodes);
        } else {
            if ($request->filled('cabang_id')) {
                $unitCodes = \App\Models\Unit::where('cabang_id', $request->cabang_id)->pluck('unit_code');
                $query->whereIn('kode_unit', $unitCodes);
            }
            if ($request->filled('kode_unit')) {
                $query->where('kode_unit', $request->kode_unit);
            }
        }

        // 2. Filter Spesifik dari UI (Sheet KKA, Risk, Tanggal)
        if ($request->filled('source_sheet')) {
            $query->where('source_sheet', $request->source_sheet);
        }
        if ($request->filled('risk_awal')) {
            $query->where('risk_awal', $request->risk_awal);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }

        // Hitung Summary Global (sebelum pagination)
        $summaryQuery = clone $query;
        $totalException = $summaryQuery->count();
        $totalNominal = $summaryQuery->sum('nominal_terkait');
        $highRiskCount = (clone $summaryQuery)->where('risk_awal', 'High')->count();
        $selesaiReviewCount = (clone $summaryQuery)->where('status_review', 'Approved')->count();

        $findings = $query->latest('tanggal_data')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $findings,
            'summary' => [
                'total_exception' => $totalException,
                'total_nominal'   => (float) $totalNominal,
                'high_risk'       => $highRiskCount,
                'selesai_review'  => $selesaiReviewCount
            ]
        ]);
    }

    /**
     * Update Inputan RA
     */
    public function updateRa(UpdateKkaRaRequest $request, $id)
    {
        $user = auth()->user();
        $finding = KkaFinding::findOrFail($id);

        // Jika user adalah RA, validasi berdasarkan izin kode_unit cabang yang diakses
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $allowedUnitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();

            if (!in_array($finding->kode_unit, $allowedUnitCodes)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data cabang ini.'
                ], 403);
            }
        }

        $finding->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Data KKA berhasil diperbarui oleh RA.',
            'data'    => $finding
        ]);
    }

    /**
     * Update Review Admin
     */
    public function updateAdmin(UpdateKkaAdminRequest $request, $id)
    {
        try {
            $finding = KkaFinding::findOrFail($id);
            $finding->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Review KKA berhasil diperbarui oleh Admin.',
                'data'    => $finding
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error Database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi Temuan KKA oleh RA / Cabang & Sinkronisasi ke RawMetric (SOP 01)
     */
    public function konfirmasiCabang(Request $request, $id)
    {
        $request->validate([
            'status_konfirmasi'     => 'required|in:Sesuai,Tidak Sesuai',
            'komitmen_penyelesaian' => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|string',
            'target_penyelesaian'   => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|date',
        ]);

        $finding = KkaFinding::findOrFail($id);

        $finding->update([
            'status_konfirmasi'     => $request->status_konfirmasi,
            'komitmen_penyelesaian' => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->komitmen_penyelesaian : null,
            'target_penyelesaian'   => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->target_penyelesaian : null,
            'tanggal_konfirmasi'    => now(),
        ]);

        // Sinkronisasi otomatis ke RawMetric berdasarkan unit dan periode temuan
        if ($finding->kode_unit && $finding->periode) {
            $this->syncRawMetricDeviations($finding->kode_unit, $finding->periode);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfirmasi KKA berhasil disimpan dan disinkronkan ke jadwal audit.',
            'data'    => $finding
        ]);
    }

    /**
     * Helper privat untuk kalkulasi ulang deviasi offsite ke tabel RawMetric
     */
    private function syncRawMetricDeviations(string $kodeUnit, string $periode)
    {
        $unit = \App\Models\Unit::where('unit_code', $kodeUnit)->first();
        if (!$unit) return;

        $yearPeriod = date('Y', strtotime($periode));

        $rawMetric = \App\Models\RawMetric::where('unit_id', $unit->id)
            ->where('period', 'LIKE', "$yearPeriod%")
            ->first();

        if ($rawMetric) {
            $totalTidakSesuai = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->count();

            $totalSignifikan = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->where('risk_awal', 'High')
                ->count();

            $rawMetric->update([
                'offsite_deviation'             => $totalTidakSesuai,
                'offsite_deviation_significant' => $totalSignifikan,
            ]);
        }
    }
}