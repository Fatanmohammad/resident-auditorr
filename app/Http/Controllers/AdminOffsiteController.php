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
    private array $kkaModels = [
    'teller-kas'      => \App\Models\KkaTellerKas::class,
    'kredit'          => \App\Models\KkaKredit::class,
    'biaya-beban'     => \App\Models\KkaBiayaBeban::class,
    'biaya-internal'  => \App\Models\KkaBiayaInternal::class,
    'pengaduan'       => \App\Models\KkaPengaduan::class,
    'transaksi-umum'  => \App\Models\KkaTransaksiUmum::class,
    'transfer-ku'     => \App\Models\KkaTransferKu::class,
    ];

    private array $kkaLabels = [
        'teller-kas'      => 'Teller/Kas',
        'kredit'          => 'Kredit',
        'biaya-beban'     => 'Biaya/Beban',
        'biaya-internal'  => 'Biaya/Internal',
        'pengaduan'       => 'Pengaduan',
        'transaksi-umum'  => 'Transaksi Umum',
        'transfer-ku'     => 'Transfer/KU',
    ];

    /**
     * Daftar KKA per area untuk 1 WP
     */
    public function kkaIndex(\App\Models\WpOffsite $wp, string $area)
    {
    if (!isset($this->kkaModels[$area])) {
        abort(404, 'Area KKA tidak dikenali.');
    }

    $model = $this->kkaModels[$area];
    $rows = $model::where('wp_offsite_id', $wp->id)->orderBy('tanggal_data')->get();

    return view('admin-offsite.kka-index', [
        'wp' => $wp,
        'area' => $area,
        'areaLabel' => $this->kkaLabels[$area],
        'rows' => $rows,
    ]);
}

/**
 * Detail 1 baris KKA
 */
public function kkaShow(\App\Models\WpOffsite $wp, string $area, int $kkaId)
{
    if (!isset($this->kkaModels[$area])) {
        abort(404, 'Area KKA tidak dikenali.');
    }

    $model = $this->kkaModels[$area];
    $kka = $model::where('wp_offsite_id', $wp->id)->findOrFail($kkaId);

    return view('admin-offsite.kka-show', [
        'wp' => $wp,
        'area' => $area,
        'areaLabel' => $this->kkaLabels[$area],
        'kka' => $kka,
    ]);
}

/**
 * Update Catatan Reviewer saja (Admin/Reviewer HANYA boleh isi ini)
 */
    public function kkaUpdateReviewerNote(Request $request, \App\Models\WpOffsite $wp, string $area, int $kkaId)
    {
    if (!isset($this->kkaModels[$area])) {
        abort(404, 'Area KKA tidak dikenali.');
    }

    $validated = $request->validate([
        'catatan_reviewer' => 'nullable|string',
    ]);

    $model = $this->kkaModels[$area];
    $kka = $model::where('wp_offsite_id', $wp->id)->findOrFail($kkaId);

    $kka->update([
        'catatan_reviewer' => $validated['catatan_reviewer'],
        'reviewer_id' => auth()->id(),
    ]);

    return back()->with('success', 'Catatan Reviewer berhasil disimpan.');
}
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

    $wp = \App\Models\WpOffsite::with(['raPelaksana', 'reviewerBagianRa'])
        ->where('unit_id', $unit->id)
        ->whereYear('periode_mulai', $tahun)
        ->whereMonth('periode_mulai', $bulan)
        ->first();

    if (!$wp) {
        return view('admin-offsite.unit-detail', [
            'unit' => $unit, 'wp' => null, 'tahun' => $tahun, 'bulan' => $bulan,
        ]);
    }

    $rows = $wp->registerHarian()
        ->orderBy('tanggal_data')
        ->orderBy('area_review')
        ->get()
        ->groupBy(fn($r) => $r->tanggal_data->format('Y-m-d'));

    $ringkasan = [
        'populasi'    => $wp->registerHarian()->sum('populasi_eligible'),
        'sampel_low'  => $wp->registerHarian()->sum('sampel_low'),
        'kka_final'   => $wp->registerHarian()->sum('kka_final'),
        'exception'   => $wp->registerHarian()->sum('exception'),
        'klarifikasi' => $wp->registerHarian()->sum('perlu_klarifikasi'),
        'eskalasi'    => $wp->registerHarian()->sum('perlu_eskalasi'),
    ];

    return view('admin-offsite.unit-detail', compact('unit', 'wp', 'rows', 'ringkasan', 'tahun', 'bulan'));
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
