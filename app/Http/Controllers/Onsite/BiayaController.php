<?php

namespace App\Models\Onsite; // Perhatikan namespace controller dibawah
namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaBiaya;
use Illuminate\Http\Request;

class BiayaController extends Controller
{
    public function index(OnsiteVisit $visit)
    {
        $findings = OnsiteKkaBiaya::where('onsite_visit_id', $visit->id)
            ->orderBy('prioritas')
            ->orderBy('tgl_tx')
            ->paginate(25);

        return view('onsite.biaya.index', compact('visit', 'findings'));
    }

    public function update(Request $request, OnsiteKkaBiaya $item)
    {
        if ($item->status_review === 'Approved') {
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

        $item->update([
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
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengujian KKA Biaya & Beban berhasil diperbarui.',
            'data' => $item
        ]);
    }
}