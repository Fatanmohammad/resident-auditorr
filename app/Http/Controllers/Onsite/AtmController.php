<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaAtm;
use Illuminate\Http\Request;

class AtmController extends Controller
{
    public function index(OnsiteVisit $visit)
    {
        $findings = OnsiteKkaAtm::where('onsite_visit_id', $visit->id)
            ->orderBy('prioritas')
            ->orderBy('tgl_transaksi')
            ->paginate(25);

        return view('onsite.atm.index', compact('visit', 'findings'));
    }

    public function update(Request $request, OnsiteKkaAtm $item)
    {
        if ($item->status_review === 'Approved') {
            return response()->json([
                'status' => 'error', 
                'message' => 'KKA Kartu ATM sudah Approved dan tidak dapat diubah.'
            ], 403);
        }

        $request->validate([
            'attr_kepatuhan_permintaan_kartu'  => 'required|string',
            'attr_otorisasi_pengeluaran'       => 'required|string',
            'attr_kesesuaian_fisik_sistem'     => 'required|string',
            'attr_validitas_berita_acara'      => 'required|string',
            'attr_pengamanan_pin_mailer'       => 'required|string',
            'attr_penatausahaan_stok'          => 'required|string',
            'attr_pemusnahan_kartu_retensi'    => 'required|string',
            'attr_kepatuhan_blokir'            => 'required|string',
            'attr_pencatatan_biaya_adm'        => 'required|string',
            'attr_kelengkapan_berkas'          => 'required|string',
            'skor_dampak'                      => 'required|integer|min:1|max:5',
            'skor_kemungkinan'                 => 'required|integer|min:1|max:5',
            'critical_trigger'                 => 'required|in:Ya,Tidak',
            'simpulan_ra'                      => 'nullable|string',
        ]);

        $attributes = [
            $request->attr_kepatuhan_permintaan_kartu, $request->attr_otorisasi_pengeluaran, 
            $request->attr_kesesuaian_fisik_sistem, $request->attr_validitas_berita_acara, 
            $request->attr_pengamanan_pin_mailer, $request->attr_penatausahaan_stok, 
            $request->attr_pemusnahan_kartu_retensi, $request->attr_kepatuhan_blokir, 
            $request->attr_pencatatan_biaya_adm, $request->attr_kelengkapan_berkas
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
            'attr_kepatuhan_permintaan_kartu'  => $request->attr_kepatuhan_permintaan_kartu,
            'attr_otorisasi_pengeluaran'       => $request->attr_otorisasi_pengeluaran,
            'attr_kesesuaian_fisik_sistem'     => $request->attr_kesesuaian_fisik_sistem,
            'attr_validitas_berita_acara'      => $request->attr_validitas_berita_acara,
            'attr_pengamanan_pin_mailer'       => $request->attr_pengamanan_pin_mailer,
            'attr_penatausahaan_stok'          => $request->attr_penatausahaan_stok,
            'attr_pemusnahan_kartu_retensi'    => $request->attr_pemusnahan_kartu_retensi,
            'attr_kepatuhan_blokir'            => $request->attr_kepatuhan_blokir,
            'attr_pencatatan_biaya_adm'        => $request->attr_pencatatan_biaya_adm,
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
            'message' => 'Pengujian KKA Kartu ATM berhasil diperbarui.',
            'data' => $item
        ]);
    }
}