<?php

namespace App\Http\Controllers;

use App\Models\ParameterAudit;
use Illuminate\Http\Request;

class ParameterAuditController extends Controller
{
    public function index()
    {
        $parameters = ParameterAudit::latest()->get();
        return view('parameter.index', compact('parameters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:255',
            'bobot'          => 'required|numeric|min:0|max:100',
            'deskripsi'      => 'nullable|string',
        ]);
        ParameterAudit::create($validated);
        return back()->with('success', 'Parameter berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_parameter' => 'required|string|max:255',
            'bobot'          => 'required|numeric|min:0|max:100',
            'deskripsi'      => 'nullable|string',
        ]);
        ParameterAudit::findOrFail($id)->update($validated);
        return back()->with('success', 'Parameter berhasil diperbarui.');
    }

    public function destroy($id)
    {
        ParameterAudit::findOrFail($id)->delete();
        return back()->with('success', 'Parameter berhasil dihapus.');
    }
}
