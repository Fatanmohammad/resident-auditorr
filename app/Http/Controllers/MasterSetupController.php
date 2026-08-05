<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterSetupController extends Controller
{
    /**
     * Tampilkan halaman Pengaturan Modul (bobot skoring).
     * Hanya admin. Menampilkan bobot indikator & bobot bidang.
     */
    public function index()
    {
        // Bobot indikator dalam bidang (field_weights), dikelompokkan per bidang
        $fieldWeights = DB::table('field_weights')
            ->orderBy('bidang')
            ->orderBy('id')
            ->get()
            ->groupBy('bidang');

        // Bobot bidang ke skor final per jenis unit (bidang_weights)
        $bidangWeights = DB::table('bidang_weights')
            ->orderBy('unit_type')
            ->orderBy('id')
            ->get()
            ->groupBy('unit_type');

        // Label urutan bidang untuk tampilan
        $bidangOrder = [
            'riwayat_ra'    => 'Bidang A — Riwayat Pemeriksaan RA',
            'kas_teller'    => 'Bidang B — Kas/Teller & Operasional',
            'cs_dpk'        => 'Bidang C — CS/DPK/APU-PPT',
            'kredit'        => 'Bidang D — Kredit',
            'ti_atm'        => 'Bidang E — TI/ATM',
            'monitoring_tl' => 'Bidang F — Monitoring Tindak Lanjut',
        ];

        $unitTypeOrder = ['KC', 'KCU', 'KCP', 'KCPLK', 'Payment Point'];

        return view('master-setup.index', compact(
            'fieldWeights', 'bidangWeights', 'bidangOrder', 'unitTypeOrder'
        ));
    }

    /**
     * Simpan perubahan bobot indikator (field_weights).
     */
    public function storeFieldWeights(Request $request)
    {
        $weights = $request->input('weights', []);

        foreach ($weights as $id => $weight) {
            $weight = (float) $weight;
            if ($weight < 0 || $weight > 1) {
                return back()->with('error', "Bobot harus antara 0 dan 1. Cek baris bobot indikator.");
            }
            DB::table('field_weights')->where('id', $id)->update([
                'weight'     => $weight,
                'updated_at' => now(),
            ]);
        }

        // Catat ke Change Log
        ChangeLog::create([
            'date'               => now(),
            'sheet_area'         => 'Pengaturan Modul',
            'change_description' => 'Ubah bobot indikator skoring (field_weights)',
            'reason'             => $request->input('reason', 'Perubahan oleh admin'),
            'approved_by'        => Auth::user()->name,
            'status'             => 'Implemented',
            'created_by'         => Auth::id(),
        ]);

        return back()->with('success', 'Bobot indikator berhasil disimpan.');
    }

    /**
     * Simpan perubahan bobot bidang ke skor final (bidang_weights).
     */
    public function storeBidangWeights(Request $request)
    {
        $weights = $request->input('weights', []);

        foreach ($weights as $id => $weight) {
            $weight = (float) $weight;
            if ($weight < 0 || $weight > 1) {
                return back()->with('error', "Bobot harus antara 0 dan 1. Cek baris bobot bidang.");
            }
            DB::table('bidang_weights')->where('id', $id)->update([
                'weight'     => $weight,
                'updated_at' => now(),
            ]);
        }

        // Catat ke Change Log
        ChangeLog::create([
            'date'               => now(),
            'sheet_area'         => 'Pengaturan Modul',
            'change_description' => 'Ubah bobot bidang ke skor final (bidang_weights)',
            'reason'             => $request->input('reason', 'Perubahan oleh admin'),
            'approved_by'        => Auth::user()->name,
            'status'             => 'Implemented',
            'created_by'         => Auth::id(),
        ]);

        return back()->with('success', 'Bobot bidang berhasil disimpan.');
    }
}
