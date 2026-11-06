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
use App\Services\DailyRegister\AutoRiskScoringService;
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
        // Backfill ringan untuk data existing yang deskripsinya masih kosong atau '-' tapi memiliki nilai di rincian
        try {
            DailyRegisterFinding::where(function($q) {
                $q->whereNull('deskripsi')->orWhere('deskripsi', '')->orWhere('deskripsi', '-');
            })->whereNotNull('rincian')->where('rincian', '!=', '')->where('rincian', '!=', '-')
            ->update(['deskripsi' => DB::raw('rincian')]);

            // Normalisasi data temuan yang belum diuji agar tidak menyimpan default risk_final 'Low'
            DailyRegisterFinding::where(function($q) {
                $q->where('review_status', DailyRegisterFinding::STATUS_BELUM_DIUJI)
                  ->orWhereNull('review_status');
            })->whereNull('hasil_uji')
            ->where('risk_final', 'Low')
            ->update(['risk_final' => null]);
        } catch (\Throwable $e) {}

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
            $defaultTab = $request->query('source_sheet', 'KKA_Teller_Kas');
            if ($defaultTab === 'KKA_Teller') {
                $defaultTab = 'KKA_Teller_Kas';
            }
            $sheetNameMap = [
                'KKA_Teller_Kas'   => 'KKA Teller & Kas',
                'KKA_Teller'       => 'KKA Teller & Kas',
                'KKA_DPK_Layanan'  => 'KKA DPK & Layanan',
                'KKA_Kredit'       => 'KKA Kredit',
                'KKA_Biaya_Beban'  => 'KKA Biaya & Beban',
                'KKA_Kartu_ATM'    => 'KKA Kartu ATM',
                'KKA_Pengaduan'    => 'KKA Pengaduan',
            ];
            $defaultTabName = $sheetNameMap[$defaultTab] ?? 'KKA Teller & Kas';
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
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

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
                case 'DUMP_01': $parseResult = $cbsParser->parse($fullPath, $kodeUnitDipilih, true, $namaUnitDipilih, $periode, $tanggalMulai, $tanggalSelesai); break;
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
        // Backfill ringan untuk data existing yang deskripsinya masih kosong atau '-' tapi memiliki nilai di rincian
        try {
            DailyRegisterFinding::where(function($q) {
                $q->whereNull('deskripsi')->orWhere('deskripsi', '')->orWhere('deskripsi', '-');
            })->whereNotNull('rincian')->where('rincian', '!=', '')->where('rincian', '!=', '-')
            ->update(['deskripsi' => DB::raw('rincian')]);

            // Normalisasi data temuan yang belum diuji agar tidak menyimpan default risk_final 'Low'
            DailyRegisterFinding::where(function($q) {
                $q->where('review_status', DailyRegisterFinding::STATUS_BELUM_DIUJI)
                  ->orWhereNull('review_status');
            })->whereNull('hasil_uji')
            ->where('risk_final', 'Low')
            ->update(['risk_final' => null]);
        } catch (\Throwable $e) {}

        $user = auth()->user();
        $isRa = ($user && strtolower($user->role) === 'ra');
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

            if ($targetUnit && strtoupper($targetUnit) !== 'ALL') {
                $query->where('kode_unit', $targetUnit);
            } elseif (strtoupper($targetUnit ?? '') === 'ALL') {
                // Admin memilih Semua Cabang: tampilkan semua unit KC/KCU/KP
                $unitCodes = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->pluck('unit_code');
                $query->whereIn('kode_unit', $unitCodes);
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

        // 2. Filter Spesifik dari UI (Sheet KKA, Risk, Tanggal, Status Workflow)
        if ($request->filled('source_sheet')) {
            $reqSheet = $request->source_sheet;
            if (in_array($reqSheet, ['KKA_Teller_Kas', 'KKA_Teller'])) {
                $query->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']);
            } else {
                $query->where('source_sheet', $reqSheet);
            }
        }
        if ($request->filled('risk_awal')) {
            $query->where('risk_awal', $request->risk_awal);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }
        if ($request->filled('review_status')) {
            $statusParam = $request->input('review_status');
            if ($statusParam === 'BELUM_DIUJI') {
                $query->where(function($q) {
                    $q->where('review_status', DailyRegisterFinding::STATUS_BELUM_DIUJI)
                      ->orWhere(function($sub) {
                          $sub->whereNull('review_status')
                              ->where(function($s) {
                                  $s->whereNull('status_review')->orWhere('status_review', 'Belum Direview');
                              })
                              ->whereNull('submitted_at');
                      });
                });
            } elseif ($statusParam === 'MENUNGGU_KONFIRMASI') {
                $query->where(function($q) {
                    $q->where('review_status', DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI)
                      ->orWhere(function($sub) {
                          $sub->whereNull('review_status')
                              ->whereNotNull('submitted_at')
                              ->where('status_review', '!=', 'Approved');
                      });
                });
            } elseif ($statusParam === 'PERLU_REVISI') {
                $query->where(function($q) {
                    $q->where('review_status', DailyRegisterFinding::STATUS_PERLU_REVISI)
                      ->orWhere('status_review', 'Revisi');
                });
            } elseif ($statusParam === 'SELESAI') {
                $query->where(function($q) {
                    $q->where('review_status', DailyRegisterFinding::STATUS_SELESAI)
                      ->orWhere('status_review', 'Approved');
                });
            }
        }

        // Hitung Summary Global (sebelum pagination)
        $summaryQuery = clone $query;
        $totalException = $summaryQuery->count();
        $totalNominal = $summaryQuery->sum('nominal_terkait');
        $highRiskCount = (clone $summaryQuery)->where('risk_awal', 'High')->count();
        $selesaiReviewCount = (clone $summaryQuery)->where(function($q) {
            $q->where('review_status', DailyRegisterFinding::STATUS_SELESAI)
              ->orWhere('status_review', 'Approved');
        })->count();

        // Smart Sorting:
        // RA: PERLU_REVISI ➔ BELUM_DIUJI ➔ MENUNGGU_KONFIRMASI ➔ SELESAI
        // Admin: MENUNGGU_KONFIRMASI ➔ PERLU_REVISI ➔ BELUM_DIUJI ➔ SELESAI
        if ($isRa) {
            $orderSql = "CASE 
                WHEN review_status = 'PERLU_REVISI' OR status_review = 'Revisi' THEN 1
                WHEN review_status = 'BELUM_DIUJI' OR review_status IS NULL OR (status_review = 'Belum Direview' AND submitted_at IS NULL) THEN 2
                WHEN review_status = 'MENUNGGU_KONFIRMASI' OR (status_review = 'Belum Direview' AND submitted_at IS NOT NULL) THEN 3
                WHEN review_status = 'SELESAI' OR status_review = 'Approved' THEN 4
                ELSE 5 END ASC";
        } else {
            $orderSql = "CASE 
                WHEN review_status = 'MENUNGGU_KONFIRMASI' OR (status_review = 'Belum Direview' AND submitted_at IS NOT NULL) THEN 1
                WHEN review_status = 'PERLU_REVISI' OR status_review = 'Revisi' THEN 2
                WHEN review_status = 'BELUM_DIUJI' OR review_status IS NULL OR (status_review = 'Belum Direview' AND submitted_at IS NULL) THEN 3
                WHEN review_status = 'SELESAI' OR status_review = 'Approved' THEN 4
                ELSE 5 END ASC";
        }

        $findings = $query->with('checklistAnswers')
            ->orderByRaw($orderSql)
            ->latest('tanggal_data')
            ->latest('id')
            ->paginate(25);

        // Hitung sinyal status per 6 Tab KKA Utama (Total, Perlu Revisi, Menunggu Konfirmasi, Selesai, dan 100% Selesai)
        $sheets = [
            'KKA_Teller_Kas',
            'KKA_DPK_Layanan',
            'KKA_Kredit',
            'KKA_Biaya_Beban',
            'KKA_Kartu_ATM',
            'KKA_Pengaduan',
        ];

        $tabCounts = [];
        $tabStatuses = [];
        foreach ($sheets as $sh) {
            $shQuery = (clone $baseQueryForTabs);
            if ($sh === 'KKA_Teller_Kas') {
                $shQuery->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']);
            } else {
                $shQuery->where('source_sheet', $sh);
            }
            $totalSh = $shQuery->count();
            $tabCounts[$sh] = $totalSh;

            $perluRevisiSh = (clone $shQuery)->where(function($q) {
                $q->where('review_status', DailyRegisterFinding::STATUS_PERLU_REVISI)
                  ->orWhere('status_review', 'Revisi');
            })->count();

            $menungguSh = (clone $shQuery)->where(function($q) {
                $q->where('review_status', DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI)
                  ->orWhere(function($sub) {
                      $sub->whereNull('review_status')->whereNotNull('submitted_at')->where('status_review', '!=', 'Approved');
                  });
            })->count();

            $selesaiSh = (clone $shQuery)->where(function($q) {
                $q->where('review_status', DailyRegisterFinding::STATUS_SELESAI)
                  ->orWhere('status_review', 'Approved');
            })->count();

            $belumSh = max(0, $totalSh - $perluRevisiSh - $menungguSh - $selesaiSh);

            $tabStatuses[$sh] = [
                'total'               => $totalSh,
                'perlu_revisi'        => $perluRevisiSh,
                'menunggu_konfirmasi' => $menungguSh,
                'belum_diuji'         => $belumSh,
                'selesai'             => $selesaiSh,
                'is_all_selesai'      => ($totalSh > 0 && $selesaiSh === $totalSh),
            ];
        }

        // Backward compatibility for legacy tests/views looking up KKA_Teller, KKA_Kas_Kecil, KKA_Kas_Khasanah
        $tabCounts['KKA_Teller'] = $tabCounts['KKA_Teller_Kas'] ?? 0;
        $tabStatuses['KKA_Teller'] = $tabStatuses['KKA_Teller_Kas'] ?? [];
        $tabCounts['KKA_Kas_Kecil'] = 0;
        $tabStatuses['KKA_Kas_Kecil'] = [];
        $tabCounts['KKA_Kas_Khasanah'] = 0;
        $tabStatuses['KKA_Kas_Khasanah'] = [];

        $activeSheet = $request->input('source_sheet', 'KKA_Teller_Kas');
        if ($activeSheet === 'KKA_Teller') {
            $activeSheet = 'KKA_Teller_Kas';
        }
        $currentSheetCounts = $tabStatuses[$activeSheet] ?? [
            'total'               => 0,
            'perlu_revisi'        => 0,
            'menunggu_konfirmasi' => 0,
            'belum_diuji'         => 0,
            'selesai'             => 0,
            'is_all_selesai'      => false,
        ];

        // Backlog Kehadiran RA: Jika RA tidak masuk kerja beberapa hari, transaksi tetap wajib muncul
        $todayStr = now()->toDateString();
        $backlogCount = (clone $baseQueryForTabs)
            ->whereDate('tanggal_data', '<', $todayStr)
            ->where(function($q) {
                $q->whereNull('review_status')
                  ->orWhere('review_status', '!=', DailyRegisterFinding::STATUS_SELESAI);
            })
            ->where(function($q) {
                $q->whereNull('status_review')
                  ->orWhere('status_review', '!=', 'Approved');
            })
            ->count();

        return response()->json([
            'status'  => 'success',
            'data'    => $findings,
            'summary' => [
                'total_exception'        => $totalException,
                'total_nominal'          => (float) $totalNominal,
                'high_risk'              => $highRiskCount,
                'selesai_review'         => $selesaiReviewCount,
                'total_belum_dikerjakan' => max(0, $totalException - $selesaiReviewCount),
                'backlog_count'          => $backlogCount,
                'tab_counts'             => $tabCounts,
                'tab_statuses'           => $tabStatuses,
                'current_sheet_counts'   => $currentSheetCounts,
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

        $sourceSheet = $finding->source_sheet;
        $targetSheets = in_array($sourceSheet, ['KKA_Teller', 'KKA_Teller_Kas', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah'])
            ? ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']
            : [$sourceSheet];

        $historyCount = DailyRegisterFinding::whereIn('kode_unit', $targetUnits)
            ->whereIn('source_sheet', $targetSheets)
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

        // Auto-seed jika master checklist belum terisi di database
        if (\App\Models\DailyRegister\DailyRegisterChecklist::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DailyRegisterChecklistSeeder',
                '--force' => true,
            ]);
        }

        // Hitung kejadian berulang di unit & source_sheet yang sama
        $recurringCount = $this->calculateRecurringCount($finding);

        $sourceSheet = $finding->source_sheet;
        if (in_array($sourceSheet, ['KKA_Teller', 'KKA_Teller_Kas', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah'])) {
            $sourceSheet = 'KKA_Teller_Kas';
        }

        $checklists = \App\Models\DailyRegister\DailyRegisterChecklist::where(function($q) use ($sourceSheet) {
            if ($sourceSheet === 'KKA_Teller_Kas') {
                $q->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller']);
            } else {
                $q->where('source_sheet', $sourceSheet);
            }
        })->orderBy('sort_order')->get();

        // Deteksi Keyword "Cek" & "TPP" (Tunjangan Perbaikan Penghasilan):
        // Ambil teks deskripsi dari kolom Ket. Transaksi (ket_tx / rincian / deskripsi / kategori / jenis_exception_awal).
        $ketTx = ($finding->ket_tx ?? '') . ' ' . ($finding->rincian ?? '') . ' ' . ($finding->deskripsi ?? '') . ' ' . ($finding->kategori ?? '') . ' ' . ($finding->jenis_exception_awal ?? '');
        $hasCek = (stripos($ketTx, 'cek') !== false);
        $hasTpp = (preg_match('/\bTPP\b/i', $ketTx) || stripos($ketTx, 'tunjangan perbaikan penghasilan') !== false || stripos($ketTx, 'tpp') !== false);

        if (($hasCek || $hasTpp) && in_array($sourceSheet, ['KKA_Teller', 'KKA_Teller_Kas'])) {
            $checklists = $checklists->map(function ($c) use ($hasCek, $hasTpp) {
                if ($c->item_no == 1 && $c->sub_area_code === 'A1') {
                    $prefix = '';
                    if ($hasCek && $hasTpp) {
                        $prefix = 'Terdapat Cek & TPP: ';
                    } elseif ($hasCek) {
                        $prefix = 'Terdapat Cek: ';
                    } elseif ($hasTpp) {
                        $prefix = 'Terdapat TPP: ';
                    }
                    $baseChecklist = preg_replace('/^(Terdapat (?:Cek & TPP|Cek|TPP):\s*)+/', '', $c->item_checklist);
                    $c->item_checklist = $prefix . $baseChecklist;
                }
                return $c;
            });
        }

        return response()->json([
            'status'          => 'success',
            'finding'         => $finding,
            'recurring_count' => $recurringCount,
            'checklists'      => $checklists,
            'has_cek'         => $hasCek,
            'has_tpp'         => $hasTpp,
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

            // Jika status sudah SELESAI, KKA terkunci menjadi Read-Only
            if ($finding->review_status === DailyRegisterFinding::STATUS_SELESAI || ($finding->status_review ?? '') === 'Approved') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak (403 Forbidden): KKA ini telah selesai dan dikonfirmasi oleh Admin (Read-Only).'
                ], 403);
            }
        }

        // Validasi Anti-Tamper: Tanggal ditemukan terkunci otomatis dan tidak boleh dimanipulasi
        if ($request->has('tanggal_ditemukan') && !empty($finding->tanggal_ditemukan)) {
            $inputTgl = date('Y-m-d', strtotime($request->input('tanggal_ditemukan')));
            $lockedTgl = date('Y-m-d', strtotime($finding->tanggal_ditemukan));
            if ($inputTgl !== $lockedTgl) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal ditemukan telah terkunci dan tidak dapat diubah.',
                    'errors' => [
                        'tanggal_ditemukan' => ['Tanggal ditemukan telah terkunci otomatis dan tidak dapat diubah secara manual.']
                    ]
                ], 422);
            }
        }

        // Ambil grup sub-area checklist yang dipilih RA
        $rawSelectedSubAreas = $request->input('selected_sub_areas');
        $selectedSubAreas = [];
        if (is_string($rawSelectedSubAreas)) {
            $selectedSubAreas = json_decode($rawSelectedSubAreas, true) ?: [];
        } elseif (is_array($rawSelectedSubAreas)) {
            $selectedSubAreas = $rawSelectedSubAreas;
        }

        // Ambil checklist answers
        $rawChecklist = $request->input('checklist_answers');
        if (is_string($rawChecklist)) {
            $rawChecklist = json_decode($rawChecklist, true) ?: [];
        }

        // Validasi: Jika request secara eksplisit mengirim selected_sub_areas tetapi kosong
        if ($request->has('selected_sub_areas') && empty($selectedSubAreas)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi Gagal: Anda wajib mencentang minimal 1 grup dropdown Sub-Area Checklist sebelum menyimpan pemeriksaan.'
            ], 422);
        }

        // Jika selected_sub_areas tidak dikirim sama sekali (misal dari tes fitur legacy/pemanggilan lama):
        if (!$request->has('selected_sub_areas') && !empty($rawChecklist) && is_array($rawChecklist)) {
            $chkIds = collect($rawChecklist)->pluck('checklist_id')->filter()->toArray();
            if (!empty($chkIds)) {
                $selectedSubAreas = \App\Models\DailyRegister\DailyRegisterChecklist::whereIn('id', $chkIds)
                    ->pluck('sub_area_code')
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        $tidakCount = 0;
        $chkMap = collect();
        if (is_array($rawChecklist) && !empty($rawChecklist)) {
            $chkMap = \App\Models\DailyRegister\DailyRegisterChecklist::whereIn('id', collect($rawChecklist)->pluck('checklist_id')->filter())
                ->get()
                ->keyBy('id');

            foreach ($rawChecklist as $ans) {
                $chkId = $ans['checklist_id'] ?? null;
                $chkModel = $chkId ? ($chkMap[$chkId] ?? null) : null;
                if (!empty($selectedSubAreas) && $chkModel && !in_array($chkModel->sub_area_code, $selectedSubAreas)) {
                    continue;
                }

                $jwb = $ans['jawaban'] ?? '';
                if ($jwb === 'Tidak' || $jwb === '✗') {
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
        $isAutoRisk = $request->boolean('use_auto_risk', false) || $request->has('use_auto_risk');

        if ($isAutoRisk) {
            $riskService = app(AutoRiskScoringService::class);
            $evalResult = $riskService->evaluateDailyRegister(
                $finding->kode_unit,
                $finding->source_sheet,
                $nominal,
                $tidakCount,
                $criticalTrigger === 'Ya',
                $finding->id,
                [
                    'has_critical_security' => ($criticalTrigger === 'Ya'),
                    'has_warkat_diff'       => in_array($finding->source_sheet, ['KKA_Kartu_ATM']),
                    'has_cash_diff'         => in_array($finding->source_sheet, ['KKA_Teller', 'KKA_Teller_Kas', 'KKA_Kas_Khasanah']) && ($criticalTrigger === 'Ya' || stripos(($finding->deskripsi ?? '') . ($finding->rincian ?? ''), 'selisih') !== false),
                ]
            );

            $skorDampak = $evalResult['impact'];
            $skorKemungkinan = $evalResult['likelihood'];
            $skorFinal = $evalResult['risk_score'];
            $riskFinal = $evalResult['risk_level'];
            $perluOnsite = $evalResult['perlu_onsite'];
        } elseif ($tidakCount === 0 && $criticalTrigger === 'Tidak') {
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

        // Simpan Langsung ke Arsip (Tanpa Tunggu Admin):
        // Pada modul Daily Register, begitu RA menekan tombol Simpan / Selesai pada suatu pemeriksaan KKA,
        // data langsung tersimpan final dan otomatis masuk ke menu Lihat Arsip Riwayat Selesai.
        $noArsip = trim((string)$request->input('no_arsip'));
        if (empty($noArsip) && empty($finding->no_arsip)) {
            $noArsip = 'ARSIP-' . date('Ymd') . '-' . str_pad((string)$finding->id, 4, '0', STR_PAD_LEFT);
        }
        $finding->no_arsip = $noArsip ?: $finding->no_arsip;

        if ($request->filled('tgl_keluhan')) {
            $finding->tgl_keluhan = $request->input('tgl_keluhan');
        }
        if ($request->filled('tgl_buku')) {
            $finding->tgl_buku = $request->input('tgl_buku');
        }
        if ($request->filled('status_keluhan')) {
            $finding->status_keluhan = $request->input('status_keluhan');
        }
        if ($finding->source_sheet === 'KKA_Pengaduan' || $request->filled('tgl_keluhan') || $request->filled('status_keluhan')) {
            $finding->lama_hari_kerja = (string) $this->calculateNetworkdays(
                $request->input('tgl_keluhan') ?: $finding->tanggal_data,
                $request->input('tgl_buku') ?: now()->toDateString(),
                $request->input('status_keluhan') ?: $finding->status_keluhan
            );
        }

        if (strtolower($user->role) === 'ra') {
            $finding->review_status = DailyRegisterFinding::STATUS_MENUNGGU_KONFIRMASI;
            $finding->status_review = 'Menunggu Konfirmasi';
            $finding->submitted_at  = now();
            $msg = 'Pemeriksaan KKA berhasil diajukan dan kini menunggu verifikasi/konfirmasi Admin.';
        } else {
            $finding->review_status = DailyRegisterFinding::STATUS_SELESAI;
            $finding->status_review = 'Approved';
            $finding->submitted_at  = now();
            $finding->reviewed_at   = now();
            $finding->reviewed_by   = $user->name ?? 'Administrator';
            $msg = 'Review KKA berhasil disimpan langsung dan disetujui (Selesai).';
        }

        if (!empty($selectedSubAreas)) {
            $finding->selected_sub_areas = array_values(array_unique($selectedSubAreas));
        }

        $finding->save();

        // Hapus jawaban checklist lama dari sub-area yang tidak dicentang / dinonaktifkan RA
        if (!empty($selectedSubAreas)) {
            $sourceSheet = $finding->source_sheet;
            $deselectedChkIds = \App\Models\DailyRegister\DailyRegisterChecklist::where(function($q) use ($sourceSheet) {
                if (in_array($sourceSheet, ['KKA_Teller', 'KKA_Teller_Kas', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah'])) {
                    $q->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller']);
                } else {
                    $q->where('source_sheet', $sourceSheet);
                }
            })->whereNotIn('sub_area_code', $selectedSubAreas)->pluck('id');

            if ($deselectedChkIds->isNotEmpty()) {
                \App\Models\DailyRegister\DailyRegisterChecklistAnswer::where('daily_register_finding_id', $finding->id)
                    ->whereIn('checklist_id', $deselectedChkIds)
                    ->delete();
            }
        }

        // Simpan jawaban checklist (hanya untuk grup sub-area yang dipilih)
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                $chkId = $ans['checklist_id'] ?? null;
                $chkModel = $chkId ? ($chkMap[$chkId] ?? null) : null;
                if (!empty($selectedSubAreas) && $chkModel && !in_array($chkModel->sub_area_code, $selectedSubAreas)) {
                    continue;
                }
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
            'message' => $msg,
            'data'    => $finding->load('checklistAnswers')
        ]);
    }

    /**
     * Update Review Admin
     */
    public function updateAdmin(UpdateKkaAdminRequest $request, $id)
    {
        try {
            $user = auth()->user();
            $finding = DailyRegisterFinding::findOrFail($id);
            $validated = $request->validated();

            $statusReview = $validated['status_review'] ?? '';
            if ($statusReview === 'Approved') {
                $validated['review_status'] = DailyRegisterFinding::STATUS_SELESAI;
                $validated['reviewed_by'] = $user->name;
                $validated['reviewed_at'] = now();
                $validated['reviewer_note'] = $validated['catatan_reviewer'] ?? null;
            } elseif (in_array($statusReview, ['Need Revision', 'Revisi', 'Rejected'])) {
                $validated['review_status'] = DailyRegisterFinding::STATUS_PERLU_REVISI;
                $validated['status_review'] = 'Revisi';
                $validated['reviewed_by'] = $user->name;
                $validated['reviewed_at'] = now();
                $validated['reviewer_note'] = $validated['catatan_reviewer'] ?? null;
            }

            $finding->update($validated);

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
     * Keputusan Verifikasi Otomatis oleh Admin/Reviewer (APPROVE atau REVISI)
     */
    public function reviewDecision(Request $request, $id)
    {
        $user = auth()->user();

        // RBAC ketat: RA dilarang keras menembak endpoint verifikasi Admin
        if (!in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai', 'pimsie', 'korwas'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak (403 Forbidden): Hanya Admin / Reviewer yang berhak melakukan verifikasi keputusan KKA.'
            ], 403);
        }

        $request->validate([
            'decision'           => 'required|in:APPROVE,REVISI',
            'catatan_reviewer'   => 'nullable|string',
            'status_klarifikasi' => 'nullable|string',
            'perluasan_sampel'   => 'nullable',
            'keputusan_onsite'   => 'nullable|string',
            'keputusan_eskalasi' => 'nullable|string',
        ]);

        $finding = DailyRegisterFinding::findOrFail($id);

        $decision = strtoupper($request->decision);
        $catatanReviewer = trim($request->input('catatan_reviewer', ''));

        if ($decision === 'REVISI') {
            if (empty($catatanReviewer)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi Gagal: Catatan Alasan Revisi wajib diisi ketika meminta perbaikan KKA dari Resident Auditor!'
                ], 422);
            }

            $finding->review_status = DailyRegisterFinding::STATUS_PERLU_REVISI;
            $finding->status_review = 'Revisi';
            $finding->reviewer_note = $catatanReviewer;
            $finding->catatan_reviewer = $catatanReviewer;
            $finding->reviewed_by = $user->name;
            $finding->reviewed_at = now();

            $msg = 'Permintaan revisi KKA berhasil dikirimkan ke Resident Auditor cabang.';
        } else {
            // APPROVE (SELESAI)
            $finding->review_status = DailyRegisterFinding::STATUS_SELESAI;
            $finding->status_review = 'Approved';
            if (!empty($catatanReviewer)) {
                $finding->reviewer_note = $catatanReviewer;
                $finding->catatan_reviewer = $catatanReviewer;
            }
            $finding->reviewed_by = $user->name;
            $finding->reviewed_at = now();

            $msg = 'KKA berhasil dikonfirmasi dan diselesaikan (Status SELESAI). Form terkunci secara otomatis.';
        }

        if ($request->has('status_klarifikasi')) {
            $finding->status_klarifikasi = $request->status_klarifikasi ?: $finding->status_klarifikasi;
        }
        if ($request->has('perluasan_sampel')) {
            $finding->perluasan_sampel = $request->perluasan_sampel ? 'Ya' : 'Tidak';
        }
        if ($request->has('keputusan_onsite')) {
            $finding->keputusan_onsite = $request->keputusan_onsite ?: $finding->keputusan_onsite;
        }
        if ($request->has('keputusan_eskalasi')) {
            $finding->keputusan_eskalasi = $request->keputusan_eskalasi ?: $finding->keputusan_eskalasi;
        }

        $finding->save();

        return response()->json([
            'status'  => 'success',
            'message' => $msg,
            'data'    => $finding
        ]);
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

    /**
     * Hitung Hari Kerja (NETWORKDAYS) dari tanggal awal s.d. tanggal akhir (Sabtu & Minggu libur).
     * Jika status adalah REJECT / DITOLAK, tampilkan string 'DITOLAK'.
     */
    public function calculateNetworkdays(?string $startDate, ?string $endDate, ?string $status = null): string|int
    {
        if (in_array(strtoupper(trim((string)$status)), ['REJECT', 'DITOLAK'])) {
            return 'DITOLAK';
        }
        if (empty($startDate) || empty($endDate)) {
            return '-';
        }
        try {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            if ($start->gt($end)) {
                return 0;
            }
            $days = 0;
            $curr = $start->copy();
            while ($curr->lte($end)) {
                if (!$curr->isWeekend()) {
                    $days++;
                }
                $curr->addDay();
            }
            return $days;
        } catch (\Throwable $e) {
            return '-';
        }
    }

    /**
     * Cetak Report Arsip Matriks KKA Harian (Tampilan Landscape)
     */
    public function printArsipMatriks(Request $request)
    {
        $user = auth()->user();
        $query = DailyRegisterFinding::query();

        // Keamanan Wilayah Cabang
        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->pluck('unit_code');
            $query->whereIn('kode_unit', $unitCodes);
        } elseif ($request->filled('kode_unit')) {
            $query->where('kode_unit', $request->kode_unit);
        }

        $sourceSheet = $request->input('source_sheet', 'KKA_Teller_Kas');
        if (in_array($sourceSheet, ['KKA_Teller_Kas', 'KKA_Teller'])) {
            $query->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']);
        } else {
            $query->where('source_sheet', $sourceSheet);
        }

        // Hanya data yang sudah Selesai / Terarsip
        $query->where(function($q) {
            $q->where('review_status', DailyRegisterFinding::STATUS_SELESAI)
              ->orWhere('status_review', 'Approved');
        });

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }

        $filterSubArea = $request->input('sub_area') ?: $request->input('sub_area_code');
        if ($filterSubArea) {
            $query->where(function($q) use ($filterSubArea) {
                $q->whereJsonContains('selected_sub_areas', $filterSubArea)
                  ->orWhere('selected_sub_areas', 'like', '%"' . $filterSubArea . '"%')
                  ->orWhereHas('checklistAnswers', function($ca) use ($filterSubArea) {
                      $ca->whereHas('checklist', function($c) use ($filterSubArea) {
                          $c->where('sub_area_code', $filterSubArea);
                      });
                  });
            });
        }

        $findings = $query->with('checklistAnswers')
            ->latest('tanggal_data')
            ->latest('id')
            ->get();

        $sheetToQuery = in_array($sourceSheet, ['KKA_Teller_Kas', 'KKA_Teller']) ? 'KKA_Teller_Kas' : $sourceSheet;
        $checklists = \App\Models\DailyRegister\DailyRegisterChecklist::where(function($q) use ($sheetToQuery) {
            if ($sheetToQuery === 'KKA_Teller_Kas') {
                $q->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller']);
            } else {
                $q->where('source_sheet', $sheetToQuery);
            }
        })->orderBy('sort_order')->get();

        if ($filterSubArea) {
            $checklists = $checklists->where('sub_area_code', $filterSubArea)->values();
        }

        $sheetNameMap = [
            'KKA_Teller_Kas'   => 'KKA Teller & Kas',
            'KKA_Teller'       => 'KKA Teller & Kas',
            'KKA_DPK_Layanan'  => 'KKA DPK & Layanan',
            'KKA_Kredit'       => 'KKA Kredit',
            'KKA_Biaya_Beban'  => 'KKA Biaya & Beban',
            'KKA_Kartu_ATM'    => 'KKA Kartu ATM',
            'KKA_Pengaduan'    => 'KKA Pengaduan',
        ];
        $namaSheet = $sheetNameMap[$sourceSheet] ?? $sourceSheet;
        $kodeUnit = $request->input('kode_unit', '');

        return view('daily_register.arsip_matriks_print', compact('findings', 'checklists', 'namaSheet', 'kodeUnit', 'sourceSheet', 'filterSubArea'));
    }

    /**
     * Export Excel Arsip Matriks KKA Harian
     */
    public function exportExcelArsipMatriks(Request $request)
    {
        $user = auth()->user();
        $query = DailyRegisterFinding::query();

        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)
                ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                ->pluck('unit_code');
            $query->whereIn('kode_unit', $unitCodes);
        } elseif ($request->filled('kode_unit')) {
            $query->where('kode_unit', $request->kode_unit);
        }

        $sourceSheet = $request->input('source_sheet', 'KKA_Teller_Kas');
        if (in_array($sourceSheet, ['KKA_Teller_Kas', 'KKA_Teller'])) {
            $query->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller', 'KKA_Kas_Kecil', 'KKA_Kas_Khasanah']);
        } else {
            $query->where('source_sheet', $sourceSheet);
        }

        $query->where(function($q) {
            $q->where('review_status', DailyRegisterFinding::STATUS_SELESAI)
              ->orWhere('status_review', 'Approved');
        });

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }

        $filterSubArea = $request->input('sub_area') ?: $request->input('sub_area_code');
        if ($filterSubArea) {
            $query->where(function($q) use ($filterSubArea) {
                $q->whereJsonContains('selected_sub_areas', $filterSubArea)
                  ->orWhere('selected_sub_areas', 'like', '%"' . $filterSubArea . '"%')
                  ->orWhereHas('checklistAnswers', function($ca) use ($filterSubArea) {
                      $ca->whereHas('checklist', function($c) use ($filterSubArea) {
                          $c->where('sub_area_code', $filterSubArea);
                      });
                  });
            });
        }

        $findings = $query->with('checklistAnswers')
            ->latest('tanggal_data')
            ->latest('id')
            ->get();

        $sheetToQuery = in_array($sourceSheet, ['KKA_Teller_Kas', 'KKA_Teller']) ? 'KKA_Teller_Kas' : $sourceSheet;
        $checklists = \App\Models\DailyRegister\DailyRegisterChecklist::where(function($q) use ($sheetToQuery) {
            if ($sheetToQuery === 'KKA_Teller_Kas') {
                $q->whereIn('source_sheet', ['KKA_Teller_Kas', 'KKA_Teller']);
            } else {
                $q->where('source_sheet', $sheetToQuery);
            }
        })->orderBy('sort_order')->get();

        if ($filterSubArea) {
            $checklists = $checklists->where('sub_area_code', $filterSubArea)->values();
        }

        $sheetNameMap = [
            'KKA_Teller_Kas'   => 'KKA Teller & Kas',
            'KKA_Teller'       => 'KKA Teller & Kas',
            'KKA_DPK_Layanan'  => 'KKA DPK & Layanan',
            'KKA_Kredit'       => 'KKA Kredit',
            'KKA_Biaya_Beban'  => 'KKA Biaya & Beban',
            'KKA_Kartu_ATM'    => 'KKA Kartu ATM',
            'KKA_Pengaduan'    => 'KKA Pengaduan',
        ];
        $namaSheet = $sheetNameMap[$sourceSheet] ?? $sourceSheet;

        $filename = 'Arsip_Matriks_' . $sourceSheet . '_' . date('Ymd_His') . '.xls';
        $viewContent = view('daily_register.arsip_matriks_print', [
            'findings'      => $findings,
            'checklists'    => $checklists,
            'namaSheet'     => $namaSheet,
            'kodeUnit'      => $request->input('kode_unit', ''),
            'sourceSheet'   => $sourceSheet,
            'filterSubArea' => $filterSubArea,
            'isExcel'       => true,
        ])->render();

        return response($viewContent, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Tambah Mandiri Pemeriksaan Kas Kecil & Kas / Khasanah (Sub-Area A2 & A3)
     */
    public function storeMandiri(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'kode_unit'       => 'required|string',
            'tanggal_data'    => 'required|date',
            'sub_area'        => 'required|in:A2,A3,BOTH',
            'nominal_terkait' => 'nullable|numeric|min:0',
            'no_arsip'        => 'nullable|string',
            'deskripsi'       => 'nullable|string',
        ]);

        $kodeUnit = $request->kode_unit;
        $cleanUnit = str_replace('BS-', '', $kodeUnit);
        $unit = \App\Models\Unit::where('unit_code', $kodeUnit)
            ->orWhere('unit_code', $cleanUnit)
            ->orWhere('unit_code', 'BS-' . $cleanUnit)
            ->first();

        if (!$unit) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unit dengan kode ' . $kodeUnit . ' tidak ditemukan.'
            ], 404);
        }

        // Cek wewenang jika RA
        if ($user && strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];
            $allowedUnitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
            $allowedUnitVariants = [];
            foreach ($allowedUnitCodes as $auc) {
                $c = str_replace('BS-', '', $auc);
                $allowedUnitVariants[] = $auc;
                $allowedUnitVariants[] = $c;
                $allowedUnitVariants[] = 'BS-' . $c;
            }

            if (!in_array($unit->unit_code, $allowedUnitCodes) && !in_array($kodeUnit, $allowedUnitVariants)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk menambahkan data pada cabang ini.'
                ], 403);
            }
        }

        $subAreaChoice = $request->sub_area;
        if ($subAreaChoice === 'A2') {
            $selectedSubAreas = ['A2'];
            $labelSub = 'Kas Kecil (A2)';
            $defaultDesc = 'Pemeriksaan Mandiri Fisik Kas Kecil';
        } elseif ($subAreaChoice === 'A3') {
            $selectedSubAreas = ['A3'];
            $labelSub = 'Kas / Khasanah (A3)';
            $defaultDesc = 'Pemeriksaan Mandiri Fisik Kas Khasanah / Brankas';
        } else {
            $selectedSubAreas = ['A2', 'A3'];
            $labelSub = 'Kas Kecil & Khasanah (A2 & A3)';
            $defaultDesc = 'Pemeriksaan Mandiri Fisik Kas Kecil dan Kas Khasanah';
        }

        $tanggal = $request->tanggal_data;
        $desc = $request->filled('deskripsi') ? $request->deskripsi : $defaultDesc;
        $noArsip = $request->filled('no_arsip') 
            ? $request->no_arsip 
            : ('ARSIP-KAS-' . $kodeUnit . '-' . date('Ymd', strtotime($tanggal)) . '-' . strtoupper(substr(uniqid(), -4)));

        $finding = DailyRegisterFinding::create([
            'tanggal_data'         => $tanggal,
            'periode'              => date('Y-m-01', strtotime($tanggal)),
            'kode_unit'            => $unit->unit_code,
            'nama_unit'            => $unit->unit_name,
            'source_sheet'         => 'KKA_Teller_Kas',
            'nominal_terkait'      => $request->nominal_terkait ?: 0,
            'risk_awal'            => 'Moderate',
            'jenis_exception_awal' => 'Pemeriksaan Mandiri: ' . $labelSub,
            'deskripsi'            => $desc,
            'rincian'              => $desc,
            'selected_sub_areas'   => $selectedSubAreas,
            'no_arsip'             => $noArsip,
            'review_status'        => DailyRegisterFinding::STATUS_BELUM_DIUJI,
            'status_review'        => 'Belum Direview',
            'status_klarifikasi'   => 'Belum Selesai',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pemeriksaan mandiri ' . $labelSub . ' berhasil ditambahkan.',
            'finding' => $finding,
        ]);
    }

    /**
     * Endpoint AJAX real-time preview kalkulasi risiko otomatis.
     */
    public function previewRisk(Request $request, AutoRiskScoringService $riskService)
    {
        $context = $request->input('context', 'physical');
        $unitCode = $request->input('kode_unit') ?? $request->input('unit_code') ?? (auth()->user()->unit_code ?? 'KC-001');

        if ($context === 'daily_register') {
            $sourceSheet = $request->input('source_sheet', 'KKA_Teller_Kas');
            $nominal = (float) $request->input('nominal', 0);
            $failedCount = (int) $request->input('failed_count', 0);
            $isCritical = filter_var($request->input('is_critical', false), FILTER_VALIDATE_BOOLEAN);
            $findingId = $request->input('finding_id') ? (int) $request->input('finding_id') : null;
            $extraContext = [
                'has_critical_security' => filter_var($request->input('has_critical_security', false), FILTER_VALIDATE_BOOLEAN),
                'has_warkat_diff'       => filter_var($request->input('has_warkat_diff', false), FILTER_VALIDATE_BOOLEAN),
            ];

            $result = $riskService->evaluateDailyRegister(
                $unitCode,
                $sourceSheet,
                $nominal,
                $failedCount,
                $isCritical,
                $findingId,
                $extraContext
            );

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        }

        // Context: Physical KKA
        $tab = $request->input('tab', 'kas');
        $payload = $request->input('payload', $request->all());
        $currentKkaId = $request->input('kka_id') ? (int) $request->input('kka_id') : null;

        $result = $riskService->evaluatePhysicalKka($tab, $unitCode, $payload, $currentKkaId);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}