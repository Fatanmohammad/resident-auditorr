<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Cabang;
use App\Models\OffsiteUnitSummary;
use App\Models\OffsiteRegisterUpload;
use App\Services\OffsiteAdminService;
use Illuminate\Http\Request;

class AdminOffsiteController extends Controller
{
    public function __construct(private OffsiteAdminService $service) {}

    /**
     * Halaman index admin offsite — daftar cabang dengan statistik unit
     */
    public function index()
    {
        $tahun = request('tahun', date('Y'));
        $bulan = request('bulan', date('m'));

        $cabangs = $this->service->getCabangStats($tahun, $bulan);

        return view('admin-offsite.index', compact('cabangs', 'tahun', 'bulan'));
    }

    /**
     * Halaman detail cabang — daftar unit dalam cabang
     */
    public function cabangDetail($cabangId)
    {
        $tahun = request('tahun', date('Y'));
        $bulan = request('bulan', date('m'));
        $cabang = Cabang::findOrFail($cabangId);

        $unitData = $this->service->getUnitsByBranch($tahun, $bulan, $cabang->id);
        
        // Filter berdasarkan status
        $status = request('status'); // 'perlu_review', 'tidak_perlu', 'selesai'
        if ($status) {
            $unitData = $unitData->filter(function ($item) use ($status) {
                if ($status === 'perlu_review') {
                    return in_array($item['status_review'], ['Perlu Review', 'Dalam Review']);
                }
                if ($status === 'tidak_perlu') {
                    return $item['status_review'] === 'Tidak Perlu Review';
                }
                if ($status === 'selesai') {
                    return $item['status_review'] === 'Selesai Review';
                }
                return true;
            });
        }

        return view('admin-offsite.cabang-detail', compact('cabang', 'unitData', 'tahun', 'bulan', 'status'));
    }

    /**
     * Halaman detail unit — lihat summary & upload CSV
     */
    public function unitDetail(Unit $unit)
    {
        $tahun = request('tahun', date('Y'));
        $bulan = request('bulan', date('m'));

        $summary = OffsiteUnitSummary::where('unit_id', $unit->id)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        $uploads = OffsiteRegisterUpload::where('unit_id', $unit->id)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->latest()
            ->get();

        return view('admin-offsite.unit-detail', compact('unit', 'summary', 'uploads', 'tahun', 'bulan'));
    }

    /**
     * Handle upload CSV register harian
     */
    /*public function uploadRegister(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2099',
            'bulan' => 'required|integer|min:1|max:12',
            'register_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $tahun = $validated['tahun'];
        $bulan = $validated['bulan'];
        $file = $validated['register_file'];

        try {
            // Simpan file
            $fileName = $unit->unit_code . '_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . uniqid() . '.csv';
            $filePath = $file->storeAs('offsite-registers', $fileName, 'local');

            // Catat upload
            $upload = OffsiteRegisterUpload::create([
                'unit_id' => $unit->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'status' => 'Processing',
                'uploaded_by' => auth()->user()->name ?? 'System',
                'uploaded_at' => now(),
            ]);

            // Parse CSV dan update summary
            $fullPath = storage_path('app/' . $filePath);
            $result = $this->service->parseAndUpdateSummary($fullPath, $tahun, $bulan, auth()->user()->name);

            if ($result) {
                $upload->update(['status' => 'Processed']);
                return back()->with('success', 'Register harian berhasil diupload dan diproses.');
            } else {
                $upload->update([
                    'status' => 'Failed',
                    'error_message' => 'Gagal memproses file CSV',
                ]);
                return back()->with('error', 'Gagal memproses file CSV. Periksa format file.');
            }
        } catch (\Exception $e) {
            \Log::error('Upload register error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }*/

    /**
     * Update status review unit manual
     */
    /*public function updateStatus(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer',
            'status_review' => 'required|in:Tidak Perlu Review,Perlu Review,Dalam Review,Selesai Review',
            'catatan' => 'nullable|string',
        ]);

        $summary = OffsiteUnitSummary::where('unit_id', $unit->id)
            ->where('tahun', $validated['tahun'])
            ->where('bulan', $validated['bulan'])
            ->first();

        if (!$summary) {
            $summary = OffsiteUnitSummary::create([
                'unit_id' => $unit->id,
                'tahun' => $validated['tahun'],
                'bulan' => $validated['bulan'],
            ]);
        }

        $summary->update([
            'status_review' => $validated['status_review'],
            'catatan' => $validated['catatan'],
        ]);

        return back()->with('success', 'Status review berhasil diperbarui.');
    }*/
}
