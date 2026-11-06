<?php

namespace App\Http\Controllers\Offsite;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Offsite\UploadDumpRequest;
use App\Models\Offsite\AuditLog;
use App\Services\Offsite\DumpCbsParser;
use App\Services\Offsite\DumpDpkParser;
use App\Services\Offsite\DumpKreditParser;
use App\Services\Offsite\DumpBiayaParser;
use App\Services\Offsite\DumpPengaduanParser;
use App\Services\Offsite\DumpKartuAtmParser;
use App\Services\Offsite\SamplingEngineService;
use Illuminate\Support\Facades\DB;

class OffsiteController extends Controller
{
    /**
     * Menampilkan Dashboard Rekapitulasi untuk Admin/Pimsie atau Redirect RA
     */
    public function index()
    {
        $user = auth()->user();

        if (strtolower($user->role) === 'ra') {
            return redirect()->route('offsite.register.index');
        }

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

            $totalLowAll = \App\Models\Offsite\DailyRegister::whereIn('kode_unit', $allUnitCodes)->count();
            $totalModerateAll = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $allUnitCodes)->where('risk_awal', 'Moderate')->count();
            $totalHighAll = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $allUnitCodes)->where('risk_awal', 'High')->count();

            return [
                'id' => $induk->id,
                'kode_unit' => $kodeKC,
                'nama_cabang' => $namaKC,
                'total_low_all' => $totalLowAll,
                'total_moderate_all' => $totalModerateAll,
                'total_high_all' => $totalHighAll,
                'total_risiko_all' => $totalModerateAll + $totalHighAll,
            ];
        });

        return view('offsite.admin_index', compact('rekapCabang'));
    }

    /**
     * Menampilkan Detail Cabang beserta KCP/KCPLK di bawahnya
     */
    public function detail($id)
    {
        $user = auth()->user();

        if (strtolower($user->role) === 'ra') {
            return redirect()->route('offsite.register.index');
        }

        $induk = \App\Models\Cabang::findOrFail($id);

        $kcUnit = \App\Models\Unit::where('cabang_id', $induk->id)
            ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
            ->first();
        
        $namaKC = $kcUnit ? $kcUnit->unit_name : $induk->nama_cabang;

        $units = [];
        
        // 1. Masukkan KC itu sendiri
        if ($kcUnit) {
            $units[] = [
                'kode_unit' => $kcUnit->unit_code,
                'nama_unit' => $kcUnit->unit_name,
                'tipe_unit' => $kcUnit->unit_type,
                'total_low' => \App\Models\Offsite\DailyRegister::where('kode_unit', $kcUnit->unit_code)->count(),
                'total_moderate' => \App\Models\Offsite\KkaFinding::where('kode_unit', $kcUnit->unit_code)->where('risk_awal', 'Moderate')->count(),
                'total_high' => \App\Models\Offsite\KkaFinding::where('kode_unit', $kcUnit->unit_code)->where('risk_awal', 'High')->count(),
            ];
        }

        // 2. Masukkan semua KCP / KCPLK (bisa dari cabang_id induk atau cabang_id anak)
        $anakCabangIds = \App\Models\Cabang::where('parent_id', $induk->id)->pluck('id')->toArray();
        $allCabangIds = array_merge([$induk->id], $anakCabangIds);

        $anakUnits = \App\Models\Unit::whereIn('cabang_id', $allCabangIds)
            ->whereNotIn('unit_type', ['KC', 'KCU', 'KP', 'Payment Point'])
            ->orderBy('unit_code', 'asc')
            ->get();

        foreach ($anakUnits as $anak) {
            $units[] = [
                'kode_unit' => $anak->unit_code,
                'nama_unit' => $anak->unit_name,
                'tipe_unit' => $anak->unit_type,
                'total_low' => \App\Models\Offsite\DailyRegister::where('kode_unit', $anak->unit_code)->count(),
                'total_moderate' => \App\Models\Offsite\KkaFinding::where('kode_unit', $anak->unit_code)->where('risk_awal', 'Moderate')->count(),
                'total_high' => \App\Models\Offsite\KkaFinding::where('kode_unit', $anak->unit_code)->where('risk_awal', 'High')->count(),
            ];
        }

        return view('offsite.admin_detail', compact('induk', 'namaKC', 'units'));
    }

    /**
     * Menampilkan Halaman Form Upload DUMP (Disesuaikan cakupan wilayah RA & Admin)
     */
    /**
     * Menampilkan Halaman Form Upload DUMP (Disesuaikan cakupan wilayah RA & Admin)
     */
    public function create()
    {
        $user = auth()->user();
        
        if (in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            $cabangs = \App\Models\Unit::orderBy('unit_name', 'asc')->get();
        } else {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : ($user->cabang_id ? [$user->cabang_id] : []);

            $cabangs = \App\Models\Unit::where(function($query) use ($allowedCabangIds) {
                $query->whereIn('cabang_id', $allowedCabangIds)
                      ->orWhereIn('cabang_id', function($sub) use ($allowedCabangIds) {
                          $sub->select('id')->from('cabangs')->whereIn('parent_id', $allowedCabangIds);
                      });
            })->orderBy('unit_name', 'asc')->get();
        }
        
        return view('offsite.upload', compact('cabangs'));
    }

    /**
     * Memproses upload file DUMP dari RA dengan validasi wilayah unit dan periode
     */
    public function upload(
        UploadDumpRequest $request,
        DumpCbsParser $cbsParser,
        DumpDpkParser $dpkParser,
        DumpKreditParser $kreditParser,
        DumpBiayaParser $biayaParser,
        DumpPengaduanParser $pengaduanParser,
        DumpKartuAtmParser $kartuAtmParser,
        SamplingEngineService $samplingEngine
    ) {
        $user = auth()->user();
        $file = $request->file('file_csv');
        $jenisFile = $request->jenis_file;
        $kodeUnitDipilih = $request->kode_unit;
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        // Normalisasi format Periode ke format tanggal awal bulan (YYYY-MM-01)
        $rawPeriode = $request->input('periode', date('Y-m'));
        $periode = date('Y-m-01', strtotime($rawPeriode . (strlen($rawPeriode) === 7 ? '-01' : '')));

        // 1. VALIDASI HAK AKSES KHUSUS RA
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : ($user->cabang_id ? [$user->cabang_id] : []);

            $allowedUnitCodes = \App\Models\Unit::where(function($query) use ($allowedCabangIds) {
                $query->whereIn('cabang_id', $allowedCabangIds)
                      ->orWhereIn('cabang_id', function($sub) use ($allowedCabangIds) {
                          $sub->select('id')->from('cabangs')->whereIn('parent_id', $allowedCabangIds);
                      });
            })->pluck('unit_code')->toArray();

            if (!in_array($kodeUnitDipilih, $allowedUnitCodes)) {
                return redirect()->back()->with('error', 'Akses ditolak! Anda tidak memiliki wewenang meng-upload data untuk unit/cabang ' . $kodeUnitDipilih . '.');
            }
        }

        // 1. Hitung SHA-256 fingerprint dari FILE ASLI secara streaming sebelum diproses parser/staging
        $fileHash = null;
        try {
            $rawPath = $file->getRealPath();
            if ($rawPath && file_exists($rawPath)) {
                $calculated = @hash_file('sha256', $rawPath);
                if ($calculated !== false && strlen($calculated) === 64) {
                    $fileHash = strtolower($calculated);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Gagal menghitung file_hash: hash_file mengembalikan false untuk file {$file->getClientOriginalName()}");
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("Gagal menghitung file_hash: file getRealPath() tidak ditemukan.");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal menghitung file_hash untuk file {$file->getClientOriginalName()}: " . $e->getMessage());
            $fileHash = null;
        }

        $tempFileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('offsite_staging', $tempFileName, 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        // Fallback perhitungan file_hash dari file tersimpan jika rawPath belum sempat terbaca
        if ($fileHash === null && file_exists($fullPath)) {
            try {
                $fallbackHash = @hash_file('sha256', $fullPath);
                if ($fallbackHash !== false && strlen($fallbackHash) === 64) {
                    $fileHash = strtolower($fallbackHash);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Fallback perhitungan file_hash gagal: " . $e->getMessage());
            }
        }

        DB::beginTransaction();
        try {
            // 2. ISOLASI DATA (CLEAN REPLACE):
            // Hapus data temuan mentah yang belum direview untuk unit, periode, dan jenis file ini
            // agar data tidak menumpuk, duplikat, atau bocor antar-upload.
            $sheetMap = [
                'DUMP_01' => ['KKA_Teller_Kas', 'KKA_Biaya_Beban', 'KKA_Kredit', 'DUMP_01_CBS'],
                'DUMP_02' => ['KKA_DPK_Layanan', 'KKA_Transaksi_Umum', 'KKA_Transfer_KU', 'DUMP_02_DPK'],
                'DUMP_03' => ['KKA_Kredit', 'DUMP_03_KREDIT'],
                'DUMP_04' => ['KKA_Biaya_Beban', 'KKA_Biaya_Internal', 'DUMP_04_BIAYA'],
                'DUMP_05' => ['KKA_Pengaduan', 'DUMP_05_PENGADUAN'],
                'DUMP_06' => ['KKA_Kartu_ATM', 'KKA_Pengaduan', 'KKA_Transaksi_Umum', 'DUMP_06_KARTU_ATM'],
            ];

            if (isset($sheetMap[$jenisFile])) {
                \App\Models\Offsite\KkaFinding::where('kode_unit', $kodeUnitDipilih)
                    ->where('periode', $periode)
                    ->whereIn('source_sheet', $sheetMap[$jenisFile])
                    ->where(function($q) {
                        $q->whereNull('status_review')
                          ->orWhere('status_review', 'Belum Di-review')
                          ->orWhere('status_review', 'Belum Direview')
                          ->orWhere('status_review', '');
                    })
                    ->delete();

                \App\Models\Offsite\DailyRegister::where('kode_unit', $kodeUnitDipilih)
                    ->where(function($q) use ($periode) {
                        $q->where('periode', $periode)
                          ->orWhereBetween('tanggal_data', [
                              date('Y-m-01', strtotime($periode)),
                              date('Y-m-t', strtotime($periode))
                          ]);
                    })
                    ->whereIn('source_sheet', $sheetMap[$jenisFile])
                    ->delete();
            }

            // 3. PARSING DATA SESUAI PERIODE
            $parseResult = null;

            switch ($jenisFile) {
                case 'DUMP_01': $parseResult = $cbsParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode, $tanggalMulai, $tanggalSelesai); break;
                case 'DUMP_02': $parseResult = $dpkParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode); break;
                case 'DUMP_03': $parseResult = $kreditParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode); break;
                case 'DUMP_04': $parseResult = $biayaParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode); break;
                case 'DUMP_05': $parseResult = $pengaduanParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode); break;
                case 'DUMP_06': $parseResult = $kartuAtmParser->parse($fullPath, $kodeUnitDipilih, false, null, $periode); break;
            }

            $totalLow      = is_array($parseResult) ? ($parseResult['total_low'] ?? 0) : 0;
            $totalModerate = is_array($parseResult) ? ($parseResult['total_moderate'] ?? 0) : 0;
            $totalHigh     = is_array($parseResult) ? ($parseResult['total_high'] ?? 0) : 0;

            $auditLog = AuditLog::create([
                'user_id'        => $user->id,
                'kode_unit'      => $kodeUnitDipilih,
                'periode'        => $periode,
                'jenis_file'     => $jenisFile,
                'nama_file'      => $file->getClientOriginalName(),
                'file_hash'      => $fileHash,
                'status'         => 'Berhasil',
                'total_low'      => $totalLow,
                'total_moderate' => $totalModerate,
                'total_high'     => $totalHigh,
            ]);

            DB::commit();

            // Simpan file secara permanen di storage agar integritasnya dapat diverifikasi di kemudian hari
            Storage::disk('local')->makeDirectory('offsite_staging');
            $permanentFileName = $auditLog->id . '_' . $file->getClientOriginalName();
            $permanentPath = Storage::disk('local')->path('offsite_staging/' . $permanentFileName);
            if (file_exists($fullPath) && $fullPath !== $permanentPath) {
                if (!@rename($fullPath, $permanentPath)) {
                    copy($fullPath, $permanentPath);
                    @unlink($fullPath);
                }
            }

            // 4. JALANKAN MESIN SAMPLING OTOMATIS TEPAT PADA PERIODE TERPILIH
            $samplingResult = $samplingEngine->runSampling($kodeUnitDipilih, $periode);

            $msgExtra = isset($samplingResult['total_sampel']) 
                ? ' (' . $samplingResult['total_sampel'] . ' sampel otomatis masuk antrean KKA)' 
                : '';

            $periodeFormatted = date('M Y', strtotime($periode));

            return redirect()->back()->with('success', 'File ' . $jenisFile . ' untuk Unit ' . $kodeUnitDipilih . ' (Periode ' . $periodeFormatted . ') berhasil diproses.' . $msgExtra);

        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($fullPath) && file_exists($fullPath)) {
                @unlink($fullPath);
            }

            \Illuminate\Support\Facades\Log::error('Offsite Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            $cleanErrorMessage = $e instanceof \Illuminate\Database\QueryException
                ? ($e->errorInfo[2] ?? $e->getMessage())
                : $e->getMessage();

            $auditLog = AuditLog::create([
                'user_id'     => $user->id,
                'kode_unit'   => $kodeUnitDipilih,
                'periode'     => $periode,
                'jenis_file'  => $jenisFile,
                'nama_file'   => $file->getClientOriginalName(),
                'file_hash'   => $fileHash,
                'status'      => 'Gagal',
                'pesan_error' => \Illuminate\Support\Str::limit($cleanErrorMessage, 250),
            ]);

            return redirect()->back()->with('error', 'Gagal memproses file: ' . \Illuminate\Support\Str::limit($cleanErrorMessage, 300));
        }
    }
}