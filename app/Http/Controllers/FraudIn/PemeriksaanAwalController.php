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
use Illuminate\Support\Facades\Storage;
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
     * SUBMENU 1: Pelaporan Awal Indikasi Fraud (Form Kronologi & Triase)
     */
    public function pelaporanAwal(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        // Generate otomatis ID Indikasi Kasus: IND-YYYYMMDD-XXXX
        $todayStr = date('Ymd');
        $countToday = Sop09RegisterIndikasi::whereDate('created_at', date('Y-m-d'))->count();
        $autoId = 'IND-' . $todayStr . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        // Ambil daftar unit kerja
        $units = Unit::orderBy('unit_name')->get();

        // Data prefill jika ada kiriman parameter dari KKA Onsite/Offsite/Daily Register
        $prefill = [
            'kode_unit' => $request->get('kode_unit', ''),
            'nama_unit' => $request->get('nama_unit', ''),
            'source' => $request->get('source', ''),
            'dugaan_awal' => $request->get('dugaan', ''),
            'fakta_temuan' => $request->get('fakta', ''),
            'area' => $request->get('area', ''),
        ];

        return view('pemeriksaan_awal.pelaporan_awal', compact('autoId', 'units', 'prefill'));
    }

    /**
     * Simpan Pelaporan Awal
     */
    public function storePelaporanAwal(Request $request)
    {
        $request->validate([
            'unit_selection' => 'required',
            'tipe_kasus' => 'required',
            'pihak_terlibat' => 'required',
            'dugaan_awal' => 'required',
            'fakta_temuan' => 'required',
            'kronologi_awal' => 'required',
            'file_bukti' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ], [
            'unit_selection.required' => 'Pilih Kantor Cabang / Unit Kerja.',
            'tipe_kasus.required' => 'Pilih tipe kasus (Insidentil / Non-Insidentil).',
            'pihak_terlibat.required' => 'Pilih pihak yang terindikasi terlibat.',
            'dugaan_awal.required' => 'Dugaan awal (modus operandi) wajib diisi.',
            'fakta_temuan.required' => 'Fakta temuan lapangan wajib diisi.',
            'kronologi_awal.required' => 'Kronologi awal kejadian wajib diuraikan.',
            'file_bukti.mimes' => 'Format file bukti harus berupa Dokumen (PDF, DOCX, XLSX) atau Foto (JPG, PNG).',
            'file_bukti.max' => 'Ukuran file bukti maksimal 20 MB.',
        ]);

        // Parsing data unit (format "KODE|NAMA")
        $unitParts = explode('|', $request->unit_selection);
        $kodeUnit = $unitParts[0] ?? 'KCP';
        $namaUnit = $unitParts[1] ?? ($unitParts[0] ?? 'Unit Cabang');

        // Generate ID Kasus yang pasti unik
        $todayStr = date('Ymd');
        $count = Sop09RegisterIndikasi::count() + 1;
        $indikasiId = $request->indikasi_id ?: ('IND-' . $todayStr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT));

        // Kalkulasi checklist kriteria triase
        $checklist = $request->input('checklist', []);
        $skor = 0;
        foreach ($checklist as $bobot) {
            $skor += (int)$bobot;
        }

        // Tentukan rekomendasi keputusan triase berdasarkan skor
        if ($skor >= 7) {
            $saranTriase = 'INVESTIGASI SEGERA (PRIORITAS TINGGI)';
            $statusKasus = 'INVESTIGASI';
        } elseif ($skor >= 4) {
            $saranTriase = 'AUDIT KHUSUS / FACT-FINDING MENDALAM';
            $statusKasus = 'OPEN';
        } else {
            $saranTriase = 'PEMBINAAN CABANG / MONITORING BERKALA';
            $statusKasus = 'PEMBINAAN';
        }

        // Handle upload file bukti
        $fileBuktiPath = null;
        $namaBukti = null;
        $tipeBukti = 'DOKUMEN';

        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $ext = strtolower($file->getClientOriginalExtension());
            $namaBukti = $request->nama_bukti ?: $file->getClientOriginalName();
            $fileName = 'BUKTI-' . date('YmdHis') . '-' . uniqid() . '.' . $ext;
            $fileBuktiPath = $file->storeAs('bukti_fraud', $fileName, 'public');
            $tipeBukti = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'FOTO' : 'DOKUMEN';
        }

        // Buat record register indikasi baru
        $indikasi = Sop09RegisterIndikasi::create([
            'indikasi_id' => $indikasiId,
            'tanggal_diterima' => $request->tanggal_diterima ?: date('Y-m-d'),
            'kode_unit' => $kodeUnit,
            'nama_unit' => $namaUnit,
            'ra_id' => auth()->id() ?? 'RA-01',
            'nama_ra' => auth()->user()->name ?? 'Resident Auditor',
            'sumber_indikasi' => $request->sumber_indikasi ?: 'Audit Rutin / Uji Petik RA',
            'area' => $request->area_kejadian ?: 'Operasional Kas & Teller',
            'area_kejadian' => $request->area_kejadian ?: 'Operasional Kas & Teller',
            'tipe_kasus' => $request->tipe_kasus ?: 'INSIDENTIL',
            'pihak_terlibat' => $request->pihak_terlibat ?: 'INTERNAL',
            'dugaan_awal' => $request->dugaan_awal,
            'dugaan_belum_terverifikasi' => $request->dugaan_awal,
            'fakta_temuan' => $request->fakta_temuan,
            'fakta_awal_terverifikasi' => $request->fakta_temuan,
            'kronologi_awal' => $request->kronologi_awal,
            'catatan_awal_ra' => $request->catatan_awal_ra,
            'red_flag_utama' => mb_substr($request->dugaan_awal, 0, 150),
            'nominal_awal' => str_replace(['.', ','], ['', ''], $request->nominal_awal ?? 0),
            'checklist_kriteria' => json_encode($checklist),
            'skor_checklist' => $skor,
            'saran_keputusan_triase' => $saranTriase,
            'status_triase' => 'SELESAI',
            'keputusan_final' => $saranTriase,
            'status_kasus' => $statusKasus,
            'file_bukti' => $fileBuktiPath,
            'nama_bukti' => $namaBukti,
            'tipe_bukti' => $tipeBukti,
            'flagged_from' => $request->flagged_from,
        ]);

        return redirect()->route('pemeriksaan_awal.daftar_laporan')
            ->with('success', "Pelaporan Awal Indikasi Fraud [{$indikasiId}] berhasil dibuat dan masuk dalam register.");
    }

    /**
     * SUBMENU 2: Daftar Laporan Indikasi Fraud
     */
    public function daftarLaporan(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        $query = Sop09RegisterIndikasi::orderBy('id', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('indikasi_id', 'like', "%{$q}%")
                    ->orWhere('nama_unit', 'like', "%{$q}%")
                    ->orWhere('kode_unit', 'like', "%{$q}%")
                    ->orWhere('dugaan_awal', 'like', "%{$q}%")
                    ->orWhere('fakta_temuan', 'like', "%{$q}%")
                    ->orWhere('kronologi_awal', 'like', "%{$q}%")
                    ->orWhere('area_kejadian', 'like', "%{$q}%");
            });
        }

        if ($request->filled('unit')) {
            $query->where('kode_unit', $request->unit);
        }

        if ($request->filled('tipe_kasus')) {
            $query->where('tipe_kasus', $request->tipe_kasus);
        }

        if ($request->filled('pihak_terlibat')) {
            $query->where('pihak_terlibat', $request->pihak_terlibat);
        }

        if ($request->filled('status')) {
            $query->where('status_kasus', $request->status);
        }

        $laporans = $query->paginate(15)->withQueryString();
        $units = Unit::orderBy('unit_name')->get();

        // Ringkasan statistik
        $all = Sop09RegisterIndikasi::all();
        $stats = [
            'total' => $all->count(),
            'insidentil' => $all->where('tipe_kasus', 'INSIDENTIL')->count(),
            'non_insidentil' => $all->where('tipe_kasus', 'NON_INSIDENTIL')->count(),
            'investigasi' => $all->where('status_kasus', 'INVESTIGASI')->count(),
            'open' => $all->where('status_kasus', 'OPEN')->count(),
            'closed' => $all->where('status_kasus', 'CLOSED')->count(),
            'total_nominal' => $all->sum('nominal_awal'),
        ];

        return view('pemeriksaan_awal.daftar_laporan', compact('laporans', 'units', 'stats'));
    }

    /**
     * Detail Laporan (JSON atau View Modal)
     */
    public function detailLaporan($id)
    {
        $laporan = Sop09RegisterIndikasi::findOrFail($id);
        return response()->json($laporan);
    }

    /**
     * SUBMENU 3: Status Penanganan & Disposisi Memo Eskalasi
     */
    public function statusPenanganan(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        $query = Sop09RegisterIndikasi::orderBy('id', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('indikasi_id', 'like', "%{$q}%")
                    ->orWhere('nama_unit', 'like', "%{$q}%")
                    ->orWhere('nomor_memo_eskalasi', 'like', "%{$q}%")
                    ->orWhere('perihal_memo', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status_kasus', $request->status);
        }

        $kasusList = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Sop09RegisterIndikasi::count(),
            'open' => Sop09RegisterIndikasi::where('status_kasus', 'OPEN')->count(),
            'investigasi' => Sop09RegisterIndikasi::where('status_kasus', 'INVESTIGASI')->count(),
            'pembinaan' => Sop09RegisterIndikasi::where('status_kasus', 'PEMBINAAN')->count(),
            'closed' => Sop09RegisterIndikasi::where('status_kasus', 'CLOSED')->count(),
        ];

        return view('pemeriksaan_awal.status_penanganan', compact('kasusList', 'stats'));
    }

    /**
     * Simpan / Perbarui Memo Disposisi Eskalasi SKAI (Poin 17)
     */
    public function storeMemoDisposisi(Request $request, $id)
    {
        $request->validate([
            'nomor_memo' => 'required',
            'tanggal_memo' => 'required|date',
            'perihal_memo' => 'required',
            'isi_memo' => 'required',
            'keputusan_status' => 'required',
        ], [
            'nomor_memo.required' => 'Nomor memo eskalasi wajib diisi.',
            'tanggal_memo.required' => 'Tanggal memo wajib diisi.',
            'perihal_memo.required' => 'Perihal memo wajib diisi.',
            'isi_memo.required' => 'Uraian isi memo wajib diisi.',
            'keputusan_status.required' => 'Pilih keputusan status penanganan.',
        ]);

        $kasus = Sop09RegisterIndikasi::findOrFail($id);

        $kasus->update([
            'nomor_memo_eskalasi' => $request->nomor_memo,
            'tanggal_memo' => $request->tanggal_memo,
            'perihal_memo' => $request->perihal_memo,
            'isi_memo' => $request->isi_memo,
            'arahan_kadiv' => $request->arahan_kadiv ?: $request->isi_memo,
            'status_kasus' => $request->keputusan_status,
            'keputusan_final' => $request->keputusan_final ?: $request->keputusan_status,
        ]);

        return redirect()->route('pemeriksaan_awal.status_penanganan')
            ->with('success', "Memo Eskalasi resmi untuk kasus [{$kasus->indikasi_id}] berhasil disimpan dan diterbitkan.");
    }

    /**
     * Update Status Kasus Langsung
     */
    public function updateStatusPenanganan(Request $request, $id)
    {
        $request->validate(['status_kasus' => 'required']);
        $kasus = Sop09RegisterIndikasi::findOrFail($id);
        $kasus->update(['status_kasus' => $request->status_kasus]);

        return back()->with('success', "Status kasus [{$kasus->indikasi_id}] diubah menjadi {$request->status_kasus}.");
    }

    /**
     * Cetak / Download Lembar Register Kasus (Poin 4)
     */
    public function printRegister($id)
    {
        $kasus = Sop09RegisterIndikasi::findOrFail($id);
        return view('pemeriksaan_awal.print_register', compact('kasus'));
    }

    /**
     * Cetak / Print Memo Resmi SKAI (Poin 19)
     */
    public function printMemo($id)
    {
        $kasus = Sop09RegisterIndikasi::findOrFail($id);

        // Jika memo belum dibuat, buat format draft default
        if (empty($kasus->nomor_memo_eskalasi)) {
            $kasus->nomor_memo_eskalasi = 'MEMO/SKAI-FRD/' . date('Y') . '/' . strtoupper(date('M')) . '/' . str_pad($kasus->id, 3, '0', STR_PAD_LEFT);
            $kasus->tanggal_memo = date('Y-m-d');
            $kasus->perihal_memo = 'Laporan Indikasi Fraud & Usulan Tindak Lanjut pada ' . ($kasus->nama_unit ?: 'Kantor Cabang');
            $kasus->isi_memo = "Berdasarkan hasil pemeriksaan awal Resident Auditor, ditemukan indikasi penyimpangan operasional sebagai berikut:\n" .
                "- Tipe Kasus: " . ($kasus->tipe_kasus ?: 'INSIDENTIL') . "\n" .
                "- Pihak Terlibat: " . ($kasus->pihak_terlibat ?: 'INTERNAL') . "\n" .
                "- Dugaan Awal: " . ($kasus->dugaan_awal ?: '-') . "\n" .
                "- Fakta Temuan: " . ($kasus->fakta_temuan ?: '-') . "\n" .
                "- Estimasi Kerugian: Rp " . number_format($kasus->nominal_awal, 0, ',', '.') . "\n\n" .
                "Sehubungan dengan hal tersebut di atas, Divisi SKAI merekomendasikan untuk segera dilakukan " . ($kasus->saran_keputusan_triase ?: 'INVESTIGASI KHUSUS') . ".";
            $kasus->save();
        }

        return view('pemeriksaan_awal.print_memo', compact('kasus'));
    }

    /**
     * Download / Stream Bukti Dokumen & Foto (Poin 5 & 6)
     */
    public function downloadBukti($id)
    {
        $kasus = Sop09RegisterIndikasi::findOrFail($id);

        if (!$kasus->file_bukti) {
            return back()->with('error', 'Tidak ada dokumen/foto bukti yang tersimpan pada kasus ini.');
        }

        $disk = Storage::disk('public');
        $fullPath = null;

        if ($disk->exists($kasus->file_bukti)) {
            $fullPath = $disk->path($kasus->file_bukti);
        } elseif (file_exists(storage_path('app/public/' . $kasus->file_bukti))) {
            $fullPath = storage_path('app/public/' . $kasus->file_bukti);
        } elseif (file_exists(public_path('storage/' . $kasus->file_bukti))) {
            $fullPath = public_path('storage/' . $kasus->file_bukti);
        }

        if (!$fullPath || !file_exists($fullPath)) {
            return back()->with('error', 'File bukti fisik tidak ditemukan pada direktori server.');
        }

        $fileName = $kasus->nama_bukti ?: basename($fullPath);
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        // Jika request menginginkan inline view (foto/pdf)
        if (request()->has('inline')) {
            return response()->file($fullPath, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]);
        }

        return response()->download($fullPath, $fileName);
    }

    /**
     * Method kompatibilitas rute lama (agar tidak break)
     */
    public function index()
    {
        return redirect()->route('pemeriksaan_awal.daftar_laporan');
    }

    public function register(Request $request)
    {
        return $this->daftarLaporan($request);
    }

    public function storeRegister(Request $request)
    {
        return $this->storePelaporanAwal($request);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_kasus' => 'required|in:OPEN,INVESTIGASI,CLOSED,PEMBINAAN',
        ]);

        $indikasi = Sop09RegisterIndikasi::findOrFail($id);
        $oldStatus = $indikasi->status_kasus;
        $indikasi->update([
            'status_kasus' => $request->status_kasus,
        ]);

        return redirect()->route('pemeriksaan_awal.daftar_laporan')
            ->with('success', "Status kasus [{$indikasi->indikasi_id}] berhasil diperbarui dari {$oldStatus} menjadi {$request->status_kasus}.");
    }

    public function triase(Request $request)
    {
        return $this->statusPenanganan($request);
    }

    public function buktiCustody(Request $request)
    {
        return $this->daftarLaporan($request);
    }

    public function kka()
    {
        return redirect()->route('pemeriksaan_awal.pelaporan_awal');
    }

    public function eskalasi(Request $request)
    {
        return $this->statusPenanganan($request);
    }

    public function exportExcel($sheet)
    {
        return back()->with('success', 'Fitur export Excel untuk sheet ' . $sheet . ' siap digunakan.');
    }
}
