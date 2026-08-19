<?php

namespace App\Http\Controllers;

use App\Models\WpOffsite;
use App\Models\Unit;
use App\Models\Ra;
use App\Services\OffsiteReviewService;
use Illuminate\Http\Request;

class OffsiteReviewController extends Controller
{
    public function __construct(private OffsiteReviewService $service) {}

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
        $units = Unit::where('is_active', true)->orderBy('unit_name')->get();
        $ras   = Ra::where('status', 'Aktif')->orderBy('ra_name')->get();
        return view('offsite-review.create', compact('units', 'ras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'               => 'required|exists:units,id',
            'ra_pelaksana_id'       => 'required|exists:users,id',
            'reviewer_bagian_ra_id' => 'nullable|exists:users,id',
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
            'reviewer_bagian_ra_id' => $validated['reviewer_bagian_ra_id'] ?? null,
            'status_wp'             => 'Draft',
            'scope_wp'              => '1 UNIT / 1 PERIODE',
            'validasi_unit'         => 'VALID',
        ]);

        return redirect()->route('offsite-review.dashboard', $wp)->with('success', 'WP Offsite berhasil dibuat.');
    }

    // Update status WP (sesuai migration enum: Draft, Aktif, Final)
    public function updateStatus(Request $request, WpOffsite $wp)
    {
        $request->validate(['status_wp' => 'required|in:Draft,Aktif,Final']);
        $wp->update(['status_wp' => $request->status_wp]);
        return back()->with('success', 'Status WP diperbarui.');
    }
}
