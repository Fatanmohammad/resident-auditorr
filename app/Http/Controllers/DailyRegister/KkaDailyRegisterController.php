<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Http\Requests\Offsite\UpdateKkaRaRequest;
use App\Http\Requests\Offsite\UpdateKkaAdminRequest;
use App\Http\Requests\Offsite\UploadDumpRequest;
use App\Services\Offsite\DumpCbsParser;
use App\Services\Offsite\DumpDpkParser;
use App\Services\Offsite\DumpKreditParser;
use App\Services\Offsite\DumpBiayaParser;
use App\Services\Offsite\DumpPengaduanParser;
use App\Services\Offsite\DumpKartuAtmParser;
use App\Services\DailyRegister\DailyRegisterSamplingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Offsite\AuditLog;

class KkaDailyRegisterController extends Controller
{
    /**
     * HANYA untuk menampilkan halaman UI (Blade)
     */
    public function index(Request $request)
    {
        $path = $request->path();
        
        // Jika route adalah rekapitulasi-cabang
        if (str_contains($path, 'rekapitulasi-cabang')) {
            // Ambil SEMUA Induk Cabang (parent_id = null)
            $indukCabangs = \App\Models\Cabang::whereNull('parent_id')
                ->where('kode_cabang', 'like', 'BS-%')
                ->orderBy('kode_cabang', 'asc')
                ->get();

            $rekapCabang = $indukCabangs->map(function($induk) {
                // Ambil KC Unit langsung dari tabel units
                $kcUnit = \App\Models\Unit::where('cabang_id', $induk->id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                
                $kodeKC = $kcUnit ? $kcUnit->unit_code : '-';
                $namaKC = $kcUnit ? $kcUnit->unit_name : $induk->nama_cabang;

                // Kumpulkan semua unit code yang dinaungi KC ini (KC itu sendiri + KCP/KCPLK)
                $allUnitCodes = \App\Models\Unit::where('cabang_id', $induk->id)
                    ->whereNotIn('unit_type', ['Payment Point'])
                    ->pluck('unit_code')->toArray();

                $anakCabangIds = \App\Models\Cabang::where('parent_id', $induk->id)->pluck('id')->toArray();
                $anakUnitCodes = \App\Models\Unit::whereIn('cabang_id', $anakCabangIds)
                    ->whereNotIn('unit_type', ['Payment Point'])
                    ->pluck('unit_code')->toArray();
                
                $allUnitCodes = array_merge($allUnitCodes, $anakUnitCodes);

                $totalLowAll = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('kode_unit', $allUnitCodes)->where('risk_awal', 'Low')->count();
                $totalModerateAll = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('kode_unit', $allUnitCodes)->where('risk_awal', 'Moderate')->count();
                $totalHighAll = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('kode_unit', $allUnitCodes)->where('risk_awal', 'High')->count();

                $latestPhysicalKka = \App\Models\DailyRegister\PhysicalKka::where('kode_unit', $kodeKC)->orderBy('created_at', 'desc')->first();

                // Hitung Kapasitas Harian & Kontrol Mingguan (SOP 04.2)
                $kapasitasService = app(\App\Services\DailyRegister\KapasitasHarianService::class);
                $kapasitas = $kapasitasService->hitungKapasitas($kodeKC);

                return [
                    'id' => $induk->id,
                    'physical_kka_id' => $latestPhysicalKka ? $latestPhysicalKka->id : null,
                    'kode_unit' => $kodeKC,
                    'nama_cabang' => $namaKC,
                    'total_low_all' => $totalLowAll,
                    'total_moderate_all' => $totalModerateAll,
                    'total_high_all' => $totalHighAll,
                    'total_risiko_all' => $totalModerateAll + $totalHighAll,
                    'kapasitas' => $kapasitas,
                ];
            });

            return view('daily_register.admin_index', compact('rekapCabang'));
        }

        // Untuk submenu lainnya (cash opname, dsb)
        $isSubmenu = true;
        if (str_contains($path, 'cash-opname')) {
            $defaultTab = 'KKA_Cash_Opname';
            $defaultTabName = 'KKA Cash Opname';
        } elseif (str_contains($path, 'observasi-lingkungan')) {
            $defaultTab = 'KKA_Observasi_Lingkungan';
            $defaultTabName = 'KKA Observasi Lingkungan';
        } elseif (str_contains($path, 'stok-opname')) {
            $defaultTab = 'KKA_Stok_Opname';
            $defaultTabName = 'KKA Stok Opname';
        } else {
            $defaultTab = 'KKA_Teller_Kas';
            $defaultTabName = 'KKA Teller & Kas';
            $isSubmenu = false;
        }

        $user = auth()->user();
        $isRa = strtolower($user->role) === 'ra';

        // Ambil seluruh Kantor Cabang (KC) untuk dropdown pemilihan Admin
        $kcUnits = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])
            ->orderBy('unit_code', 'asc')
            ->get();

        // Tentukan selected unit
        $selectedKodeUnit = $request->input('kode_unit');
        $selectedCabangId = $request->input('cabang_id') ?: $request->input('cabang');

        if ($selectedKodeUnit && str_contains($selectedKodeUnit, '?')) {
            $selectedKodeUnit = explode('?', $selectedKodeUnit)[0];
        }

        if (!$selectedKodeUnit) {
            if ($selectedCabangId) {
                $kc = $kcUnits->firstWhere('cabang_id', $selectedCabangId);
                if ($kc) $selectedKodeUnit = $kc->unit_code;
            } elseif ($isRa && $user->cabang_id) {
                $kc = $kcUnits->firstWhere('cabang_id', $user->cabang_id);
                if ($kc) {
                    $selectedKodeUnit = $kc->unit_code;
                    $selectedCabangId = $kc->cabang_id;
                }
            } else {
                // Admin: Default ke KC yang memiliki data temuan aktif
                $kcWithData = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('risk_awal', ['Moderate', 'High'])
                    ->whereNotNull('kode_unit')
                    ->value('kode_unit');
                if ($kcWithData) {
                    $selectedKodeUnit = $kcWithData;
                } else {
                    $firstKc = $kcUnits->first();
                    $selectedKodeUnit = $firstKc ? $firstKc->unit_code : '003';
                }
            }
        }

        if ($selectedKodeUnit && !$selectedCabangId) {
            $kc = $kcUnits->firstWhere('unit_code', $selectedKodeUnit);
            if ($kc) $selectedCabangId = $kc->cabang_id;
        }

        $allChecklists = \App\Models\DailyRegister\DailyRegisterChecklist::orderBy('sort_order')->get()->groupBy('source_sheet');

        return view('daily_register.index', compact('defaultTab', 'defaultTabName', 'isSubmenu', 'kcUnits', 'selectedKodeUnit', 'selectedCabangId', 'allChecklists'));
    }

    /**
     * Menampilkan Halaman Form Upload DUMP khusus Daily Register
     */
    public function create()
    {
        $user = auth()->user();
        
        if (in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            $cabangs = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->orderBy('unit_name', 'asc')->get();
        } else {
            // Memastikan RA hanya melihat KC miliknya
            $cabangs = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->where(function($query) use ($user) {
                    if ($user->cabang_id) {
                        $query->where('cabang_id', $user->cabang_id)
                              ->orWhereIn('cabang_id', function($sub) use ($user) {
                                  $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                              });
                    }
                })->orderBy('unit_name', 'asc')->get();
        }
        
        return view('daily_register.upload', compact('cabangs'));
    }

    /**
     * Memproses upload file DUMP khusus Daily Register
     */
    public function upload(
        UploadDumpRequest $request,
        DumpCbsParser $cbsParser,
        DumpDpkParser $dpkParser,
        DumpKreditParser $kreditParser,
        DumpBiayaParser $biayaParser,
        DumpPengaduanParser $pengaduanParser,
        DumpKartuAtmParser $kartuAtmParser,
        DailyRegisterSamplingService $samplingService
    ) {
        $user = auth()->user();
        $file = $request->file('file_csv');
        $jenisFile = $request->jenis_file;
        $kodeUnitDipilih = $request->kode_unit;
        $periode = $request->input('periode', date('Y-m-01')); 

        // VALIDASI HAK AKSES
        if (!in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai', 'ra'])) {
            $unitValid = \App\Models\Unit::where('unit_code', $kodeUnitDipilih)
                ->where(function($query) use ($user) {
                    if ($user->cabang_id) {
                        $query->where('cabang_id', $user->cabang_id)
                              ->orWhereIn('cabang_id', function($sub) use ($user) {
                                  $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                              });
                    }
                })->exists();

            if (!$unitValid) {
                return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki wewenang meng-upload data untuk unit/cabang tersebut.');
            }
        }

        $filePath = $file->storeAs('daily_register_staging', time() . '_' . $file->getClientOriginalName(), 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        DB::beginTransaction();
        try {
            $namaUnitDipilih = \App\Models\Unit::where('unit_code', $kodeUnitDipilih)->value('unit_name');
            $parseResult = null;
            // PARAMETER KETIGA ADALAH $isDailyRegister = true, PARAMETER KEEMPAT ADALAH $namaUnit
            switch ($jenisFile) {
                case 'DUMP_01': $parseResult = $cbsParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
                case 'DUMP_02': $parseResult = $dpkParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
                case 'DUMP_03': $parseResult = $kreditParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
                case 'DUMP_04': $parseResult = $biayaParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
                case 'DUMP_05': $parseResult = $pengaduanParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
                case 'DUMP_06': $parseResult = $kartuAtmParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih); break;
            }

            $totalLow      = is_array($parseResult) ? ($parseResult['total_low'] ?? 0) : 0;
            $totalModerate = is_array($parseResult) ? ($parseResult['total_moderate'] ?? 0) : 0;
            $totalHigh     = is_array($parseResult) ? ($parseResult['total_high'] ?? 0) : 0;

            AuditLog::create([
                'user_id'        => $user->id,
                'kode_unit'      => $kodeUnitDipilih,
                'jenis_file'     => $jenisFile,
                'nama_file'      => $file->getClientOriginalName(),
                'status'         => 'Berhasil',
                'total_low'      => $totalLow,
                'total_moderate' => $totalModerate,
                'total_high'     => $totalHigh,
            ]);

            DB::commit();

            if (file_exists($fullPath)) unlink($fullPath);

            // JALANKAN MESIN SAMPLING OTOMATIS KHUSUS DAILY REGISTER
            $samplingResult = $samplingService->runSampling($kodeUnitDipilih, $periode);

            // OTOMATIS SINKRONKAN TEMUAN KE MODUL RISK TRIGGER (SOP 06)
            try {
                app(\App\Services\RiskTrigger\RiskTriggerService::class)->syncAll($user, $kodeUnitDipilih);
            } catch (\Throwable $e) {
                \Log::warning("Auto-sync Risk Trigger gagal: " . $e->getMessage());
            }

            $msgExtra = isset($samplingResult['total_sampel']) 
                ? ' (' . $samplingResult['total_sampel'] . ' sampel KKA otomatis terpilih melalui sampling engine)' 
                : '';

            return redirect()->back()->with('success', 'File ' . $jenisFile . ' berhasil diunggah dan disimpan di tabel khusus Daily Register.' . $msgExtra);

        } catch (\Exception $e) {
            DB::rollBack();

            if (file_exists($fullPath)) unlink($fullPath);

            AuditLog::create([
                'user_id'     => $user->id,
                'kode_unit'   => $kodeUnitDipilih,
                'jenis_file'  => $jenisFile,
                'nama_file'   => $file->getClientOriginalName(),
                'status'      => 'Gagal',
                'pesan_error' => \Illuminate\Support\Str::limit($e->getMessage(), 250),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * HANYA untuk mengambil data JSON (dipanggil oleh Axios/AJAX di Javascript)
     * Automatic Filter berdasarkan Wewenang Cabang
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        $query = DailyRegisterFinding::query();

        // KHUSUS KKA DAILY REGISTER HANYA MODERATE DAN HIGH
        $query->whereIn('risk_awal', ['Moderate', 'High']);

        // 1. Filter Keamanan Wilayah RA vs Admin (HANYA KC)
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            // Dapatkan unit_code TAPI KHUSUS KC SAJA
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->pluck('unit_code');
            $query->whereIn('kode_unit', $unitCodes);
        } else {
            $targetUnit = $request->input('kode_unit');
            if ($targetUnit && str_contains($targetUnit, '?')) {
                $targetUnit = explode('?', $targetUnit)[0];
            }

            if ($targetUnit) {
                $query->where('kode_unit', $targetUnit);
            } elseif ($request->filled('cabang_id') || $request->filled('cabang')) {
                $cabangParam = $request->input('cabang_id') ?: $request->input('cabang');
                $unitCodes = \App\Models\Unit::where('cabang_id', $cabangParam)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->pluck('unit_code');
                $query->whereIn('kode_unit', $unitCodes);
            } else {
                // Secara default jika Admin tidak memberikan filter unit, cari KC dengan temuan aktif
                $kcWithData = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('risk_awal', ['Moderate', 'High'])
                    ->whereNotNull('kode_unit')
                    ->value('kode_unit');
                if ($kcWithData) {
                    $query->where('kode_unit', $kcWithData);
                } else {
                    $unitCodes = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->pluck('unit_code');
                    $query->whereIn('kode_unit', $unitCodes);
                }
            }
        }

        // CLONE untuk menghitung tab counts sebelum filter spesifik UI
        $baseQueryForTabs = clone $query;

        // 2. Filter Spesifik dari UI (Sheet KKA, Risk, Tanggal)
        if ($request->filled('source_sheet')) {
            $query->where('source_sheet', $request->source_sheet);
        }
        if ($request->filled('risk_awal')) {
            $query->where('risk_awal', $request->risk_awal);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }

        // Hitung Summary Global (sebelum pagination)
        $summaryQuery = clone $query;
        $totalException = $summaryQuery->count();
        $totalNominal = $summaryQuery->sum('nominal_terkait');
        $highRiskCount = (clone $summaryQuery)->where('risk_awal', 'High')->count();
        $selesaiReviewCount = (clone $summaryQuery)->where('status_review', 'Approved')->count();

        $findings = $query->with('checklistAnswers')->latest('tanggal_data')->paginate(15);

        $tabCounts = [
            'KKA_Teller_Kas' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Teller_Kas')->count(),
            'KKA_DPK_Layanan' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_DPK_Layanan')->count(),
            'KKA_Kredit' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Kredit')->count(),
            'KKA_Biaya_Beban' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Biaya_Beban')->count(),
            'KKA_Kartu_ATM' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Kartu_ATM')->count(),
            'KKA_Pengaduan' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Pengaduan')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $findings,
            'summary' => [
                'total_exception' => $totalException,
                'total_nominal'   => (float) $totalNominal,
                'high_risk'       => $highRiskCount,
                'selesai_review'  => $selesaiReviewCount,
                'tab_counts'      => $tabCounts
            ]
        ]);
    }

    /**
     * Mengambil master checklist per sheet (AJAX)
     */
    public function getChecklists(Request $request)
    {
        $sheet = $request->input('source_sheet');
        $query = \App\Models\DailyRegister\DailyRegisterChecklist::orderBy('sort_order');
        if ($sheet) {
            $query->where('source_sheet', $sheet);
        }
        return response()->json([
            'status' => 'success',
            'data'   => $query->get()
        ]);
    }

    /**
     * Hitung otomatis jumlah kejadian exception/temuan sebelumnya pada unit & sheet yang sama (termasuk saat ini)
     */
    protected function calculateRecurringCount(DailyRegisterFinding $finding): int
    {
        $unit = (string)$finding->kode_unit;
        $cleanUnit = str_replace('BS-', '', $unit);
        $targetUnits = array_values(array_unique(array_filter([$unit, $cleanUnit, 'BS-' . $cleanUnit])));

        $historyCount = DailyRegisterFinding::whereIn('kode_unit', $targetUnits)
            ->where('source_sheet', $finding->source_sheet)
            ->where('id', '!=', $finding->id)
            ->where(function ($q) {
                $q->whereHas('checklistAnswers', function ($qa) {
                    $qa->where('jawaban', 'Tidak');
                })
                ->orWhereIn('risk_final', ['Moderate', 'Moderate to High', 'High', 'Critical'])
                ->orWhere(function ($sub) {
                    $sub->whereNull('risk_final')
                        ->whereIn('risk_awal', ['Moderate', 'High', 'Critical']);
                });
            })
            ->count();

        return $historyCount + 1;
    }

    /**
     * Mengambil detail KKA lengkap dengan checklist answers & hitungan kejadian berulang
     */
    public function getReviewData($id)
    {
        $finding = DailyRegisterFinding::with(['checklistAnswers'])->findOrFail($id);

        // Kunci Otomatis Tanggal Ditemukan / Diuji: Saat RA pertama kali membuka KKA
        if (empty($finding->tanggal_ditemukan)) {
            $finding->tanggal_ditemukan = now()->format('Y-m-d');
            $finding->save();
        }

        // Hitung kejadian berulang di unit & source_sheet yang sama
        $recurringCount = $this->calculateRecurringCount($finding);

        $checklists = \App\Models\DailyRegister\DailyRegisterChecklist::where('source_sheet', $finding->source_sheet)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status'          => 'success',
            'finding'         => $finding,
            'recurring_count' => $recurringCount,
            'checklists'      => $checklists,
        ]);
    }

    /**
     * Update Inputan RA dengan Otomasi Penuh Skor Risiko & Checklist
     */
    public function updateRa(UpdateKkaRaRequest $request, $id)
    {
        $user = auth()->user();
        $finding = DailyRegisterFinding::findOrFail($id);

        // Jika user adalah RA, validasi berdasarkan izin kode_unit cabang yang diakses
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $allowedUnitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();

            if (!in_array($finding->kode_unit, $allowedUnitCodes)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data cabang ini.'
                ], 403);
            }
        }

        // Ambil checklist answers
        $rawChecklist = $request->input('checklist_answers');
        if (is_string($rawChecklist)) {
            $rawChecklist = json_decode($rawChecklist, true) ?: [];
        }

        $tidakCount = 0;
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                if (($ans['jawaban'] ?? 'Ya') === 'Tidak') {
                    $tidakCount++;
                }
            }
        }

        // Validasi kemunculan kondisional
        if ($tidakCount >= 1) {
            if (empty($request->input('jenis_exception_ra')) || empty($request->input('hasil_uji')) || empty($request->input('klarifikasi_unit'))) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi Gagal: Terdapat ' . $tidakCount . ' item checklist bernilai Tidak. Jenis Exception, Hasil Pengujian Lapangan, dan Klarifikasi Unit Kerja wajib diisi!'
                ], 422);
            }
            $jenisExceptionRa = $request->input('jenis_exception_ra');
            $hasilUji = $request->input('hasil_uji');
            $klarifikasiUnit = $request->input('klarifikasi_unit');
        } else {
            // Jika semua bernilai Ya, kosongkan kembali field exception
            $jenisExceptionRa = null;
            $hasilUji = null;
            $klarifikasiUnit = null;
        }

        // Kunci Tanggal Ditemukan / Diuji: Otomatis hari itu jika kosong, lalu dikunci
        $tanggalDitemukan = $finding->tanggal_ditemukan ?: now()->format('Y-m-d');

        // File Bukti PDF
        $fileBuktiPath = $finding->file_bukti;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $fileBuktiPath = $file->storeAs('daily_register_evidence', time() . '_' . $file->getClientOriginalName(), 'public');
        }

        // Otomasi Penuh & Penguncian Skor Risiko (Matriks 5x5)
        $criticalTrigger = $request->input('critical_trigger', 'Tidak') === 'Ya' ? 'Ya' : 'Tidak';
        $nominal = (float)($finding->nominal_terkait ?? 0);

        if ($tidakCount === 0 && $criticalTrigger === 'Tidak') {
            $skorDampak = 1;
            $skorKemungkinan = 1;
            $skorFinal = 1;
            $riskFinal = 'Low';
            $perluOnsite = 'Tidak Perlu';
        } else {
            // Skor Dampak (1-5)
            if ($criticalTrigger === 'Ya' || $nominal >= 500000000) {
                $skorDampak = 5;
            } else {
                $hasNominal = ($finding->nominal_terkait !== null && $nominal > 0);

                $chkScore = 1;
                if ($tidakCount >= 5) {
                    $chkScore = 4;
                } elseif ($tidakCount >= 3) {
                    $chkScore = 3;
                } elseif ($tidakCount >= 2) {
                    $chkScore = 2;
                } elseif ($tidakCount === 1) {
                    $chkScore = $hasNominal ? 1 : 2;
                }

                $nomScore = 0;
                if ($hasNominal) {
                    if ($nominal >= 100000000) {
                        $nomScore = 4;
                    } elseif ($nominal >= 50000000) {
                        $nomScore = 3;
                    } elseif ($nominal >= 10000000) {
                        $nomScore = 2;
                    } else {
                        $nomScore = 1;
                    }
                }

                $skorDampak = max($chkScore, $nomScore);
                if (!$hasNominal && $tidakCount >= 1 && $skorDampak < 2) {
                    $skorDampak = 2;
                }
                $skorDampak = min(max($skorDampak, 1), 5);
            }

            // Skor Kemungkinan (1-5) Berdasarkan Kejadian Berulang di Database
            $kejadianKe = $this->calculateRecurringCount($finding);

            if ($kejadianKe >= 7) {
                $skorKemungkinan = 5;
            } elseif ($kejadianKe >= 5) {
                $skorKemungkinan = 4;
            } elseif ($kejadianKe >= 3) {
                $skorKemungkinan = 3;
            } elseif ($kejadianKe == 2) {
                $skorKemungkinan = 2;
            } else {
                $skorKemungkinan = ($tidakCount >= 2) ? 2 : 1;
            }

            // Skor & Risk Final
            $skorFinal = $skorDampak * $skorKemungkinan;

            if ($criticalTrigger === 'Ya') {
                $riskFinal = 'Critical';
                if ($skorFinal < 16) $skorFinal = 16;
            } elseif ($skorFinal >= 16) {
                $riskFinal = 'Critical';
            } elseif ($skorFinal >= 10) {
                $riskFinal = 'High';
            } elseif ($skorFinal >= 8) {
                $riskFinal = 'Moderate to High';
            } elseif ($skorFinal >= 5) {
                $riskFinal = 'Moderate';
            } else {
                $riskFinal = 'Low';
            }

            // Perlu Onsite Inspection?
            if (in_array($riskFinal, ['Moderate to High', 'High', 'Critical']) || $skorFinal >= 8 || $criticalTrigger === 'Ya') {
                $perluOnsite = 'Perlu';
            } else {
                $perluOnsite = 'Tidak Perlu';
            }
        }

        // Update finding
        $finding->bukti_referensi = $request->input('bukti_referensi');
        $finding->critical_trigger = $criticalTrigger;
        $finding->jenis_exception_ra = $jenisExceptionRa;
        $finding->hasil_uji = $hasilUji;
        $finding->klarifikasi_unit = $klarifikasiUnit;
        $finding->skor_dampak = $skorDampak;
        $finding->skor_kemungkinan = $skorKemungkinan;
        $finding->skor_final = $skorFinal;
        $finding->risk_final = $riskFinal;
        $finding->risk_awal = $riskFinal;
        $finding->perlu_onsite = $perluOnsite;
        $finding->tanggal_ditemukan = $tanggalDitemukan;
        $finding->file_bukti = $fileBuktiPath;
        $finding->save();

        // Simpan jawaban checklist
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                $chkId = $ans['checklist_id'] ?? null;
                if ($chkId) {
                    \App\Models\DailyRegister\DailyRegisterChecklistAnswer::updateOrCreate(
                        [
                            'daily_register_finding_id' => $finding->id,
                            'checklist_id'              => $chkId,
                        ],
                        [
                            'jawaban'    => ($ans['jawaban'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya',
                            'keterangan' => $ans['keterangan'] ?? null,
                        ]
                    );
                }
            }
        }

        // Sinkronisasi otomatis ke modul Risk Trigger (SOP 06)
        try {
            app(\App\Services\RiskTrigger\RiskTriggerService::class)->syncAll($user, $finding->kode_unit);
        } catch (\Throwable $e) {
            \Log::warning("Auto-sync Risk Trigger gagal di updateRa: " . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Review RA dan checklist berhasil disimpan.',
            'data'    => $finding->load('checklistAnswers')
        ]);
    }

    /**
     * Update Review Admin
     */
    public function updateAdmin(UpdateKkaAdminRequest $request, $id)
    {
        try {
            $finding = DailyRegisterFinding::findOrFail($id);
            $finding->update($request->validated());

            return response()->json([
                'status'  => 'success',
                'message' => 'Review KKA berhasil diperbarui oleh Admin.',
                'data'    => $finding
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error Database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi Temuan KKA oleh RA / Cabang & Sinkronisasi ke RawMetric (SOP 01)
     */
    public function konfirmasiCabang(Request $request, $id)
    {
        $request->validate([
            'status_konfirmasi'     => 'required|in:Sesuai,Tidak Sesuai',
            'komitmen_penyelesaian' => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|string',
            'target_penyelesaian'   => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|date',
        ]);

        $finding = DailyRegisterFinding::findOrFail($id);

        $finding->update([
            'status_konfirmasi'     => $request->status_konfirmasi,
            'komitmen_penyelesaian' => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->komitmen_penyelesaian : null,
            'target_penyelesaian'   => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->target_penyelesaian : null,
            'tanggal_konfirmasi'    => now(),
        ]);

        // Sinkronisasi otomatis ke RawMetric berdasarkan unit dan periode temuan
        if ($finding->kode_unit && $finding->periode) {
            $this->syncRawMetricDeviations($finding->kode_unit, $finding->periode);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfirmasi KKA berhasil disimpan dan disinkronkan ke jadwal audit.',
            'data'    => $finding
        ]);
    }

    /**
     * Helper privat untuk kalkulasi ulang deviasi offsite ke tabel RawMetric
     */
    private function syncRawMetricDeviations(string $kodeUnit, string $periode)
    {
        $unit = \App\Models\Unit::where('unit_code', $kodeUnit)->first();
        if (!$unit) return;

        $yearPeriod = date('Y', strtotime($periode));

        $rawMetric = \App\Models\RawMetric::where('unit_id', $unit->id)
            ->where('period', 'LIKE', "$yearPeriod%")
            ->first();

        if ($rawMetric) {
            $totalTidakSesuai = DailyRegisterFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->count();

            $totalSignifikan = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->where('risk_awal', 'High')
                ->count();

            $rawMetric->update([
                'offsite_deviation'             => $totalTidakSesuai,
                'offsite_deviation_significant' => $totalSignifikan,
            ]);
        }
    }

    /**
     * API JSON: Mengambil Rekapitulasi 11 Area KKA Control & Integrasi (SOP 04.2)
     */
    public function getControlIntegrasi(Request $request, \App\Services\DailyRegister\KkaControlIntegrasiService $controlService)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $kodeUnit = $request->input('kode_unit');

        if ($kodeUnit && str_contains($kodeUnit, '?')) {
            $kodeUnit = explode('?', $kodeUnit)[0];
        }

        if (!$kodeUnit) {
            $cabangParam = $request->input('cabang_id') ?: $request->input('cabang');
            if ($cabangParam) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $cabangParam)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                }
            } elseif ($user && $user->cabang_id && strtolower($user->role) === 'ra') {
                $kcUnit = \App\Models\Unit::where('cabang_id', $user->cabang_id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                } elseif ($user->cabang && $user->cabang->kode_cabang) {
                    $kodeUnit = str_replace('BS-', '', $user->cabang->kode_cabang);
                }
            } elseif ($user && !empty($user->unit_code) && strtolower($user->role) === 'ra') {
                $kodeUnit = $user->unit_code;
            } else {
                $kcWithData = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('risk_awal', ['Moderate', 'High'])
                    ->whereNotNull('kode_unit')
                    ->value('kode_unit');
                if ($kcWithData) {
                    $kodeUnit = $kcWithData;
                } else {
                    $firstKc = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->first();
                    $kodeUnit = $firstKc ? $firstKc->unit_code : '003';
                }
            }
        }

        if ($kodeUnit && str_starts_with($kodeUnit, 'BS-')) {
            $clean = str_replace('BS-', '', $kodeUnit);
            $kcUnit = \App\Models\Unit::where('unit_code', $clean)->first();
            if ($kcUnit) {
                $kodeUnit = $kcUnit->unit_code;
            }
        }

        $tanggal = $request->input('tanggal');
        $data = $controlService->hitungControlIntegrasi($kodeUnit, $tanggal);

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Menjalankan ulang mesin sampling khusus Daily Register sesuai standar SOP 02
     */
    public function rerunSampling(Request $request, DailyRegisterSamplingService $samplingService)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $kodeUnit = $request->input('kode_unit');

        if ($kodeUnit && str_contains($kodeUnit, '?')) {
            $kodeUnit = explode('?', $kodeUnit)[0];
        }

        if (!$kodeUnit) {
            $cabangParam = $request->input('cabang_id') ?: $request->input('cabang');
            if ($cabangParam) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $cabangParam)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                }
            } elseif ($user && $user->cabang_id && strtolower($user->role) === 'ra') {
                $kcUnit = \App\Models\Unit::where('cabang_id', $user->cabang_id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                } elseif ($user->cabang && $user->cabang->kode_cabang) {
                    $kodeUnit = str_replace('BS-', '', $user->cabang->kode_cabang);
                }
            } elseif ($user && !empty($user->unit_code) && strtolower($user->role) === 'ra') {
                $kodeUnit = $user->unit_code;
            } else {
                $kcWithData = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('risk_awal', ['Moderate', 'High'])
                    ->whereNotNull('kode_unit')
                    ->value('kode_unit');
                if ($kcWithData) {
                    $kodeUnit = $kcWithData;
                } else {
                    $firstKc = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->first();
                    $kodeUnit = $firstKc ? $firstKc->unit_code : '003';
                }
            }
        }

        if ($kodeUnit && str_starts_with($kodeUnit, 'BS-')) {
            $clean = str_replace('BS-', '', $kodeUnit);
            $kcUnit = \App\Models\Unit::where('unit_code', $clean)->first();
            if ($kcUnit) {
                $kodeUnit = $kcUnit->unit_code;
            }
        }

        // VALIDASI HAK AKSES
        if (!in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai', 'pimsie'])) {
            if ($user->cabang_id) {
                $allowed = \App\Models\Unit::where('unit_code', $kodeUnit)
                    ->where(function($query) use ($user) {
                        $query->where('cabang_id', $user->cabang_id)
                              ->orWhereIn('cabang_id', function($sub) use ($user) {
                                  $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                              });
                    })->exists();

                if (!$allowed) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Akses ditolak! Anda tidak memiliki wewenang untuk cabang ini.',
                    ], 403);
                }
            }
        }

        $periode = $request->input('periode');
        $result = $samplingService->runSampling($kodeUnit, $periode);

        return response()->json($result);
    }
}