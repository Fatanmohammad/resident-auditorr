<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaFinding;
use App\Models\ScheduledVisit;
use App\Models\Unit;
use App\Services\Onsite\CbsOnsiteParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OnsiteController extends Controller
{
    /**
     * Daftar kunjungan Onsite
     */
    public function index()
    {
        $user = auth()->user();

        $query = OnsiteVisit::orderByDesc('tanggal_mulai');

        if (strtolower($user->role) === 'ra') {
            // RA hanya lihat kunjungan unit di wilayahnya
            $unitCodes = Unit::where(function ($q) use ($user) {
                if ($user->cabang_id) {
                    $q->where('cabang_id', $user->cabang_id)
                      ->orWhereIn('cabang_id', function ($sub) use ($user) {
                          $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                      });
                }
            })->pluck('unit_code');

            $query->whereIn('kode_unit', $unitCodes);
        }

        $visits = $query->paginate(20);

        return view('onsite.index', compact('visits'));
    }

    /**
     * Form buat kunjungan baru
     */
    public function create()
    {
        $user = auth()->user();

        // Hanya tampilkan unit KCP dan KCPLK
        $query = Unit::whereIn('unit_type', ['KCP', 'KCPLK'])->orderBy('unit_name');

        if (strtolower($user->role) === 'ra') {
            $query->where(function ($q) use ($user) {
                if ($user->cabang_id) {
                    $q->where('cabang_id', $user->cabang_id)
                      ->orWhereIn('cabang_id', function ($sub) use ($user) {
                          $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                      });
                }
            });
        }

        $units = $query->get();

        return view('onsite.create', compact('units'));
    }

    /**
     * Simpan kunjungan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_unit'       => 'required|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $unit = Unit::where('unit_code', $request->kode_unit)->first();

        $visit = OnsiteVisit::create([
            'kode_unit'       => $request->kode_unit,
            'nama_unit'       => $unit?->unit_name,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'ra_id'           => auth()->id(),
            'status'          => 'Persiapan',
            'periode'         => date('Y-m', strtotime($request->tanggal_mulai)),
        ]);

        return redirect()->route('onsite.upload', $visit->id)
            ->with('success', 'Kunjungan berhasil dibuat. Silakan upload data CBS populasi.');
    }

    /**
     * Form upload CBS untuk kunjungan
     */
    public function uploadForm(OnsiteVisit $visit)
    {
        return view('onsite.upload', compact('visit'));
    }

    /**
     * Proses upload CBS
     */
    public function uploadProcess(Request $request, OnsiteVisit $visit, CbsOnsiteParser $parser)
    {
        $request->validate([
            'file_cbs' => 'required|file|mimes:csv,txt|max:20480',
        ]);

        $file     = $request->file('file_cbs');
        $filePath = $file->storeAs('onsite_staging', time() . '_' . $file->getClientOriginalName(), 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        DB::beginTransaction();
        try {
            $result = $parser->parse($fullPath, $visit);

            $visit->update(['status' => 'Berlangsung']);

            DB::commit();

            if (file_exists($fullPath)) unlink($fullPath);

            return redirect()->route('onsite.index')
                ->with('success', "Upload CBS berhasil. {$result['total_populasi']} populasi diproses, {$result['total_sampel']} sampel dipilih otomatis.");

        } catch (\Exception $e) {
            DB::rollBack();
            if (file_exists($fullPath)) unlink($fullPath);

            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * KKA Onsite — halaman UI
     */
    public function kka(OnsiteVisit $visit)
    {
        return view('onsite.kka', compact('visit'));
    }

    /**
     * KKA Onsite — data JSON untuk AJAX
     */
    public function kkaData(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaFinding::where('onsite_visit_id', $visit->id)
            ->orderBy('prioritas')
            ->orderBy('tgl_tx');

        if ($request->filled('jenis_sampling')) {
            $query->where('jenis_sampling', $request->jenis_sampling);
        }

        $findings = $query->get();

        return response()->json(['status' => 'success', 'data' => $findings]);
    }

    /**
     * Update input RA pada satu sampel KKA
     */
    public function updateKka(Request $request, OnsiteKkaFinding $finding)
    {
        if ($finding->status_review === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved dan tidak dapat diubah.'], 403);
        }

        $request->validate([
            'hasil_uji'         => 'nullable|string',
            'bukti_referensi'   => 'nullable|string',
            'skor_dampak'       => 'nullable|integer|min:1|max:5',
            'skor_kemungkinan'  => 'nullable|integer|min:1|max:5',
            'simpulan_ra'       => 'nullable|string',
            'tanggal_ditemukan' => 'nullable|date',
        ]);

        // Hitung risk_level otomatis
        $skor = ($request->skor_dampak ?? 1) * ($request->skor_kemungkinan ?? 1);
        $riskLevel = $skor >= 20 ? 'High' : ($skor >= 12 ? 'High' : ($skor >= 6 ? 'Moderate' : 'Low'));

        $finding->update(array_merge(
            $request->only(['hasil_uji', 'bukti_referensi', 'skor_dampak', 'skor_kemungkinan', 'simpulan_ra', 'tanggal_ditemukan']),
            ['risk_level' => $riskLevel]
        ));

        return response()->json(['status' => 'success', 'message' => 'KKA berhasil diperbarui.', 'data' => $finding]);
    }

    /**
     * Review KKA oleh Admin/Kabag/Kadiv
     */
    public function reviewKka(Request $request, OnsiteKkaFinding $finding)
    {
        $request->validate([
            'status_review'    => 'required|in:Belum Direview,Revisi,Approved',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $finding->update($request->only(['status_review', 'catatan_reviewer']));

        return response()->json(['status' => 'success', 'message' => 'Review KKA berhasil disimpan.', 'data' => $finding]);
    }
}
