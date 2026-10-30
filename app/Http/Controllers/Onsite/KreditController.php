<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaKredit;
use Illuminate\Http\Request;

class KreditController extends Controller
{
    public function index(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaKredit::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('no_rekening', 'like', "%{$q}%")
                    ->orWhere('nama_debitur', 'like', "%{$q}%")
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

        $allBase = OnsiteKkaKredit::where('onsite_visit_id', $visit->id);
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
            ->orderBy('tgl_akad', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('onsite.kredit.index', compact('visit', 'findings', 'summary'));
    }

    public function update(Request $request, OnsiteKkaKredit $item)
    {
        if ($item->status_review === 'Approved' && !in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA Kredit sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_analisa_kelayakan'       => 'required|string',
            'attr_otorisasi_kredit'        => 'required|string',
            'attr_keabsahan_agunan'        => 'required|string',
            'attr_pengikatan_hukum'        => 'required|string',
            'attr_asuransi_kredit'         => 'required|string',
            'attr_kepatuhan_kolektibilitas'=> 'required|string',
            'attr_pemenuhan_syarat_cair'   => 'required|string',
            'attr_kesesuaian_baki_debet'   => 'required|string',
            'attr_posting_gl'              => 'required|string',
            'attr_kelengkapan_berkas'      => 'required|string',
            'skor_dampak'                  => 'required|integer|min:1|max:5',
            'skor_kemungkinan'             => 'required|integer|min:1|max:5',
            'critical_trigger'             => 'required|in:Ya,Tidak',
            'simpulan_ra'                  => 'nullable|string',
            'status_review'                => 'nullable|string',
            'catatan_reviewer'             => 'nullable|string',
        ]);

        $attributes = [
            $request->attr_analisa_kelayakan, $request->attr_otorisasi_kredit, $request->attr_keabsahan_agunan,
            $request->attr_pengikatan_hukum, $request->attr_asuransi_kredit, $request->attr_kepatuhan_kolektibilitas,
            $request->attr_pemenuhan_syarat_cair, $request->attr_kesesuaian_baki_debet, $request->attr_posting_gl,
            $request->attr_kelengkapan_berkas
        ];

        $statusUji = in_array('Tidak Sesuai', $attributes) ? 'Ada Exception' : 'Sesuai';

        $skorDampak = (int) $request->skor_dampak;
        $skorKemungkinan = (int) $request->skor_kemungkinan;
        $skorRisiko = $skorDampak * $skorKemungkinan;

        $riskLevel = 'Low';
        if ($request->critical_trigger === 'Ya' || $skorRisiko >= 20) {
            $riskLevel = 'Kritis';
        } elseif ($skorRisiko >= 12) {
            $riskLevel = 'High';
        } elseif ($skorRisiko >= 6) {
            $riskLevel = 'Moderate';
        }

        $dataUpdate = [
            'attr_analisa_kelayakan'       => $request->attr_analisa_kelayakan,
            'attr_otorisasi_kredit'        => $request->attr_otorisasi_kredit,
            'attr_keabsahan_agunan'        => $request->attr_keabsahan_agunan,
            'attr_pengikatan_hukum'        => $request->attr_pengikatan_hukum,
            'attr_asuransi_kredit'         => $request->attr_asuransi_kredit,
            'attr_kepatuhan_kolektibilitas'=> $request->attr_kepatuhan_kolektibilitas,
            'attr_pemenuhan_syarat_cair'   => $request->attr_pemenuhan_syarat_cair,
            'attr_kesesuaian_baki_debet'   => $request->attr_kesesuaian_baki_debet,
            'attr_posting_gl'              => $request->attr_posting_gl,
            'attr_kelengkapan_berkas'      => $request->attr_kelengkapan_berkas,
            'status_uji'                   => $statusUji,
            'jenis_exception'              => $request->jenis_exception,
            'kondisi'                      => $request->kondisi,
            'kriteria'                     => $request->kriteria,
            'sebab'                        => $request->sebab,
            'dampak'                       => $request->dampak,
            'skor_dampak'                  => $skorDampak,
            'skor_kemungkinan'             => $skorKemungkinan,
            'skor_risiko'                  => $skorRisiko,
            'risk_level'                   => $riskLevel,
            'critical_trigger'             => $request->critical_trigger,
            'simpulan_ra'                  => $request->simpulan_ra,
        ];

        if ($request->filled('status_review')) {
            $dataUpdate['status_review'] = $request->status_review;
        }
        if ($request->has('catatan_reviewer')) {
            $dataUpdate['catatan_reviewer'] = $request->catatan_reviewer;
        }

        $item->update($dataUpdate);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengujian KKA Kredit berhasil diperbarui.',
            'data' => $item
        ]);
    }
}