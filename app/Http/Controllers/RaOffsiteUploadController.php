<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\WpOffsite;
use App\Models\WpOffsiteStaging;
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
        $domainType = $request->domain_type;
        $cabangId = $request->cabang_id;
        $tanggalManual = $request->tanggal_data_manual;

        DB::beginTransaction();
        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle, 4096, ',');   // <-- header dipakai sekarang

            $insertedCount = 0;
            $insertedIds = [];             // <-- kumpulkan ID hasil insert
            $sampleDate = $tanggalManual;

            while (($row = fgetcsv($handle, 4096, ',')) !== FALSE) {
                if (empty(array_filter($row))) continue;

                // Normalisasi panjang elemen baris agar cocok dengan header
                $rowPadded = array_pad(array_slice($row, 0, count($header)), count($header), null);
                $rowAssoc = array_combine($header, $rowPadded);

                $tglTransaksi = $sampleDate;
                if (!in_array($domainType, ['kredit', 'dpk']) && isset($rowAssoc[$header[0]])) {
                    try {
                        $tglTransaksi = Carbon::parse($rowAssoc[$header[0]])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglTransaksi = date('Y-m-d');
                    }
                }
                if (!$sampleDate && $tglTransaksi) {
                    $sampleDate = $tglTransaksi;
                }

                $staging = WpOffsiteStaging::create([
                    'cabang_id'    => $cabangId,
                    'domain_type'  => $domainType,
                    'tgl_transaksi'=> $tglTransaksi,
                    'raw_data'     => json_encode($rowAssoc),
                    'uploaded_by'  => auth()->id(),
                ]);
                $insertedIds[] = $staging->id;
                $insertedCount++;
            }
            fclose($handle);

            $wp = null;
            if ($sampleDate) {
                $dt = Carbon::parse($sampleDate);
                $tahun = $dt->year;
                $bulan = $dt->month;
                $kodeWp = 'SOP02-CAB-' . str_pad($cabangId, 3, '0', STR_PAD_LEFT) . '-' . $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);

                $wp = WpOffsite::firstOrCreate(
                    [
                        'unit_id'      => $cabangId, // catatan lama: ini masih perlu diperbaiki ke unit_id asli nanti
                        'periode_mulai'=> $dt->firstOfMonth()->format('Y-m-d'),
                    ],
                    [
                        'kode_wp'               => $kodeWp,
                        'periode_selesai'       => $dt->endOfMonth()->format('Y-m-d'),
                        'ra_pelaksana_id'       => auth()->id(),
                        'reviewer_bagian_ra_id' => null,
                        'status_wp'             => 'Draft',
                    ]
                );

                // KAITKAN semua staging yang baru diinsert ke WP ini (INI FIX #2)
                WpOffsiteStaging::whereIn('id', $insertedIds)->update(['wp_offsite_id' => $wp->id]);
            }

            DB::commit();

            if (isset($wp)) {
                $engine = new OffsiteDetectorEngine(new \App\Services\OffsiteDetectionService());
                $flagged = $engine->scan($wp, $domainType);
            }

            return redirect()->back()->with('success', "Berhasil! $insertedCount baris data domain $domainType sukses diunggah dan diproses.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['file_csv' => 'Gagal memproses file CSV: ' . $e->getMessage()]);
        }
    }
}