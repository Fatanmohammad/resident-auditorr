<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Cabang;
use App\Models\WpOffsite;
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
        
        // Filter berdasarkan status review
        $status = request('status');
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
     * Halaman detail unit — lihat summary & register
     */
    public function unitDetail(Unit $unit)
    {
        $tahun = (int) request('tahun', date('Y'));
        $bulan = (int) request('bulan', date('m'));

        $wp = WpOffsite::with(['raPelaksana', 'reviewerBagianRa'])
            ->where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return view('admin-offsite.unit-detail', [
                'unit' => $unit, 'wp' => null, 'tahun' => $tahun, 'bulan' => $bulan,
            ]);
        }

        // Ambil register harian dengan proteksi aman
        // Ambil data langsung dari relasi stagingOffsite
        $stagingData = $wp->stagingOffsite()->orderBy('tanggal_data')->get();

        // Grouping berdasarkan tanggal_data untuk tampilan accordion Blade
        $rows = $stagingData->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_data)->format('Y-m-d');
        });

        // Hitung ringkasan statistik
        $ringkasan = [
            'populasi'    => $stagingData->count(),
            'sampel_low'  => $stagingData->where('sampel_low', 1)->count(),
            'kka_final'   => $stagingData->where('masuk_kka_final', 1)->count(),
            'exception'   => $stagingData->where('exception_awal', 1)->count(),
            'klarifikasi' => 0,
            'eskalasi'    => 0,
        ];

        return view('admin-offsite.unit-detail', compact('unit', 'wp', 'rows', 'ringkasan', 'tahun', 'bulan'));
    }

    /**
     * Daftar KKA per area untuk 1 WP
     */
    public function kkaIndex(WpOffsite $wp, string $area)
    {
        if (!isset($this->kkaLabels[$area])) {
            abort(404, 'Area KKA tidak dikenali.');
        }

        $areaLabel = $this->kkaLabels[$area];

        // Ambil HANYA data yang teridentifikasi Temuan / High / Exception / Sampel Low
        $rows = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('area_review', $areaLabel)
            ->where(function ($q) {
                $q->where('exception_awal', true)
                  ->orWhere('masuk_kka_final', true)
                  ->orWhere('risk_level', 'High')
                  ->orWhere('sampel_low', true);
            })
            ->orderBy('tanggal_data')
            ->get();

        return view('admin-offsite.kka-index', [
            'wp'        => $wp,
            'area'      => $area,
            'areaLabel' => $areaLabel,
            'rows'      => $rows,
        ]);
    }

    /**
     * Detail 1 baris KKA
     */
    public function kkaShow(WpOffsite $wp, string $area, int $kkaId)
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
     * Update Catatan Reviewer (Admin/Reviewer HANYA boleh isi ini)
     */
    public function kkaUpdateReviewerNote(Request $request, WpOffsite $wp, string $area, int $kkaId)
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

    /**
     * Update Hasil Review KKA dari Form Reviewer
     */
    public function kkaUpdate(Request $request, WpOffsite $wp, string $area, $kkaId)
    {
        // Validasi input: Admin HANYA diizinkan mengisi Catatan Reviewer
        $validated = $request->validate([
            'catatan_reviewer' => 'nullable|string|max:2000',
        ]);

        // 1. Cari data di Staging Offsite
        $kka = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('id', $kkaId)
            ->first();

        // 2. Jika tidak ditemukan di Staging, cari di tabel spesifik KKA area
        if (!$kka && isset($this->kkaModels[$area])) {
            $model = $this->kkaModels[$area];
            $kka = $model::where('wp_offsite_id', $wp->id)
                ->where(function($q) use ($kkaId) {
                    $q->where('kka_id', $kkaId)->orWhere('id', $kkaId);
                })
                ->first();
        }

        if (!$kka) {
            return back()->with('error', 'Data KKA tidak ditemukan.');
        }

        // HANYA update catatan_reviewer dan reviewer_id
        // status_review TIDAK BISA diubah oleh Admin (Sesuai Spesifikasi §1.4)
        $kka->update([
            'catatan_reviewer' => $validated['catatan_reviewer'],
            'reviewer_id'      => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Catatan Reviewer berhasil disimpan!');
    }
}