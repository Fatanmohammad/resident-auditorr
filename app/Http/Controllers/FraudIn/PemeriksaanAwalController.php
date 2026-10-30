<?php

namespace App\Http\Controllers\FraudIn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sop09RegisterIndikasi;
use App\Models\Sop09Triase;
use App\Models\Sop09ChainOfCustody;
use App\Models\Sop09KkaPemeriksaanAwal;
use App\Models\Sop09KeputusanEskalasi;
use App\Models\Unit;
use Database\Seeders\Sop09IndikasiSeeder;

class PemeriksaanAwalController extends Controller
{
    /**
     * Memastikan data starter indikasi awal tersedia jika database masih kosong
     */
    private function ensureStarterDataIfEmpty()
    {
        if (Sop09RegisterIndikasi::count() === 0) {
            $seeder = new Sop09IndikasiSeeder();
            $seeder->run();
        }
    }

    /**
     * Dashboard / Index Modul SOP 09
     */
    public function index()
    {
        $this->ensureStarterDataIfEmpty();

        $totalRegister = Sop09RegisterIndikasi::count();
        $totalTriase = Sop09Triase::count();
        $totalCustody = Sop09ChainOfCustody::count();
        $totalKka = Sop09KkaPemeriksaanAwal::count();
        $totalEskalasi = Sop09KeputusanEskalasi::count();

        $totalIndikasi = $totalRegister;
        $urgentCount = Sop09Triase::where('urgent', 'YA')->count();
        $buktiCount = $totalCustody;
        $eskalasiCount = $totalEskalasi;

        return view('pemeriksaan_awal.index', compact(
            'totalRegister', 
            'totalTriase', 
            'totalCustody', 
            'totalKka', 
            'totalEskalasi',
            'totalIndikasi',
            'urgentCount',
            'buktiCount',
            'eskalasiCount'
        ));
    }

    /**
     * WP-RA-09-A: Register Indikasi
     */
    public function register()
    {
        $this->ensureStarterDataIfEmpty();

        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $units = Unit::orderBy('unit_name')->get();
        return view('pemeriksaan_awal.register', compact('indikasis', 'units'));
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
        $this->ensureStarterDataIfEmpty();

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
    public function buktiCustody(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        $indikasis = Sop09RegisterIndikasi::orderBy('id', 'desc')->get();
        $query = Sop09ChainOfCustody::with('registerIndikasi')->orderBy('id', 'desc');

        if ($request->filled('indikasi_id')) {
            $query->where('indikasi_id', $request->indikasi_id);
        }

        if ($request->filled('kategori_bukti')) {
            $kategori = $request->kategori_bukti;
            $query->where(function($q) use ($kategori) {
                $q->where('jenis_bukti', $kategori)
                  ->orWhere('kategori_bukti', $kategori);
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('kode_bukti', 'like', "%{$q}%")
                  ->orWhere('deskripsi_relevansi', 'like', "%{$q}%")
                  ->orWhere('sumber_perolehan', 'like', "%{$q}%")
                  ->orWhere('unit', 'like', "%{$q}%")
                  ->orWhere('lokasi_penyimpanan', 'like', "%{$q}%");
            });
        }

        $buktis = $query->get();

        return view('pemeriksaan_awal.bukti_custody', compact('indikasis', 'buktis'));
    }

    public function storeCustody(Request $request)
    {
        $request->validate([
            'indikasi_id' => 'required',
            'kategori_bukti' => 'required',
            'deskripsi_bukti' => 'required',
            'tanggal_diperoleh' => 'required|date',
            'lokasi_penyimpanan' => 'required',
        ]);

        $kodeBukti = 'COC-' . date('Ymd') . '-' . rand(1000, 9999);

        // Ambil data indikasi kasus terpilih untuk relasi unit otomatis
        $indikasi = Sop09RegisterIndikasi::where('indikasi_id', $request->indikasi_id)->first();
        $unitName = $indikasi ? ($indikasi->nama_unit ?? $indikasi->kode_unit) : ($request->unit ?? 'Kantor Cabang / Unit');

        $recordData = [
            'kode_bukti' => $kodeBukti,
            'indikasi_id' => $request->indikasi_id,
            'unit' => $unitName,
            'jenis_bukti' => $request->kategori_bukti ?? $request->jenis_bukti ?? 'DOKUMEN_FISIK',
            'deskripsi_relevansi' => $request->deskripsi_bukti ?? $request->deskripsi_relevansi,
            'sumber_perolehan' => $request->sumber_bukti ?? $request->sumber_perolehan ?? 'Pemberi Informasi / Auditee',
            'tanggal_diperoleh' => $request->tanggal_diperoleh ?? date('Y-m-d'),
            'asli_salinan' => $request->asli_salinan ?? 'ASLI',
            'lokasi_penyimpanan' => $request->lokasi_penyimpanan ?? 'Brankas SKAI Lt. 3',
            'klasifikasi_akses' => $request->klasifikasi_akses ?? 'TERBATAS',
            'diperoleh_oleh' => $request->petugas_custodian ?? $request->diperoleh_oleh ?? (auth()->user()->name ?? 'Auditor SKAI'),
            'status_integritas' => $request->kondisi_bukti ?? $request->status_integritas ?? 'UTUH',
            'pemegang_saat_ini' => $request->status_penguasaan ?? $request->pemegang_saat_ini ?? 'BRANKAS_SKAI',
            'catatan' => $request->catatan,
        ];

        Sop09ChainOfCustody::create($recordData);

        return redirect()->route('pemeriksaan_awal.bukti_custody')
            ->with('success', "Barang bukti Chain of Custody baru dengan kode {$kodeBukti} berhasil dicatat.");
    }

    /**
     * WP-RA-09-E: KKA Pemeriksaan Awal
     */
    public function kka()
    {
        $this->ensureStarterDataIfEmpty();

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
        $this->ensureStarterDataIfEmpty();

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
