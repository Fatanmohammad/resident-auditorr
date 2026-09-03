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
     * Menampilkan Halaman Form Upload DUMP
     */
    public function create()
    {
        return view('offsite.upload');
    }

    /**
     * Memproses upload file DUMP dari RA
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

        $filePath = $file->storeAs('offsite_staging', time() . '_' . $file->getClientOriginalName(), 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        // Ambil kode unit dari relasi cabang (jika RA punya cabang, kalau tidak set '000')
        $kodeUnit = $user->cabang ? $user->cabang->kode_cabang : '000';

        DB::beginTransaction();
        try {
            // Proses parsing sesuai jenis file DUMP
            switch ($jenisFile) {
                case 'DUMP_01': $cbsParser->parse($fullPath, $kodeUnit); break;
                case 'DUMP_02': $dpkParser->parse($fullPath, $kodeUnit); break;
                case 'DUMP_03': $kreditParser->parse($fullPath, $kodeUnit); break;
                case 'DUMP_04': $biayaParser->parse($fullPath, $kodeUnit); break;
                case 'DUMP_05': $pengaduanParser->parse($fullPath, $kodeUnit); break;
            }

            // Catat log sukses
            AuditLog::create([
                'user_id'    => $user->id,
                'kode_unit'  => $kodeUnit,
                'jenis_file' => $jenisFile,
                'nama_file'  => $file->getClientOriginalName(),
                'status'     => 'Berhasil',
            ]);

            DB::commit();

            // Hapus file staging setelah selesai diproses
            if (file_exists($fullPath)) unlink($fullPath);

            // Redirect kembali ke halaman web dengan pesan sukses
            return redirect()->back()->with('success', 'File ' . $jenisFile . ' berhasil diproses. Data telah dikategorikan ke Register Harian & Temuan KKA.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Tetap hapus file staging jika terjadi error
            if (file_exists($fullPath)) unlink($fullPath);

            // Catat log gagal
            AuditLog::create([
                'user_id'     => $user->id,
                'kode_unit'   => $kodeUnit,
                'jenis_file'  => $jenisFile,
                'nama_file'   => $file->getClientOriginalName(),
                'status'      => 'Gagal',
                'pesan_error' => \Illuminate\Support\Str::limit($e->getMessage(), 250),
            ]);

            // Redirect kembali dengan pesan error
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}