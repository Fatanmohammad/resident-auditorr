<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaDpk;
use Illuminate\Http\Request;

class DpkController extends Controller
{
    public function index(Request $request, OnsiteVisit $visit)
    {
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

        return view('onsite.dpk.index', compact('visit', 'findings', 'summary'));
    }

    public function update(Request $request, OnsiteKkaDpk $item)
    {
        if ($item->status_review === 'Approved' && !in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA DPK sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_kyc_cif'              => 'required|string',
            'attr_otorisasi_trx'        => 'required|string',
            'attr_sesuai_bunga_tarif'   => 'required|string',
            'attr_validitas_dokumen'    => 'required|string',
            'attr_kesesuaian_cbs'       => 'required|string',
            'attr_blokir_situs_khusus'  => 'required|string',
            'attr_konfirmasi_nasabah'   => 'required|string',
            'attr_kepatuhan_aml'        => 'required|string',
            'attr_posting_gl'           => 'required|string',
            'attr_kelengkapan_berkas'   => 'required|string',
            'skor_dampak'               => 'required|integer|min:1|max:5',
            'skor_kemungkinan'          => 'required|integer|min:1|max:5',
            'critical_trigger'          => 'required|in:Ya,Tidak',
            'simpulan_ra'               => 'nullable|string',
            'status_review'             => 'nullable|string',
            'catatan_reviewer'          => 'nullable|string',
        ]);

        $attributes = [
            $request->attr_kyc_cif, $request->attr_otorisasi_trx, $request->attr_sesuai_bunga_tarif,
            $request->attr_validitas_dokumen, $request->attr_kesesuaian_cbs, $request->attr_blokir_situs_khusus,
            $request->attr_konfirmasi_nasabah, $request->attr_kepatuhan_aml, $request->attr_posting_gl,
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
            'attr_kyc_cif'              => $request->attr_kyc_cif,
            'attr_otorisasi_trx'        => $request->attr_otorisasi_trx,
            'attr_sesuai_bunga_tarif'   => $request->attr_sesuai_bunga_tarif,
            'attr_validitas_dokumen'    => $request->attr_validitas_dokumen,
            'attr_kesesuaian_cbs'       => $request->attr_kesesuaian_cbs,
            'attr_blokir_situs_khusus'  => $request->attr_blokir_situs_khusus,
            'attr_konfirmasi_nasabah'   => $request->attr_konfirmasi_nasabah,
            'attr_kepatuhan_aml'        => $request->attr_kepatuhan_aml,
            'attr_posting_gl'           => $request->attr_posting_gl,
            'attr_kelengkapan_berkas'   => $request->attr_kelengkapan_berkas,
            'status_uji'                => $statusUji,
            'jenis_exception'           => $request->jenis_exception,
            'kondisi'                   => $request->kondisi,
            'kriteria'                  => $request->kriteria,
            'sebab'                     => $request->sebab,
            'dampak'                    => $request->dampak,
            'skor_dampak'               => $skorDampak,
            'skor_kemungkinan'          => $skorKemungkinan,
            'skor_risiko'               => $skorRisiko,
            'risk_level'                => $riskLevel,
            'critical_trigger'          => $request->critical_trigger,
            'simpulan_ra'               => $request->simpulan_ra,
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
            'message' => 'Pengujian KKA DPK & Layanan berhasil diperbarui.',
            'data' => $item
        ]);
    }
}