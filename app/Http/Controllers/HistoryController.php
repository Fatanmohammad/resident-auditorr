<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DB::table('kka_activity_logs');

        // Filter Hak Akses Berdasarkan Role
        if ($user && $user->role === 'admin') {
            // Admin: Akses seluruh log aktivitas & filter status review
            if ($request->filled('kode_unit')) {
                $query->where('kode_unit', $request->kode_unit);
            }
            if ($request->filled('status_review')) {
                $query->where('status_review', $request->status_review);
            }
        } else {
            // RA: Hanya melihat aktivitas di cabangnya sendiri (termasuk sesama RA 1 cabang)
            $userUnit = $user->kode_unit ?? '001';
            $query->where('kode_unit', $userUnit);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Menghitung data yang belum direview untuk indikator Admin
        $unreviewedCount = DB::table('kka_activity_logs')
            ->when($user && $user->role !== 'admin', fn($q) => $q->where('kode_unit', $user->kode_unit ?? '001'))
            ->where('status_review', 'Belum')
            ->count();

        return view('history.index', compact('logs', 'user', 'unreviewedCount'));
    }
}