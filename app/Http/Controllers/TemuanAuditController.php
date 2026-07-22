<?php

namespace App\Http\Controllers;

use App\Models\TemuanAudit;
use App\Models\KertasKerjaAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemuanAuditController extends Controller
{
    public function indexAll()
    {
        $user = Auth::user();
        $query = TemuanAudit::with(['kka.auditPlan.cabang', 'tindakLanjuts']);

        if ($user->role === 'ra') {
            $query->whereHas('kka.auditPlan', fn($q) => $q->where('ra_user_id', $user->id));
        }

        $temuans = $query->latest()->get();
        return view('temuan.index', compact('temuans'));
    }

    public function index($kkaId)
    {
        $temuans = TemuanAudit::where('kka_id', $kkaId)->with('tindakLanjuts')->get();
        return response()->json(['status' => 'success', 'data' => $temuans]);
    }

    public function create()
    {
        $user = Auth::user();
        $kkas = KertasKerjaAudit::whereHas('auditPlan', fn($q) => $q->where('ra_user_id', $user->id))
            ->with('auditPlan.cabang')->get();
        return view('temuan.create', compact('kkas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kka_id'            => 'required|exists:kertas_kerja_audits,id',
            'judul_temuan'      => 'required|string',
            'kategori'          => 'required|in:signifikan,berulang,operasional,kepatuhan,lainnya',
            'kondisi'           => 'required|string',
            'kriteria'          => 'required|string',
            'sebab'             => 'required|string',
            'akibat'            => 'required|string',
            'rekomendasi_ra'    => 'required|string',
            'target_selesai_tl' => 'nullable|date',
        ]);
        TemuanAudit::create($validated);
        return redirect()->route('temuan.index')->with('success', 'Temuan berhasil dicatat.');
    }

    public function show($id)
    {
        $temuan = TemuanAudit::with(['kka.auditPlan.cabang', 'tindakLanjuts.auditeeUser'])->findOrFail($id);
        return view('temuan.show', compact('temuan'));
    }
}
