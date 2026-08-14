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
        $wps = WpOffsite::with(['unit', 'ra'])
            ->orderByDesc('tahun')->orderByDesc('bulan')
            ->paginate(20);
        return view('offsite-review.index', compact('wps'));
    }

    // Dashboard utama satu WP
    public function dashboard(WpOffsite $wp)
    {
        $wp->load(['unit.cabang', 'ra']);

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
            'unit_id'      => 'required|exists:units,id',
            'ra_id'        => 'nullable|exists:ras,id',
            'tahun'        => 'required|integer|min:2020|max:2099',
            'bulan'        => 'required|integer|min:1|max:12',
            'reviewer'     => 'nullable|string|max:100',
            'validasi_unit'=> 'boolean',
        ]);

        $bulanLabel = \Carbon\Carbon::create($validated['tahun'], $validated['bulan'])->isoFormat('MMMM Y');
        $unit       = Unit::find($validated['unit_id']);
        $kodeWp     = 'WP-OFF-' . $unit->unit_code . '-' . $validated['tahun'] . str_pad($validated['bulan'], 2, '0', STR_PAD_LEFT);

        $wp = WpOffsite::create(array_merge($validated, [
            'kode_wp'       => $kodeWp,
            'periode_data'  => $bulanLabel,
            'validasi_unit' => $request->boolean('validasi_unit'),
        ]));

        return redirect()->route('offsite-review.dashboard', $wp)->with('success', 'WP Offsite berhasil dibuat.');
    }

    // Update status WP
    public function updateStatus(Request $request, WpOffsite $wp)
    {
        $request->validate(['status_wp' => 'required|in:Draft,In Review,Final,Approved']);
        $wp->update(['status_wp' => $request->status_wp]);
        return back()->with('success', 'Status WP diperbarui.');
    }
}
