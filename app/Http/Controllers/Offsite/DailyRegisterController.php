<?php

namespace App\Http\Controllers\Offsite;

use App\Http\Controllers\Controller;
use App\Models\Offsite\DailyRegister;
use Illuminate\Http\Request;

class DailyRegisterController extends Controller
{
    /**
     * Tampilkan halaman UI Blade
     */
    public function index()
    {
        return view('offsite.register.index');
    }

    /**
     * Ambil data JSON Register Harian (Low Risk) untuk AJAX
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        $query = DailyRegister::query();

        // Filter Keamanan Cabang untuk RA
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            // Ambil unit_code yang terikat ke cabang_id tersebut
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code');

            $query->whereIn('kode_unit', $unitCodes);
        } else {
            if ($request->filled('kode_unit')) {
                $query->where('kode_unit', $request->kode_unit);
            }
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }

        $registers = $query->latest('tanggal_data')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $registers
        ]);
    }
}