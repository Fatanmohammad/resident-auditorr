<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteCashOpname;
use App\Models\Onsite\OnsiteCashOpnameItem;
use Illuminate\Http\Request;

class CashOpnameController extends Controller
{
    public function create(OnsiteVisit $visit)
    {
        $opname = OnsiteCashOpname::where('onsite_visit_id', $visit->id)->first();
        return view('onsite.cash_opname.create', compact('visit', 'opname'));
    }

    public function store(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'ra_pelaksana'   => 'required|string',
            'pemegang_kas'   => 'required|string',
            'tanggal_opname' => 'required|date',
            'saldo_gl'       => 'nullable|numeric',
        ]);

        $opname = OnsiteCashOpname::updateOrCreate(
            ['onsite_visit_id' => $visit->id],
            [
                'ra_pelaksana'   => $request->ra_pelaksana,
                'pemegang_kas'   => $request->pemegang_kas,
                'tanggal_opname' => $request->tanggal_opname,
                'saldo_gl'       => $request->saldo_gl ?? 0,
            ]
        );

        if ($opname->items()->count() === 0) {
            $defaultItems = [
                ['procedure_id' => 'FLD-ONS-CSH-001', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp100.000'],
                ['procedure_id' => 'FLD-ONS-CSH-002', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp50.000'],
                ['procedure_id' => 'FLD-ONS-CSH-003', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp20.000'],
                ['procedure_id' => 'FLD-ONS-CSH-004', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp10.000'],
                ['procedure_id' => 'FLD-ONS-CSH-005', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp5.000'],
                ['procedure_id' => 'FLD-ONS-CSH-006', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp2.000'],
                ['procedure_id' => 'FLD-ONS-CSH-007', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang kertas layak edar', 'pecahan_seri' => 'Pecahan Rp1.000'],
                ['procedure_id' => 'FLD-ONS-CSH-008', 'sub_area' => 'Khasanah', 'objek_lokasi' => 'Uang logam', 'pecahan_seri' => 'Logam Rp1.000 / Rp500 / Lainnya'],
                ['procedure_id' => 'FLD-ONS-ATM-001', 'sub_area' => 'ATM', 'objek_lokasi' => 'ATM Utama / Cabang (4 Kaset)', 'pecahan_seri' => 'Rekonsiliasi Kaset 1-4'],
            ];

            foreach ($defaultItems as $item) {
                $opname->items()->create($item);
            }
        }

        return redirect()->route('onsite.cash_opname.show', [$visit, $opname])
                         ->with('success', 'Lembar Kerja Cash Opname berhasil diinisialisasi.');
    }

    public function show(OnsiteVisit $visit, OnsiteCashOpname $opname)
    {
        $items = $opname->items()->get();
        return view('onsite.cash_opname.show', compact('visit', 'opname', 'items'));
    }

    public function updateItem(Request $request, OnsiteCashOpnameItem $item)
    {
        $item->update([
            'hasil_fisik'      => $request->hasil_fisik ?? $item->hasil_fisik,
            'kaset_1_pecahan'  => $request->kaset_1_pecahan ?? $item->kaset_1_pecahan,
            'kaset_1_lembar'   => $request->kaset_1_lembar ?? $item->kaset_1_lembar,
            'kaset_2_pecahan'  => $request->kaset_2_pecahan ?? $item->kaset_2_pecahan,
            'kaset_2_lembar'   => $request->kaset_2_lembar ?? $item->kaset_2_lembar,
            'kaset_3_pecahan'  => $request->kaset_3_pecahan ?? $item->kaset_3_pecahan,
            'kaset_3_lembar'   => $request->kaset_3_lembar ?? $item->kaset_3_lembar,
            'kaset_4_pecahan'  => $request->kaset_4_pecahan ?? $item->kaset_4_pecahan,
            'kaset_4_lembar'   => $request->kaset_4_lembar ?? $item->kaset_4_lembar,
            'status_uji'       => $request->status_uji ?? $item->status_uji,
            'kondisi'          => $request->kondisi ?? $item->kondisi,
            'simpulan_ra'      => $request->simpulan_ra ?? $item->simpulan_ra,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
    }
}