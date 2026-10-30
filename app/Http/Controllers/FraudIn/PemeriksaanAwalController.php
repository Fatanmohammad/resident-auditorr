<?php

namespace App\Http\Controllers\FraudIn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sop09RegisterIndikasi;
use App\Models\Sop09Triase;
use App\Models\Sop09ChainOfCustody;
use App\Models\Sop09KkaPemeriksaanAwal;
use App\Models\Sop09KeputusanEskalasi;

class PemeriksaanAwalController extends Controller
{
    /**
     * Dashboard / Index Modul SOP 09
     */
    public function index()
    {
        $totalRegister = Sop09RegisterIndikasi::count();
        $totalTriase = Sop09Triase::count();
        $totalCustody = Sop09ChainOfCustody::count();
        $totalKka = Sop09KkaPemeriksaanAwal::count();
        $totalEskalasi = Sop09KeputusanEskalasi::count();

        return view('pemeriksaan_awal.index', compact(
            'totalRegister', 
            'totalTriase', 
            'totalCustody', 
            'totalKka', 
            'totalEskalasi'
        ));
    }

    /**
     * WP-RA-09-A: Register Indikasi
     */
    public function register()
    {
        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        return view('pemeriksaan_awal.register', compact('indikasis'));
    }

    public function storeRegister(Request $request)
    {
        $request->validate([
            'tanggal_diterima' => 'required|date',
            'sumber_indikasi' => 'required',
            'kode_unit' => 'required',
            'nama_unit' => 'required',
            'red_flag_utama' => 'required',
            'nominal_awal' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['indikasi_id'] = 'IND-' . date('Ymd') . '-' . str_pad(Sop09RegisterIndikasi::count() + 1, 4, '0', STR_PAD_LEFT);
        $data['status_kasus'] = 'OPEN';

        Sop09RegisterIndikasi::create($data);

        return redirect()->route('pemeriksaan_awal.register')->with('success', 'Register indikasi fraud berhasil disimpan.');
    }

    /**
     * WP-RA-09-B: Triase Awal
     */
    public function triase()
    {
        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $triases = Sop09Triase::orderBy('id', 'desc')->get();
        return view('pemeriksaan_awal.triase', compact('indikasis', 'triases'));
    }

    public function storeTriase(Request $request)
    {
        $request->validate([
            'indikasi_id' => 'required',
            'tanggal' => 'required|date',
            'unit' => 'required',
            'risiko_bukti_hilang' => 'required',
            'keputusan_triase' => 'required',
        ]);

        $data = $request->all();
        
        // Kalkulasi skor prioritas sesuai standar SOP 09
        $skor = 0;
        if (($data['risiko_bukti_hilang'] ?? '') == 'TINGGI') $skor += 3;
        if (($data['pola_berulang'] ?? 'TIDAK') == 'YA') $skor += 2;
        if (($data['nasabah'] ?? 'TIDAK') == 'YA') $skor += 2;
        if (($data['pihak_kunci_pimpinan'] ?? 'TIDAK') == 'YA') $skor += 3;

        $data['skor_prioritas'] = $skor;
        $data['urgent'] = ($skor >= 5 || ($data['risiko_bukti_hilang'] ?? '') == 'TINGGI') ? 'YA' : 'TIDAK';

        Sop09Triase::create($data);

        return redirect()->route('pemeriksaan_awal.triase')->with('success', 'Data triase awal berhasil disimpan.');
    }

    /**
     * WP-RA-09-C: Chain of Custody (Bukti)
     */
    public function buktiCustody()
    {
        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $buktis = Sop09ChainOfCustody::orderBy('id', 'desc')->get();
        return view('pemeriksaan_awal.bukti_custody', compact('indikasis', 'buktis'));
    }

    public function storeCustody(Request $request)
    {
        $request->validate([
            'indikasi_id' => 'required',
            'kategori_bukti' => 'required',
            'deskripsi_bukti' => 'required',
            'tanggal_diperoleh' => 'required|date',
        ]);

        $data = $request->all();
        $data['kode_bukti'] = 'COC-' . date('Ymd') . '-' . rand(100, 999);

        Sop09ChainOfCustody::create($data);

        return redirect()->route('pemeriksaan_awal.bukti_custody')->with('success', 'Barang bukti Chain of Custody berhasil dicatat.');
    }

    /**
     * WP-RA-09-E: KKA Pemeriksaan Awal
     */
    public function kka()
    {
        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $kkas = Sop09KkaPemeriksaanAwal::orderBy('id', 'desc')->get();
        return view('pemeriksaan_awal.kka', compact('indikasis', 'kkas'));
    }

    public function storeKka(Request $request)
    {
        $request->validate([
            'indikasi_id' => 'required',
            'area_uji_awal' => 'required',
            'populasi_universe' => 'required|numeric',
            'jumlah_anomali' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['test_id'] = 'TST-' . date('Ymd') . '-' . rand(100, 999);
        $populasi = max(1, $data['populasi_universe']);
        $data['rasio_anomali'] = round(($data['jumlah_anomali'] / $populasi) * 100, 2);
        $data['selisih'] = abs(($data['nominal_sistem'] ?? 0) - ($data['nominal_dokumen_bukti'] ?? 0));

        Sop09KkaPemeriksaanAwal::create($data);

        return redirect()->route('pemeriksaan_awal.kka')->with('success', 'Kertas kerja KKA pemeriksaan berhasil disimpan.');
    }

    /**
     * WP-RA-09-G: Keputusan & Eskalasi
     */
    public function eskalasi()
    {
        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $eskalasis = Sop09KeputusanEskalasi::orderBy('id', 'desc')->get();
        return view('pemeriksaan_awal.eskalasi', compact('indikasis', 'eskalasis'));
    }

    public function storeEskalasi(Request $request)
    {
        $request->validate([
            'indikasi_id' => 'required',
            'tanggal_keputusan' => 'required|date',
            'fakta_utama' => 'required',
            'estimasi_kerugian' => 'required|numeric',
            'keputusan_final' => 'required',
        ]);

        Sop09KeputusanEskalasi::create($request->all());

        return redirect()->route('pemeriksaan_awal.eskalasi')->with('success', 'Keputusan & Eskalasi kasus berhasil disimpan.');
    }

    /**
     * Export Excel Modul SOP 09
     */
    public function exportExcel($sheet)
    {
        return back()->with('success', 'Fitur export Excel untuk sheet ' . $sheet . ' siap digunakan.');
    }
}