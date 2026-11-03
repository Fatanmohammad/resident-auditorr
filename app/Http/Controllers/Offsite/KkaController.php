<?php

namespace App\Http\Controllers\Offsite;

use App\Http\Controllers\Controller;
use App\Models\Offsite\KkaFinding;
use App\Models\Offsite\OffsiteKkaChecklistAnswer;
use App\Models\DailyRegister\DailyRegisterChecklist;
use App\Http\Requests\Offsite\UpdateKkaRaRequest;
use App\Http\Requests\Offsite\UpdateKkaAdminRequest;
use Illuminate\Http\Request;

class KkaController extends Controller
{
    /**
     * HANYA untuk menampilkan halaman UI (Blade)
     */
    public function index()
    {
        return view('offsite.kka.index');
    }

    /**
     * HANYA untuk mengambil data JSON (dipanggil oleh Axios/AJAX di Javascript)
     * Automatic Filter berdasarkan Wewenang Cabang
     */
    public function data(Request $request)
    {
        $user = auth()->user();
        $query = KkaFinding::query();

        // 1. Filter Keamanan Wilayah RA vs Admin
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            // Dapatkan unit_code dari cabang id yang diizinkan
            $unitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code');
            $query->whereIn('kode_unit', $unitCodes);
        } else {
            if ($request->filled('cabang_id')) {
                $unitCodes = \App\Models\Unit::where('cabang_id', $request->cabang_id)->pluck('unit_code');
                $query->whereIn('kode_unit', $unitCodes);
            }
            if ($request->filled('kode_unit')) {
                $query->where('kode_unit', $request->kode_unit);
            }
        }

        // CLONE untuk menghitung tab counts sebelum filter spesifik UI
        $baseQueryForTabs = clone $query;

        // 2. Filter Spesifik dari UI (Sheet KKA, Risk, Tanggal, Periode)
        if ($request->filled('source_sheet')) {
            $query->where('source_sheet', $request->source_sheet);
        }
        if ($request->filled('risk_awal')) {
            $query->where('risk_awal', $request->risk_awal);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_data', $request->tanggal);
        }
        if ($request->filled('periode')) {
            $rawPeriode = $request->periode;
            $periodeFormatted = date('Y-m-01', strtotime($rawPeriode . (strlen($rawPeriode) === 7 ? '-01' : '')));
            $query->where('periode', $periodeFormatted);
            $baseQueryForTabs->where('periode', $periodeFormatted);
        }

        // Hitung Summary Global (sebelum pagination)
        $summaryQuery = clone $query;
        $totalException = $summaryQuery->count();
        $totalNominal = $summaryQuery->sum('nominal_terkait');
        $highRiskCount = (clone $summaryQuery)->where(function($q) {
            $q->where('risk_final', 'High')
              ->orWhere('risk_final', 'Critical')
              ->orWhere(function($sub) {
                  $sub->whereNull('risk_final')->where('risk_awal', 'High');
              });
        })->count();
        $selesaiReviewCount = (clone $summaryQuery)->where('status_review', 'Approved')->count();

        $findings = $query->latest('tanggal_data')->paginate(15);

        $tabCounts = [
            'KKA_Teller_Kas' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Teller_Kas')->count(),
            'KKA_Kredit' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Kredit')->count(),
            'KKA_Biaya_Beban' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Biaya_Beban')->count(),
            'KKA_Biaya_Internal' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Biaya_Internal')->count(),
            'KKA_Pengaduan' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Pengaduan')->count(),
            'KKA_Transaksi_Umum' => (clone $baseQueryForTabs)->where('source_sheet', 'KKA_Transaksi_Umum')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $findings,
            'summary' => [
                'total_exception' => $totalException,
                'total_nominal'   => (float) $totalNominal,
                'high_risk'       => $highRiskCount,
                'selesai_review'  => $selesaiReviewCount,
                'tab_counts'      => $tabCounts
            ]
        ]);
    }

    /**
     * Pemetaan nama sheet KKA Offsite ke master checklist
     */
    protected function mapChecklistSheet(string $sheet): string
    {
        $map = [
            'KKA_Teller_Kas'      => 'KKA_Teller_Kas',
            'KKA_Kredit'          => 'KKA_Kredit',
            'KKA_Biaya_Beban'     => 'KKA_Biaya_Beban',
            'KKA_Biaya_Internal'  => 'KKA_Biaya_Beban',
            'KKA_Pengaduan'       => 'KKA_Pengaduan',
            'KKA_Transaksi_Umum'  => 'KKA_DPK_Layanan',
            'KKA_Transfer_KU'     => 'KKA_DPK_Layanan',
            'KKA_DPK_Layanan'     => 'KKA_DPK_Layanan',
            'KKA_Kartu_ATM'       => 'KKA_Kartu_ATM',
        ];
        return $map[$sheet] ?? 'KKA_Teller_Kas';
    }

    /**
     * Hitung otomatis jumlah kejadian exception/temuan sebelumnya pada unit & sheet yang sama (termasuk saat ini)
     */
    protected function calculateRecurringCount(KkaFinding $finding): int
    {
        $unit = (string)$finding->kode_unit;
        $cleanUnit = str_replace('BS-', '', $unit);
        $targetUnits = array_values(array_unique(array_filter([$unit, $cleanUnit, 'BS-' . $cleanUnit])));

        $historyCount = KkaFinding::whereIn('kode_unit', $targetUnits)
            ->where('source_sheet', $finding->source_sheet)
            ->where('id', '!=', $finding->id)
            ->where(function ($q) {
                $q->whereHas('checklistAnswers', function ($qa) {
                    $qa->where('jawaban', 'Tidak');
                })
                ->orWhereIn('risk_final', ['Moderate', 'Moderate to High', 'High', 'Critical'])
                ->orWhere(function ($sub) {
                    $sub->whereNull('risk_final')
                        ->whereIn('risk_awal', ['Moderate', 'High', 'Critical']);
                });
            })
            ->count();

        return $historyCount + 1;
    }

    /**
     * Mengambil detail KKA lengkap dengan checklist answers & hitungan kejadian berulang
     */
    public function getReviewData($id)
    {
        $finding = KkaFinding::with(['checklistAnswers'])->findOrFail($id);

        // Kunci Otomatis Tanggal Ditemukan / Diuji: Saat RA pertama kali membuka KKA
        if (empty($finding->tanggal_ditemukan)) {
            $finding->tanggal_ditemukan = now()->format('Y-m-d');
            $finding->save();
        }

        // Hitung kejadian berulang di unit & source_sheet yang sama
        $recurringCount = $this->calculateRecurringCount($finding);

        $targetSheet = $this->mapChecklistSheet($finding->source_sheet);
        $checklists = DailyRegisterChecklist::where('source_sheet', $targetSheet)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status'          => 'success',
            'finding'         => $finding,
            'recurring_count' => $recurringCount,
            'checklists'      => $checklists,
        ]);
    }

    /**
     * Update Inputan RA dengan Checklist & Matriks Risiko 5x5 Otomatis
     */
    public function updateRa(UpdateKkaRaRequest $request, $id)
    {
        $user = auth()->user();
        $finding = KkaFinding::findOrFail($id);

        // Jika user adalah RA, validasi berdasarkan izin kode_unit cabang yang diakses
        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses') 
                ? $user->cabangIdYangDapatDiakses() 
                : [$user->cabang_id];

            $allowedUnitCodes = \App\Models\Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();

            if (!in_array($finding->kode_unit, $allowedUnitCodes)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah data cabang ini.'
                ], 403);
            }
        }

        // Ambil checklist answers
        $rawChecklist = $request->input('checklist_answers');
        if (is_string($rawChecklist)) {
            $rawChecklist = json_decode($rawChecklist, true) ?: [];
        }

        $tidakCount = 0;
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                if (($ans['jawaban'] ?? 'Ya') === 'Tidak') {
                    $tidakCount++;
                }
            }
        }

        // Validasi kemunculan kondisional
        if ($tidakCount >= 1) {
            if (empty($request->input('jenis_exception_ra')) || empty($request->input('hasil_uji')) || empty($request->input('klarifikasi_unit'))) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi Gagal: Terdapat ' . $tidakCount . ' item checklist bernilai Tidak. Jenis Exception, Hasil Pengujian Lapangan, dan Klarifikasi Unit Kerja wajib diisi!'
                ], 422);
            }
            $jenisExceptionRa = $request->input('jenis_exception_ra');
            $hasilUji = $request->input('hasil_uji');
            $klarifikasiUnit = $request->input('klarifikasi_unit');
        } else {
            // Jika semua bernilai Ya, kosongkan kembali field exception
            $jenisExceptionRa = null;
            $hasilUji = null;
            $klarifikasiUnit = null;
        }

        // Kunci Tanggal Ditemukan / Diuji: Otomatis hari itu jika kosong, lalu dikunci
        $tanggalDitemukan = $finding->tanggal_ditemukan ?: now()->format('Y-m-d');

        // File Bukti PDF
        $fileBuktiPath = $finding->file_bukti;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $fileBuktiPath = $file->storeAs('offsite_kka_evidence', time() . '_' . $file->getClientOriginalName(), 'public');
        }

        // Otomasi Penuh & Penguncian Skor Risiko (Matriks 5x5)
        $criticalTrigger = $request->input('critical_trigger', 'Tidak') === 'Ya' ? 'Ya' : 'Tidak';
        $nominal = (float)($finding->nominal_terkait ?? 0);

        if ($tidakCount === 0 && $criticalTrigger === 'Tidak') {
            $skorDampak = 1;
            $skorKemungkinan = 1;
            $skorFinal = 1;
            $riskFinal = 'Low';
            $perluOnsite = 'Tidak Perlu';
        } else {
            // Skor Dampak (1-5)
            if ($criticalTrigger === 'Ya' || $nominal >= 500000000) {
                $skorDampak = 5;
            } else {
                $hasNominal = ($finding->nominal_terkait !== null && $nominal > 0);

                $chkScore = 1;
                if ($tidakCount >= 5) {
                    $chkScore = 4;
                } elseif ($tidakCount >= 3) {
                    $chkScore = 3;
                } elseif ($tidakCount >= 2) {
                    $chkScore = 2;
                } elseif ($tidakCount === 1) {
                    $chkScore = $hasNominal ? 1 : 2;
                }

                $nomScore = 0;
                if ($hasNominal) {
                    if ($nominal >= 100000000) {
                        $nomScore = 4;
                    } elseif ($nominal >= 50000000) {
                        $nomScore = 3;
                    } elseif ($nominal >= 10000000) {
                        $nomScore = 2;
                    } else {
                        $nomScore = 1;
                    }
                }

                $skorDampak = max($chkScore, $nomScore);
                if (!$hasNominal && $tidakCount >= 1 && $skorDampak < 2) {
                    $skorDampak = 2;
                }
                $skorDampak = min(max($skorDampak, 1), 5);
            }

            // Skor Kemungkinan (1-5) Berdasarkan Kejadian Berulang di Database
            $kejadianKe = $this->calculateRecurringCount($finding);

            if ($kejadianKe >= 7) {
                $skorKemungkinan = 5;
            } elseif ($kejadianKe >= 5) {
                $skorKemungkinan = 4;
            } elseif ($kejadianKe >= 3) {
                $skorKemungkinan = 3;
            } elseif ($kejadianKe == 2) {
                $skorKemungkinan = 2;
            } else {
                $skorKemungkinan = ($tidakCount >= 2) ? 2 : 1;
            }

            // Skor & Risk Final
            $skorFinal = $skorDampak * $skorKemungkinan;

            if ($criticalTrigger === 'Ya') {
                $riskFinal = 'Critical';
                if ($skorFinal < 16) $skorFinal = 16;
            } elseif ($skorFinal >= 16) {
                $riskFinal = 'Critical';
            } elseif ($skorFinal >= 10) {
                $riskFinal = 'High';
            } elseif ($skorFinal >= 8) {
                $riskFinal = 'Moderate to High';
            } elseif ($skorFinal >= 5) {
                $riskFinal = 'Moderate';
            } else {
                $riskFinal = 'Low';
            }

            // Perlu Onsite Inspection?
            if (in_array($riskFinal, ['Moderate to High', 'High', 'Critical']) || $skorFinal >= 8 || $criticalTrigger === 'Ya') {
                $perluOnsite = 'Perlu';
            } else {
                $perluOnsite = 'Tidak Perlu';
            }
        }

        // Update finding
        $finding->bukti_referensi = $request->input('bukti_referensi');
        $finding->critical_trigger = $criticalTrigger;
        $finding->jenis_exception_ra = $jenisExceptionRa;
        $finding->hasil_uji = $hasilUji;
        $finding->klarifikasi_unit = $klarifikasiUnit;
        $finding->skor_dampak = $skorDampak;
        $finding->skor_kemungkinan = $skorKemungkinan;
        $finding->skor_final = $skorFinal;
        $finding->risk_final = $riskFinal;
        $finding->risk_awal = $riskFinal;
        $finding->perlu_onsite = $perluOnsite;
        $finding->tanggal_ditemukan = $tanggalDitemukan;
        $finding->file_bukti = $fileBuktiPath;
        $finding->save();

        // Simpan jawaban checklist
        if (is_array($rawChecklist)) {
            foreach ($rawChecklist as $ans) {
                $chkId = $ans['checklist_id'] ?? null;
                if ($chkId) {
                    OffsiteKkaChecklistAnswer::updateOrCreate(
                        [
                            'kka_finding_id' => $finding->id,
                            'checklist_id'   => $chkId,
                        ],
                        [
                            'jawaban'    => ($ans['jawaban'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya',
                            'keterangan' => $ans['keterangan'] ?? null,
                        ]
                    );
                }
            }
        }

        // Sinkronisasi otomatis ke modul Risk Trigger (SOP 06)
        try {
            app(\App\Services\RiskTrigger\RiskTriggerService::class)->syncAll($user, $finding->kode_unit);
        } catch (\Throwable $e) {
            \Log::warning("Auto-sync Risk Trigger gagal di updateRa Offsite: " . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Review RA dan checklist berhasil disimpan.',
            'data'    => $finding->load('checklistAnswers')
        ]);
    }

    /**
     * Update Review Admin
     */
    public function updateAdmin(UpdateKkaAdminRequest $request, $id)
    {
        try {
            $finding = KkaFinding::findOrFail($id);
            $finding->update([
                'status_klarifikasi' => $request->status_klarifikasi,
                'perluasan_sampel'   => $request->perluasan_sampel,
                'keputusan_onsite'   => $request->keputusan_onsite,
                'keputusan_eskalasi' => $request->keputusan_eskalasi,
                'status_review'      => $request->status_review,
                'catatan_reviewer'   => $request->catatan_reviewer,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Review KKA berhasil diperbarui oleh Admin.',
                'data'    => $finding
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error Database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi Temuan KKA oleh RA / Cabang & Sinkronisasi ke RawMetric (SOP 01)
     */
    public function konfirmasiCabang(Request $request, $id)
    {
        $request->validate([
            'status_konfirmasi'     => 'required|in:Sesuai,Tidak Sesuai',
            'komitmen_penyelesaian' => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|string',
            'target_penyelesaian'   => 'required_if:status_konfirmasi,Tidak Sesuai|nullable|date',
        ]);

        $finding = KkaFinding::findOrFail($id);

        $finding->update([
            'status_konfirmasi'     => $request->status_konfirmasi,
            'komitmen_penyelesaian' => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->komitmen_penyelesaian : null,
            'target_penyelesaian'   => $request->status_konfirmasi === 'Tidak Sesuai' ? $request->target_penyelesaian : null,
            'tanggal_konfirmasi'    => now(),
        ]);

        // Sinkronisasi otomatis ke RawMetric berdasarkan unit dan periode temuan
        if ($finding->kode_unit && $finding->periode) {
            $this->syncRawMetricDeviations($finding->kode_unit, $finding->periode);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Konfirmasi KKA berhasil disimpan dan disinkronkan ke jadwal audit.',
            'data'    => $finding
        ]);
    }

    /**
     * Helper privat untuk kalkulasi ulang deviasi offsite ke tabel RawMetric
     */
    private function syncRawMetricDeviations(string $kodeUnit, string $periode)
    {
        $unit = \App\Models\Unit::where('unit_code', $kodeUnit)->first();
        if (!$unit) return;

        $yearPeriod = date('Y', strtotime($periode));

        $rawMetric = \App\Models\RawMetric::where('unit_id', $unit->id)
            ->where('period', 'LIKE', "$yearPeriod%")
            ->first();

        if ($rawMetric) {
            $totalTidakSesuai = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->count();

            $totalSignifikan = KkaFinding::where('kode_unit', $kodeUnit)
                ->where('periode', 'LIKE', "$yearPeriod%")
                ->where('status_konfirmasi', 'Tidak Sesuai')
                ->where('risk_awal', 'High')
                ->count();

            $rawMetric->update([
                'offsite_deviation'             => $totalTidakSesuai,
                'offsite_deviation_significant' => $totalSignifikan,
            ]);
        }
    }
}
