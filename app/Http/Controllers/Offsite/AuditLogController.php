<?php

namespace App\Http\Controllers\Offsite;

use App\Http\Controllers\Controller;
use App\Models\Offsite\AuditLog;
use App\Models\Unit;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AuditLog::with('user');

        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : ($user->cabang_id ? [$user->cabang_id] : []);

            $allowedKodeUnit = Unit::whereIn('cabang_id', $allowedCabangIds)
                ->pluck('unit_code')
                ->toArray();

            if (!empty($allowedKodeUnit)) {
                $query->whereIn('kode_unit', $allowedKodeUnit);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        } elseif ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('created_at', $request->bulan)
                  ->whereYear('created_at', $request->tahun);
        } elseif ($request->filled('tahun')) {
            $query->whereYear('created_at', $request->tahun);
        }

        if ($request->filled('kode_unit')) {
            $query->where('kode_unit', $request->kode_unit);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('offsite.history.index', compact('logs'));
    }
}