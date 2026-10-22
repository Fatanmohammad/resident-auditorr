<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteRekonBarangCetakan;
use App\Models\Onsite\OnsiteRekonBarangCetakanItem;
use App\Models\Onsite\OnsiteRekonCekBg;
use App\Models\Onsite\OnsiteRekonCekBgItem;
use App\Services\Onsite\RekonTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekonController extends Controller
{
    // =========================================================
    // REKON BARANG CETAKAN
    // =========================================================

    public function createBct(OnsiteVisit $visit)
    {
        $rekon = OnsiteRekonBarangCetakan::where('onsite_visit_id', $visit->id)->first();
        return view('onsite.rekon.bct_create', compact('visit', 'rekon'));
    }

    public function storeBct(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'ra_pelaksana'   => 'required|string',
            'tanggal_opname' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $rekon = OnsiteRekonBarangCetakan::updateOrCreate(
                ['onsite_visit_id' => $visit->id],
                [
                    'kode_unit'      => $visit->kode_unit,
                    'ra_pelaksana'   => $request->ra_pelaksana,
                    'tanggal_opname' => $request->tanggal_opname,
                ]
            );

            // Hapus dan generate ulang items
            $rekon->items()->delete();
            $items = RekonTemplateService::generateBarangCetakan(
                $visit->kode_unit,
                $request->tanggal_opname,
                $rekon->id
            );
            DB::table('onsite_rekon_barang_cetakan_items')->insert($items);

            DB::commit();
            return redirect()->route('onsite.rekon.bct.show', [$visit, $rekon])
                ->with('success', 'Checklist Rekon Barang Cetakan berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function showBct(OnsiteVisit $visit, OnsiteRekonBarangCetakan $rekon)
    {
        $items = $rekon->items()->orderBy('id')->get();
        return view('onsite.rekon.bct_show', compact('visit', 'rekon', 'items'));
    }

    public function updateBctItem(Request $request, OnsiteRekonBarangCetakanItem $item)
    {
        if ($item->rekon->status === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved.'], 403);
        }

        $request->validate([
            'stok_laporan'  => 'nullable|integer',
            'hasil_fisik'   => 'nullable|integer',
            'kondisi'       => 'nullable|in:Baik,Rusak,Outdated,N/A',
            'impact'        => 'nullable|integer|min:1|max:5',
            'likelihood'    => 'nullable|integer|min:1|max:5',
            'referensi_bukti' => 'nullable|string',
            'simpulan_ra'   => 'nullable|string',
            'tanggal_uji'   => 'nullable|date',
            'status_review' => 'nullable|in:Belum Diuji,Sesuai,Tidak Sesuai,Tidak Berlaku',
        ]);

        $data = $request->only([
            'stok_laporan', 'hasil_fisik', 'kondisi', 'kriteria_tidak_dipenuhi',
            'penyebab', 'dampak', 'impact', 'likelihood',
            'referensi_bukti', 'simpulan_ra', 'tanggal_uji', 'status_review',
        ]);

        // Kosongkan kolom exception jika Sesuai/Tidak Berlaku
        if (in_array($request->status_review, ['Sesuai', 'Tidak Berlaku'])) {
            $data['kriteria_tidak_dipenuhi'] = null;
            $data['penyebab']  = null;
            $data['dampak']    = null;
            $data['impact']    = null;
            $data['likelihood']= null;
            $data['risk_level']= null;
        } elseif ($request->status_review === 'Tidak Sesuai') {
            $score = ($request->impact ?? 1) * ($request->likelihood ?? 1);
            $data['risk_level'] = $score >= 20 ? 'Critical' : ($score >= 12 ? 'High' : ($score >= 6 ? 'Moderate' : 'Low'));
        }

        $item->update($data);
        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function reviewBctItem(Request $request, OnsiteRekonBarangCetakanItem $item)
    {
        $request->validate([
            'status_review'    => 'required|in:Belum Diuji,Sesuai,Tidak Sesuai,Tidak Berlaku',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $item->update([
            'status_review'    => $request->status_review,
            'catatan_reviewer' => $request->catatan_reviewer,
            'reviewer'         => auth()->user()->name,
        ]);

        return response()->json(['status' => 'success', 'data' => $item]);
    }

    // =========================================================
    // REKON CEK & BG
    // =========================================================

    public function createCekBg(OnsiteVisit $visit)
    {
        $rekon = OnsiteRekonCekBg::where('onsite_visit_id', $visit->id)->first();
        return view('onsite.rekon.cekbg_create', compact('visit', 'rekon'));
    }

    public function storeCekBg(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'ra_pelaksana'   => 'required|string',
            'tanggal_opname' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $rekon = OnsiteRekonCekBg::updateOrCreate(
                ['onsite_visit_id' => $visit->id],
                [
                    'kode_unit'      => $visit->kode_unit,
                    'ra_pelaksana'   => $request->ra_pelaksana,
                    'tanggal_opname' => $request->tanggal_opname,
                ]
            );

            $rekon->items()->delete();
            $items = RekonTemplateService::generateCekBg(
                $visit->kode_unit,
                $request->tanggal_opname,
                $rekon->id
            );
            DB::table('onsite_rekon_cek_bg_items')->insert($items);

            DB::commit();
            return redirect()->route('onsite.rekon.cekbg.show', [$visit, $rekon])
                ->with('success', 'Checklist Rekon Cek & BG berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function showCekBg(OnsiteVisit $visit, OnsiteRekonCekBg $rekon)
    {
        $items = $rekon->items()->orderBy('id')->get();
        return view('onsite.rekon.cekbg_show', compact('visit', 'rekon', 'items'));
    }

    public function updateCekBgItem(Request $request, OnsiteRekonCekBgItem $item)
    {
        if ($item->rekon->status === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved.'], 403);
        }

        $request->validate([
            'saldo_nominatif' => 'nullable|string',
            'hasil_fisik'     => 'nullable|string',
            'selisih'         => 'nullable|string',
            'hasil_kontrol'   => 'nullable|string',
            'impact'          => 'nullable|integer|min:1|max:5',
            'likelihood'      => 'nullable|integer|min:1|max:5',
            'referensi_bukti' => 'nullable|string',
            'simpulan_ra'     => 'nullable|string',
            'tanggal_uji'     => 'nullable|date',
            'status_review'   => 'nullable|in:Belum Diuji,Sesuai,Tidak Sesuai,Tidak Berlaku',
        ]);

        $data = $request->only([
            'saldo_nominatif', 'hasil_fisik', 'selisih', 'hasil_kontrol',
            'kriteria', 'sebab', 'dampak', 'impact', 'likelihood',
            'referensi_bukti', 'simpulan_ra', 'tanggal_uji', 'status_review',
        ]);

        if (in_array($request->status_review, ['Sesuai', 'Tidak Berlaku'])) {
            $data['kriteria']  = null;
            $data['sebab']     = null;
            $data['dampak']    = null;
            $data['impact']    = null;
            $data['likelihood']= null;
            $data['risk_level']= null;
        } elseif ($request->status_review === 'Tidak Sesuai') {
            $score = ($request->impact ?? 1) * ($request->likelihood ?? 1);
            $data['risk_level'] = $score >= 20 ? 'Critical' : ($score >= 12 ? 'High' : ($score >= 6 ? 'Moderate' : 'Low'));
        }

        $item->update($data);
        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function reviewCekBgItem(Request $request, OnsiteRekonCekBgItem $item)
    {
        $request->validate([
            'status_review'    => 'required|in:Belum Diuji,Sesuai,Tidak Sesuai,Tidak Berlaku',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $item->update([
            'status_review'    => $request->status_review,
            'catatan_reviewer' => $request->catatan_reviewer,
            'reviewer'         => auth()->user()->name,
        ]);

        return response()->json(['status' => 'success', 'data' => $item]);
    }
}
