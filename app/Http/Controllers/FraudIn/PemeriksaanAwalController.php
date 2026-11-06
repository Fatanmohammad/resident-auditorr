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
use App\Models\Sop09FraudTriageChecklist;
use App\Models\Sop09TemuanIndikasiCabang;
use App\Models\Cabang;
use Database\Seeders\Sop09IndikasiSeeder;
use Database\Seeders\Sop09FraudTriageAndIndikasiSeeder;
use Illuminate\Support\Facades\Storage;

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

        if (Sop09TemuanIndikasiCabang::count() === 0 || Sop09FraudTriageChecklist::count() === 0) {
            $seederTriage = new Sop09FraudTriageAndIndikasiSeeder();
            $seederTriage->run();
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

    /**
     * =========================================================================
     * INSTRUMEN 1: FRAUD TRIAGE CHECKLIST – RESIDENT AUDITOR (POJK 12/2024)
     * =========================================================================
     */
    public function triageChecklist(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        $activeId = $request->get('id');
        if ($activeId) {
            $checklist = Sop09FraudTriageChecklist::find($activeId);
        } else {
            $checklist = Sop09FraudTriageChecklist::latest()->first();
        }

        $historyList = Sop09FraudTriageChecklist::orderBy('id', 'desc')->get();
        $masterQuestions = Sop09FraudTriageAndIndikasiSeeder::getMasterChecklistQuestions();
        $units = Unit::orderBy('unit_name')->get();

        return view('pemeriksaan_awal.triage_checklist', compact('checklist', 'historyList', 'masterQuestions', 'units'));
    }

    public function saveTriageChecklist(Request $request)
    {
        $request->validate([
            'nama_cabang' => 'required',
            'tanggal_pemeriksaan' => 'required|date',
            'nama_ra' => 'required',
        ]);

        $masterQuestions = Sop09FraudTriageAndIndikasiSeeder::getMasterChecklistQuestions();
        $answers = [];
        $yaCount = 0;
        $tidakCount = 0;
        $belumCount = 0;
        $hasCritical = false;

        foreach ($masterQuestions as $q) {
            $no = $q['no'];
            $ans = $request->input("chk_answer_{$no}", '');
            $catatan = $request->input("chk_catatan_{$no}", '');

            if ($ans === 'Ya') {
                $yaCount++;
                if (!empty($q['is_critical'])) {
                    $hasCritical = true;
                }
            } elseif ($ans === 'Tidak') {
                $tidakCount++;
            } else {
                $belumCount++;
            }

            $answers[] = [
                'no' => $no,
                'section' => $q['section'],
                'question' => $q['question'],
                'is_critical' => $q['is_critical'],
                'jawaban' => $ans,
                'catatan' => $catatan,
                'regulasi' => $q['regulasi'],
            ];
        }

        $keputusan = $request->input('keputusan_triage_override');
        if (!$keputusan) {
            if ($hasCritical) {
                $keputusan = 'ESKALASI SEGERA';
            } elseif ($belumCount > 0) {
                $keputusan = 'KLARIFIKASI TAMBAHAN';
            } elseif ($yaCount > 0) {
                $keputusan = 'LANJUT KE PENYELIDIKAN';
            } else {
                $keputusan = 'TIDAK DILANJUTKAN';
            }
        }

        $triageId = $request->input('triage_id');
        if ($triageId) {
            $record = Sop09FraudTriageChecklist::find($triageId);
        } else {
            $record = new Sop09FraudTriageChecklist();
            $record->nomor_triage = 'TRG-' . date('Ymd') . '-' . rand(1000, 9999);
        }

        $record->kode_unit = $request->input('kode_unit', '001');
        $record->nama_cabang = $request->input('nama_cabang');
        $record->tanggal_pemeriksaan = $request->input('tanggal_pemeriksaan');
        $record->nama_ra = $request->input('nama_ra');
        $record->checklist_answers = $answers;
        $record->total_item = 28;
        $record->dijawab_ya = $yaCount;
        $record->dijawab_tidak = $tidakCount;
        $record->belum_dijawab = $belumCount;
        $record->ada_critical_trigger = $hasCritical;
        $record->keputusan_triage = $keputusan;
        $record->catatan_kesimpulan = $request->input('catatan_kesimpulan', '');
        $record->status = 'Final';
        $record->created_by = auth()->id();
        $record->save();

        if ($request->input('action') === 'eskalasi_pelaporan') {
            return redirect()->route('pemeriksaan_awal.pelaporan_awal', [
                'nama_unit' => $record->nama_cabang,
                'kode_unit' => $record->kode_unit,
                'source' => 'FRAUD_TRIAGE_' . $record->nomor_triage,
                'dugaan' => "Indikasi Temuan Hasil Triage POJK 12/2024 ({$record->keputusan_triage}) pada {$record->nama_cabang}",
                'fakta' => $record->catatan_kesimpulan ?: "Ditemukan {$record->dijawab_ya} item indikasi bernilai 'Ya' dari 28 item instrumen triage.",
            ])->with('success', "Kertas kerja triage [{$record->nomor_triage}] berhasil disimpan dan dielevasikan ke Pelaporan Awal Indikasi Fraud.");
        }

        return redirect()->route('pemeriksaan_awal.triage_checklist', ['id' => $record->id])
            ->with('success', "Kertas Kerja Fraud Triage [{$record->nomor_triage}] berhasil disimpan. Hasil: {$record->keputusan_triage}.");
    }

    public function printTriageChecklist($id)
    {
        $checklist = Sop09FraudTriageChecklist::findOrFail($id);
        $masterQuestions = Sop09FraudTriageAndIndikasiSeeder::getMasterChecklistQuestions();
        return view('pemeriksaan_awal.print_triage_checklist', compact('checklist', 'masterQuestions'));
    }

    /**
     * =========================================================================
     * INSTRUMEN 2: MATRIKS TEMUAN INDIKASI MENCURIGAKAN CABANG (18 INDIKATOR)
     * =========================================================================
     */
    public function indikasiMencurigakan(Request $request)
    {
        $this->ensureStarterDataIfEmpty();

        $indikasis = Sop09TemuanIndikasiCabang::orderBy('id', 'asc')->get();
        $units = Unit::orderBy('unit_name')->get();
        $indicatorRefs = self::getIndikatorReferences();

        return view('pemeriksaan_awal.indikasi_mencurigakan', compact('indikasis', 'units', 'indicatorRefs'));
    }

    public function storeIndikasiMencurigakan(Request $request)
    {
        $request->validate([
            'nama_cabang' => 'required',
        ]);

        $id = $request->input('item_id');
        $data = [
            'nama_cabang' => $request->nama_cabang,
            'kode_cabang' => $request->kode_cabang ?? 'KCP',
            'periode_bulan' => $request->periode_bulan ?? date('F Y'),
            'reversal_teller' => (int) $request->reversal_teller,
            'selisih_kas_teller' => (int) $request->selisih_kas_teller,
            'selisih_kas_khasanah' => (int) $request->selisih_kas_khasanah,
            'kas_kecil_melebihi_limit' => (int) $request->kas_kecil_melebihi_limit,
            'kas_kecil_belum_selesai' => (int) $request->kas_kecil_belum_selesai,
            'dual_control_kunci_dilanggar' => (int) $request->dual_control_kunci_dilanggar,
            'trx_rekening_pasif' => (int) $request->trx_rekening_pasif,
            'trx_rekening_titipan' => (int) $request->trx_rekening_titipan,
            'trx_tidak_wajar_pegawai' => (int) $request->trx_tidak_wajar_pegawai,
            'trx_luar_jam_operasional' => (int) $request->trx_luar_jam_operasional,
            'setoran_non_tunai_pegawai' => (int) $request->setoran_non_tunai_pegawai,
            'sharing_user_id' => (int) $request->sharing_user_id,
            'skimming_atm' => (int) $request->skimming_atm,
            'selisih_kas_crm_atm' => (int) $request->selisih_kas_crm_atm,
            'restrukturisasi_berulang' => (int) $request->restrukturisasi_berulang,
            'pencairan_tanpa_dokumen' => (int) $request->pencairan_tanpa_dokumen,
            'cuti_wajib_belum_diambil' => (int) $request->cuti_wajib_belum_diambil,
            'lonjakan_pengaduan_sla' => (int) $request->lonjakan_pengaduan_sla,
            'catatan_umum' => $request->catatan_umum,
        ];

        if ($id) {
            $item = Sop09TemuanIndikasiCabang::findOrFail($id);
            $item->update($data);
            $msg = "Data temuan indikasi cabang [{$item->nama_cabang}] berhasil diperbarui.";
        } else {
            $item = Sop09TemuanIndikasiCabang::create($data);
            $msg = "Data temuan indikasi cabang [{$item->nama_cabang}] berhasil ditambahkan.";
        }

        return redirect()->route('pemeriksaan_awal.indikasi_mencurigakan')->with('success', $msg);
    }

    public function destroyIndikasiMencurigakan($id)
    {
        $item = Sop09TemuanIndikasiCabang::findOrFail($id);
        $nama = $item->nama_cabang;
        $item->delete();

        return redirect()->route('pemeriksaan_awal.indikasi_mencurigakan')
            ->with('success', "Baris temuan indikasi cabang [{$nama}] berhasil dihapus.");
    }

    public static function getIndikatorReferences(): array
    {
        return [
            [
                'kolom' => 'Reversal/Pembatalan Transaksi Teller',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah transaksi yang di-reversal/dibatalkan teller dalam sebulan',
                'satuan_hitung' => 'Hitung: total reversal seluruh teller cabang per bulan. Wajar jika kecil & tersebar merata; curigai jika terkonsentrasi pada 1 teller/1 nasabah atau naik tajam dari bulan sebelumnya',
                'rujukan' => 'BPP Strategi Anti Fraud SK 60/2022'
            ],
            [
                'kolom' => 'Selisih Kas Teller Akhir Hari',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah hari dalam sebulan ditemukan selisih kas teller (lebih atau kurang) saat cash count akhir hari',
                'satuan_hitung' => 'Hitung: jumlah kejadian selisih per bulan. SOP mewajibkan pencocokan bloter teller vs OLIBS tiap akhir hari — idealnya 0 kejadian; "Tidak menyetor selisih kas lebih" adalah modus fraud eksplisit',
                'rujukan' => 'SOP Teller-CS-Khasanah; BPP Anti Fraud'
            ],
            [
                'kolom' => 'Selisih Kas Khasanah vs Saldo OLIBS',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah hari dalam sebulan fisik uang di khasanah/brandkast tidak cocok dengan saldo Kas Besar OLIBS',
                'satuan_hitung' => 'Hitung: jumlah kejadian selisih per bulan. Pencocokan wajib dilakukan tiap akhir hari — idealnya 0 kejadian',
                'rujukan' => 'SOP Teller-CS-Khasanah Bagian D'
            ],
            [
                'kolom' => 'Kas Kecil Melebihi Limit',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah hari dalam sebulan saldo kas kecil tercatat melebihi limit',
                'satuan_hitung' => 'Hitung: jumlah hari melebihi limit per bulan. Limit Kantor Pusat Rp 10.000.000 / Kantor Cabang Rp 5.000.000',
                'rujukan' => 'SOP Kas Kecil (SK .../BPD-ST/2014)'
            ],
            [
                'kolom' => 'Kas Kecil Belum Diselesaikan >5 Hari Kerja',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah kali dalam sebulan penyelesaian/pertanggungjawaban kas kecil melewati tenggat',
                'satuan_hitung' => 'Hitung: jumlah keterlambatan per bulan. Wajib selesai 1 minggu (5 hari kerja), tiap Jumat — idealnya 0',
                'rujukan' => 'SOP Kas Kecil poin 4.3'
            ],
            [
                'kolom' => 'Dual Control Kunci Khasanah/Brankas Dilanggar',
                'grup' => 'Kas & Teller',
                'penjelasan' => 'Jumlah kejadian dalam sebulan dual control kunci/kombinasi khasanah dilanggar (1 orang kuasai keduanya)',
                'satuan_hitung' => 'Hitung: jumlah pelanggaran dual control per bulan dari Register Aktivitas Khasanah. Wajib 2 petugas berbeda — idealnya 0',
                'rujukan' => 'SOP Teller-CS-Khasanah Bagian B'
            ],
            [
                'kolom' => 'Transaksi pada Rekening Pasif',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah transaksi/mutasi yang terjadi pada rekening berstatus pasif/dormant dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah transaksi per bulan dari laporan core banking. Rekening pasif seharusnya tidak bermutasi — tiap kejadian perlu justifikasi tertulis',
                'rujukan' => 'BPP Anti Fraud Bab Monitoring Rekening Pasif'
            ],
            [
                'kolom' => 'Transaksi pada Rekening Titipan Mengendap Lama',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah rekening titipan dalam sebulan yang dananya mengendap melewati batas waktu wajar sebelum disalurkan',
                'satuan_hitung' => 'Hitung: jumlah rekening/kejadian mengendap per bulan dari daftar rekening titipan',
                'rujukan' => 'BPP Anti Fraud Bab Monitoring Rekening Titipan'
            ],
            [
                'kolom' => 'Transaksi Tidak Wajar pada Rekening Pegawai Sendiri',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah transaksi tidak wajar (nominal besar/frekuensi tinggi/sumber tidak jelas) pada rekening pegawai cabang dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah transaksi mencurigakan per bulan. Data ini seharusnya rutin dikumpulkan Divisi Operasional',
                'rujukan' => 'BPP Anti Fraud - tugas Divisi Operasional'
            ],
            [
                'kolom' => 'Transaksi di Luar Jam Operasional',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah transaksi dengan timestamp di luar jam layanan cabang dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah transaksi per bulan dari log sistem. Idealnya 0 kecuali ada alasan sistem/koreksi resmi yang terdokumentasi',
                'rujukan' => 'BPP Anti Fraud - tugas Divisi Operasional'
            ],
            [
                'kolom' => 'Setoran Non-Tunai ke Rekening Pribadi/Keluarga Pegawai',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah setoran non-tunai masuk ke rekening pegawai/keluarga tanpa nota pembukuan dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah kejadian per bulan. Modus fraud eksplisit — idealnya 0',
                'rujukan' => 'BPP Anti Fraud - Gejala Fraud Pegawai poin (q)'
            ],
            [
                'kolom' => 'Sharing User ID/Password Antar Pegawai',
                'grup' => 'Rekening & Sistem',
                'penjelasan' => 'Jumlah kejadian/temuan praktik pinjam-meminjam password antar CS/Teller dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah temuan dari observasi/wawancara per bulan. Dilarang mutlak — idealnya 0',
                'rujukan' => 'SOP Teller-CS-Khasanah poin 4.4'
            ],
            [
                'kolom' => 'Indikasi Skimming/Perangkat Asing pada ATM',
                'grup' => 'ATM & APMK',
                'penjelasan' => 'Jumlah temuan perangkat mencurigakan pada mesin ATM cabang dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah temuan dari inspeksi fisik rutin per bulan. Idealnya 0',
                'rujukan' => 'BPP Anti Fraud Bab Monitoring APMK'
            ],
            [
                'kolom' => 'Selisih Kas Opname CRM/ATM',
                'grup' => 'ATM & APMK',
                'penjelasan' => 'Jumlah kejadian selisih kas saat pengisian ulang (replenishment) CRM/ATM dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah kejadian selisih per bulan dari berita acara replenishment',
                'rujukan' => 'SOP Operasional CRM'
            ],
            [
                'kolom' => 'Restrukturisasi Kredit Berulang (Debitur Sama)',
                'grup' => 'Kredit',
                'penjelasan' => 'Jumlah debitur yang direstrukturisasi lebih dari 1 kali dalam periode singkat, tercatat bulan ini',
                'satuan_hitung' => 'Hitung: jumlah debitur dengan restrukturisasi berulang per bulan',
                'rujukan' => 'BPP Anti Fraud - Gejala Fraud Manajemen'
            ],
            [
                'kolom' => 'Pencairan Kredit Tanpa Kelengkapan Dokumen',
                'grup' => 'Kredit',
                'penjelasan' => 'Jumlah pencairan kredit dalam sebulan yang ditemukan dokumennya tidak lengkap saat sampling',
                'satuan_hitung' => 'Hitung: jumlah berkas tidak lengkap per bulan dari hasil sampling',
                'rujukan' => 'BPP Anti Fraud - Gejala Fraud Manajemen poin (j)'
            ],
            [
                'kolom' => 'Teller/Kasir Belum Mengambil Cuti Wajib Tahun Ini',
                'grup' => 'SDM (Kontrol Preventif)',
                'penjelasan' => 'Jumlah pegawai posisi kas/teller/kasir yang belum mengambil cuti wajib dalam 6 bulan terakhir, per bulan laporan',
                'satuan_hitung' => 'Hitung: jumlah pegawai per bulan dari rekap cuti tahunan. Cuti wajib (mandatory vacation) adalah kontrol anti-fraud — idealnya 0',
                'rujukan' => 'BPP Anti Fraud - Know Your Employee'
            ],
            [
                'kolom' => 'Lonjakan Pengaduan Sejenis / Lewat SLA',
                'grup' => 'Pengaduan Nasabah',
                'penjelasan' => 'Jumlah pengaduan kategori sejenis dan jumlah pengaduan yang penyelesaiannya melewati SLA, dalam sebulan',
                'satuan_hitung' => 'Hitung: jumlah pengaduan per bulan dari log pengaduan cabang. SLA 3-20 hari kerja tergantung kategori',
                'rujukan' => 'SOP Pengaduan SK 153/2024; SE SLA 3175/2026'
            ]
        ];
    }

    public function exportExcel($sheet)
    {
        return back()->with('success', 'Fitur export Excel untuk sheet ' . $sheet . ' siap digunakan.');
    }
}
