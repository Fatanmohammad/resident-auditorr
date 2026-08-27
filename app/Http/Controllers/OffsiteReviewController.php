<?php

namespace App\Http\Controllers;

use App\Models\WpOffsite;
use App\Models\Unit;
use App\Models\User;
use App\Services\OffsiteReviewService;
use App\Services\OffsiteGenerationService;
use Illuminate\Http\Request;

class OffsiteReviewController extends Controller
{
    public function __construct(
        private OffsiteReviewService $service,
        private OffsiteGenerationService $generation,
    ) {}

    // Daftar semua WP Offsite
    public function index()
    {
        $wps = WpOffsite::with(['unit', 'raPelaksana'])
            ->orderByDesc('periode_mulai')
            ->paginate(20);
        return view('offsite-review.index', compact('wps'));
    }

    // Dashboard utama satu WP
    public function dashboard(WpOffsite $wp)
    {
        $wp->load(['unit.cabang', 'raPelaksana', 'reviewerBagianRa']);


        $stats        = $this->service->statCards($wp);
        $areaRows     = $this->service->ringkasanPerArea($wp);
        $rekonsiliasi = $this->service->rekonsiliasi($wp);
        $distribusiKka= $this->service->distribusiKka($wp);
        $kontrol      = $this->service->kontrolKesiapan($wp);

        return view('offsite-review.dashboard', compact(
            'wp', 'stats', 'areaRows', 'rekonsiliasi', 'distribusiKka', 'kontrol'
        ));
    }

    // Form buat WP baru
    public function create()
    {
        $units     = Unit::where('is_active', true)->orderBy('unit_name')->get();
        $ras       = User::where('role', 'ra')->orderBy('name')->get();
        $reviewers = User::where('role', 'kabag_ra')->orderBy('name')->get();
        return view('offsite-review.create', compact('units', 'ras', 'reviewers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'               => 'required|exists:units,id',
            'ra_pelaksana_id'       => 'required|exists:users,id',
            'reviewer_bagian_ra_id' => 'required|exists:users,id',
            'tahun'                 => 'required|integer|min:2020|max:2099',
            'bulan'                 => 'required|integer|min:1|max:12',
        ]);

        $unit = Unit::find($validated['unit_id']);
        $tahun = $validated['tahun'];

        $bulan = $validated['bulan'];
        $kodeWp = 'WP-OFF-' . $unit->unit_code . '-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $periodeMulai  = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodeSelesai = $periodeMulai->copy()->endOfMonth();

        $wp = WpOffsite::create([
            'kode_wp'               => $kodeWp,
            'unit_id'               => $validated['unit_id'],
            'kode_unit'             => $unit->unit_code,
            'nama_unit'             => $unit->unit_name,
            'jenis_unit'            => $unit->unit_type,
            'kantor_induk'          => $unit->parent_office,
            'periode_mulai'         => $periodeMulai,
            'periode_selesai'       => $periodeSelesai,
            'ra_pelaksana_id'       => $validated['ra_pelaksana_id'],
            'reviewer_bagian_ra_id' => $validated['reviewer_bagian_ra_id'],
            'status_wp'             => 'Draft',
            'scope_wp'              => '1 UNIT / 1 PERIODE',
            'validasi_unit'         => 'VALID',
        ]);

        // Jalankan pipeline Dump -> Staging -> Register -> KKA untuk WP yang baru dibuat.
        // Kalau data Dump untuk unit/periode ini belum ada, hasilnya cuma 0 baris
        // (bukan error) — RA/Admin bisa klik "Generate Ulang" nanti setelah Dump diisi.
        $hasil = $this->generation->generate($wp);

        $pesan = $hasil['total_diproses'] > 0
            ? "WP Offsite berhasil dibuat. {$hasil['total_diproses']} baris data diproses, {$hasil['total_masuk_kka']} masuk KKA."
            : 'WP Offsite berhasil dibuat, tapi belum ada data Dump untuk unit & periode ini. Isi data Dump lalu klik "Generate Ulang".';

        return redirect()->route('offsite-review.dashboard', $wp)->with('success', $pesan);

    }

    /**
     * Jalankan ulang pipeline Dump -> Staging -> Register -> KKA untuk WP ini.
     * Berguna kalau data Dump baru ditambahkan/diubah setelah WP dibuat.
     */
    public function refresh(WpOffsite $wp)
    {
        $hasil = $this->generation->generate($wp);

        $pesan = $hasil['total_diproses'] > 0
            ? "Data berhasil digenerate ulang. {$hasil['total_diproses']} baris diproses, {$hasil['total_masuk_kka']} masuk KKA."
            : 'Belum ada data Dump untuk unit & periode WP ini.';

        return redirect()->route('offsite-review.dashboard', $wp)->with('success', $pesan);
    }

    // Update status WP (sesuai migration enum: Draft, Aktif, Final)
    public function updateStatus(Request $request, WpOffsite $wp)
    {
        $request->validate(['status_wp' => 'required|in:Draft,Aktif,Final']);
        $wp->update(['status_wp' => $request->status_wp]);
        return back()->with('success', 'Status WP diperbarui.');
    }
}