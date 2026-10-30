<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaTellerKas;
use Illuminate\Http\Request;

class TellerKasController extends Controller
{
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
                foreach ($findingsKka as $f) {
                    $syncData[] = [
                        'onsite_visit_id'       => $visit->id,
                        'onsite_population_id'  => $f->onsite_population_id,
                        'sample_id'             => $f->sample_id,
                        'tgl_tx'                => $f->tgl_tx,
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

        return view('onsite.teller_kas.index', compact('visit', 'findings', 'summary'));
    }

    /**
     * Update hasil pengujian 10 atribut Teller/Kas dan hitung risiko otomatis
     */
    public function update(Request $request, OnsiteKkaTellerKas $item)
    {
        if ($item->status_review === 'Approved') {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA Teller & Kas sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_bukti_asli'        => 'required|string',
            'attr_sesuai_cbs'        => 'required|string',
            'attr_otorisasi'         => 'required|string',
            'attr_identitas'         => 'required|string',
            'attr_slip_denominasi'   => 'required|string',
            'attr_cutoff'            => 'required|string',
            'attr_override_reversal' => 'required|string',
            'attr_tunai_besar'       => 'required|string',
            'attr_posting_gl'        => 'required|string',
            'attr_dokumentasi'       => 'required|string',
            'status_uji'             => 'required|string',
            'jenis_exception'        => 'nullable|string',
            'kondisi'                => 'nullable|string',
            'kriteria'               => 'nullable|string',
            'sebab'                  => 'nullable|string',
            'dampak'                 => 'nullable|string',
            'skor_dampak'            => 'required|integer|min:1|max:5',
            'skor_kemungkinan'       => 'required|integer|min:1|max:5',
            'critical_trigger'       => 'required|in:Ya,Tidak',
            'simpulan_ra'            => 'nullable|string',
        ]);

        // Hitung skor risiko otomatis dengan operator perkalian yang benar (*)
        $skorDampak = (int) $request->skor_dampak;
        $skorKemungkinan = (int) $request->skor_kemungkinan;
        $skorRisiko = $skorDampak * $skorKemungkinan;

        // Tentukan tingkat risiko & critical trigger
        $riskLevel = 'Low';
        if ($request->critical_trigger === 'Ya' || $skorRisiko >= 20) {
            $riskLevel = 'Kritis';
        } elseif ($skorRisiko >= 12) {
            $riskLevel = 'High';
        } elseif ($skorRisiko >= 6) {
            $riskLevel = 'Moderate';
        }

        $item->update([
            'attr_bukti_asli'        => $request->attr_bukti_asli,
            'attr_sesuai_cbs'        => $request->attr_sesuai_cbs,
            'attr_otorisasi'         => $request->attr_otorisasi,
            'attr_identitas'         => $request->attr_identitas,
            'attr_slip_denominasi'   => $request->attr_slip_denominasi,
            'attr_cutoff'            => $request->attr_cutoff,
            'attr_override_reversal' => $request->attr_override_reversal,
            'attr_tunai_besar'       => $request->attr_tunai_besar,
            'attr_posting_gl'        => $request->attr_posting_gl,
            'attr_dokumentasi'       => $request->attr_dokumentasi,
            'status_uji'             => $request->status_uji,
            'jenis_exception'        => $request->jenis_exception,
            'kondisi'                => $request->kondisi,
            'kriteria'               => $request->kriteria,
            'sebab'                  => $request->sebab,
            'dampak'                 => $request->dampak,
            'skor_dampak'            => $skorDampak,
            'skor_kemungkinan'       => $skorKemungkinan,
            'skor_risiko'            => $skorRisiko,
            'risk_level'             => $riskLevel,
            'critical_trigger'       => $request->critical_trigger,
            'simpulan_ra'            => $request->simpulan_ra,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengujian KKA Teller & Kas berhasil diperbarui.',
            'data' => $item
        ]);
    }

    /**
     * Review KKA oleh Atasan (Admin/Kabag/Kadiv)
     */
    public function review(Request $request, OnsiteKkaTellerKas $item)
    {
        $request->validate([
            'status_review'    => 'required|in:Belum Direview,Revisi,Approved',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $item->update($request->only(['status_review', 'catatan_reviewer']));

        return response()->json([
            'status' => 'success',
            'message' => 'Status review berhasil disimpan.',
            'data' => $item
        ]);
    }
}