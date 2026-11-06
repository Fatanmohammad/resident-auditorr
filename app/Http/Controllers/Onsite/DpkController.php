<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Onsite\Concerns\HandlesKkaChecklistUpdate;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaDpk;
use App\Models\DailyRegister\DailyRegisterChecklist;
use Illuminate\Http\Request;

class DpkController extends Controller
{
    use HandlesKkaChecklistUpdate;

    public function index(Request $request, OnsiteVisit $visit)
    {
        // Auto-distribusi sampel dari populasi CBS jika KKA DPK belum memiliki data sampel
        if (OnsiteKkaDpk::where('onsite_visit_id', $visit->id)->count() === 0) {
            app(\App\Services\Onsite\CbsOnsiteParser::class)->distributePopulationsToAllKka($visit);
        }

        $query = OnsiteKkaDpk::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('no_rekening', 'like', "%{$q}%")
                    ->orWhere('nama_nasabah', 'like', "%{$q}%")
                    ->orWhere('alasan_sampling', 'like', "%{$q}%");
            });
        }

        if ($request->filled('jenis_sampling')) {
            $query->where('jenis_sampling', $request->jenis_sampling);
        }

        if ($request->filled('status_uji')) {
            $query->where('status_uji', $request->status_uji);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('status_review')) {
            $query->where('status_review', $request->status_review);
        }

        $allBase = OnsiteKkaDpk::where('onsite_visit_id', $visit->id);
        $summary = [
            'total'       => (clone $allBase)->count(),
            'sesuai'      => (clone $allBase)->where('status_uji', 'Sesuai')->count(),
            'exception'   => (clone $allBase)->where('status_uji', 'Ada Exception')->count(),
            'high_risk'   => (clone $allBase)->whereIn('risk_level', ['High', 'Kritis'])->count(),
            'approved'    => (clone $allBase)->where('status_review', 'Approved')->count(),
            'belum_diuji' => (clone $allBase)->where(function($q) {
                $q->whereNull('status_uji')->orWhere('status_uji', 'Belum Diuji')->orWhere('status_uji', '');
            })->count(),
        ];

        $findings = $query->orderBy('prioritas')
            ->orderBy('tgl_tx', 'desc')
            ->paginate(25)
            ->withQueryString();

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_DPK_Layanan')
            ->orderBy('sort_order')
            ->get();

        return view('onsite.dpk.index', compact('visit', 'findings', 'summary', 'checklists'));
    }

    public function update(Request $request, OnsiteKkaDpk $item)
    {
        return $this->processKkaUpdate($request, $item, 'DPK & Layanan');
    }

    public function review(Request $request, OnsiteKkaDpk $item)
    {
        return $this->processKkaReview($request, $item, 'DPK & Layanan');
    }
}
