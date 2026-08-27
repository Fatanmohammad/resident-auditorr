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
     * PERBAIKAN: route ini sebelumnya menunjuk ke method yang belum ada
     * (akan error kalau diklik). Untuk sekarang, ini menjalankan ulang
     * pipeline Dump -> Staging -> Register -> KKA untuk WP aktif unit ini.
     *
     * CATATAN: ini BELUM termasuk upload file Excel/CSV berisi data Dump.
     * Data di tabel dump_* masih perlu diisi lewat cara lain (seeder/manual/
     * fitur import terpisah) sebelum tombol ini berguna.
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
     * PERBAIKAN: route 'admin-offsite.update-status' menunjuk ke method ini,
     * tapi sebelumnya belum ditulis sama sekali (akan error kalau diklik).
     * Ini mengubah status WP (Draft/Aktif/Final) untuk unit & periode terkait.
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

        // Ambil register harian dengan proteksi aman
        $registerQuery = method_exists($wp, 'registerHarian') ? $wp->registerHarian() : null;

        $rows = $registerQuery 
            ? $registerQuery->orderBy('tanggal_data')->orderBy('area_review')->get()->groupBy(fn($r) => optional($r->tanggal_data)->format('Y-m-d') ?? 'N/A')
            : collect();

        $ringkasan = [
            'populasi'    => $registerQuery ? $registerQuery->sum('populasi_eligible') : 0,
            'sampel_low'  => $registerQuery ? $registerQuery->sum('sampel_low') : 0,
            'kka_final'   => $registerQuery ? $registerQuery->sum('kka_final') : 0,
            'exception'   => $registerQuery ? $registerQuery->sum('exception') : 0,
            'klarifikasi' => $registerQuery ? $registerQuery->sum('perlu_klarifikasi') : 0,
            'eskalasi'    => $registerQuery ? $registerQuery->sum('perlu_eskalasi') : 0,
        ];

        return view('admin-offsite.unit-detail', compact('unit', 'wp', 'rows', 'ringkasan', 'tahun', 'bulan'));
    }

    /**
     * Daftar KKA per area untuk 1 WP
     */
    public function kkaIndex(WpOffsite $wp, string $area)
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
}