<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaBiaya;
use Illuminate\Http\Request;

class BiayaController extends Controller
{
    public function index(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaBiaya::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('no_voucher', 'like', "%{$q}%")
                    ->orWhere('keterangan_biaya', 'like', "%{$q}%")
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

        $allBase = OnsiteKkaBiaya::where('onsite_visit_id', $visit->id);
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

        return view('onsite.biaya.index', compact('visit', 'findings', 'summary'));
    }

    public function update(Request $request, OnsiteKkaBiaya $item)
    {
        if ($item->status_review === 'Approved' && !in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA Biaya & Beban sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_ketersediaan_anggaran'       => 'required|string',
            'attr_otorisasi_pengeluaran'       => 'required|string',
            'attr_kelengkapan_dok_pendukung'   => 'required|string',
            'attr_kepatuhan_pajak_pph'         => 'required|string',
            'attr_validitas_perhitungan'       => 'required|string',
            'attr_kewajaran_transaksi'         => 'required|string',
            'attr_pemisahan_periode_akuntansi' => 'required|string',
            'attr_kesesuaian_cbs_gl'           => 'required|string',
            'attr_pencatatan_akurat'           => 'required|string',
            'attr_kelengkapan_berkas'          => 'required|string',
            'skor_dampak'                      => 'required|integer|min:1|max:5',
            'skor_kemungkinan'                 => 'required|integer|min:1|max:5',
            'critical_trigger'                 => 'required|in:Ya,Tidak',
            'simpulan_ra'                      => 'nullable|string',
            'status_review'                    => 'nullable|string',
            'catatan_reviewer'                 => 'nullable|string',
        ]);

        $attributes = [
            $request->attr_ketersediaan_anggaran, $request->attr_otorisasi_pengeluaran, 
            $request->attr_kelengkapan_dok_pendukung, $request->attr_kepatuhan_pajak_pph, 
            $request->attr_validitas_perhitungan, $request->attr_kewajaran_transaksi, 
            $request->attr_pemisahan_periode_akuntansi, $request->attr_kesesuaian_cbs_gl, 
            $request->attr_pencatatan_akurat, $request->attr_kelengkapan_berkas
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
            'attr_ketersediaan_anggaran'       => $request->attr_ketersediaan_anggaran,
            'attr_otorisasi_pengeluaran'       => $request->attr_otorisasi_pengeluaran,
            'attr_kelengkapan_dok_pendukung'   => $request->attr_kelengkapan_dok_pendukung,
            'attr_kepatuhan_pajak_pph'         => $request->attr_kepatuhan_pajak_pph,
            'attr_validitas_perhitungan'       => $request->attr_validitas_perhitungan,
            'attr_kewajaran_transaksi'         => $request->attr_kewajaran_transaksi,
            'attr_pemisahan_periode_akuntansi' => $request->attr_pemisahan_periode_akuntansi,
            'attr_kesesuaian_cbs_gl'           => $request->attr_kesesuaian_cbs_gl,
            'attr_pencatatan_akurat'           => $request->attr_pencatatan_akurat,
            'attr_kelengkapan_berkas'          => $request->attr_kelengkapan_berkas,
            'status_uji'                       => $statusUji,
            'jenis_exception'                  => $request->jenis_exception,
            'kondisi'                          => $request->kondisi,
            'kriteria'                         => $request->kriteria,
            'sebab'                            => $request->sebab,
            'dampak'                           => $request->dampak,
            'skor_dampak'                      => $skorDampak,
            'skor_kemungkinan'                 => $skorKemungkinan,
            'skor_risiko'                      => $skorRisiko,
            'risk_level'                       => $riskLevel,
            'critical_trigger'                 => $request->critical_trigger,
            'simpulan_ra'                      => $request->simpulan_ra,
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
            'message' => 'Pengujian KKA Biaya & Beban berhasil diperbarui.',
            'data' => $item
        ]);
    }
}