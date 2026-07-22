<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    // Menampilkan seluruh struktur cabang beserta anak cabangnya sesuai diagram
    public function index()
    {
        $cabangs = Cabang::with(['anakCabang', 'parentCabang'])->get();
        return view('cabang.index', compact('cabangs'));
    }

    // Menambah cabang/anak cabang baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_cabang' => 'required|string|max:255',
            'kode_cabang' => 'required|string|unique:cabangs,kode_cabang',
            'tipe' => 'required|in:pusat,kcu,cabang_pembantu,siting,cabang_a,cabang_b,anak_cabang',
            'parent_id' => 'nullable|exists:cabangs,id',
        ]);

        $cabang = Cabang::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Data cabang berhasil ditambahkan.', 'data' => $cabang], 201);
    }

    // Detail cabang spesifik
    public function show($id)
    {
        $cabang = Cabang::with(['anakCabang', 'users', 'auditPlans'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $cabang]);
    }
}