<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteObservasi;
use App\Models\Onsite\OnsiteObservasiItem;
use App\Services\Onsite\ObservasiTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObservasiController extends Controller
{
    // Form setup awal (jumlah ATM, tanggal, RA pelaksana)
    public function create(OnsiteVisit $visit)
    {
        $observasi = OnsiteObservasi::where('onsite_visit_id', $visit->id)->first();
        return view('onsite.observasi.create', compact('visit', 'observasi'));
    }

    // Generate checklist berdasarkan jumlah ATM
    public function store(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'jumlah_atm'       => 'required|integer|min:0|max:10',
            'tanggal_observasi' => 'required|date',
            'ra_pelaksana'     => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Hapus observasi lama jika ada (re-generate)
            OnsiteObservasi::where('onsite_visit_id', $visit->id)->delete();

            $observasi = OnsiteObservasi::create([
                'onsite_visit_id'   => $visit->id,
                'jumlah_atm'        => $request->jumlah_atm,
                'tanggal_observasi' => $request->tanggal_observasi,
                'ra_pelaksana'      => $request->ra_pelaksana,
                'status'            => 'Draft',
            ]);

            $items = ObservasiTemplateService::generate(
                $request->jumlah_atm,
                $visit->kode_unit,
                $request->tanggal_observasi,
                $observasi->id
            );

            OnsiteObservasiItem::insert($items);

            DB::commit();

            return redirect()->route('onsite.observasi.show', [$visit, $observasi])
                ->with('success', 'Checklist observasi berhasil dibuat. Silakan isi hasil pengamatan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat checklist: ' . $e->getMessage());
        }
    }

    // Halaman isi checklist
    public function show(OnsiteVisit $visit, OnsiteObservasi $observasi)
    {
        $itemsGedung = $observasi->itemsGedung()->get();
        $itemsAtm    = $observasi->itemsAtm()->get()->groupBy('atm_slot');

        return view('onsite.observasi.show', compact('visit', 'observasi', 'itemsGedung', 'itemsAtm'));
    }

    // Update satu item checklist
    public function updateItem(Request $request, OnsiteObservasiItem $item)
    {
        if ($item->status_review === 'Approved') {
            return redirect()->back()->with('error', 'Item ini sudah Approved dan tidak dapat diubah.');
        }

        $request->validate([
            'hasil_observasi' => 'required|in:Sesuai,Tidak Sesuai,N/A',
            'kondisi_aktual'  => 'nullable|string',
            'impact'          => 'nullable|integer|min:1|max:5',
            'likelihood'      => 'nullable|integer|min:1|max:5',
        ]);

        $data = $request->only([
            'kondisi_aktual', 'hasil_observasi', 'uraian_ketidaksesuaian',
            'penyebab', 'dampak', 'impact', 'likelihood', 'referensi_bukti',
        ]);

        // Kosongkan kolom ketidaksesuaian jika hasil Sesuai/N/A
        if (in_array($request->hasil_observasi, ['Sesuai', 'N/A'])) {
            $data['uraian_ketidaksesuaian'] = null;
            $data['penyebab']   = null;
            $data['dampak']     = null;
            $data['impact']     = null;
            $data['likelihood'] = null;
            $data['risk_score'] = null;
            $data['risk_level'] = null;
        } else {
            // Hitung risk otomatis
            if ($request->impact && $request->likelihood) {
                $score = $request->impact * $request->likelihood;
                $data['risk_score'] = $score;
                $data['risk_level'] = match(true) {
                    $score >= 20 => 'Critical',
                    $score >= 12 => 'High',
                    $score >= 6  => 'Moderate',
                    default      => 'Low',
                };
            }
        }

        $item->update($data);

        return redirect()->back()->with('success', 'Item berhasil disimpan.');
    }

    // Review oleh admin/kabag/kadiv
    public function reviewItem(Request $request, OnsiteObservasiItem $item)
    {
        $request->validate([
            'status_review'    => 'required|in:Belum Direview,Revisi,Approved',
            'catatan_reviewer' => 'nullable|string',
        ]);

        $item->update($request->only(['status_review', 'catatan_reviewer']));

        return redirect()->back()->with('success', 'Review berhasil disimpan.');
    }
}
