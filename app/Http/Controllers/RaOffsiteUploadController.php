<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\WpOffsite;
use App\Models\StagingOffsite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\OffsiteDetectorEngine;

class RaOffsiteUploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $accessibleIds = $user->cabangIdYangDapatDiakses();

        if ($accessibleIds === null) {
            $cabangs = Cabang::all();
        } else {
            $cabangs = Cabang::whereIn('id', $accessibleIds)->get();
        }

        return view('ra-offsite.upload', compact('cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cabang_id'          => 'required|exists:cabangs,id',
            'domain_type'        => 'required|in:cbs,dpk,kredit,biaya,pengaduan',
            'file_csv'           => 'required|file|mimes:csv,txt|max:20480',
            'tanggal_data_manual'=> 'nullable|required_if:domain_type,kredit,dpk|date',
        ]);

        $file = $request->file('file_csv');
        $domainType = strtoupper($request->domain_type);
        $cabangId = $request->cabang_id;
        $tanggalManual = $request->tanggal_data_manual;
        $user = Auth::user();

        DB::beginTransaction();
        try {
            // 1. Tentukan tanggal sampel & buat/dapatkan WpOffsite LEBIH DULU
            $sampleDate = $tanggalManual ?? now()->format('Y-m-d');
            $dt = Carbon::parse($sampleDate);
            $tahun = $dt->year;
            $bulan = $dt->month;
            $kodeUnit = str_pad($cabangId, 3, '0', STR_PAD_LEFT);
            $kodeWp = 'SOP02-CAB-' . $kodeUnit . '-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);

            $wp = WpOffsite::firstOrCreate(
                [
                    'unit_id'      => $cabangId,
                    'periode_mulai'=> $dt->copy()->firstOfMonth()->format('Y-m-d'),
                ],
                [
                    'kode_wp'               => $kodeWp,
                    'periode_selesai'       => $dt->copy()->endOfMonth()->format('Y-m-d'),
                    'ra_pelaksana_id'       => auth()->id(),
                    'reviewer_bagian_ra_id' => null,
                    'status_wp'             => 'Draft',
                ]
            );

            // 2. Baca file CSV
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle, 4096, ',');

            $insertedCount = 0;
            $sampleAssoc = null; // Menampung sampel baris data pertama

            while (($row = fgetcsv($handle, 4096, ',')) !== FALSE) {
                if (empty(array_filter($row))) continue;

                // Normalisasi panjang elemen baris agar cocok dengan header
                $rowPadded = array_pad(array_slice($row, 0, count($header)), count($header), null);
                $rowAssoc = array_combine($header, $rowPadded);

                // Tangkap data baris pertama sebagai contoh sampel
                if ($sampleAssoc === null) {
                    $sampleAssoc = $rowAssoc;
                }

                $tglTransaksi = $tanggalManual;
                if (!in_array(strtolower($domainType), ['kredit', 'dpk']) && isset($rowAssoc[$header[0]])) {
                    try {
                        $tglTransaksi = Carbon::parse($rowAssoc[$header[0]])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglTransaksi = date('Y-m-d');
                    }
                }

                // 3. Simpan ke model StagingOffsite
                StagingOffsite::create([
                    'wp_offsite_id'    => $wp->id,
                    'tanggal_data'     => $tglTransaksi ?? now()->format('Y-m-d'),
                    'kode_unit'        => $kodeUnit,
                    'nama_unit'        => $rowAssoc['NAMA_UNIT'] ?? $rowAssoc['nama_unit'] ?? 'Kantor Cabang',
                    'ra_id'            => auth()->id(),
                    'source_table'     => $domainType,
                    'deskripsi_narasi' => json_encode($rowAssoc),
                    'nominal'          => $rowAssoc['JUMLAH_TX'] ?? $rowAssoc['NOMINAL'] ?? $rowAssoc['nominal'] ?? 0,
                    'user_maker'       => $rowAssoc['USER_MAKER'] ?? $rowAssoc['user_maker'] ?? null,
                ]);

                $insertedCount++;
            }
            fclose($handle);

            // 4. Rangkai narasi harian yang mengalir rapi (Opsi 1)
            $catatanNarasi = $this->generateNarasiLog(
                $domainType, 
                $file->getClientOriginalName(), 
                $insertedCount, 
                $kodeUnit, 
                $sampleAssoc
            );

            // 5. Catat aktivitas UPLOAD ke kka_activity_logs
            DB::table('kka_activity_logs')->insert([
                'user_id'             => $user?->id ?? 1,
                'user_name'           => $user?->name ?? 'System',
                'kode_unit'           => $kodeUnit,
                'case_id'             => (string) $wp->id,
                'sheet_name'          => $domainType,
                'action'              => 'UPLOAD',
                'deskripsi_perubahan' => $catatanNarasi,
                'status_review'       => 'Belum',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::commit();

            // 6. Eksekusi Engine Scan Deteksi Risiko setelah commit
            $engine = new OffsiteDetectorEngine(new \App\Services\OffsiteDetectionService());
            $flagged = $engine->scan($wp, $domainType);

            return redirect()->back()->with('success', "Berhasil! $insertedCount baris data domain $domainType sukses diunggah dan diproses.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['file_csv' => 'Gagal memproses file CSV: ' . $e->getMessage()]);
        }
    }

    /**
     * Generator narasi bahasa Indonesia yang mengalir untuk tampilan UI History (Opsi 1)
     */
    private function generateNarasiLog(string $domain, string $fileName, int $rowCount, string $kodeUnit, ?array $sample): string
    {
        $noRek = $sample['NO_REK'] ?? $sample['no_rek'] ?? $sample['NO_REKENING'] ?? null;
        
        $narasi = "Mengunggah data transaksi {$domain} sebanyak {$rowCount} baris dari file {$fileName} untuk Cabang {$kodeUnit}.";
        
        if ($noRek) {
            $narasi .= " Sampel data yang tercatat yaitu rekening {$noRek}.";
        }

        return $narasi;
    }
}