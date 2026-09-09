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

        $findings = $query->latest('tanggal_data')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $findings
        ]);
    }

    /**
     * Update Inputan RA
     */
    public function updateRa(UpdateKkaRaRequest $request, $id)
    {
        $user = auth()->user();
        $finding = KkaFinding::findOrFail($id);

        $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
            ? $user->cabangIdYangDapatDiakses() 
            : [$user->cabang_id];

        if (!in_array($finding->cabang_id, $allowedCabangIds)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data cabang ini.'
            ], 403);
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
        $finding = KkaFinding::findOrFail($id);
        $finding->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Review KKA berhasil diperbarui oleh Admin.',
            'data'    => $finding
        ]);
    }
}