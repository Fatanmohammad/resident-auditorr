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
    public function create()
    {
        $user = auth()->user();
        
        if (in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai'])) {
            $cabangs = \App\Models\Unit::orderBy('unit_name', 'asc')->get();
        } else {

            // Memastikan RA (termasuk jika 1 wilayah ada 2 RA) hanya melihat cabang induk & anak cabangnya
            $cabangs = \App\Models\Unit::where(function($query) use ($user) {
                if ($user->cabang_id) {
                    $query->where('cabang_id', $user->cabang_id)
                          ->orWhereIn('cabang_id', function($sub) use ($user) {
                              $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                          });
                }
            })->orderBy('unit_name', 'asc')->get();
        }
        
        return view('offsite.upload', compact('cabangs'));
    }

    /**
     * Memproses upload file DUMP dari RA dengan validasi wilayah unit
     */
    public function upload(
        UploadDumpRequest $request,
        DumpCbsParser $cbsParser,
        DumpDpkParser $dpkParser,
        DumpKreditParser $kreditParser,
        DumpBiayaParser $biayaParser,
        DumpPengaduanParser $pengaduanParser,
        SamplingEngineService $samplingEngine
    ) {
        $user = auth()->user();
        $file = $request->file('file_csv');
        $jenisFile = $request->jenis_file;
        $kodeUnitDipilih = $request->kode_unit;
        $periode = $request->input('periode', date('Y-m-01')); // Awal bulan sebagai penanda periode sampling


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

        $filePath = $file->storeAs('offsite_staging', time() . '_' . $file->getClientOriginalName(), 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        DB::beginTransaction();
        try {
            // Hasil parser sekarang berupa array ['total_low'=>.., 'total_moderate'=>.., 'total_high'=>..]
            // Beberapa parser lama mungkin masih return true (belum diperbarui) -> ditangani aman di bawah.
            $parseResult = null;

            switch ($jenisFile) {
                case 'DUMP_01': $parseResult = $cbsParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_02': $parseResult = $dpkParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_03': $parseResult = $kreditParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_04': $parseResult = $biayaParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_05': $parseResult = $pengaduanParser->parse($fullPath, $kodeUnitDipilih); break;

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

            // JALANKAN MESIN SAMPLING OTOMATIS
            $samplingResult = $samplingEngine->runSampling($kodeUnitDipilih, $periode);

            $msgExtra = isset($samplingResult['total_sampel']) 
                ? ' (' . $samplingResult['total_sampel'] . ' sampel otomatis masuk antrean KKA)' 
                : '';

            return redirect()->back()->with('success', 'File ' . $jenisFile . ' untuk Unit ' . $kodeUnitDipilih . ' berhasil diproses.' . $msgExtra);

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

            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}