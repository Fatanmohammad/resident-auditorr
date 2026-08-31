<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Cabang;
use App\Models\WpOffsite;
use App\Models\KkaTellerKas;
use App\Models\KkaKredit;
use App\Models\KkaBiayaBeban;
use App\Models\KkaBiayaInternal;
use App\Models\KkaPengaduan;
use App\Models\KkaTransaksiUmum;
use App\Models\KkaTransferKu;
use App\Services\OffsiteAdminService;
use App\Services\OffsiteGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOffsiteController extends Controller
{
    private array $kkaModels = [
        'teller-kas'      => KkaTellerKas::class,
        'kredit'          => KkaKredit::class,
        'biaya-beban'     => KkaBiayaBeban::class,
        'biaya-internal'  => KkaBiayaInternal::class,
        'pengaduan'       => KkaPengaduan::class,
        'transaksi-umum'  => KkaTransaksiUmum::class,
        'transfer-ku'     => KkaTransferKu::class,
    ];

    private array $kkaLabels = [
        'teller-kas'      => 'Teller/Kas',
        'kredit'          => 'Kredit',
        'biaya-beban'     => 'Biaya/Beban',
        'biaya-internal'  => 'Biaya/Internal',
        'pengaduan'       => 'Pengaduan',
        'transaksi-umum'  => 'Transaksi Umum',
        'transfer-ku'     => 'Transfer/KU',
    ];

    public function __construct(
        private OffsiteAdminService $service,
        private OffsiteGenerationService $generation,
    ) {}

    /**
     * Helper resolver class model berdasarkan string area
     */
    private function getModelByArea(string $area)
    {
        return match (strtolower($area)) {
            'cbs', 'teller', 'teller_kas', 'teller-kas' => KkaTellerKas::class,
            'kredit'                                     => KkaKredit::class,
            'biaya', 'biaya_beban', 'biaya-beban'       => KkaBiayaBeban::class,
            'biaya_internal', 'biaya-internal'          => KkaBiayaInternal::class,
            'pengaduan'                                  => KkaPengaduan::class,
            'transaksi_umum', 'transaksi-umum'          => KkaTransaksiUmum::class,
            'transfer_ku', 'transfer-ku'                => KkaTransferKu::class,
            default                                      => throw new \InvalidArgumentException("Area KKA '{$area}' tidak valid."),
        };
    }

    /**
     * Jalankan ulang pipeline Dump -> Staging -> Register -> KKA untuk WP aktif unit ini.
     */
    public function uploadRegister(Request $request, Unit $unit)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('m'));

        $wp = WpOffsite::where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return back()->with('error', 'Belum ada WP Offsite untuk unit dan periode ini. Buat WP-nya dulu di menu Offsite Review.');
        }

        $hasil = $this->generation->generate($wp);

        $pesan = $hasil['total_diproses'] > 0
            ? "Data berhasil digenerate ulang. {$hasil['total_diproses']} baris diproses, {$hasil['total_masuk_kka']} masuk KKA."
            : 'Belum ada data Dump untuk unit & periode ini.';

        return back()->with('success', $pesan);
    }

    /**
     * Mengubah status WP (Draft/Aktif/Final) untuk unit & periode terkait.
     */
    public function updateStatus(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'status_wp' => 'required|in:Draft,Aktif,Final',
            'tahun'     => 'nullable|integer',
            'bulan'     => 'nullable|integer',
        ]);

        $tahun = $validated['tahun'] ?? date('Y');
        $bulan = $validated['bulan'] ?? date('m');

        $wp = WpOffsite::where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return back()->with('error', 'WP Offsite untuk unit dan periode ini tidak ditemukan.');
        }

        $wp->update(['status_wp' => $validated['status_wp']]);

        return back()->with('success', 'Status WP berhasil diperbarui.');
    }

    /**
     * Halaman index admin offsite — daftar cabang dengan statistik unit
     */
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan', date('m'));

        $cabangs = $this->service->getCabangStats($tahun, $bulan);

        return view('admin-offsite.index', compact('cabangs', 'tahun', 'bulan'));
    }

    /**
     * Halaman detail cabang — daftar unit dalam cabang
     */
    public function cabangDetail($cabangId)
    {
        $tahun = request('tahun', date('Y'));
        $bulan = request('bulan', date('m'));
        $cabang = Cabang::findOrFail($cabangId);

        $unitData = $this->service->getUnitsByBranch($tahun, $bulan, $cabang->id);
        
        $status = request('status');
        if ($status) {
            $unitData = $unitData->filter(function ($item) use ($status) {
                if ($status === 'perlu_review') {
                    return in_array($item['status_review'], ['Perlu Review', 'Dalam Review']);
                }
                if ($status === 'tidak_perlu') {
                    return $item['status_review'] === 'Tidak Perlu Review';
                }
                if ($status === 'selesai') {
                    return $item['status_review'] === 'Selesai Review';
                }
                return true;
            });
        }

        return view('admin-offsite.cabang-detail', compact('cabang', 'unitData', 'tahun', 'bulan', 'status'));
    }

    /**
     * Halaman detail unit — lihat summary & register
     */
    public function unitDetail(Unit $unit)
    {
        $tahun = (int) request('tahun', date('Y'));
        $bulan = (int) request('bulan', date('m'));

        $wp = WpOffsite::with(['stagingOffsite', 'raPelaksana', 'reviewerBagianRa'])
            ->where('unit_id', $unit->id)
            ->whereYear('periode_mulai', $tahun)
            ->whereMonth('periode_mulai', $bulan)
            ->first();

        if (!$wp) {
            return view('admin-offsite.unit-detail', [
                'unit'      => $unit, 
                'wp'        => null, 
                'tahun'     => $tahun, 
                'bulan'     => $bulan,
                'ringkasan' => [
                    'populasi'    => 0,
                    'sampel_low'  => 0,
                    'kka_final'   => 0,
                    'exception'   => 0,
                    'klarifikasi' => 0,
                    'eskalasi'    => 0,
                ],
                'rows'      => collect()
            ]);
        }

        $stagingData = $wp->stagingOffsite->sortBy('tanggal_data');

        $rows = $stagingData->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal_data)->format('Y-m-d');
        });

        $ringkasan = [
            'populasi'    => $stagingData->count(),
            'sampel_low'  => $stagingData->where('sampel_low', 1)->count(),
            'kka_final'   => $stagingData->where('masuk_kka_final', 1)->count(),
            'exception'   => $stagingData->where('exception_awal', 1)->count(),
            'klarifikasi' => $stagingData->where('perlu_klarifikasi', 1)->count(),
            'eskalasi'    => $stagingData->where('perlu_eskalasi', 1)->count(),
        ];

        return view('admin-offsite.unit-detail', compact('unit', 'wp', 'rows', 'ringkasan', 'tahun', 'bulan'));
    }

    /**
     * Daftar KKA per area untuk 1 WP (Dilengkapi Fallback ke Staging jika KKA belum ter-generate)
     */
    public function kkaIndex(WpOffsite $wp, string $area)
    {
        if (!isset($this->kkaLabels[$area]) || !isset($this->kkaModels[$area])) {
            abort(404, 'Area KKA tidak dikenali.');
        }

        $areaLabel = $this->kkaLabels[$area];
        $modelClass = $this->kkaModels[$area];

        $rows = $modelClass::where('wp_offsite_id', $wp->id)
            ->orderBy('tanggal_data')
            ->get();

        if ($rows->isEmpty()) {
            $rows = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
                ->where(function ($q) use ($areaLabel, $area) {
                    $q->where('area_review', $areaLabel)
                      ->orWhere('area_review', $area)
                      ->orWhere('area_review', str_replace('-', '_', $area));
                })
                ->orderBy('tanggal_data')
                ->get();
        }

        return view('admin-offsite.kka-index', [
            'wp'        => $wp,
            'area'      => $area,
            'areaLabel' => $areaLabel,
            'rows'      => $rows,
        ]);
    }

    /**
     * Detail 1 baris KKA (Membaca data Staging secara dinamis tanpa membuat record dummy)
     */
    public function kkaShow(WpOffsite $wp, string $area, int $kkaId)
    {
        if (!isset($this->kkaModels[$area])) {
            abort(404, 'Area KKA tidak dikenali.');
        }

        $areaLabel = $this->kkaLabels[$area];
        $modelClass = $this->kkaModels[$area];
        $instance = new $modelClass;
        $primaryKey = $instance->getKeyName();

        $kka = $modelClass::where('wp_offsite_id', $wp->id)
            ->where(function($q) use ($primaryKey, $kkaId) {
                $q->where($primaryKey, $kkaId);
                if ($primaryKey !== 'kka_id') {
                    $q->orWhere('kka_id', $kkaId);
                }
                $q->orWhere('staging_id', $kkaId);
            })
            ->first();

        if (!$kka) {
            $staging = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)
                ->where('id', $kkaId)
                ->firstOrFail();

            $kka = (object) [
                'id'                   => $staging->id,
                'staging_id'           => $staging->id,
                'wp_offsite_id'        => $wp->id,
                'object_id'            => $staging->object_id ?? 'STG-' . $staging->id,
                'area_review'          => $staging->area_review ?? $areaLabel,
                'kode_unit'            => $staging->kode_unit ?? $wp->kode_unit,
                'nama_unit'            => $staging->nama_unit ?? $wp->nama_unit,
                'tanggal_data'         => $staging->tanggal_data,
                'user_maker'           => $staging->user_maker ?? $staging->user_id ?? 'Maker',
                'nominal'              => $staging->nominal ?? $staging->amount ?? 0,
                'risk_awal'            => $staging->risk_level ?? $staging->risk_awal ?? 'Low',
                'exception_awal'       => $staging->exception_awal ?? false,
                'jenis_exception_awal' => $staging->jenis_exception_awal ?? '-',
                'sampel_low'           => $staging->sampel_low ?? false,
                'deskripsi_narasi'     => $staging->deskripsi_narasi ?? $staging->deskripsi ?? $staging->uraian ?? '-',
                'status_review'        => $staging->status_review ?? 'Belum Review',
                'catatan_reviewer'     => $staging->catatan_reviewer ?? null,
                'bukti_referensi'      => $staging->bukti_referensi ?? '-',
                'hasil_uji_ra'         => $staging->hasil_uji_ra ?? $staging->hasil_uji ?? '-',
                'dampak'               => $staging->dampak ?? '-',
                'kemungkinan'          => $staging->kemungkinan ?? '-',
                'skor_risiko'          => $staging->skor_risiko ?? '-',
                'kategori_final'       => $staging->kategori_final ?? $staging->kategori_risiko_final ?? '-',
                'simpulan_ra'          => $staging->simpulan_ra ?? '-',
                'updated_at'           => $staging->updated_at,
            ];
        }

        return view('admin-offsite.kka-show', [
            'wp'        => $wp,
            'area'      => $area,
            'areaLabel' => $areaLabel,
            'kka'       => $kka,
        ]);
    }

    /**
     * Update Catatan Reviewer
     */
    public function kkaUpdateReviewerNote(Request $request, $wpOrArea, $areaOrKkaId = null, $kkaId = null)
    {
        if ($wpOrArea instanceof WpOffsite) {
            $wp = $wpOrArea;
            $area = $areaOrKkaId;
            $id = $kkaId;

            $request->validate(['catatan_reviewer' => 'nullable|string']);
            $modelClass = $this->getModelByArea($area);
            $kka = $modelClass::where('wp_offsite_id', $wp->id)->where(function($q) use ($id) {
                $q->where('id', $id)->orWhere('staging_id', $id);
            })->first();

            $dataBaru = ['catatan_reviewer' => $request->catatan_reviewer];
            
            if ($kka) {
                $this->catatPerubahan($kka, $dataBaru, $area, 'REVIEW');
                $kka->update(array_merge($dataBaru, ['reviewer_id' => auth()->id()]));
            } else {
                \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)->where('id', $id)->update($dataBaru);
            }

            return back()->with('success', 'Catatan Reviewer berhasil disimpan.');
        }

        $area = $wpOrArea;
        $id = $areaOrKkaId;

        $request->validate(['reviewer_note' => 'nullable|string']);

        try {
            $modelClass = $this->getModelByArea($area);
            $kka = $modelClass::findOrFail($id);
            $dataBaru = ['catatan_reviewer' => $request->reviewer_note];

            $this->catatPerubahan($kka, $dataBaru, $area, 'REVIEW');
            $kka->update($dataBaru);

            return back()->with('success', 'Catatan reviewer berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui catatan reviewer: ' . $e->getMessage());
        }
    }

    /**
     * Update Hasil Review KKA dari Form Reviewer (Dukungan Penuh Input Admin)
     */
    public function kkaUpdate(Request $request, $wpOrArea, $areaOrKkaId = null, $kkaId = null)
    {
        if ($wpOrArea instanceof WpOffsite) {
            $wp = $wpOrArea;
            $area = $areaOrKkaId;
            $id = $kkaId;

            $validated = $request->validate([
                'status_review'        => 'required|string',
                'catatan_reviewer'     => 'nullable|string|max:2000',
                'bukti_referensi'      => 'nullable|string',
                'hasil_uji_ra'         => 'nullable|string',
                'jenis_exception_ra'   => 'nullable|string',
                'dampak'               => 'nullable|string',
                'kemungkinan'          => 'nullable|string',
                'skor_risiko'          => 'nullable|string',
                'kategori_risiko_final'=> 'nullable|string',
                'critical_trigger'     => 'nullable|string',
                'klarifikasi_unit'     => 'nullable|string',
                'status_klarifikasi'   => 'nullable|string',
                'perlu_onsite'         => 'nullable|boolean',
                'simpulan_ra'          => 'nullable|string',
            ]);

            $reviewerId = auth()->id();
            
            // Siapkan data payload
            $payload = array_filter([
                'status_review'         => $validated['status_review'],
                'catatan_reviewer'      => $validated['catatan_reviewer'] ?? null,
                'bukti_referensi'       => $validated['bukti_referensi'] ?? null,
                'hasil_uji_ra'          => $validated['hasil_uji_ra'] ?? null,
                'jenis_exception_ra'    => $validated['jenis_exception_ra'] ?? null,
                'dampak'                => $validated['dampak'] ?? null,
                'kemungkinan'           => $validated['kemungkinan'] ?? null,
                'skor_risiko'           => $validated['skor_risiko'] ?? null,
                'kategori_risiko_final' => $validated['kategori_risiko_final'] ?? null,
                'critical_trigger'      => $validated['critical_trigger'] ?? null,
                'klarifikasi_unit'      => $validated['klarifikasi_unit'] ?? null,
                'status_klarifikasi'    => $validated['status_klarifikasi'] ?? null,
                'perlu_onsite'          => $request->has('perlu_onsite') ? (bool) $request->perlu_onsite : null,
                'simpulan_ra'           => $validated['simpulan_ra'] ?? null,
                'reviewer_id'           => $reviewerId,
                'updated_at'            => now(),
            ], fn($val) => !is_null($val));

            // Update di Staging jika ada
            $staging = \App\Models\StagingOffsite::where('wp_offsite_id', $wp->id)->where('id', $id)->first();
            if ($staging) {
                $staging->update($payload);
            }

            // Update atau Create di Tabel KKA
            $modelClass = $this->getModelByArea($area);
            $instance = new $modelClass;
            $primaryKey = $instance->getKeyName();

            $kka = $modelClass::where('wp_offsite_id', $wp->id)
                ->where(function($q) use ($primaryKey, $id, $staging) {
                    $q->where($primaryKey, $id)->orWhere('kka_id', $id);
                    if ($staging) {
                        $q->orWhere('staging_id', $staging->id);
                    }
                })->first();

            if ($kka) {
                $this->catatPerubahan($kka, $payload, $area, 'ADMIN_UPDATE');
                $kka->update($payload);
            } else if ($staging) {
                // Buat record baru di tabel KKA jika belum dibuat sebelumnya
                $newKkaData = array_merge([
                    'wp_offsite_id' => $wp->id,
                    'staging_id'    => $staging->id,
                    'kode_unit'     => $staging->kode_unit ?? $wp->kode_unit,
                    'nama_unit'     => $staging->nama_unit ?? $wp->nama_unit,
                    'tanggal_data'  => $staging->tanggal_data,
                    'user_maker'    => $staging->user_maker ?? 'Maker',
                    'nominal'       => $staging->nominal ?? 0,
                ], $payload);

                $kka = $modelClass::create($newKkaData);
                $this->catatPerubahan($kka, $payload, $area, 'ADMIN_CREATE');
            }

            return back()->with('updated_success', 'Data Hasil Review & KKA berhasil diperbarui oleh Admin!');
        }

        $area = $wpOrArea;
        $id = $areaOrKkaId;

        $request->validate([
            'status_review'    => 'required|string',
            'catatan_reviewer' => 'nullable|string',
        ]);

        try {
            $modelClass = $this->getModelByArea($area);
            $kka = $modelClass::findOrFail($id);

            $dataBaru = [
                'status_review'    => $request->status_review,
                'catatan_reviewer' => $request->catatan_reviewer,
            ];

            $this->catatPerubahan($kka, $dataBaru, $area, 'REVIEW');
            $kka->update($dataBaru);

            return back()->with('success', 'Status review KKA berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status KKA: ' . $e->getMessage());
        }
    }

    /**
     * Catat perubahan oleh Admin ke kka_activity_logs
     */
    private function catatPerubahan($kka, array $dataBaru, string $area, string $action = 'UPDATE'): void
    {
        $user = auth()->user();
        $perubahan = [];

        foreach ($dataBaru as $field => $nilaiBaru) {
            $nilaiLama = is_object($kka) ? ($kka->{$field} ?? null) : null;

            if ((string) $nilaiLama !== (string) $nilaiBaru) {
                $perubahan[] = "{$field}: '" . ($nilaiLama ?? '-') . "' -> '" . ($nilaiBaru ?? '-') . "'";
            }
        }

        if (empty($perubahan)) {
            return;
        }

        $statusReview = $dataBaru['status_review'] ?? (is_object($kka) ? ($kka->status_review ?? 'Belum') : 'Belum');

        DB::table('kka_activity_logs')->insert([
            'user_id'             => $user?->id ?? 1,
            'user_name'           => $user?->name ?? 'Admin',
            'kode_unit'           => is_object($kka) ? ($kka->kode_unit ?? '001') : '001',
            'case_id'             => is_object($kka) && method_exists($kka, 'getKey') ? (string) $kka->getKey() : (string) ($kka->id ?? 1),
            'sheet_name'          => strtoupper($area),
            'action'              => $action,
            'deskripsi_perubahan' => implode(' | ', $perubahan),
            'status_review'       => in_array($statusReview, ['Belum', 'Selesai', 'Pending', 'Dalam Review', 'Perlu Klarifikasi', 'Selesai Review']) ? $statusReview : 'Pending',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }
}