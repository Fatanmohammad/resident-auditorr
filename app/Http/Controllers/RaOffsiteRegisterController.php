<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WpOffsiteStaging;
use App\Models\Cabang;
use Illuminate\Support\Facades\DB;

class RaOffsiteRegisterController extends Controller
{
    /**
     * Tampilkan daftar register data staging ter-scan (Monitoring Log)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $accessibleIds = $user->cabangIdYangDapatDiakses();

        // Scope unit sesuai hak akses RA
        $cabangs = $accessibleIds === null 
            ? Cabang::all() 
            : Cabang::whereIn('id', $accessibleIds)->get();

        $cabangId = $request->get('cabang_id');
        $domainType = $request->get('domain_type');
        $statusFlag = $request->get('status_flag');

        $query = WpOffsiteStaging::query();

        // 1. Filter Cabang (jika dipilih)
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        // 2. Filter Domain Type (Case-Insensitive & Multi-Alias Support)
        if ($request->filled('domain_type')) {
            $domain = strtolower($domainType);

            if (in_array($domain, ['biaya', 'biaya_beban', 'jurnal_biaya'])) {
                $query->whereIn(DB::raw('LOWER(domain_type)'), ['biaya', 'biaya_beban', 'jurnal_biaya']);
            } elseif (in_array($domain, ['cbs', 'teller_kas', 'teller'])) {
                $query->whereIn(DB::raw('LOWER(domain_type)'), ['cbs', 'teller_kas', 'teller']);
            } else {
                $query->whereRaw('LOWER(domain_type) = ?', [$domain]);
            }
        }

        // 3. Filter Status Deteksi Engine (Flagged vs Cleared)
        if ($request->filled('status_flag')) {
            if ($statusFlag === 'flagged') {
                $query->where('perlu_kka', true);
            } elseif ($statusFlag === 'cleared') {
                $query->where(function($q) {
                    $q->where('perlu_kka', false)
                      ->orWhereNull('perlu_kka');
                });
            }
        }

        // Ambil data dengan urutan terbaru & paginasi
        $stagings = $query->orderBy('tgl_transaksi', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        // --- PIPELINE BACKEND: Murni merapikan raw_data tanpa ganggu View ---
        $stagings->getCollection()->transform(function ($item) {
            if (is_string($item->raw_data)) {
                $json = json_decode($item->raw_data, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $uraian = $json['URAIAN'] ?? $json['uraian'] ?? $json['DESKRIPSI'] ?? '';
                    $noRek  = $json['NO_REK'] ?? $json['no_rek'] ?? '';
                    
                    // Timpa variabel raw_data di backend dengan string hasil parsing
                    if ($uraian || $noRek) {
                        $item->raw_data = trim($uraian . ($noRek ? " (Rek: {$noRek})" : ''));
                    }
                }
            }
            return $item;
        });

        return view('ra-offsite.register', compact('stagings', 'cabangs', 'cabangId', 'domainType', 'statusFlag'));
    }

    /**
     * Optional / Opsional jika masih dibutuhkan
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