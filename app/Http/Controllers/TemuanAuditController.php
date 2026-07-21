<?php

namespace App\Http\Controllers;

use App\Models\TemuanAudit;
use Illuminate\Http\Request;

class TemuanAuditController extends Controller
{
    public function index($kkaId)
    {
        $temuans = TemuanAudit::where('kka_id', $kkaId)->with('tindakLanjuts')->get();
        return response()->json(['status' => 'success', 'data' => $temuans]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kka_id' => 'required|exists:kertas_kerja_audits,id',
            'judul_temuan' => 'required|string',
            'kategori' => 'required|in:signifikan,berulang,operasional,kepatuhan,lainnya',
            'kondisi' => 'required|string',
            'kriteria' => 'required|string',
            'sebab' => 'required|string',
            'akibat' => 'required|string',
            'rekomendasi_ra' => 'required|string',
            'target_selesai_tl' => 'nullable|date',
        ]);

        $temuan = TemuanAudit::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Temuan audit berhasil dicatat.', 'data' => $temuan], 201);
    }
}