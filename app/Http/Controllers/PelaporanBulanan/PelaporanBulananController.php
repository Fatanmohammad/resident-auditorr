<?php

namespace App\Http\Controllers\PelaporanBulanan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PelaporanBulanan\Sop08MasterParameter;
use App\Models\PelaporanBulanan\Sop08DaftarHariLibur;
use App\Models\PelaporanBulanan\Sop08MasterUnit;
use App\Models\PelaporanBulanan\Sop08KalenderLaporan;
use App\Models\PelaporanBulanan\Sop08ChecklistLaporan;
use App\Models\PelaporanBulanan\Sop08RekapKegiatan;
use App\Models\PelaporanBulanan\Sop08RekapIsuTl;
use App\Models\PelaporanBulanan\Sop08NarasiLaporan;
use App\Models\PelaporanBulanan\Sop08ReviewLaporan;
use App\Models\PelaporanBulanan\Sop08KoreksiApproval;
use App\Models\PelaporanBulanan\Sop08IsuSignifikan;
use App\Models\PelaporanBulanan\Sop08DistribusiQuality;
use App\Models\PelaporanBulanan\Sop08Feedback;
use App\Models\PelaporanBulanan\Sop08MasterRa;
use App\Models\PelaporanBulanan\Sop08LogHarianRa;
use App\Services\PelaporanBulanan\Sop08Service;

class PelaporanBulananController extends Controller
{
    protected Sop08Service $service;

    public function __construct(Sop08Service $service)
    {
        $this->service = $service;
    }

    /**
     * Helper: Cek apakah user adalah Role RA
     */
    protected function isRa(): bool
    {
        $user = auth()->user();
        return $user && strtolower($user->role) === 'ra';
    }

    /**
     * Helper: Cek apakah user adalah Role Admin Sistem
     */
    protected function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai']);
    }

    /**
     * Helper: Ambil daftar kode unit yang diizinkan untuk user saat ini.
     * Jika role RA, kembalikan hanya kode unit milik cabang & anak cabangnya.
     * Jika admin/reviewer/petinggi, kembalikan null (akses seluruh unit).
     */
    protected function getAllowedUnitCodes(): ?array
    {
        $user = auth()->user();
        if (!$this->isRa()) {
            return null; // Bebas akses semua unit
        }

        $accessibleCabangIds = $user->cabangIdYangDapatDiakses() ?? [];
        if (empty($accessibleCabangIds) && $user->cabang_id) {
            $accessibleCabangIds = [$user->cabang_id];
        }

        return Sop08MasterUnit::whereIn('cabang_id', $accessibleCabangIds)
            ->pluck('kode_unit')
            ->toArray();
    }

    /**
     * Helper: Validasi kepemilikan unit untuk aksi individual (403 jika akses ditolak).
     */
    protected function authorizeUnitAccess(string $kodeUnit): void
    {
        $allowedCodes = $this->getAllowedUnitCodes();
        if ($allowedCodes !== null && !in_array($kodeUnit, $allowedCodes)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk unit kerja ini (' . $kodeUnit . ').');
        }
    }

    // =========================================================================
    // 1. SUB-MENU KALENDER LAPORAN (WP-RA-08-A | 17 KOLOM)
    // =========================================================================
    public function kalender(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();

        $query = Sop08KalenderLaporan::with(['checklist', 'narasi'])
            ->whereDate('periode', $periodeDate);

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($request->filled('kode_unit')) {
            $this->authorizeUnitAccess($request->kode_unit);
            $query->where('kode_unit', $request->kode_unit);
        }

        if ($request->filled('ketepatan')) {
            $query->where('ketepatan', $request->ketepatan);
        }

        $kalenderList = $query->orderBy('kode_unit')->get();

        // Rekalkulasi status & ketepatan otomatis setiap dibuka
        foreach ($kalenderList as $item) {
            $this->service->recalculateKalenderRow($item);
        }

        // Unit Options untuk filter
        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        // Summary KPI Kalender
        $totalLaporan = $kalenderList->count();
        $tepatWaktu = $kalenderList->where('ketepatan', 'TEPAT WAKTU')->count();
        $terlambat = $kalenderList->where('ketepatan', 'TERLAMBAT')->count();
        $belumMasuk = $kalenderList->where('ketepatan', 'BELUM MASUK')->count();
        $siapKonsolidasi = $kalenderList->where('masuk_konsolidasi', 'YA')->count();

        return view('pelaporan_bulanan.kalender', compact(
            'params',
            'periode',
            'kalenderList',
            'masterUnits',
            'totalLaporan',
            'tepatWaktu',
            'terlambat',
            'belumMasuk',
            'siapKonsolidasi'
        ));
    }

    public function createKalender(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $allowedUnits = $this->getAllowedUnitCodes();

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        return view('pelaporan_bulanan.kalender_create', compact('params', 'periode', 'masterUnits'));
    }

    public function editKalender($id)
    {
        $kalender = Sop08KalenderLaporan::findOrFail($id);
        $this->authorizeUnitAccess($kalender->kode_unit);

        return view('pelaporan_bulanan.kalender_edit', compact('kalender'));
    }

    public function generateKalender(Request $request)
    {
        $request->validate([
            'periode' => 'required',
        ]);

        $periode = Carbon::parse($request->periode . '-01')->format('Y-m-d');
        $kodeUnit = null;

        if ($this->isRa()) {
            $allowed = $this->getAllowedUnitCodes();
            $kodeUnit = $allowed[0] ?? null;
            if (!$kodeUnit) {
                return redirect()->back()->with('error', 'Cabang penugasan Anda belum terdaftar di Master Unit.');
            }
        } elseif ($request->filled('kode_unit')) {
            $kodeUnit = $request->kode_unit;
        }

        $generated = $this->service->generateKalenderForPeriode($periode, $kodeUnit);

        return redirect()->route('pelaporan-bulanan.kalender', ['periode' => Carbon::parse($periode)->format('Y-m')])
            ->with('success', count($generated) . ' Kalender Laporan Bulanan berhasil diinisiasi untuk periode ' . Carbon::parse($periode)->translatedFormat('F Y') . '.');
    }

    public function submitLaporan(Request $request, $id)
    {
        $kalender = Sop08KalenderLaporan::findOrFail($id);
        $this->authorizeUnitAccess($kalender->kode_unit);

        $result = $this->service->submitLaporan($kalender);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    public function updateKalender(Request $request, $id)
    {
        $kalender = Sop08KalenderLaporan::findOrFail($id);
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isAdmin()) {
            if ($request->has('wajib_lapor')) {
                $kalender->wajib_lapor = $request->wajib_lapor;
            }
            if ($request->filled('tanggal_submit')) {
                $kalender->tanggal_submit = $request->tanggal_submit;
            }
            if ($request->filled('status_final')) {
                $kalender->status_final = $request->status_final;
            }
            if ($request->filled('status_review')) {
                $kalender->status_review = $request->status_review;
            }
        }

        if ($request->has('catatan')) {
            $kalender->catatan = $request->catatan;
        }

        $kalender->save();
        $this->service->recalculateKalenderRow($kalender);

        return redirect()->route('pelaporan-bulanan.kalender', ['periode' => Carbon::parse($kalender->periode)->format('Y-m')])->with('success', "Data Kalender {$kalender->report_id} berhasil diperbarui.");
    }

    // =========================================================================
    // 2. SUB-MENU CHECKLIST LAPORAN (WP-RA-08-B | 17 KOLOM)
    // =========================================================================
    public function checklist(Request $request, $reportId = null)
    {
        $allowedUnits = $this->getAllowedUnitCodes();

        // Jika reportId tidak diberikan, ambil laporan pertama yang relevan
        if (!$reportId) {
            $firstReport = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                    $q->whereIn('kode_unit', $allowedUnits);
                })
                ->orderBy('periode', 'desc')
                ->orderBy('kode_unit', 'asc')
                ->first();

            $reportId = $firstReport?->report_id;
        }

        $kalender = null;
        $checklist = null;

        if ($reportId) {
            $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
            $this->authorizeUnitAccess($kalender->kode_unit);

            $checklist = Sop08ChecklistLaporan::firstOrCreate(
                ['report_id' => $reportId],
                [
                    'komp_identitas_cutoff' => 'BELUM LENGKAP',
                    'komp_ringkasan_eksekutif' => 'BELUM LENGKAP',
                    'komp_rekap_kegiatan' => 'BELUM LENGKAP',
                    'komp_coverage_sampel' => 'BELUM LENGKAP',
                    'komp_exception_temuan' => 'BELUM LENGKAP',
                    'komp_tindak_lanjut' => 'BELUM LENGKAP',
                    'komp_fraud_insidentil' => 'BELUM LENGKAP',
                    'komp_kendala' => 'BELUM LENGKAP',
                    'komp_rencana_berikut' => 'BELUM LENGKAP',
                    'komp_referensi_wp' => 'BELUM LENGKAP',
                    'komp_lampiran_bukti' => 'BELUM LENGKAP',
                    'komp_konsistensi_angka' => 'BELUM LENGKAP',
                ]
            );

            $this->service->recalculateChecklist($checklist);
        }

        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->orderBy('kode_unit', 'asc')
            ->get();

        $komponenList = Sop08ChecklistLaporan::komponenList();

        return view('pelaporan_bulanan.checklist', compact(
            'kalender',
            'checklist',
            'availableReports',
            'komponenList',
            'reportId'
        ));
    }

    public function updateChecklist(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        // Kunci jika sudah APPROVED/FINAL
        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Laporan telah dikunci (APPROVED/FINAL) dan tidak dapat diubah.');
        }

        $checklist = Sop08ChecklistLaporan::where('report_id', $reportId)->firstOrFail();

        $komponenKeys = array_keys(Sop08ChecklistLaporan::komponenList());
        foreach ($komponenKeys as $k) {
            if ($request->has($k)) {
                $checklist->$k = $request->$k;
            }
        }

        if ($request->has('catatan')) {
            $checklist->catatan = $request->catatan;
        }

        $checklist->save();
        $this->service->recalculateChecklist($checklist);

        return redirect()->back()->with('success', "Checklist kelengkapan {$reportId} berhasil disimpan. Status: {$checklist->status_kesiapan} ({$checklist->persen_lengkap}%).");
    }

    // =========================================================================
    // 3. SUB-MENU REKAP KEGIATAN (WP-RA-08-D | 20 KOLOM)
    // =========================================================================
    public function rekapKegiatan(Request $request, $reportId = null)
    {
        $allowedUnits = $this->getAllowedUnitCodes();

        if (!$reportId) {
            $firstReport = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                    $q->whereIn('kode_unit', $allowedUnits);
                })
                ->orderBy('periode', 'desc')
                ->first();
            $reportId = $firstReport?->report_id;
        }

        $kalender = null;
        $kegiatanList = collect();

        if ($reportId) {
            $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
            $this->authorizeUnitAccess($kalender->kode_unit);

            $kegiatanList = Sop08RekapKegiatan::where('report_id', $reportId)
                ->orderBy('id', 'asc')
                ->get();
        }

        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->get();

        $jenisKegiatanList = Sop08RekapKegiatan::jenisKegiatanList();
        $areaList = Sop08RekapKegiatan::areaList();

        return view('pelaporan_bulanan.rekap_kegiatan', compact(
            'kalender',
            'kegiatanList',
            'availableReports',
            'jenisKegiatanList',
            'areaList',
            'reportId'
        ));
    }

    public function syncKegiatan(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Laporan telah berstatus FINAL dan tidak dapat diubah.');
        }

        $res = $this->service->syncFromOtherModules($reportId);

        return redirect()->back()->with('success', $res['message']);
    }

    public function createKegiatan($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $reportId])
                ->with('error', 'Laporan telah FINAL.');
        }

        $jenisKegiatanList = Sop08RekapKegiatan::jenisKegiatanList();
        $areaList = Sop08RekapKegiatan::areaList();

        return view('pelaporan_bulanan.rekap_kegiatan_create', compact('kalender', 'jenisKegiatanList', 'areaList', 'reportId'));
    }

    public function editKegiatan($id)
    {
        $kegiatan = Sop08RekapKegiatan::findOrFail($id);
        $this->authorizeUnitAccess($kegiatan->kode_unit);

        $kalender = Sop08KalenderLaporan::where('report_id', $kegiatan->report_id)->firstOrFail();
        $jenisKegiatanList = Sop08RekapKegiatan::jenisKegiatanList();
        $areaList = Sop08RekapKegiatan::areaList();

        return view('pelaporan_bulanan.rekap_kegiatan_edit', compact('kegiatan', 'kalender', 'jenisKegiatanList', 'areaList'));
    }

    public function storeKegiatan(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Laporan telah FINAL.');
        }

        $request->validate([
            'jenis_kegiatan' => 'required',
            'area' => 'required',
            'sumber_sop' => 'required',
        ]);

        $count = Sop08RekapKegiatan::where('report_id', $reportId)->count() + 1;
        $sourceExportId = "EXP-MANUAL-{$reportId}-" . str_pad($count, 2, '0', STR_PAD_LEFT);

        Sop08RekapKegiatan::create([
            'source_export_id' => $sourceExportId,
            'report_id' => $kalender->report_id,
            'periode' => $kalender->periode,
            'kode_unit' => $kalender->kode_unit,
            'nama_unit' => $kalender->nama_unit,
            'resident_auditor' => $kalender->resident_auditor,
            'sumber_sop' => $request->sumber_sop,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'area' => $request->area,
            'rencana' => $request->rencana ?? 0,
            'realisasi' => $request->realisasi ?? 0,
            'populasi' => $request->populasi ?? 0,
            'sampel_terpilih' => $request->sampel_terpilih ?? 0,
            'sudah_diuji' => $request->sudah_diuji ?? 0,
            'exception' => $request->exception ?? 0,
            'finding' => $request->finding ?? 0,
            'high_risk' => $request->high_risk ?? 0,
            'referensi_wp_kka' => $request->referensi_wp_kka,
            'status_kegiatan' => $request->status_kegiatan ?? 'SELESAI',
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $reportId])->with('success', 'Baris Rekap Kegiatan berhasil ditambahkan.');
    }

    public function updateKegiatan(Request $request, $id)
    {
        $kegiatan = Sop08RekapKegiatan::findOrFail($id);
        $this->authorizeUnitAccess($kegiatan->kode_unit);

        $kegiatan->update($request->only([
            'sumber_sop',
            'jenis_kegiatan',
            'area',
            'rencana',
            'realisasi',
            'populasi',
            'sampel_terpilih',
            'sudah_diuji',
            'exception',
            'finding',
            'high_risk',
            'referensi_wp_kka',
            'status_kegiatan',
            'catatan',
        ]));

        return redirect()->route('pelaporan-bulanan.rekap-kegiatan', ['reportId' => $kegiatan->report_id])->with('success', 'Data kegiatan berhasil diperbarui.');
    }

    public function destroyKegiatan($id)
    {
        $kegiatan = Sop08RekapKegiatan::findOrFail($id);
        $this->authorizeUnitAccess($kegiatan->kode_unit);
        $kegiatan->delete();

        return redirect()->back()->with('success', 'Baris kegiatan berhasil dihapus.');
    }

    // =========================================================================
    // 4. SUB-MENU REKAP ISU & TL (WP-RA-08-E | 23 KOLOM)
    // =========================================================================
    public function rekapIsu(Request $request, $reportId = null)
    {
        $allowedUnits = $this->getAllowedUnitCodes();

        if (!$reportId) {
            $firstReport = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                    $q->whereIn('kode_unit', $allowedUnits);
                })
                ->orderBy('periode', 'desc')
                ->first();
            $reportId = $firstReport?->report_id;
        }

        $kalender = null;
        $isuList = collect();

        if ($reportId) {
            $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
            $this->authorizeUnitAccess($kalender->kode_unit);

            $isuList = Sop08RekapIsuTl::where('report_id', $reportId)
                ->orderBy('id', 'asc')
                ->get();

            // Hitung ulang aging & flag signifikan
            foreach ($isuList as $isu) {
                $this->service->recalculateRekapIsu($isu);
            }
        }

        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->get();

        $areaList = Sop08RekapIsuTl::areaList();

        return view('pelaporan_bulanan.rekap_isu_tl', compact(
            'kalender',
            'isuList',
            'availableReports',
            'areaList',
            'reportId'
        ));
    }

    public function createIsu($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $reportId])
                ->with('error', 'Laporan telah FINAL.');
        }

        $areaList = Sop08RekapIsuTl::areaList();

        return view('pelaporan_bulanan.rekap_isu_tl_create', compact('kalender', 'areaList', 'reportId'));
    }

    public function editIsu($id)
    {
        $isu = Sop08RekapIsuTl::findOrFail($id);
        $this->authorizeUnitAccess($isu->kode_unit);

        $kalender = Sop08KalenderLaporan::where('report_id', $isu->report_id)->firstOrFail();
        $areaList = Sop08RekapIsuTl::areaList();

        return view('pelaporan_bulanan.rekap_isu_tl_edit', compact('isu', 'kalender', 'areaList'));
    }

    public function storeIsu(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Laporan telah FINAL.');
        }

        $request->validate([
            'sumber_sop' => 'required',
            'area' => 'required',
            'jenis_isu' => 'required',
            'risk_level' => 'required',
            'uraian_isu' => 'required',
        ]);

        $count = Sop08RekapIsuTl::where('report_id', $reportId)->count() + 1;
        $issueId = "ISU-" . Carbon::parse($kalender->periode)->format('Ym') . "-" . str_pad($kalender->kode_unit, 3, '0', STR_PAD_LEFT) . "-" . str_pad($count, 2, '0', STR_PAD_LEFT);

        $isu = new Sop08RekapIsuTl();
        $isu->issue_id = $issueId;
        $isu->report_id = $kalender->report_id;
        $isu->periode = $kalender->periode;
        $isu->kode_unit = $kalender->kode_unit;
        $isu->nama_unit = $kalender->nama_unit;
        $isu->resident_auditor = $kalender->resident_auditor;
        $isu->sumber_sop = $request->sumber_sop;
        $isu->referensi_sumber = $request->referensi_sumber;
        $isu->area = $request->area;
        $isu->jenis_isu = $request->jenis_isu;
        $isu->risk_level = $request->risk_level;
        $isu->uraian_isu = $request->uraian_isu;
        $isu->sebab_awal = $request->sebab_awal;
        $isu->dampak_risiko = $request->dampak_risiko;
        $isu->rekomendasi = $request->rekomendasi;
        $isu->pic = $request->pic;
        $isu->target_tl = $request->target_tl;
        $isu->status_tl = $request->status_tl ?? 'OPEN';
        $isu->berulang = $request->berulang ?? 'TIDAK';
        $isu->nilai_dampak = $request->nilai_dampak ?? 0;
        $isu->save();

        $this->service->recalculateRekapIsu($isu);

        return redirect()->route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $reportId])->with('success', "Isu {$issueId} berhasil ditambahkan [Flag Signifikan: {$isu->flag_signifikan}, Jalur: {$isu->jalur_tindak_lanjut}].");
    }

    public function updateIsu(Request $request, $id)
    {
        $isu = Sop08RekapIsuTl::findOrFail($id);
        $this->authorizeUnitAccess($isu->kode_unit);

        $isu->fill($request->only([
            'sumber_sop',
            'referensi_sumber',
            'area',
            'jenis_isu',
            'risk_level',
            'uraian_isu',
            'sebab_awal',
            'dampak_risiko',
            'rekomendasi',
            'pic',
            'target_tl',
            'status_tl',
            'berulang',
            'nilai_dampak',
        ]));
        $isu->save();

        $this->service->recalculateRekapIsu($isu);

        return redirect()->route('pelaporan-bulanan.rekap-isu-tl', ['reportId' => $isu->report_id])->with('success', "Data Isu {$isu->issue_id} berhasil diperbarui.");
    }

    public function destroyIsu($id)
    {
        $isu = Sop08RekapIsuTl::findOrFail($id);
        $this->authorizeUnitAccess($isu->kode_unit);
        $isu->delete();

        return redirect()->back()->with('success', 'Baris isu berhasil dihapus.');
    }

    // =========================================================================
    // 5. SUB-MENU NARASI LAPORAN (6 BAGIAN NARASI)
    // =========================================================================
    public function narasi(Request $request, $reportId = null)
    {
        $allowedUnits = $this->getAllowedUnitCodes();

        if (!$reportId) {
            $firstReport = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                    $q->whereIn('kode_unit', $allowedUnits);
                })
                ->orderBy('periode', 'desc')
                ->first();
            $reportId = $firstReport?->report_id;
        }

        $kalender = null;
        $narasi = null;

        if ($reportId) {
            $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
            $this->authorizeUnitAccess($kalender->kode_unit);

            $narasi = Sop08NarasiLaporan::firstOrCreate(
                ['report_id' => $reportId],
                [
                    'ringkasan_eksekutif' => "Laporan bulanan pengawasan Resident Auditor periode " . Carbon::parse($kalender->periode)->translatedFormat('F Y') . " pada {$kalender->nama_unit}.",
                ]
            );
        }

        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->get();

        return view('pelaporan_bulanan.narasi', compact(
            'kalender',
            'narasi',
            'availableReports',
            'reportId'
        ));
    }

    public function updateNarasi(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($kalender->status_final === 'APPROVED/FINAL' && !$this->isAdmin()) {
            return redirect()->back()->with('error', 'Laporan telah FINAL.');
        }

        $narasi = Sop08NarasiLaporan::firstOrCreate(['report_id' => $reportId]);
        $narasi->update($request->only([
            'ringkasan_eksekutif',
            'hasil_utama',
            'analisis_risiko_tren',
            'kendala',
            'rencana_bulan_berikut',
            'arahan_diperlukan',
        ]));

        return redirect()->back()->with('success', "Narasi laporan {$reportId} berhasil disimpan.");
    }

    // =========================================================================
    // 6. VIEW & CETAK LAPORAN BULANAN (WP-RA-08-C)
    // =========================================================================
    public function laporanBulanan(Request $request, $reportId = null)
    {
        $allowedUnits = $this->getAllowedUnitCodes();

        if (!$reportId) {
            $firstReport = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                    $q->whereIn('kode_unit', $allowedUnits);
                })
                ->orderBy('periode', 'desc')
                ->first();
            $reportId = $firstReport?->report_id;
        }

        $kalender = null;
        $narasi = null;
        $capaianKegiatan = [];
        $ringkasanIsu = [];

        if ($reportId) {
            $kalender = Sop08KalenderLaporan::with('checklist')->where('report_id', $reportId)->firstOrFail();
            $this->authorizeUnitAccess($kalender->kode_unit);

            $narasi = Sop08NarasiLaporan::firstOrCreate(['report_id' => $reportId]);

            // Tabel Capaian 6 Kegiatan (SUMIFS Realisasi dan Sudah Diuji dari Rekap Kegiatan)
            $standarKegiatan = Sop08RekapKegiatan::jenisKegiatanList();
            $kegiatanRows = Sop08RekapKegiatan::where('report_id', $reportId)->get();

            foreach ($standarKegiatan as $jk) {
                $filtered = $kegiatanRows->where('jenis_kegiatan', $jk);
                $capaianKegiatan[$jk] = [
                    'realisasi' => $filtered->sum('realisasi'),
                    'sudah_diuji' => $filtered->sum('sudah_diuji'),
                ];
            }

            // Tabel Ringkasan Hasil & Risiko (COUNTIFS dari Rekap Isu & TL)
            $isuRows = Sop08RekapIsuTl::where('report_id', $reportId)->get();

            $ringkasanIsu = [
                'Exception' => [
                    'jumlah' => $isuRows->where('jenis_isu', 'EXCEPTION')->count(),
                    'high_risk' => $isuRows->where('jenis_isu', 'EXCEPTION')->where('risk_level', 'HIGH')->count(),
                ],
                'Finding' => [
                    'jumlah' => $isuRows->where('jenis_isu', 'FINDING')->count(),
                    'high_risk' => $isuRows->where('jenis_isu', 'FINDING')->where('risk_level', 'HIGH')->count(),
                ],
                'Indikasi Fraud' => [
                    'jumlah' => $isuRows->where('jenis_isu', 'INDIKASI FRAUD')->count(),
                    'high_risk' => $isuRows->where('jenis_isu', 'INDIKASI FRAUD')->where('risk_level', 'HIGH')->count(),
                ],
                'TL Overdue' => [
                    'jumlah' => $isuRows->where('status_tl', 'OVERDUE')->count(),
                    'high_risk' => $isuRows->where('status_tl', 'OVERDUE')->where('risk_level', 'HIGH')->count(),
                ],
                'Isu Signifikan' => [
                    'jumlah' => $isuRows->where('flag_signifikan', 'YA')->count(),
                    'high_risk' => $isuRows->where('flag_signifikan', 'YA')->where('risk_level', 'HIGH')->count(),
                ],
            ];
            $raPerformance = $this->service->calculateRaPerformanceForReport($reportId);
        } else {
            $raPerformance = null;
        }

        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->get();

        return view('pelaporan_bulanan.laporan_bulanan', compact(
            'kalender',
            'narasi',
            'capaianKegiatan',
            'ringkasanIsu',
            'raPerformance',
            'availableReports',
            'reportId'
        ));
    }

    // =========================================================================
    // 7. KONFIGURASI MASTER PARAMETER & DAFTAR HARI LIBUR (KHUSUS ADMIN)
    // =========================================================================
    public function masterParameter()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Bagian RA yang berhak mengubah konfigurasi Master Parameter.');
        }

        $params = Sop08MasterParameter::getActive();
        $holidays = Sop08DaftarHariLibur::orderBy('tanggal', 'asc')->get();
        $masterUnits = Sop08MasterUnit::with(['cabang', 'unit', 'userRa'])->orderBy('kode_unit', 'asc')->get();

        return view('pelaporan_bulanan.master_parameter', compact('params', 'holidays', 'masterUnits'));
    }

    public function updateMasterParameter(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $params = Sop08MasterParameter::getActive();
        $params->update($request->only([
            'tanggal_cutoff',
            'periode_dashboard',
            'hari_kerja_jatuh_tempo',
            'reviewer_default',
            'unit_ra_default',
            'target_quality_score',
            'bobot_tepat_waktu',
            'bobot_kelengkapan',
            'bobot_akurasi',
            'bobot_analisis',
            'bobot_bukti',
            'bobot_tindak_lanjut',
            'bobot_eskalasi',
            'ambang_high_attention',
            'bobot_konsistensi',
            'bobot_penyelesaian',
            'bobot_kualitas',
            'ambang_baik_konsisten',
            'ambang_memadai',
            'ambang_perlu_peningkatan',
        ]));

        return redirect()->back()->with('success', 'Konfigurasi Master Parameter SOP 08 berhasil diperbarui.');
    }

    public function storeHariLibur(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'tanggal' => 'required|date|unique:sop08_daftar_hari_liburs,tanggal',
            'keterangan' => 'required|string|max:200',
        ]);

        Sop08DaftarHariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'tahun' => Carbon::parse($request->tanggal)->year,
        ]);

        return redirect()->back()->with('success', 'Hari Libur Nasional berhasil ditambahkan.');
    }

    public function destroyHariLibur($id)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        Sop08DaftarHariLibur::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Hari Libur berhasil dihapus.');
    }

    // =========================================================================
    // FASE 2: 1. SUB-MENU REVIEW LAPORAN (WP-RA-08-F | 19 KOLOM)
    // =========================================================================
    public function reviewLaporan(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();

        $query = Sop08KalenderLaporan::with(['checklist', 'review'])
            ->whereDate('periode', $periodeDate);

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($request->filled('kode_unit')) {
            $this->authorizeUnitAccess($request->kode_unit);
            $query->where('kode_unit', $request->kode_unit);
        }

        $kalenderList = $query->orderBy('kode_unit')->get();

        foreach ($kalenderList as $kal) {
            if (!$kal->review) {
                $this->service->initFase2Records($kal->report_id);
            }
        }

        $reviewQuery = Sop08ReviewLaporan::with('kalender')
            ->whereHas('kalender', function ($q) use ($periodeDate, $allowedUnits, $request) {
                $q->whereDate('periode', $periodeDate);
                if ($allowedUnits !== null) {
                    $q->whereIn('kode_unit', $allowedUnits);
                }
                if ($request->filled('kode_unit')) {
                    $q->where('kode_unit', $request->kode_unit);
                }
            });

        if ($request->filled('status_review_final')) {
            $reviewQuery->where('status_review_final', $request->status_review_final);
        }

        $reviewList = $reviewQuery->get();

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.review_laporan', compact(
            'params',
            'periode',
            'reviewList',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    public function editReviewLaporan($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Resident Auditor hanya memiliki hak akses Read-Only pada form Review Laporan.');
        }

        $this->service->initFase2Records($reportId);
        $review = Sop08ReviewLaporan::where('report_id', $reportId)->firstOrFail();
        $this->service->recalculateReviewLaporan($review);
        $review->refresh();

        $checklist = Sop08ChecklistLaporan::where('report_id', $reportId)->first();
        $opsiPenilaian = Sop08ReviewLaporan::opsiPenilaian();
        $opsiOverride = Sop08ReviewLaporan::opsiOverride();

        return view('pelaporan_bulanan.review_laporan_edit', compact('kalender', 'review', 'checklist', 'opsiPenilaian', 'opsiOverride'));
    }

    public function updateReviewLaporan(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Resident Auditor tidak memiliki hak mengubah hasil review.');
        }

        $request->validate([
            'akurasi_data' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'konsistensi' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'kualitas_analisis' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'bukti_pendukung' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'tindak_lanjut' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'eskalasi' => 'required|in:MEMADAI,PERLU PERBAIKAN,TIDAK MEMADAI',
            'alasan_override' => 'nullable|required_with:override|string',
        ]);

        $review = Sop08ReviewLaporan::where('report_id', $reportId)->firstOrFail();
        $review->reviewer = $request->reviewer ?: (auth()->user()->name ?? 'Reviewer SKAI');
        $review->tanggal_review = $request->tanggal_review ?: now()->format('Y-m-d');
        $review->akurasi_data = $request->akurasi_data;
        $review->konsistensi = $request->konsistensi;
        $review->kualitas_analisis = $request->kualitas_analisis;
        $review->bukti_pendukung = $request->bukti_pendukung;
        $review->tindak_lanjut = $request->tindak_lanjut;
        $review->eskalasi = $request->eskalasi;
        $review->override = $request->override ?: null;
        $review->alasan_override = $request->override ? $request->alasan_override : null;
        $review->save();

        $this->service->recalculateReviewLaporan($review);

        return redirect()->route('pelaporan-bulanan.review-laporan', ['periode' => Carbon::parse($kalender->periode)->format('Y-m')])
            ->with('success', "Penilaian Review untuk {$reportId} berhasil disimpan [Status Final: {$review->status_review_final}, Skor: {$review->skor_review}].");
    }

    // =========================================================================
    // FASE 2: 2. SUB-MENU KOREKSI & APPROVAL (WP-RA-08-G | 15 KOLOM)
    // =========================================================================
    public function koreksiApproval(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();

        $query = Sop08KalenderLaporan::with('koreksi')
            ->whereDate('periode', $periodeDate);

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($request->filled('kode_unit')) {
            $this->authorizeUnitAccess($request->kode_unit);
            $query->where('kode_unit', $request->kode_unit);
        }

        $kalenderList = $query->orderBy('kode_unit')->get();

        foreach ($kalenderList as $kal) {
            if (!$kal->koreksi) {
                $this->service->initFase2Records($kal->report_id);
            }
        }

        $koreksiQuery = Sop08KoreksiApproval::with('kalender')
            ->whereHas('kalender', function ($q) use ($periodeDate, $allowedUnits, $request) {
                $q->whereDate('periode', $periodeDate);
                if ($allowedUnits !== null) {
                    $q->whereIn('kode_unit', $allowedUnits);
                }
                if ($request->filled('kode_unit')) {
                    $q->where('kode_unit', $request->kode_unit);
                }
            });

        if ($request->filled('status_final')) {
            $koreksiQuery->where('status_final', $request->status_final);
        }

        $koreksiList = $koreksiQuery->get();

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.koreksi_approval', compact(
            'params',
            'periode',
            'koreksiList',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    public function editKoreksiApproval($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Resident Auditor tidak dapat menetapkan approval atau meminta revisi.');
        }

        $this->service->initFase2Records($reportId);
        $koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->firstOrFail();
        $review = Sop08ReviewLaporan::where('report_id', $reportId)->first();
        $opsiKeputusan = Sop08KoreksiApproval::opsiKeputusan();

        return view('pelaporan_bulanan.koreksi_approval_edit', compact('kalender', 'koreksi', 'review', 'opsiKeputusan'));
    }

    public function updateKoreksiApproval(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Reviewer / Admin yang berwenang menetapkan keputusan approval.');
        }

        $koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->firstOrFail();
        $koreksi->catatan_reviewer = $request->catatan_reviewer;
        $koreksi->tanggal_permintaan = $request->tanggal_permintaan ?: $koreksi->tanggal_permintaan;
        $koreksi->tanggal_review_ulang = $request->tanggal_review_ulang;
        $koreksi->keputusan_approval = $request->keputusan_approval ?: null;
        $koreksi->approved_by = $request->keputusan_approval ? ($request->approved_by ?: (auth()->user()->name ?? 'Approver SKAI')) : null;
        $koreksi->tanggal_approval = $request->keputusan_approval ? ($request->tanggal_approval ?: now()->format('Y-m-d')) : null;
        $koreksi->versi_final = $request->versi_final ?: ($koreksi->versi_final ?: 'v1.0 Final');
        $koreksi->save();

        $this->service->recalculateKoreksiApproval($koreksi);

        return redirect()->route('pelaporan-bulanan.koreksi-approval', ['periode' => Carbon::parse($kalender->periode)->format('Y-m')])
            ->with('success', "Data Koreksi & Approval untuk {$reportId} berhasil disimpan [Status Final: {$koreksi->status_final}].");
    }

    public function resubmitKoreksiApproval($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        $this->service->initFase2Records($reportId);
        $koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->firstOrFail();

        if ($koreksi->status_final === 'APPROVED/FINAL') {
            return redirect()->route('pelaporan-bulanan.koreksi-approval')
                ->with('error', 'Laporan telah APPROVED/FINAL. Pengiriman revisi sudah terkunci.');
        }

        return view('pelaporan_bulanan.koreksi_approval_resubmit', compact('kalender', 'koreksi'));
    }

    public function postResubmitKoreksiApproval(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        $koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->firstOrFail();

        if ($koreksi->status_final === 'APPROVED/FINAL') {
            return redirect()->back()->with('error', 'Laporan telah APPROVED/FINAL.');
        }

        $request->validate([
            'tanggal_resubmit' => 'required|date',
            'perbaikan_ra' => 'required|string',
        ]);

        $koreksi->tanggal_resubmit = $request->tanggal_resubmit;
        $koreksi->perbaikan_ra = $request->perbaikan_ra;
        $koreksi->save();

        $this->service->recalculateKoreksiApproval($koreksi);

        return redirect()->route('pelaporan-bulanan.koreksi-approval', ['periode' => Carbon::parse($kalender->periode)->format('Y-m')])
            ->with('success', "Laporan revisi {$reportId} berhasil dikirimkan kembali (Resubmit) pada " . Carbon::parse($koreksi->tanggal_resubmit)->format('d/m/Y') . ".");
    }

    // =========================================================================
    // FASE 2: 3. SUB-MENU ISU SIGNIFIKAN (WP-RA-08-H | 17 KOLOM)
    // =========================================================================
    public function isuSignifikan(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();

        // Ambil daftar laporan periode
        $reportsQuery = Sop08KalenderLaporan::whereDate('periode', $periodeDate);
        if ($allowedUnits !== null) {
            $reportsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $availableReports = $reportsQuery->orderBy('kode_unit')->get();

        $selectedReportId = $request->get('reportId', $availableReports->first()->report_id ?? null);
        $kalender = $selectedReportId ? Sop08KalenderLaporan::where('report_id', $selectedReportId)->first() : null;

        $query = Sop08IsuSignifikan::query();
        if ($selectedReportId) {
            $query->where('report_id', $selectedReportId);
        } elseif ($availableReports->isNotEmpty()) {
            $query->whereIn('report_id', $availableReports->pluck('report_id'));
        }

        $isuList = $query->orderBy('issue_id')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.isu_signifikan', compact(
            'params',
            'periode',
            'availableReports',
            'selectedReportId',
            'kalender',
            'isuList',
            'isRa',
            'isAdmin'
        ));
    }

    public function pullIsuSignifikanAction($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        $count = $this->service->pullIsuSignifikan($reportId);

        return redirect()->back()->with('success', "Berhasil menarik {$count} isu dengan Flag Signifikan = YA ke Matriks Isu Signifikan.");
    }

    public function editIsuSignifikan($id)
    {
        $isu = Sop08IsuSignifikan::findOrFail($id);
        $kalender = Sop08KalenderLaporan::where('report_id', $isu->report_id)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        $isRa = $this->isRa();
        $opsiKeputusan = Sop08IsuSignifikan::opsiKeputusan();
        $opsiTujuan = Sop08IsuSignifikan::opsiTujuan();

        return view('pelaporan_bulanan.isu_signifikan_edit', compact('isu', 'kalender', 'isRa', 'opsiKeputusan', 'opsiTujuan'));
    }

    public function updateIsuSignifikan(Request $request, $id)
    {
        $isu = Sop08IsuSignifikan::findOrFail($id);
        $kalender = Sop08KalenderLaporan::where('report_id', $isu->report_id)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            // RA hanya boleh input referensi bukti
            $isu->referensi_bukti = $request->referensi_bukti;
        } else {
            // Admin / Reviewer full access
            $isu->keputusan_reviewer = $request->keputusan_reviewer;
            $isu->tujuan_eskalasi = $request->tujuan_eskalasi;
            $isu->tanggal_eskalasi = $request->tanggal_eskalasi;
            $isu->referensi_bukti = $request->referensi_bukti;
            $isu->arahan_tindak_lanjut = $request->arahan_tindak_lanjut;
        }
        $isu->save();

        return redirect()->route('pelaporan-bulanan.isu-signifikan', ['reportId' => $isu->report_id, 'periode' => Carbon::parse($isu->periode)->format('Y-m')])
            ->with('success', "Keputusan eskalasi isu {$isu->issue_id} berhasil disimpan.");
    }

    // =========================================================================
    // FASE 2: 4. SUB-MENU DISTRIBUSI, ARSIP & QUALITY SCORE (WP-RA-08-I | 18 KOLOM)
    // =========================================================================
    public function distribusiQuality(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();

        $query = Sop08KalenderLaporan::with('distribusi')
            ->whereDate('periode', $periodeDate);

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($request->filled('kode_unit')) {
            $this->authorizeUnitAccess($request->kode_unit);
            $query->where('kode_unit', $request->kode_unit);
        }

        $kalenderList = $query->orderBy('kode_unit')->get();

        foreach ($kalenderList as $kal) {
            if (!$kal->distribusi) {
                $this->service->initFase2Records($kal->report_id);
            }
        }

        $distQuery = Sop08DistribusiQuality::with('kalender')
            ->whereHas('kalender', function ($q) use ($periodeDate, $allowedUnits, $request) {
                $q->whereDate('periode', $periodeDate);
                if ($allowedUnits !== null) {
                    $q->whereIn('kode_unit', $allowedUnits);
                }
                if ($request->filled('kode_unit')) {
                    $q->where('kode_unit', $request->kode_unit);
                }
            });

        if ($request->filled('kategori')) {
            $distQuery->where('kategori', $request->kategori);
        }

        $distribusiList = $distQuery->get();

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.distribusi_quality', compact(
            'params',
            'periode',
            'distribusiList',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    public function editDistribusiQuality($reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Resident Auditor hanya memiliki hak akses Read-Only pada Quality Score & Distribusi.');
        }

        $this->service->initFase2Records($reportId);
        $dist = Sop08DistribusiQuality::where('report_id', $reportId)->firstOrFail();
        $review = Sop08ReviewLaporan::where('report_id', $reportId)->first();
        $this->service->recalculateDistribusiQuality($dist, $review);
        $dist->refresh();

        $opsiStatusArsip = Sop08DistribusiQuality::opsiStatusArsip();

        return view('pelaporan_bulanan.distribusi_quality_edit', compact('kalender', 'dist', 'review', 'opsiStatusArsip'));
    }

    public function updateDistribusiQuality(Request $request, $reportId)
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $this->authorizeUnitAccess($kalender->kode_unit);

        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Reviewer / Admin yang berwenang mengubah data Distribusi & Quality.');
        }

        if ($request->filled('penyesuaian_reviewer') && (float)$request->penyesuaian_reviewer != 0) {
            $request->validate([
                'catatan_kpi' => 'required|string',
            ], [
                'catatan_kpi.required' => 'Penyesuaian Reviewer wajib menyertakan alasan transparansi pada Catatan KPI (SOP 08).',
            ]);
        }

        $dist = Sop08DistribusiQuality::where('report_id', $reportId)->firstOrFail();
        $dist->tanggal_distribusi = $request->tanggal_distribusi;
        $dist->penerima = $request->penerima;
        $dist->media_arsip = $request->media_arsip;
        $dist->versi = $request->versi ?: 'v1.0 Final';
        $dist->penyesuaian_reviewer = $request->penyesuaian_reviewer ?: 0.00;
        $dist->catatan_kpi = $request->catatan_kpi;
        $dist->status_arsip = $request->status_arsip ?: 'BELUM DIARSIP';
        $dist->save();

        $this->service->recalculateDistribusiQuality($dist);

        return redirect()->route('pelaporan-bulanan.distribusi-quality', ['periode' => Carbon::parse($kalender->periode)->format('Y-m')])
            ->with('success', "Distribusi & Quality Score untuk {$reportId} berhasil disimpan [Quality Score: {$dist->quality_score}, Kategori: {$dist->kategori}].");
    }

    // =========================================================================
    // FASE 3: 1. SUB-MENU KONSOLIDASI LAPORAN (WP-RA-08-J | 21 KOLOM)
    // =========================================================================
    public function konsolidasi(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();
        $kodeUnit = $request->get('kode_unit');
        if ($kodeUnit) {
            $this->authorizeUnitAccess($kodeUnit);
        }

        $konsolidasiRows = $this->service->getKonsolidasiData($periodeDate, $allowedUnits, $kodeUnit);

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.konsolidasi', compact(
            'params',
            'periode',
            'konsolidasiRows',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    // =========================================================================
    // FASE 3: 2. SUB-MENU TREN ISU (WP-RA-08-K | 10 KOLOM)
    // =========================================================================
    public function trenIsu(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();
        $kodeUnit = $request->get('kode_unit');
        if ($kodeUnit) {
            $this->authorizeUnitAccess($kodeUnit);
        }

        $trenRows = $this->service->getTrenIsuData($periodeDate, $allowedUnits, $kodeUnit);

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.tren_isu', compact(
            'params',
            'periode',
            'trenRows',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    // =========================================================================
    // FASE 3: 3. SUB-MENU DASHBOARD SOP 08 (WP-RA-08-L)
    // =========================================================================
    public function dashboard(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();
        $kodeUnit = $request->get('kode_unit');
        if ($kodeUnit) {
            $this->authorizeUnitAccess($kodeUnit);
        }

        $dashboardData = $this->service->getDashboardData($periodeDate, $allowedUnits, $kodeUnit);

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        return view('pelaporan_bulanan.dashboard', compact(
            'params',
            'periode',
            'dashboardData',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    // =========================================================================
    // FASE 3: 4. SUB-MENU FEEDBACK KONSOLIDASI (WP-RA-08-M | 14 KOLOM)
    // =========================================================================
    public function feedback(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $allowedUnits = $this->getAllowedUnitCodes();

        $query = Sop08Feedback::with('kalender');

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($request->filled('kode_unit')) {
            $this->authorizeUnitAccess($request->kode_unit);
            $query->where('kode_unit', $request->kode_unit);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_feedback')) {
            $query->where('jenis_feedback', $request->jenis_feedback);
        }

        $feedbackList = $query->orderBy('tanggal', 'desc')->get();

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();
        $statusList = Sop08Feedback::statusList();
        $jenisList = Sop08Feedback::jenisFeedbackList();

        return view('pelaporan_bulanan.feedback', compact(
            'params',
            'feedbackList',
            'masterUnits',
            'statusList',
            'jenisList',
            'isRa',
            'isAdmin'
        ));
    }

    public function createFeedback(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Bagian RA yang berhak membuat feedback konsolidasi.');
        }

        $allowedUnits = $this->getAllowedUnitCodes();
        $availableReports = Sop08KalenderLaporan::when($allowedUnits !== null, function ($q) use ($allowedUnits) {
                $q->whereIn('kode_unit', $allowedUnits);
            })
            ->orderBy('periode', 'desc')
            ->orderBy('kode_unit')
            ->get();

        $autoFeedbackId = $this->service->generateFeedbackId(now()->format('Y-m-d'));
        $jenisList = Sop08Feedback::jenisFeedbackList();

        return view('pelaporan_bulanan.feedback_create', compact(
            'availableReports',
            'autoFeedbackId',
            'jenisList'
        ));
    }

    public function storeFeedback(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Bagian RA yang berhak membuat feedback.');
        }

        $request->validate([
            'report_id' => 'required|exists:sop08_kalender_laporans,report_id',
            'tanggal' => 'required|date',
            'jenis_feedback' => 'required|string',
            'uraian_feedback' => 'required|string',
        ]);

        $kalender = Sop08KalenderLaporan::where('report_id', $request->report_id)->firstOrFail();
        $feedbackId = $this->service->generateFeedbackId($request->tanggal);

        Sop08Feedback::create([
            'feedback_id' => $feedbackId,
            'report_id' => $kalender->report_id,
            'kode_unit' => $kalender->kode_unit,
            'nama_unit' => $kalender->nama_unit,
            'tanggal' => $request->tanggal,
            'diberikan_kepada' => $kalender->resident_auditor . ' (' . $kalender->nama_unit . ')',
            'jenis_feedback' => $request->jenis_feedback,
            'uraian_feedback' => $request->uraian_feedback,
            'pic' => $request->pic ?: $kalender->resident_auditor,
            'target' => $request->target ?: null,
            'status' => 'OPEN',
            'verifikasi_reviewer' => 'BELUM DIVERIFIKASI',
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('pelaporan-bulanan.feedback')
            ->with('success', "Feedback {$feedbackId} berhasil dibuat dan dikirimkan kepada RA {$kalender->nama_unit}.");
    }

    public function editFeedback($id)
    {
        $feedback = Sop08Feedback::findOrFail($id);
        $this->authorizeUnitAccess($feedback->kode_unit);

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();
        $jenisList = Sop08Feedback::jenisFeedbackList();
        $statusList = Sop08Feedback::statusList();
        $verifikasiList = Sop08Feedback::verifikasiReviewerList();

        return view('pelaporan_bulanan.feedback_edit', compact(
            'feedback',
            'isRa',
            'isAdmin',
            'jenisList',
            'statusList',
            'verifikasiList'
        ));
    }

    public function updateFeedback(Request $request, $id)
    {
        $feedback = Sop08Feedback::findOrFail($id);
        $this->authorizeUnitAccess($feedback->kode_unit);

        if ($this->isRa()) {
            // RA hanya boleh mengisi Tindakan Perbaikan, Bukti, Tanggal Selesai, dan update status ke IN PROGRESS / MENUNGGU VERIFIKASI
            $request->validate([
                'tindakan_perbaikan' => 'required|string',
            ]);

            $feedback->tindakan_perbaikan = $request->tindakan_perbaikan;
            $feedback->bukti = $request->bukti;
            $feedback->tanggal_selesai = $request->tanggal_selesai ?: now()->format('Y-m-d');
            $feedback->status = $request->status ?: 'MENUNGGU VERIFIKASI';
        } else {
            // Admin / Reviewer memiliki full access
            $feedback->tanggal = $request->tanggal ?: $feedback->tanggal;
            $feedback->jenis_feedback = $request->jenis_feedback ?: $feedback->jenis_feedback;
            $feedback->uraian_feedback = $request->uraian_feedback ?: $feedback->uraian_feedback;
            $feedback->pic = $request->pic ?: $feedback->pic;
            $feedback->target = $request->target ?: $feedback->target;
            $feedback->tindakan_perbaikan = $request->tindakan_perbaikan ?: $feedback->tindakan_perbaikan;
            $feedback->bukti = $request->bukti ?: $feedback->bukti;
            $feedback->tanggal_selesai = $request->tanggal_selesai ?: $feedback->tanggal_selesai;
            $feedback->status = $request->status ?: $feedback->status;
            $feedback->verifikasi_reviewer = $request->verifikasi_reviewer ?: $feedback->verifikasi_reviewer;
            $feedback->catatan = $request->catatan;

            if ($feedback->verifikasi_reviewer === 'MEMADAI / CLOSED') {
                $feedback->status = 'CLOSED';
            }
        }

        $feedback->save();

        return redirect()->route('pelaporan-bulanan.feedback')
            ->with('success', "Data Feedback {$feedback->feedback_id} berhasil diperbarui.");
    }

    // =========================================================================
    // FASE 4: 1. SUB-MENU MASTER_RA (ROSTER RESIDENT AUDITOR - 9 KOLOM: A s.d. I)
    // =========================================================================
    public function masterRa(Request $request)
    {
        $allowedUnits = $this->getAllowedUnitCodes();
        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        $query = Sop08MasterRa::with('masterUnit');

        if ($isRa) {
            // RA hanya melihat profil penempatan dirinya sendiri
            $user = auth()->user();
            $query->where(function($q) use ($user, $allowedUnits) {
                $q->where('user_id', $user->id);
                if ($allowedUnits !== null) {
                    $q->orWhereIn('kode_kc_penempatan', $allowedUnits);
                }
            });
        } else {
            if ($request->filled('kode_kc')) {
                $query->where('kode_kc_penempatan', $request->kode_kc);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $masterRaList = $query->orderBy('kode_kc_penempatan')->get();
        $masterUnits = Sop08MasterUnit::where('is_active', true)->orderBy('kode_unit')->get();

        return view('pelaporan_bulanan.master_ra', compact(
            'masterRaList',
            'masterUnits',
            'isRa',
            'isAdmin'
        ));
    }

    public function createMasterRa()
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: RA tidak memiliki hak untuk menambah Master Roster RA.');
        }

        $masterUnits = Sop08MasterUnit::where('is_active', true)->orderBy('kode_unit')->get();
        $users = \App\Models\User::where('role', 'ra')->orderBy('name')->get();
        $statusList = Sop08MasterRa::statusList();

        $nextNum = Sop08MasterRa::count() + 1;
        $autoRaId = "RA-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('pelaporan_bulanan.master_ra_create', compact(
            'masterUnits',
            'users',
            'statusList',
            'autoRaId'
        ));
    }

    public function storeMasterRa(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: RA tidak memiliki hak untuk menambah Master Roster RA.');
        }

        $request->validate([
            'ra_id' => 'required|unique:sop08_master_ras,ra_id',
            'nama_ra' => 'required|string|max:150',
            'kode_kc_penempatan' => 'required|string|max:10',
            'status' => 'required|string',
        ]);

        $unit = Sop08MasterUnit::where('kode_unit', $request->kode_kc_penempatan)->first();

        Sop08MasterRa::create([
            'ra_id' => $request->ra_id,
            'user_id' => $request->user_id ?: null,
            'nama_ra' => $request->nama_ra,
            'kode_kc_penempatan' => $request->kode_kc_penempatan,
            'nama_kc' => $unit ? $unit->nama_unit : $request->nama_kc,
            'unit_ra' => $request->unit_ra ?: ($unit ? $unit->unit_ra : 'Bagian Resident Auditor'),
            'status' => $request->status,
            'tanggal_mulai' => $request->tanggal_mulai ?: null,
            'tanggal_selesai' => $request->tanggal_selesai ?: null,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('pelaporan-bulanan.master-ra')
            ->with('success', "Resident Auditor {$request->nama_ra} ({$request->ra_id}) berhasil ditambahkan ke Roster.");
    }

    public function editMasterRa($id)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: RA tidak berhak mengedit Master Roster RA.');
        }

        $masterRa = Sop08MasterRa::findOrFail($id);
        $masterUnits = Sop08MasterUnit::where('is_active', true)->orderBy('kode_unit')->get();
        $users = \App\Models\User::where('role', 'ra')->orderBy('name')->get();
        $statusList = Sop08MasterRa::statusList();

        return view('pelaporan_bulanan.master_ra_edit', compact(
            'masterRa',
            'masterUnits',
            'users',
            'statusList'
        ));
    }

    public function updateMasterRa(Request $request, $id)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: RA tidak berhak mengedit Master Roster RA.');
        }

        $masterRa = Sop08MasterRa::findOrFail($id);

        $request->validate([
            'nama_ra' => 'required|string|max:150',
            'kode_kc_penempatan' => 'required|string|max:10',
            'status' => 'required|string',
        ]);

        $unit = Sop08MasterUnit::where('kode_unit', $request->kode_kc_penempatan)->first();

        $masterRa->update([
            'user_id' => $request->user_id ?: $masterRa->user_id,
            'nama_ra' => $request->nama_ra,
            'kode_kc_penempatan' => $request->kode_kc_penempatan,
            'nama_kc' => $unit ? $unit->nama_unit : $masterRa->nama_kc,
            'unit_ra' => $request->unit_ra ?: $masterRa->unit_ra,
            'status' => $request->status,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('pelaporan-bulanan.master-ra')
            ->with('success', "Data Resident Auditor {$masterRa->nama_ra} berhasil diperbarui.");
    }

    // =========================================================================
    // FASE 4: 2. SUB-MENU LOG_HARIAN_RA (17 KOLOM: A s.d. Q)
    // =========================================================================
    public function logHarian(Request $request)
    {
        $allowedUnits = $this->getAllowedUnitCodes();
        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        $periode = $request->get('periode', now()->format('Y-m'));
        $startMonth = Carbon::parse($periode . '-01')->startOfMonth()->format('Y-m-d');
        $endMonth = Carbon::parse($periode . '-01')->endOfMonth()->format('Y-m-d');

        $query = Sop08LogHarianRa::whereBetween('tanggal', [$startMonth, $endMonth]);

        $myMasterRa = null;
        if ($isRa) {
            $user = auth()->user();
            $myMasterRa = Sop08MasterRa::where('user_id', $user->id)
                ->orWhere(function($q) use ($user) {
                    if ($user->unit_kerja) {
                        $q->where('kode_kc_penempatan', $user->unit_kerja);
                    }
                })->first();

            if ($myMasterRa) {
                $query->where('ra_id', $myMasterRa->ra_id);
            } elseif ($allowedUnits !== null) {
                $query->whereIn('kode_unit', $allowedUnits);
            }
        } else {
            if ($request->filled('kode_unit')) {
                $query->where('kode_unit', $request->kode_unit);
            }
            if ($request->filled('ra_id')) {
                $query->where('ra_id', $request->ra_id);
            }
        }

        if ($request->filled('status_hari')) {
            $query->where('status_hari', $request->status_hari);
        }
        if ($request->filled('hasil_review')) {
            $query->where('hasil_review', $request->hasil_review);
        }

        $logList = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        $masterUnits = Sop08MasterUnit::where('is_active', true)->orderBy('kode_unit')->get();
        $masterRas = Sop08MasterRa::where('status', 'Aktif')->orderBy('kode_kc_penempatan')->get();

        $statusHariList = Sop08LogHarianRa::statusHariList();
        $hasilReviewList = Sop08LogHarianRa::hasilReviewList();

        return view('pelaporan_bulanan.log_harian', compact(
            'logList',
            'periode',
            'masterUnits',
            'masterRas',
            'myMasterRa',
            'statusHariList',
            'hasilReviewList',
            'isRa',
            'isAdmin'
        ));
    }

    public function createLogHarian()
    {
        $isRa = $this->isRa();
        $user = auth()->user();

        $myMasterRa = null;
        if ($isRa) {
            $myMasterRa = Sop08MasterRa::where('user_id', $user->id)
                ->orWhere(function($q) use ($user) {
                    if ($user->unit_kerja) {
                        $q->where('kode_kc_penempatan', $user->unit_kerja);
                    }
                })->first();
        }

        $masterRas = Sop08MasterRa::where('status', 'Aktif')->orderBy('kode_kc_penempatan')->get();
        $statusHariList = Sop08LogHarianRa::statusHariList();
        $sumberSopList = Sop08LogHarianRa::sumberSopList();
        $statusPenyelesaianList = Sop08LogHarianRa::statusPenyelesaianList();
        $areaList = Sop08LogHarianRa::areaList();

        $autoLogId = $this->service->generateLogId(now()->format('Y-m-d'));

        return view('pelaporan_bulanan.log_harian_create', compact(
            'myMasterRa',
            'masterRas',
            'statusHariList',
            'sumberSopList',
            'statusPenyelesaianList',
            'areaList',
            'autoLogId',
            'isRa'
        ));
    }

    public function storeLogHarian(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'ra_id' => 'required|exists:sop08_master_ras,ra_id',
            'status_hari' => 'required|string',
        ]);

        $ra = Sop08MasterRa::where('ra_id', $request->ra_id)->firstOrFail();

        if ($this->isRa()) {
            $allowedUnits = $this->getAllowedUnitCodes();
            if ($allowedUnits !== null && !in_array($ra->kode_kc_penempatan, $allowedUnits)) {
                abort(403, 'Akses Ditolak: Anda hanya boleh menginput log harian untuk cabang penugasan Anda.');
            }
        }

        $logId = $this->service->generateLogId($request->tanggal);

        Sop08LogHarianRa::create([
            'log_id' => $logId,
            'tanggal' => $request->tanggal,
            'ra_id' => $ra->ra_id,
            'user_id' => $ra->user_id ?: auth()->id(),
            'nama_ra' => $ra->nama_ra,
            'kode_unit' => $ra->kode_kc_penempatan,
            'nama_unit' => $ra->nama_kc,
            'status_hari' => $request->status_hari,
            'sumber_sop' => $request->sumber_sop ?: null,
            'jenis_aktivitas' => $request->jenis_aktivitas ?: ($request->status_hari !== 'KERJA' ? $request->status_hari : 'Aktivitas Harian Audit'),
            'area' => $request->area ?: 'Umum / Administrasi',
            'output_referensi_wp' => $request->output_referensi_wp ?: null,
            'status_penyelesaian' => $request->status_hari === 'KERJA' ? ($request->status_penyelesaian ?: 'COMPLETED') : null,
            'hasil_review' => 'BELUM DIREVIEW',
            'jumlah_item' => (int)($request->jumlah_item ?? 0),
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('pelaporan-bulanan.log-harian', ['periode' => Carbon::parse($request->tanggal)->format('Y-m')])
            ->with('success', "Log Aktivitas {$logId} berhasil dicatat.");
    }

    public function editLogHarian($id)
    {
        $log = Sop08LogHarianRa::findOrFail($id);
        $this->authorizeUnitAccess($log->kode_unit);

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        $statusHariList = Sop08LogHarianRa::statusHariList();
        $sumberSopList = Sop08LogHarianRa::sumberSopList();
        $statusPenyelesaianList = Sop08LogHarianRa::statusPenyelesaianList();
        $hasilReviewList = Sop08LogHarianRa::hasilReviewList();
        $areaList = Sop08LogHarianRa::areaList();

        return view('pelaporan_bulanan.log_harian_edit', compact(
            'log',
            'statusHariList',
            'sumberSopList',
            'statusPenyelesaianList',
            'hasilReviewList',
            'areaList',
            'isRa',
            'isAdmin'
        ));
    }

    public function updateLogHarian(Request $request, $id)
    {
        $log = Sop08LogHarianRa::findOrFail($id);
        $this->authorizeUnitAccess($log->kode_unit);

        if ($this->isRa()) {
            // RA hanya boleh mengubah aktivitas kerja miliknya, tidak boleh mengubah hasil review
            $request->validate([
                'tanggal' => 'required|date',
                'status_hari' => 'required|string',
            ]);

            $log->tanggal = $request->tanggal;
            $log->status_hari = $request->status_hari;
            $log->sumber_sop = $request->sumber_sop;
            $log->jenis_aktivitas = $request->jenis_aktivitas;
            $log->area = $request->area;
            $log->output_referensi_wp = $request->output_referensi_wp;
            $log->status_penyelesaian = $request->status_hari === 'KERJA' ? $request->status_penyelesaian : null;
            $log->jumlah_item = (int)($request->jumlah_item ?? 0);
            $log->catatan = $request->catatan;
        } else {
            // Admin / Reviewer memiliki full access termasuk kolom Review
            $log->tanggal = $request->tanggal ?: $log->tanggal;
            $log->status_hari = $request->status_hari ?: $log->status_hari;
            $log->sumber_sop = $request->sumber_sop ?: $log->sumber_sop;
            $log->jenis_aktivitas = $request->jenis_aktivitas ?: $log->jenis_aktivitas;
            $log->area = $request->area ?: $log->area;
            $log->output_referensi_wp = $request->output_referensi_wp ?: $log->output_referensi_wp;
            $log->status_penyelesaian = $request->status_penyelesaian ?: $log->status_penyelesaian;
            $log->jumlah_item = isset($request->jumlah_item) ? (int)$request->jumlah_item : $log->jumlah_item;
            $log->catatan = $request->catatan;

            if ($request->filled('hasil_review')) {
                $log->hasil_review = $request->hasil_review;
                $log->reviewer = $request->reviewer ?: (auth()->user()->name ?? 'Reviewer SKAI');
                $log->tanggal_review = $request->tanggal_review ?: now()->format('Y-m-d');
            }
        }

        $log->save();

        return redirect()->route('pelaporan-bulanan.log-harian', ['periode' => Carbon::parse($log->tanggal)->format('Y-m')])
            ->with('success', "Log Harian {$log->log_id} berhasil diperbarui.");
    }

    public function batchReviewLogHarian(Request $request)
    {
        if ($this->isRa()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Reviewer yang berhak melakukan review.');
        }

        $request->validate([
            'log_ids' => 'required|array',
            'hasil_review' => 'required|string',
        ]);

        $reviewer = auth()->user()->name ?? 'Reviewer SKAI';
        $today = now()->format('Y-m-d');

        Sop08LogHarianRa::whereIn('id', $request->log_ids)
            ->update([
                'hasil_review' => $request->hasil_review,
                'reviewer' => $reviewer,
                'tanggal_review' => $today,
            ]);

        return redirect()->back()
            ->with('success', count($request->log_ids) . " log harian berhasil dinilai sebagai '{$request->hasil_review}' oleh {$reviewer}.");
    }

    public function pullLogHarian(Request $request)
    {
        $allowedUnits = $this->getAllowedUnitCodes();
        $isRa = $this->isRa();

        $raId = $request->get('ra_id');
        if ($isRa) {
            $user = auth()->user();
            $myMasterRa = Sop08MasterRa::where('user_id', $user->id)
                ->orWhere(function($q) use ($user) {
                    if ($user->unit_kerja) {
                        $q->where('kode_kc_penempatan', $user->unit_kerja);
                    }
                })->first();

            if (!$myMasterRa) {
                return redirect()->back()->with('error', 'Profil Master RA Anda belum terdaftar.');
            }
            $raId = $myMasterRa->ra_id;
        }

        if (!$raId) {
            return redirect()->back()->with('error', 'Silakan tentukan Resident Auditor.');
        }

        $periode = $request->get('periode', now()->format('Y-m'));
        $startDate = Carbon::parse($periode . '-01')->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::parse($periode . '-01')->endOfMonth()->format('Y-m-d');

        $result = $this->service->pullLogsFromWorkModules($raId, $startDate, $endDate);

        return redirect()->route('pelaporan-bulanan.log-harian', ['periode' => $periode])
            ->with('success', $result['message']);
    }

    // =========================================================================
    // FASE 4: 3. SUB-MENU KALENDER_KINERJA_RA (47 KOLOM: A s.d. AU)
    // =========================================================================
    public function kalenderKinerjaRa(Request $request)
    {
        $params = Sop08MasterParameter::getActive();
        $periode = $request->get('periode', Carbon::parse($params->periode_dashboard)->format('Y-m'));
        $periodeDate = Carbon::parse($periode . '-01')->format('Y-m-d');

        $allowedUnits = $this->getAllowedUnitCodes();
        $kodeUnit = $request->get('kode_unit');
        if ($kodeUnit) {
            $this->authorizeUnitAccess($kodeUnit);
        }

        $isRa = $this->isRa();
        $isAdmin = $this->isAdmin();

        $myRaId = null;
        if ($isRa) {
            $user = auth()->user();
            $myMasterRa = Sop08MasterRa::where('user_id', $user->id)
                ->orWhere(function($q) use ($user) {
                    if ($user->unit_kerja) {
                        $q->where('kode_kc_penempatan', $user->unit_kerja);
                    }
                })->first();
            $myRaId = $myMasterRa?->ra_id;
        }

        $raId = $request->get('ra_id', $myRaId);

        $kinerjaData = $this->service->getKalenderKinerjaRaData($periodeDate, $allowedUnits, $kodeUnit, $raId);

        $masterUnits = Sop08MasterUnit::where('is_active', true)->orderBy('kode_unit')->get();
        $masterRas = Sop08MasterRa::where('status', 'Aktif')->orderBy('kode_kc_penempatan')->get();

        return view('pelaporan_bulanan.kalender_kinerja', compact(
            'params',
            'periode',
            'kinerjaData',
            'masterUnits',
            'masterRas',
            'myRaId',
            'isRa',
            'isAdmin'
        ));
    }
}


