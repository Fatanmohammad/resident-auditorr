<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Onsite\Concerns\HandlesKkaChecklistUpdate;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaTellerKas;
use App\Models\DailyRegister\DailyRegisterChecklist;
use Illuminate\Http\Request;

class TellerKasController extends Controller
{
    use HandlesKkaChecklistUpdate;
    /**
     * Tampilkan halaman KKA Teller & Kas untuk kunjungan onsite tertentu
     */
    public function index(Request $request, OnsiteVisit $visit)
    {
        // Auto-sync jika OnsiteKkaFinding ada data tapi OnsiteKkaTellerKas masih kosong
        if (OnsiteKkaTellerKas::where('onsite_visit_id', $visit->id)->count() === 0) {
            $findingsKka = \App\Models\Onsite\OnsiteKkaFinding::where('onsite_visit_id', $visit->id)->get();
            if ($findingsKka->isNotEmpty()) {
                $syncData = [];
                $seenSamples = [];
                foreach ($findingsKka as $f) {
                    $sampleId = $f->sample_id;
                    $cleanTgl = preg_replace('/[^0-9]/', '', substr((string)$f->tgl_tx, 0, 10)) ?: date('Ymd');
                    if (!$sampleId || isset($seenSamples[$sampleId]) || OnsiteKkaTellerKas::where('sample_id', $sampleId)->exists()) {
                        $sampleId = "SMP-ONS-TLR-{$cleanTgl}-V{$visit->id}-F{$f->id}-" . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                    }
                    $seenSamples[$sampleId] = true;

                    $syncData[] = [
                        'onsite_visit_id'       => $visit->id,
                        'onsite_population_id'  => $f->onsite_population_id,
                        'sample_id'             => $sampleId,
                        'tgl_tx'                => substr((string)$f->tgl_tx, 0, 10),
                        'no_arsip'              => $f->no_arsip,
                        'ket_tx'                => $f->ket_tx,
                        'jumlah_tx'             => $f->jumlah_tx,
                        'kd_user'               => $f->kd_user,
                        'jenis_sampling'        => $f->jenis_sampling,
                        'prioritas'             => $f->prioritas ?? 5,
                        'alasan_sampling'       => $f->alasan_sampling,
                        'status_uji'            => $f->hasil_uji ? 'Ada Exception' : 'Belum Diuji',
                        'risk_level'            => $f->risk_level ?? 'Low',
                        'status_review'         => $f->status_review ?? 'Belum Direview',
                        'simpulan_ra'           => $f->simpulan_ra,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }
                foreach (array_chunk($syncData, 500) as $chunk) {
                    OnsiteKkaTellerKas::insert($chunk);
                }
            }
        }

        $query = OnsiteKkaTellerKas::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('ket_tx', 'like', "%{$q}%")
                    ->orWhere('kd_user', 'like', "%{$q}%")
                    ->orWhere('no_arsip', 'like', "%{$q}%");
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

        $allBase = OnsiteKkaTellerKas::where('onsite_visit_id', $visit->id);
        $summary = [
            'total'           => (clone $allBase)->count(),
            'sesuai'          => (clone $allBase)->where('status_uji', 'Sesuai')->count(),
            'exception'       => (clone $allBase)->where('status_uji', 'Ada Exception')->count(),
            'high_risk'       => (clone $allBase)->whereIn('risk_level', ['High', 'Kritis'])->count(),
            'menunggu_review' => (clone $allBase)->where('status_review', 'Menunggu Review')->count(),
            'approved'        => (clone $allBase)->where('status_review', 'Approved')->count(),
            'belum_diuji'     => (clone $allBase)->where(function($q) {
                $q->whereNull('status_uji')->orWhere('status_uji', 'Belum Diuji')->orWhere('status_uji', '');
            })->count(),
        ];

        $findings = $query->orderBy('prioritas')
            ->orderBy('tgl_tx', 'desc')
            ->paginate(25)
            ->withQueryString();

        $checklists = DailyRegisterChecklist::where('source_sheet', 'KKA_Teller_Kas')
            ->orderBy('sort_order')
            ->get();

        return view('onsite.teller_kas.index', compact('visit', 'findings', 'summary', 'checklists'));
    }

    /**
     * Update hasil pengujian checklist Teller/Kas dan hitung risiko otomatis
     */
    public function update(Request $request, OnsiteKkaTellerKas $item)
    {
        return $this->processKkaUpdate($request, $item, 'Teller & Kas');
    }

    /**
     * Review KKA oleh Atasan (Admin/Kabag/Kadiv)
     */
    public function review(Request $request, OnsiteKkaTellerKas $item)
    {
        return $this->processKkaReview($request, $item, 'Teller & Kas');
    }
}