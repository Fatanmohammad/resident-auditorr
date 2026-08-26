<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WpOffsiteStaging;
use App\Models\WpOffsite;
use App\Models\Cabang;

class RaOffsiteRegisterController extends Controller
{
    /**
     * Tampilkan daftar register data staging ter-scan
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $accessibleIds = $user->cabangIdYangDapatDiakses();

        // Scope unit sesuai hak akses RA
        $cabangs = $accessibleIds === null 
            ? Cabang::all() 
            : Cabang::whereIn('id', $accessibleIds)->get();

        $cabangId = $request->get('cabang_id', $cabangs->first()->id ?? null);
        $domainType = $request->get('domain_type');

        $query = WpOffsiteStaging::query();

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        if ($domainType) {
            $query->where('domain_type', $domainType);
        }

        $stagings = $query->orderBy('tgl_transaksi', 'desc')->paginate(15);

        return view('ra-offsite.register', compact('stagings', 'cabangs', 'cabangId', 'domainType'));
    }

    /**
     * Update Catatan & Status Verifikasi RA per item Staging
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status_review' => 'required|in:Pending,Verified,Escalated,Rejected',
            'catatan_ra'    => 'nullable|string',
        ]);

        $staging = WpOffsiteStaging::findOrFail($id);
        $staging->update([
            'status_review' => $request->status_review,
            'catatan_ra'    => $request->catatan_ra,
        ]);

        return redirect()->back()->with('success', 'Status review berhasil diperbarui.');
    }
}