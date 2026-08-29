<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KkaTellerKas;
use App\Models\KkaKredit;
use App\Models\KkaBiayaBeban;
use App\Models\KkaBiayaInternal;
use App\Models\KkaPengaduan;
use App\Models\KkaTransaksiUmum;
use App\Models\KkaTransferKu;

class RaKkaController extends Controller
{
    public function index(Request $request, $sheet = 'teller-kas')
    {
        // 1. Normalisasi format parameter URL
        $normalizedSheet = str_replace('-', '_', $sheet);

        // 2. Pemetaan daftar Sheet KKA yang valid
        $validSheets = [
            'teller_kas'     => ['model' => KkaTellerKas::class,     'title' => 'KKA Teller & Kas',      'route_param' => 'teller-kas'],
            'kredit'         => ['model' => KkaKredit::class,         'title' => 'KKA Kredit',            'route_param' => 'kredit'],
            'biaya_beban'    => ['model' => KkaBiayaBeban::class,    'title' => 'KKA Biaya & Beban',     'route_param' => 'biaya-beban'],
            'biaya_internal' => ['model' => KkaBiayaInternal::class, 'title' => 'KKA Biaya Internal',    'route_param' => 'biaya-internal'],
            'pengaduan'      => ['model' => KkaPengaduan::class,      'title' => 'KKA Pengaduan',         'route_param' => 'pengaduan'],
            'transaksi_umum' => ['model' => KkaTransaksiUmum::class, 'title' => 'KKA Transaksi Umum',   'route_param' => 'transaksi-umum'],
            'transfer_ku'    => ['model' => KkaTransferKu::class,    'title' => 'KKA Transfer & Pasiva',  'route_param' => 'transfer-ku'],
        ];

        // Validasi ketersediaan sheet
        if (!array_key_exists($normalizedSheet, $validSheets)) {
            abort(404, 'Sheet KKA tidak ditemukan');
        }

        $activeSheetInfo = $validSheets[$normalizedSheet];
        $modelClass = $activeSheetInfo['model'];

        // 3. Ambil data KKA aktif dengan pagination
        $items = $modelClass::latest()->paginate(15);

        return view('ra-offsite.kka.index', [
            'items'           => $items,
            'currentSheet'    => $normalizedSheet,
            'sheetTitle'      => $activeSheetInfo['title'],
            'availableSheets' => $validSheets
        ]);
    }

    /**
     * Menampilkan Form Detail & Input Pengujian KKA untuk RA
     */
    public function show($area, $kkaId)
    {
        $modelClass = $this->getModelByArea($area);
        $kka = $modelClass::findOrFail($kkaId);
        $wp = $kka->wpOffsite ?? null;

        $areaLabels = [
            'teller-kas'     => 'Teller & Kas',
            'kredit'         => 'Kredit',
            'biaya-beban'    => 'Biaya & Beban',
            'biaya-internal' => 'Biaya Internal',
            'pengaduan'      => 'Pengaduan',
            'transaksi-umum' => 'Transaksi Umum',
            'transfer-ku'    => 'Transfer & Pasiva',
        ];

        $areaLabel = $areaLabels[$area] ?? ucfirst($area);

        return view('ra-offsite.kka-show', compact('kka', 'wp', 'area', 'areaLabel'));
    }

    /**
     * Menyimpan/Mengupdate Hasil Kerja RA
     */
    public function update(Request $request, $area, $kkaId)
    {
        $request->validate([
            'bukti_referensi' => 'nullable|string|max:255',
            'hasil_uji'       => 'nullable|string',
            'status_review'   => 'required|string',
            'simpulan_ra'     => 'nullable|string',
            'dampak'          => 'nullable|string|max:255',
            'kemungkinan'     => 'nullable|string|max:255',
        ]);

        try {
            $modelClass = $this->getModelByArea($area);
            $kka = $modelClass::findOrFail($kkaId);

            $kka->update([
                'bukti_referensi' => $request->bukti_referensi,
                'hasil_uji'       => $request->hasil_uji,
                'status_review'   => $request->status_review,
                'simpulan_ra'     => $request->simpulan_ra,
                'dampak'          => $request->dampak,
                'kemungkinan'     => $request->kemungkinan,
            ]);

            return redirect()->back()->with('updated_success', 'Hasil pengujian KKA berhasil diperbarui oleh RA.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data KKA: ' . $e->getMessage());
        }
    }

    /**
     * Helper Pemetaan Area ke Model Class
     */
    private function getModelByArea($area)
    {
        $map = [
            'teller-kas'     => KkaTellerKas::class,
            'kredit'         => KkaKredit::class,
            'biaya-beban'    => KkaBiayaBeban::class,
            'biaya-internal' => KkaBiayaInternal::class,
            'pengaduan'      => KkaPengaduan::class,
            'transaksi-umum' => KkaTransaksiUmum::class,
            'transfer-ku'    => KkaTransferKu::class,
            // Fallback jika dikirim underscore
            'teller_kas'     => KkaTellerKas::class,
            'biaya_beban'    => KkaBiayaBeban::class,
            'biaya_internal' => KkaBiayaInternal::class,
            'transaksi_umum' => KkaTransaksiUmum::class,
            'transfer_ku'    => KkaTransferKu::class,
        ];

        if (!isset($map[$area])) {
            abort(404, 'Area KKA tidak valid');
        }

        return $map[$area];
    }
}