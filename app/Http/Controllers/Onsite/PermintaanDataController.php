<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsitePermintaanData;
use App\Models\Onsite\OnsitePermintaanItem;
use Illuminate\Http\Request;

class PermintaanDataController extends Controller
{
    // Daftar dokumen default yang biasa diminta saat onsite teller
    const DOKUMEN_DEFAULT = [
        'Laporan Kas Harian Teller',
        'Daftar Mutasi Rekening Teller',
        'Bukti Transaksi Tunai (Slip Setor/Tarik)',
        'Laporan Selisih Kas',
        'Daftar Transaksi Reversal',
        'Laporan Transaksi Internal Account',
        'Daftar Nasabah Walk-in Nominal Besar',
        'Buku Kas Besar Harian',
        'Laporan Rekonsiliasi Kas Akhir Hari',
        'Dokumen Pendukung Lainnya',
    ];

    /**
     * Form buat surat permintaan data untuk kunjungan
     */
    public function create(OnsiteVisit $visit)
    {
        $existing = OnsitePermintaanData::where('onsite_visit_id', $visit->id)->with('items')->first();

        return view('onsite.permintaan.create', [
            'visit'    => $visit,
            'existing' => $existing,
            'dokumenDefault' => self::DOKUMEN_DEFAULT,
        ]);
    }

    /**
     * Simpan surat permintaan data
     */
    public function store(Request $request, OnsiteVisit $visit)
    {
        $request->validate([
            'kepada'          => 'required|string|max:255',
            'jabatan_kepada'  => 'nullable|string|max:255',
            'tanggal_surat'   => 'required|date',
            'catatan'         => 'nullable|string',
            'dokumen'         => 'required|array|min:1',
            'dokumen.*'       => 'required|string',
            'periode_data'    => 'nullable|array',
            'keterangan_item' => 'nullable|array',
        ]);

        // Hapus yang lama jika ada
        OnsitePermintaanData::where('onsite_visit_id', $visit->id)->delete();

        // Generate nomor surat
        $nomorSurat = 'SPD-ONS-' . $visit->kode_unit . '-' . date('Ymd', strtotime($request->tanggal_surat));

        $permintaan = OnsitePermintaanData::create([
            'onsite_visit_id' => $visit->id,
            'nomor_surat'     => $nomorSurat,
            'tanggal_surat'   => $request->tanggal_surat,
            'kepada'          => $request->kepada,
            'jabatan_kepada'  => $request->jabatan_kepada,
            'catatan'         => $request->catatan,
        ]);

        $items = [];
        foreach ($request->dokumen as $i => $dok) {
            if (empty(trim($dok))) continue;
            $items[] = [
                'permintaan_id' => $permintaan->id,
                'urut'          => $i + 1,
                'jenis_dokumen' => $dok,
                'periode_data'  => $request->periode_data[$i] ?? null,
                'keterangan'    => $request->keterangan_item[$i] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        OnsitePermintaanItem::insert($items);

        return redirect()->route('onsite.permintaan.print', [$visit->id, $permintaan->id])
            ->with('success', 'Surat permintaan data berhasil dibuat.');
    }

    /**
     * Halaman cetak surat permintaan data
     */
    public function print(OnsiteVisit $visit, OnsitePermintaanData $permintaan)
    {
        $permintaan->load('items');
        return view('onsite.permintaan.print', compact('visit', 'permintaan'));
    }
}
