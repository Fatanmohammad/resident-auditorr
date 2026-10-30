<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaPengaduan;
use Illuminate\Http\Request;

class PengaduanController extends Controller
{
    public function index(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaPengaduan::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('no_tiket', 'like', "%{$q}%")
                    ->orWhere('nama_nasabah', 'like', "%{$q}%")
                    ->orWhere('kategori_pengaduan', 'like', "%{$q}%")
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

        $allBase = OnsiteKkaPengaduan::where('onsite_visit_id', $visit->id);
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
            ->orderBy('tgl_pengaduan', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('onsite.pengaduan.index', compact('visit', 'findings', 'summary'));
    }

    public function update(Request $request, OnsiteKkaPengaduan $item)
    {
        if ($item->status_review === 'Approved' && !in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'kabag_ra', 'kadiv_skai'])) {
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
            'status_review'                       => 'nullable|string',
            'catatan_reviewer'                    => 'nullable|string',
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

        $dataUpdate = [
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
            'message' => 'Pengujian KKA Pengaduan Nasabah berhasil diperbarui.',
            'data' => $item
        ]);
    }
}