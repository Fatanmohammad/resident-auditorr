<?php

namespace App\Http\Controllers;

use App\Models\ParameterAudit;
use Illuminate\Http\Request;

class ParameterAuditController extends Controller
{
    public function index()
    {
        $parameters = ParameterAudit::all();
        return response()->json(['status' => 'success', 'data' => $parameters]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:255',
            'bobot' => 'required|numeric|min:0|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        $parameter = ParameterAudit::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Parameter audit berhasil ditambahkan.', 'data' => $parameter], 201);
    }
}