<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteRekonKartuAtm;
use App\Models\Onsite\OnsiteRekonKartuAtmItem;
use App\Services\Onsite\RekonTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekonKartuAtmController extends Controller
{
    public function create(OnsiteVisit $visit)
    {
        $rekon = OnsiteRekonKartuAtm::where('onsite_visit_id', $visit->id)->first();
        return view('onsite.rekon.katm_create', compact('visit', 'rekon'));
    }

    public function store(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'ra_pelaksana'   => 'required|string',
            'tanggal_opname' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $rekon = OnsiteRekonKartuAtm::updateOrCreate(
                ['onsite_visit_id' => $visit->id],
                [
                    'kode_unit'      => $visit->kode_unit,
                    'ra_pelaksana'   => $request->ra_pelaksana,
                    'tanggal_opname' => $request->tanggal_opname,
                ]
            );

            // Hapus kontrol lama, generate ulang — baris persediaan tetap
            $rekon->kontrol()->delete();
            $kontrolItems = RekonTemplateService::generateKontrolKartuAtm(
                $visit->kode_unit,
                $request->tanggal_opname,
                $rekon->id
            );
            DB::table('onsite_rekon_kartu_atm_items')->insert($kontrolItems);

            DB::commit();
            return redirect()->route('onsite.rekon.katm.show', [$visit, $rekon])
                ->with('success', 'Checklist Rekon Kartu ATM berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show(OnsiteVisit $visit, OnsiteRekonKartuAtm $rekon)
    {
        $persediaan = $rekon->persediaan()->get();
        $kontrol    = $rekon->kontrol()->get();
        return view('onsite.rekon.katm_show', compact('visit', 'rekon', 'persediaan', 'kontrol'));
    }

    // Tambah baris persediaan baru (AJAX)
    public function addRow(Request $request, OnsiteRekonKartuAtm $rekon)
    {
        if ($rekon->status === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved.'], 403);
        }

        $lastUrutan = $rekon->persediaan()->max('urutan') ?? 0;

        $item = OnsiteRekonKartuAtmItem::create([
            'rekon_id'    => $rekon->id,
            'tipe'        => 'persediaan',
            'sub_area'    => 'Persediaan Kartu ATM',
            'status_review' => 'Belum Diuji',
            'urutan'      => $lastUrutan + 1,
        ]);

        return response()->json(['status' => 'success', 'data' => $item]);
    }

    // Hapus baris persediaan (AJAX)
    public function deleteRow(OnsiteRekonKartuAtmItem $item)
    {
        if ($item->tipe !== 'persediaan') {
            return response()->json(['status' => 'error', 'message' => 'Baris kontrol tidak dapat dihapus.'], 403);
        }
        if ($item->rekon->status === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved.'], 403);
        }

        $item->delete();
        return response()->json(['status' => 'success']);
    }

    // Update item (persediaan atau kontrol)
    public function updateItem(Request $request, OnsiteRekonKartuAtmItem $item)
    {
        if ($item->rekon->status === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved.'], 403);
        }

        $data = $request->only([
            'jenis_kartu', 'keterangan', 'saldo_nominatif', 'fisik_kondisi', 'selisih',
            'hasil_kontrol', 'kriteria', 'sebab', 'dampak', 'impact', 'likelihood',
            'referensi_bukti', 'simpulan_ra', 'tanggal_uji', 'status_review',
        ]);

        if (in_array($request->status_review, ['Sesuai', 'Tidak Berlaku'])) {
            $data['kriteria']   = $item->tipe === 'kontrol' ? $item->kriteria : null;
            $data['sebab']      = null;
            $data['dampak']     = null;
            $data['impact']     = null;
            $data['likelihood'] = null;
            $data['risk_level'] = null;
        } elseif ($request->status_review === 'Tidak Sesuai') {
            $score = ($request->impact ?? 1) * ($request->likelihood ?? 1);
            $data['risk_level'] = $score >= 20 ? 'Critical' : ($score >= 12 ? 'High' : ($score >= 6 ? 'Moderate' : 'Low'));
        }

        $item->update($data);
        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function reviewItem(Request $request, OnsiteRekonKartuAtmItem $item)
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
