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
use App\Services\CbsFilterService;

class RaKkaController extends Controller
{
    protected $filterService;

    /**
     * Inject CbsFilterService ke Controller
     */
    public function __construct(CbsFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Menampilkan halaman KKA berdasarkan Tab / Sheet yang dipilih.
     * Default fallback diarahkan ke 'transfer_ku' agar data filter seeder langsung terlihat jika diakses tanpa parameter.
     */
    public function index(Request $request, $sheet = 'transfer-ku')
    {
        // 1. Jalankan filter otomatis dari data dump CBS sebelum data ditayangkan
        $this->filterService->processDumpData();

        // 2. Normalisasi format parameter URL (mengubah strip '-' menjadi underscore '_')
        $normalizedSheet = str_replace('-', '_', $sheet);

        // 3. Pemetaan daftar Sheet KKA yang valid
        $validSheets = [
            'transfer_ku'    => ['model' => KkaTransferKu::class,    'title' => 'KKA Transfer & Pasiva',  'route_param' => 'transfer-ku'],
            'transaksi_umum' => ['model' => KkaTransaksiUmum::class, 'title' => 'KKA Transaksi Umum',   'route_param' => 'transaksi-umum'],
            'teller_kas'     => ['model' => KkaTellerKas::class,     'title' => 'KKA Teller & Kas',      'route_param' => 'teller-kas'],
            'kredit'         => ['model' => KkaKredit::class,         'title' => 'KKA Kredit',            'route_param' => 'kredit'],
            'biaya_beban'    => ['model' => KkaBiayaBeban::class,    'title' => 'KKA Biaya & Beban',     'route_param' => 'biaya-beban'],
            'biaya_internal' => ['model' => KkaBiayaInternal::class, 'title' => 'KKA Biaya Internal',    'route_param' => 'biaya-internal'],
            'pengaduan'      => ['model' => KkaPengaduan::class,      'title' => 'KKA Pengaduan',         'route_param' => 'pengaduan'],
        ];

        // Validasi ketersediaan sheet
        if (!array_key_exists($normalizedSheet, $validSheets)) {
            abort(404, 'Sheet KKA tidak ditemukan');
        }

        $activeSheetInfo = $validSheets[$normalizedSheet];
        $modelClass = $activeSheetInfo['model'];

        // 4. Ambil data KKA aktif dengan pagination
        $items = $modelClass::latest()->paginate(15);

        return view('ra-offsite.kka.index', [
            'items'           => $items,
            'currentSheet'    => $normalizedSheet,
            'sheetTitle'      => $activeSheetInfo['title'],
            'availableSheets' => $validSheets
        ]);
    }
}