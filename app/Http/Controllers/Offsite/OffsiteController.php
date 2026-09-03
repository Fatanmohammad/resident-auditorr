<?php

namespace App\Http\Controllers\Offsite;

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

        // Simpan file sementara di folder tmp (staging)
        $filePath = $file->storeAs('offsite_staging', time() . '_' . $file->getClientOriginalName());
        $fullPath = storage_path('app/' . $filePath);

        // Ambil kode unit / cabang utama tempat RA bertugas
        $kodeUnit = $user->cabang ? $user->cabang->kode_cabang : '000';

        DB::beginTransaction();
        try {
            // Jalankan parser sesuai jenis file yang diunggah
            switch ($jenisFile) {
                case 'DUMP_01':
                    $cbsParser->parse($fullPath, $kodeUnit);
                    break;
                case 'DUMP_02':
                    $dpkParser->parse($fullPath, $kodeUnit);
                    break;
                case 'DUMP_03':
                    $kreditParser->parse($fullPath, $kodeUnit);
                    break;
                case 'DUMP_04':
                    $biayaParser->parse($fullPath, $kodeUnit);
                    break;
                case 'DUMP_05':
                    $pengaduanParser->parse($fullPath, $kodeUnit);
                    break;
            }

            // Catat log aktivitas upload
            AuditLog::create([
                'user_id'    => $user->id,
                'kode_unit'  => $kodeUnit,
                'jenis_file' => $jenisFile,
                'nama_file'  => $file->getClientOriginalName(),
                'status'     => 'Sukses',
            ]);

            DB::commit();

            // Hapus file fisik dari tmp/staging agar storage server tidak penuh (bebas bloatware)
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'File ' . $jenisFile . ' berhasil diproses dan dikategorikan otomatis.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Hapus file fisik jika terjadi error
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Catat log error
            AuditLog::create([
                'user_id'     => $user->id,
                'kode_unit'   => $kodeUnit,
                'jenis_file'  => $jenisFile,
                'nama_file'   => $file->getClientOriginalName(),
                'status'      => 'Gagal',
                'pesan_error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses file: ' . $e->getMessage()
            ], 500);
        }
    }
}