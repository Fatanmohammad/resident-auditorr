<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\DailyRegister\DailyRegisterTmpService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyRegisterController extends Controller
{
    /**
     * Tampilkan halaman UI Blade
     */
    public function index()
    {
        // Jalankan auto-cleanup file expired (< hari ini) dan data low risk lama
        DailyRegisterTmpService::cleanupExpired();

        $user = auth()->user();
        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $units = Unit::whereIn('cabang_id', $allowedCabangIds)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->orderBy('unit_code')
                ->get();
        } else {
            $units = Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->orderBy('unit_code')
                ->get();
        }

        return view('daily_register.register.index', compact('units'));
    }

    /**
     * Ambil data JSON Register Harian (Low Risk) untuk AJAX dari Temporary Storage (tmp)
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        $allowedUnitCodes = [];

        // Filter Keamanan Cabang untuk RA vs Admin (Khusus KC)
        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $allowedUnitCodes = Unit::whereIn('cabang_id', $allowedCabangIds)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->pluck('unit_code')
                ->toArray();
        } else {
            if ($request->filled('kode_unit')) {
                $allowedUnitCodes = [$request->kode_unit];
            } else {
                $allowedUnitCodes = Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->pluck('unit_code')
                    ->toArray();
            }
        }

        // Ambil data low risk dari file tmp
        $records = DailyRegisterTmpService::getTodayData(
            $allowedUnitCodes,
            $request->kode_unit,
            $request->tanggal
        );

        // Filter pencarian / keyword jika ada
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $records = $records->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['kode_unit'] ?? ''), $search)
                    || str_contains(strtolower($item['source_sheet'] ?? ''), $search)
                    || str_contains(strtolower($item['rincian'] ?? ''), $search)
                    || str_contains(strtolower($item['kategori'] ?? ''), $search);
            });
        }

        // Filter berdasarkan sumber sheet jika ada
        if ($request->filled('source_sheet')) {
            $sourceSheet = $request->source_sheet;
            $records = $records->filter(function ($item) use ($sourceSheet) {
                return ($item['source_sheet'] ?? '') === $sourceSheet;
            });
        }

        // Sorting: data terbaru di atas
        $sorted = $records->sortByDesc('tanggal_data')->values();

        // Pagination Manual untuk Collection (15 item per halaman)
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $itemsForCurrentPage = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $registers = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'status' => 'success',
            'data'   => $registers
        ]);
    }
}