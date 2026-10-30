<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaAtm;
use Illuminate\Http\Request;

class AtmController extends Controller
{
    public function index(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaAtm::where('onsite_visit_id', $visit->id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('sample_id', 'like', "%{$q}%")
                    ->orWhere('no_rekening', 'like', "%{$q}%")
                    ->orWhere('nomor_kartu', 'like', "%{$q}%")
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

        $allBase = OnsiteKkaAtm::where('onsite_visit_id', $visit->id);
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
            ->orderBy('tgl_transaksi', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('onsite.atm.index', compact('visit', 'findings', 'summary'));
    }

    public function update(Request $request, OnsiteKkaAtm $item)
    {
        if ($item->status_review === 'Approved' && !in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'kabag_ra', 'kadiv_skai'])) {
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
            'status_review'                    => 'nullable|string',
            'catatan_reviewer'                 => 'nullable|string',
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

        $dataUpdate = [
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
            'message' => 'Pengujian KKA Kartu ATM berhasil diperbarui.',
            'data' => $item
        ]);
    }
}