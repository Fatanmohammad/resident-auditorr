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
            return redirect()->route('offsite.register');
        }

        $cabangs = \App\Models\Cabang::whereIn('tipe', ['pusat', 'kcu', 'cabang_a', 'cabang_b'])->get();

        $rekapCabang = $cabangs->map(function($cabang) {
            $unitCodes = \App\Models\Unit::where('cabang_id', $cabang->id)
                ->orWhereIn('cabang_id', function($q) use ($cabang) {
                    $q->select('id')->from('cabangs')->where('parent_id', $cabang->id);
                })
                ->pluck('unit_code');

            $totalLow = \App\Models\Offsite\DailyRegister::whereIn('kode_unit', $unitCodes)->count();
            $totalModerate = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodes)->where('risk_awal', 'Moderate')->count();
            $totalHigh = \App\Models\Offsite\KkaFinding::whereIn('kode_unit', $unitCodes)->where('risk_awal', 'High')->count();

            return [
                'id' => $cabang->id,
                'kode_cabang' => $cabang->kode_cabang,
                'nama_cabang' => $cabang->nama_cabang,
                'total_low' => $totalLow,
                'total_moderate' => $totalModerate,
                'total_high' => $totalHigh,
                'total_risiko' => $totalModerate + $totalHigh
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