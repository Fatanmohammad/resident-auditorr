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
    /**
     * Menampilkan halaman KKA berdasarkan Tab / Sheet yang dipilih.
     */
    public function index(Request $request, $sheet = 'teller_kas')
    {
        $validSheets = [
            'teller_kas'     => ['model' => KkaTellerKas::class,     'title' => 'KKA Teller & Kas'],
            'kredit'         => ['model' => KkaKredit::class,         'title' => 'KKA Kredit'],
            'biaya_beban'    => ['model' => KkaBiayaBeban::class,    'title' => 'KKA Biaya & Beban'],
            'biaya_internal' => ['model' => KkaBiayaInternal::class, 'title' => 'KKA Biaya Internal'],
            'pengaduan'      => ['model' => KkaPengaduan::class,      'title' => 'KKA Pengaduan'],
            'transaksi_umum' => ['model' => KkaTransaksiUmum::class, 'title' => 'KKA Transaksi Umum'],
            'transfer_ku'    => ['model' => KkaTransferKu::class,    'title' => 'KKA Transfer & Pasiva'],
        ];

        if (!array_key_exists($sheet, $validSheets)) {
            abort(404, 'Sheet KKA tidak ditemukan');
        }

        $activeSheetInfo = $validSheets[$sheet];
        $modelClass = $activeSheetInfo['model'];

        // Ambil data KKA dengan pagination
        $items = $modelClass::latest()->paginate(15);

        return view('ra-offsite.kka.index', [
            'items'           => $items,
            'currentSheet'    => $sheet,
            'sheetTitle'      => $activeSheetInfo['title'],
            'availableSheets' => $validSheets
        ]);
    }
}