<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use Illuminate\Support\Facades\Auth;

class RaOffsiteUploadController extends Controller
{
    /**
     * Tampilkan form upload CSV khusus RA.
     * Dropdown unit otomatis dibatasi hanya unit naungan RA yang login.
     */
    public function index()
    {
        $user = Auth::user();

        // Scope unit sesuai hak akses RA (cabang utama + anak cabang)
        $accessibleIds = $user->cabangIdYangDapatDiakses();

        if ($accessibleIds === null) {
            $cabangs = Cabang::all();
        } else {
            $cabangs = Cabang::whereIn('id', $accessibleIds)->get();
        }

        return view('ra-offsite.upload', compact('cabangs'));
    }

    /**
     * Proses pemrosesan file CSV upload 5 domain.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'domain_type' => 'required|in:cbs,dpk,kredit,biaya,pengaduan',
            'file_csv' => 'required|file|mimes:csv,txt|max:20480', // Maks 20MB
            'tanggal_data_manual' => 'nullable|date', // Wajib diisi untuk tipe Nominatif (DPK/Kredit)
        ]);

        // Logic parser CSV & Mesin Deteksi akan dipanggil di sini
        return redirect()->back()->with('success', 'File CSV berhasil diunggah dan diproses ke Staging Offsite!');
    }
}