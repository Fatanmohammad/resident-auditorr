<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Cabang;
use App\Models\WpOffsite;
use App\Services\OffsiteAdminService;
use App\Services\OffsiteGenerationService;
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

    public function __construct(
        private OffsiteAdminService $service,
        private OffsiteGenerationService $generation,
    ) {}

    /**
     * Jalankan ulang pipeline Dump -> Staging -> Register -> KKA untuk WP aktif unit ini.
     */
    public function uploadRegister(Request $request, Unit $unit)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('m'));

        $wp = WpOffsite::where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return back()->with('error', 'Belum ada WP Offsite untuk unit dan periode ini. Buat WP-nya dulu di menu Offsite Review.');
        }

        $hasil = $this->generation->generate($wp);

        $pesan = $hasil['total_diproses'] > 0
            ? "Data berhasil digenerate ulang. {$hasil['total_diproses']} baris diproses, {$hasil['total_masuk_kka']} masuk KKA."
            : 'Belum ada data Dump untuk unit & periode ini.';

        return back()->with('success', $pesan);
    }

    /**
     * Mengubah status WP (Draft/Aktif/Final) untuk unit & periode terkait.
     */
    public function updateStatus(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'status_wp' => 'required|in:Draft,Aktif,Final',
            'tahun'     => 'nullable|integer',
            'bulan'     => 'nullable|integer',
        ]);

        $tahun = $validated['tahun'] ?? date('Y');
        $bulan = $validated['bulan'] ?? date('m');

        $wp = WpOffsite::where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return back()->with('error', 'WP Offsite untuk unit dan periode ini tidak ditemukan.');
        }

        $wp->update(['status_wp' => $validated['status_wp']]);

        return back()->with('success', 'Status WP berhasil diperbarui.');
    }

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
        if (!isset($this->kkaLabels[$area]) || !isset($this->kkaModels[$area])) {
            abort(404, 'Area KKA tidak dikenali.');
        }

        $areaLabel = $this->kkaLabels[$area];
        $modelClass = $this->kkaModels[$area];

        $rows = $modelClass::where('wp_offsite_id', $wp->id)
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

        $areaLabel = $this->kkaLabels[$area];
        $modelClass = $this->kkaModels[$area];
        $instance = new $modelClass;
        $primaryKey = $instance->getKeyName();

        // 1. Cari data di tabel KKA Spesifik memakai primary key dinamis & kka_id
        $kka = $modelClass::where('wp_offsite_id', $wp->id)
            ->where(function($q) use ($primaryKey, $kkaId) {
                $q->where($primaryKey, $kkaId);
                if ($primaryKey !== 'kka_id') {
                    $q->orWhere('kka_id', $kkaId);
                }
            })
            ->first();

        // 2. Fallback: Jika belum didistribusikan ke tabel KKA area, ambil dari StagingOffsite
        if (!$kka) {
            $staging = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
                ->where('id', $kkaId)
                ->firstOrFail();

            // Auto-create/sync ke tabel KKA area spesifik dengan kelengkapan field NOT NULL
            $kka = $modelClass::firstOrCreate(
                [
                    'wp_offsite_id' => $wp->id,
                    'staging_id'    => $staging->id,
                ],
                [
                    'object_id'            => $staging->object_id ?? 'STG-' . $staging->id,
                    'area_review'          => $staging->area_review ?? $areaLabel,
                    'kode_unit'            => $staging->kode_unit ?? $wp->kode_unit,
                    'nama_unit'            => $staging->nama_unit ?? $wp->nama_unit,
                    'ra_id'                => $staging->ra_id ?? $wp->ra_pelaksana_id,
                    'nama_ra'              => $staging->nama_ra,
                    'tanggal_data'         => $staging->tanggal_data,
                    'user_maker'           => $staging->user_maker ?? $staging->user_id ?? 'Maker',
                    'nominal'              => $staging->nominal ?? 0,
                    'risk_awal'            => $staging->risk_level ?? 'Low',
                    'exception_awal'       => $staging->exception_awal ?? false,
                    'jenis_exception_awal' => $staging->jenis_exception_awal ?? null,
                    'sampel_low'           => $staging->sampel_low ?? false,
                    'deskripsi_narasi'     => $staging->deskripsi_narasi ?? $staging->deskripsi ?? null,
                    'status_review'        => $staging->status_review ?? 'Belum Review',
                    'catatan_reviewer'     => $staging->catatan_reviewer ?? null,
                ]
            );
        }

        return view('admin-offsite.kka-show', [
            'wp'        => $wp,
            'area'      => $area,
            'areaLabel' => $areaLabel,
            'kka'       => $kka,
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
        // 1. Validasi input catatan reviewer
        $validated = $request->validate([
            'catatan_reviewer' => 'nullable|string|max:2000',
        ]);

        $reviewerId = auth()->id();
        $updatedData = [
            'catatan_reviewer' => $validated['catatan_reviewer'],
            'reviewer_id'      => $reviewerId,
            'updated_at'       => now(),
        ];

        // 2. Cari dan update di tabel StagingOffsite (jika ada)
        $staging = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
            ->where('id', $kkaId)
            ->first();

        if ($staging) {
            $staging->update($updatedData);
        }

        // 3. Cari dan update di tabel KKA Spesifik (misal: kka_teller_kas)
        if (isset($this->kkaModels[$area])) {
            $modelClass = $this->kkaModels[$area];
            $instance = new $modelClass;
            $primaryKey = $instance->getKeyName();

            $modelClass::where('wp_offsite_id', $wp->id)
                ->where(function($q) use ($primaryKey, $kkaId, $staging) {
                    $q->where($primaryKey, $kkaId)
                      ->orWhere('kka_id', $kkaId);
                    if ($staging) {
                        $q->orWhere('staging_id', $staging->id);
                    }
                })
                ->update($updatedData);
        }

        return redirect()->back()->with('updated_success', 'Catatan Reviewer berhasil disimpan!');
    }
}