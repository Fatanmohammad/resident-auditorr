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
            ->orderBy('kode_cabang', 'asc')
            ->get();

        $rekapCabang = $indukCabangs->map(function($induk) {
            // Ambil unit code milik Induk Cabang Ini
            $unitCodesInduk = \App\Models\Unit::where('cabang_id', $induk->id)->pluck('unit_code')->toArray();
            
            // Bersihkan prefix BS- untuk kecocokan langsung ke kode DUMP (contoh: BS-001 -> 001)
            $cleanKodeInduk = ltrim(str_replace('BS-', '', $induk->kode_cabang), '0');
            if (!empty($cleanKodeInduk)) {
                $unitCodesInduk[] = str_pad($cleanKodeInduk, 3, '0', STR_PAD_LEFT);
            }

            // Hitung temuan khusus Induk Cabang
            $lowInduk = \App\Models\Offsite\DailyRegister::whereIn('kode_unit', $unitCodesInduk)->count();
            $modInduk = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodesInduk)->where('risk_awal', 'Moderate')->count();
            $highInduk = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodesInduk)->where('risk_awal', 'High')->count();

            // Ambil Anak Cabang yang terhubung via parent_id
            $anakCabangs = \App\Models\Cabang::where('parent_id', $induk->id)->get()->map(function($anak) {
                $unitCodesAnak = \App\Models\Unit::where('cabang_id', $anak->id)->pluck('unit_code')->toArray();

                return [
                    'id' => $anak->id,
                    'kode_cabang' => $anak->kode_cabang,
                    'nama_cabang' => $anak->nama_cabang,
                    'total_low' => \App\Models\Offsite\DailyRegister::whereIn('kode_unit', $unitCodesAnak)->count(),
                    'total_moderate' => \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodesAnak)->where('risk_awal', 'Moderate')->count(),
                    'total_high' => \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodesAnak)->where('risk_awal', 'High')->count(),
                ];
            });

            // Total gabungan Induk + Seluruh Anak Cabang
            $totalLowAll = $lowInduk + $anakCabangs->sum('total_low');
            $totalModerateAll = $modInduk + $anakCabangs->sum('total_moderate');
            $totalHighAll = $highInduk + $anakCabangs->sum('total_high');

            return [
                'id' => $induk->id,
                'kode_cabang' => $induk->kode_cabang,
                'nama_cabang' => $induk->nama_cabang,
                'total_low' => $lowInduk,
                'total_moderate' => $modInduk,
                'total_high' => $highInduk,
                'total_low_all' => $totalLowAll,
                'total_moderate_all' => $totalModerateAll,
                'total_high_all' => $totalHighAll,
                'total_risiko_all' => $totalModerateAll + $totalHighAll,
                'anak_cabang' => $anakCabangs
            ];
        });

        return view('offsite.admin_index', compact('rekapCabang'));
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
        DumpPengaduanParser $pengaduanParser
    ) {
        $user = auth()->user();
        $file = $request->file('file_csv');
        $jenisFile = $request->jenis_file;
        $kodeUnitDipilih = $request->kode_unit;

        if (!in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai'])) {
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
            switch ($jenisFile) {
                case 'DUMP_01': $cbsParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_02': $dpkParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_03': $kreditParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_04': $biayaParser->parse($fullPath, $kodeUnitDipilih); break;
                case 'DUMP_05': $pengaduanParser->parse($fullPath, $kodeUnitDipilih); break;
            }

            AuditLog::create([
                'user_id'    => $user->id,
                'kode_unit'  => $kodeUnitDipilih,
                'jenis_file' => $jenisFile,
                'nama_file'  => $file->getClientOriginalName(),
                'status'     => 'Berhasil',
            ]);

            DB::commit();

            if (file_exists($fullPath)) unlink($fullPath);

            return redirect()->back()->with('success', 'File ' . $jenisFile . ' untuk Unit ' . $kodeUnitDipilih . ' berhasil diproses. Data telah dikategorikan ke Register Harian & Temuan KKA.');

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