<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyRegister\RegisterKlarifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KlarifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower($user->role), ['admin', 'reviewer', 'kabag_ra', 'kadiv_skai', 'pimsie', 'korwas']);
        
        $query = RegisterKlarifikasi::with('source');
        
        // Filter khusus untuk role RA (hanya melihat cabang penugasan miliknya)
        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $allowedUnits = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
            $allowedCabangCodes = \App\Models\Cabang::whereIn('id', $allowedCabangIds)->pluck('kode_cabang')->toArray();
            
            $targetUnitCodes = array_unique(array_filter(array_merge($allowedUnits, $allowedCabangCodes)));
            
            $expandedCodes = [];
            foreach ($targetUnitCodes as $code) {
                $expandedCodes[] = $code;
                $expandedCodes[] = str_replace('BS-', '', $code);
                $expandedCodes[] = 'BS-' . str_replace('BS-', '', $code);
            }
            $targetUnitCodes = array_unique(array_filter($expandedCodes));

            $query->whereIn('kode_unit', $targetUnitCodes);

            // Jika RA juga memilih filter sub-unit tertentu dari cabangnya
            if ($request->filled('kode_unit')) {
                $unitFilter = $request->input('kode_unit');
                $clean = str_replace('BS-', '', $unitFilter);
                $query->where(function($q) use ($unitFilter, $clean) {
                    $q->where('kode_unit', $unitFilter)
                      ->orWhere('kode_unit', $clean)
                      ->orWhere('kode_unit', 'BS-' . $clean);
                });
            }

            // Units dropdown untuk RA: hanya unit yang berada di dalam cabang penugasannya
            $units = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->orderBy('unit_code', 'asc')->get();
            $userCabangName = $user->cabang ? $user->cabang->nama_cabang : 'Cabang Penugasan';
        } else {
            // Untuk Admin: Izinkan filter berdasarkan kode_unit jika dipilih di filter dropdown
            if ($request->filled('kode_unit')) {
                $unitFilter = $request->input('kode_unit');
                $clean = str_replace('BS-', '', $unitFilter);
                $query->where(function($q) use ($unitFilter, $clean) {
                    $q->where('kode_unit', $unitFilter)
                      ->orWhere('kode_unit', $clean)
                      ->orWhere('kode_unit', 'BS-' . $clean);
                });
            }

            $units = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->orderBy('unit_code', 'asc')->get();
            $userCabangName = null;
        }

        $klarifikasis = $query->latest()->get();

        return view('daily_register.klarifikasi', compact('klarifikasis', 'isAdmin', 'units', 'userCabangName'));
    }

    public function submit(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower($user->role), ['admin', 'reviewer', 'kabag_ra', 'kadiv_skai', 'pimsie', 'korwas']);

        $rules = ['klarifikasi_id' => 'required|exists:register_klarifikasis,id'];
        
        if (!$isAdmin) {
            $rules['tanggapan_cabang'] = 'required|string';
            $rules['simpulan_ra'] = 'required|string';
        } else {
            $rules['action'] = 'required|in:clear,escalate';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $klarifikasi = RegisterKlarifikasi::findOrFail($request->klarifikasi_id);
            
            if (!$isAdmin) {
                // Validasi otorisasi: RA hanya dapat mengisi klarifikasi pada cabang penugasannya
                $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                    ? $user->cabangIdYangDapatDiakses() 
                    : [$user->cabang_id];
                $allowedUnits = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
                $allowedCabangCodes = \App\Models\Cabang::whereIn('id', $allowedCabangIds)->pluck('kode_cabang')->toArray();
                $expandedCodes = [];
                foreach (array_merge($allowedUnits, $allowedCabangCodes) as $code) {
                    $expandedCodes[] = $code;
                    $expandedCodes[] = str_replace('BS-', '', $code);
                    $expandedCodes[] = 'BS-' . str_replace('BS-', '', $code);
                }
                if (!in_array($klarifikasi->kode_unit, array_unique(array_filter($expandedCodes)))) {
                    return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk klarifikasi unit ini.');
                }

                $klarifikasi->tanggapan_cabang = $request->tanggapan_cabang;
                $klarifikasi->simpulan_ra = $request->simpulan_ra;
                $klarifikasi->status = 'Menunggu Klarifikasi'; // Tetap menunggu admin
            } else {
                if ($request->action === 'clear') {
                    $klarifikasi->status = 'Selesai - Clear';
                } else {
                    $klarifikasi->status = 'Selesai - Eskalasi Temuan';
                    // Trigger creation of a formal Finding here
                }
            }
            
            $klarifikasi->save();

            DB::commit();
            return redirect()->back()->with('success', 'Tindak lanjut klarifikasi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses klarifikasi: ' . $e->getMessage());
        }
    }
}
