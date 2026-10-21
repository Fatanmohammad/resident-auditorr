<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaPengaduan;
use Illuminate\Http\Request;

class PengaduanController extends Controller
{
    public function index(OnsiteVisit $visit)
    {
        $findings = OnsiteKkaPengaduan::where('onsite_visit_id', $visit->id)
            ->orderBy('prioritas')
            ->orderBy('tgl_pengaduan')
            ->paginate(25);

        return view('onsite.pengaduan.index', compact('visit', 'findings'));
    }

    public function update(Request $request, OnsiteKkaPengaduan $item)
    {
        if ($item->status_review === 'Approved') {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA Pengaduan sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_pencatatan_register'            => 'required|string',
            'attr_pemenuhan_sla'                  => 'required|string',
            'attr_validitas_investigasi'          => 'required|string',
            'attr_otorisasi_pemberian_ganti_rugi' => 'required|string',
            'attr_penyampaian_tanggapan_nasabah'  => 'required|string',
            'attr_pelaporan_ojk_laps'             => 'required|string',
            'attr_kesesuaian_posting_ganti_rugi'  => 'required|string',
            'attr_kerahasiaan_data_nasabah'       => 'required|string',
            'attr_tindak_lanjut_perbaikan'        => 'required|string',
            'attr_kelengkapan_berkas'             => 'required|string',
            'skor_dampak'                         => 'required|integer|min:1|max:5',
            'skor_kemungkinan'                    => 'required|integer|min:1|max:5',
            'critical_trigger'                    => 'required|in:Ya,Tidak',
            'simpulan_ra'                         => 'nullable|string',
        ]);

        $attributes = [
            $request->attr_pencatatan_register, $request->attr_pemenuhan_sla, 
            $request->attr_validitas_investigasi, $request->attr_otorisasi_pemberian_ganti_rugi, 
            $request->attr_penyampaian_tanggapan_nasabah, $request->attr_pelaporan_ojk_laps, 
            $request->attr_kesesuaian_posting_ganti_rugi, $request->attr_kerahasiaan_data_nasabah, 
            $request->attr_tindak_lanjut_perbaikan, $request->attr_kelengkapan_berkas
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
            'attr_pencatatan_register'            => $request->attr_pencatatan_register,
            'attr_pemenuhan_sla'                  => $request->attr_pemenuhan_sla,
            'attr_validitas_investigasi'          => $request->attr_validitas_investigasi,
            'attr_otorisasi_pemberian_ganti_rugi' => $request->attr_otorisasi_pemberian_ganti_rugi,
            'attr_penyampaian_tanggapan_nasabah'  => $request->attr_penyampaian_tanggapan_nasabah,
            'attr_pelaporan_ojk_laps'             => $request->attr_pelaporan_ojk_laps,
            'attr_kesesuaian_posting_ganti_rugi'  => $request->attr_kesesuaian_posting_ganti_rugi,
            'attr_kerahasiaan_data_nasabah'       => $request->attr_kerahasiaan_data_nasabah,
            'attr_tindak_lanjut_perbaikan'        => $request->attr_tindak_lanjut_perbaikan,
            'attr_kelengkapan_berkas'             => $request->attr_kelengkapan_berkas,
            'status_uji'                          => $statusUji,
            'jenis_exception'                     => $request->jenis_exception,
            'kondisi'                             => $request->kondisi,
            'kriteria'                            => $request->kriteria,
            'sebab'                               => $request->sebab,
            'dampak'                              => $request->dampak,
            'skor_dampak'                         => $skorDampak,
            'skor_kemungkinan'                    => $skorKemungkinan,
            'skor_risiko'                         => $skorRisiko,
            'risk_level'                          => $riskLevel,
            'critical_trigger'                    => $request->critical_trigger,
            'simpulan_ra'                         => $request->simpulan_ra,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengujian KKA Pengaduan Nasabah berhasil diperbarui.',
            'data' => $item
        ]);
    }
}