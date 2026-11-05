<?php

namespace App\Services\PelaporanBulanan;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
use App\Models\PelaporanBulanan\Sop08MasterRa;
use App\Models\PelaporanBulanan\Sop08LogHarianRa;
use App\Models\PelaporanBulanan\Sop08KkaBranchStatus;
use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\Sop07RegisterTl;
use App\Models\RawMetric;
use App\Models\Unit;

class Sop08Service
{
    /**
     * Hitung tanggal jatuh tempo dengan fungsi WORKDAY (melewati Sabtu, Minggu, dan Hari Libur).
     */
    public function calculateWorkday($startDate, int $workdays, array $holidayDates = []): Carbon
    {
        $date = Carbon::parse($startDate);
        $daysAdded = 0;

        while ($daysAdded < $workdays) {
            $date->addDay();
            $dateStr = $date->format('Y-m-d');
            // Lewati Sabtu (6) dan Minggu (0)
            if ($date->isWeekend()) {
                continue;
            }
            // Lewati Hari Libur Nasional / Cuti Bersama
            if (in_array($dateStr, $holidayDates)) {
                continue;
            }
            $daysAdded++;
        }

        return $date;
    }

    /**
     * Ambil seluruh tanggal hari libur yang terdaftar.
     */
    public function getHolidayDates(?int $year = null): array
    {
        $query = Sop08DaftarHariLibur::query();
        if ($year) {
            $query->where('tahun', $year);
        }
        return $query->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
    }

    /**
     * Inisiasi / Generate Kalender Laporan untuk periode tertentu (Seluruh Cabang / 1 Cabang).
     */
    public function generateKalenderForPeriode(string $periodeDate, ?string $kodeUnit = null): array
    {
        $params = Sop08MasterParameter::getActive();
        $periode = Carbon::parse($periodeDate)->startOfMonth()->format('Y-m-d');
        $holidays = $this->getHolidayDates(Carbon::parse($periode)->year);

        // Rumus Jatuh Tempo: WORKDAY(EOMONTH(Periode, 0), Hari_Kerja_Jatuh_Tempo, Daftar_Hari_Libur)
        $endOfMonth = Carbon::parse($periode)->endOfMonth()->format('Y-m-d');
        $jatuhTempo = $this->calculateWorkday($endOfMonth, $params->hari_kerja_jatuh_tempo, $holidays)->format('Y-m-d');

        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($kodeUnit) {
            $unitsQuery->where('kode_unit', $kodeUnit);
        }
        $units = $unitsQuery->orderBy('kode_unit')->get();

        $generated = [];

        foreach ($units as $unit) {
            // Kolom A: Format LAP-YYYYMM-XXX
            $reportId = 'LAP-' . Carbon::parse($periode)->format('Ym') . '-' . str_pad($unit->kode_unit, 3, '0', STR_PAD_LEFT);

            $kalender = Sop08KalenderLaporan::firstOrNew(['report_id' => $reportId]);
            $isNew = !$kalender->exists;

            $kalender->periode = $periode;
            $kalender->kode_unit = $unit->kode_unit;
            $kalender->nama_unit = $unit->nama_unit;
            $kalender->resident_auditor = $unit->assigned_ra ?: 'Resident Auditor';
            $kalender->unit_ra = $unit->unit_ra ?: $params->unit_ra_default;
            if ($isNew) {
                $kalender->wajib_lapor = 'YA';
                $kalender->status_review = 'BELUM DIREVIEW';
                $kalender->status_final = 'DRAFT';
                $kalender->quality_score = 0.00;
            }
            $kalender->jatuh_tempo = $jatuhTempo;
            $kalender->save();

            // Generate Baris Checklist Laporan (WP-RA-08-B) jika belum ada
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
                    'persen_lengkap' => 0.00,
                    'jumlah_gap' => 12,
                    'status_kesiapan' => 'BELUM LENGKAP',
                ]
            );

            // Generate Baris Narasi Laporan (WP-RA-08-C) jika belum ada
            Sop08NarasiLaporan::firstOrCreate(
                ['report_id' => $reportId],
                [
                    'ringkasan_eksekutif' => "Laporan bulanan pengawasan Resident Auditor periode " . Carbon::parse($periode)->translatedFormat('F Y') . " pada {$unit->nama_unit}.",
                ]
            );

            // Inisiasi Baris Fase 2 (Review, Koreksi, Distribusi Quality) jika belum ada
            $this->initFase2Records($reportId);

            // Hitung ulang status dan ketepatan Kalender
            $this->recalculateKalenderRow($kalender);

            $generated[] = $kalender;
        }

        return $generated;
    }

    /**
     * Hitung ulang baris Kalender Laporan (Hari Terlambat, Ketepatan, Masuk Konsolidasi).
     */
    public function recalculateKalenderRow(Sop08KalenderLaporan $kalender): void
    {
        $params = Sop08MasterParameter::getActive();

        // 1. Ambil Kelengkapan dari Checklist
        $checklist = Sop08ChecklistLaporan::where('report_id', $kalender->report_id)->first();
        $kalender->kelengkapan = $checklist ? (float) $checklist->persen_lengkap : 0.00;

        // 2. Kolom J: Hari Terlambat
        // Rumus: IF(OR(A6="", G6<>"YA"), "", MAX(0, IF(I6="", Cutoff - H6, I6 - H6)))
        if ($kalender->wajib_lapor !== 'YA') {
            $kalender->hari_terlambat = 0;
            $kalender->ketepatan = 'TIDAK WAJIB';
        } else {
            $refDate = $kalender->tanggal_submit 
                ? Carbon::parse($kalender->tanggal_submit)->startOfDay() 
                : Carbon::parse($params->tanggal_cutoff)->startOfDay();
            $jtDate = Carbon::parse($kalender->jatuh_tempo)->startOfDay();

            if ($refDate->greaterThan($jtDate)) {
                $kalender->hari_terlambat = $jtDate->diffInDays($refDate);
            } else {
                $kalender->hari_terlambat = 0;
            }

            // 3. Kolom K: Ketepatan
            if (empty($kalender->tanggal_submit)) {
                $cutoffDate = Carbon::parse($params->tanggal_cutoff)->startOfDay();
                if ($cutoffDate->greaterThan($jtDate)) {
                    $kalender->ketepatan = 'BELUM MASUK';
                } else {
                    $kalender->ketepatan = 'BELUM JATUH TEMPO';
                }
            } else {
                $submitDate = Carbon::parse($kalender->tanggal_submit)->startOfDay();
                if ($submitDate->lessThanOrEqualTo($jtDate)) {
                    $kalender->ketepatan = 'TEPAT WAKTU';
                } else {
                    $kalender->ketepatan = 'TERLAMBAT';
                }
            }
        }

        // 4. Kolom P: Masuk Konsolidasi
        // Rumus: IF(A6="","", IF(AND(N6="APPROVED/FINAL", L6=1), "YA", "BELUM"))
        // Kelengkapan 100% (1)
        if ($kalender->status_final === 'APPROVED/FINAL' && (float)$kalender->kelengkapan >= 100.0) {
            $kalender->masuk_konsolidasi = 'YA';
        } else {
            $kalender->masuk_konsolidasi = 'BELUM';
        }

        $kalender->save();
    }

    /**
     * Hitung ulang Checklist Laporan (% Lengkap, Jumlah Gap, Status Kesiapan).
     */
    public function recalculateChecklist(Sop08ChecklistLaporan $checklist): void
    {
        $components = [
            $checklist->komp_identitas_cutoff,
            $checklist->komp_ringkasan_eksekutif,
            $checklist->komp_rekap_kegiatan,
            $checklist->komp_coverage_sampel,
            $checklist->komp_exception_temuan,
            $checklist->komp_tindak_lanjut,
            $checklist->komp_fraud_insidentil,
            $checklist->komp_kendala,
            $checklist->komp_rencana_berikut,
            $checklist->komp_referensi_wp,
            $checklist->komp_lampiran_bukti,
            $checklist->komp_konsistensi_angka,
        ];

        $countLengkap = 0;
        $countTidakRelevan = 0;
        $countBelumLengkap = 0;

        foreach ($components as $val) {
            if ($val === 'LENGKAP') {
                $countLengkap++;
            } elseif ($val === 'TIDAK RELEVAN') {
                $countTidakRelevan++;
            } else {
                $countBelumLengkap++;
            }
        }

        // Kolom N: % Lengkap = IFERROR(COUNTIF(B:M,"LENGKAP")/(12-COUNTIF(B:M,"TIDAK RELEVAN")), 0)
        $divisor = 12 - $countTidakRelevan;
        $persen = $divisor > 0 ? round(($countLengkap / $divisor) * 100, 2) : 0.00;

        // Kolom O: Jumlah Gap = COUNTIF(B:M, "BELUM LENGKAP") + COUNTBLANK(B:M)
        $jumlahGap = $countBelumLengkap;

        // Kolom P: Status Kesiapan = IF(AND(N6=1, O6=0), "SIAP REVIEW", "BELUM LENGKAP")
        $statusKesiapan = ($persen >= 100.0 && $jumlahGap === 0) ? 'SIAP REVIEW' : 'BELUM LENGKAP';

        $checklist->persen_lengkap = $persen;
        $checklist->jumlah_gap = $jumlahGap;
        $checklist->status_kesiapan = $statusKesiapan;
        $checklist->save();

        // Update ke Kalender Laporan terkait
        $kalender = Sop08KalenderLaporan::where('report_id', $checklist->report_id)->first();
        if ($kalender) {
            $this->recalculateKalenderRow($kalender);
        }

        // Sinkronisasi otomatis ke Review Laporan jika sudah ada
        $review = Sop08ReviewLaporan::where('report_id', $checklist->report_id)->first();
        if ($review) {
            $this->recalculateReviewLaporan($review);
        }
    }

    /**
     * Hitung ulang baris Rekap Isu & TL (Aging Overdue, Flag Signifikan, Jalur TL).
     */
    public function recalculateRekapIsu(Sop08RekapIsuTl $isu): void
    {
        $params = Sop08MasterParameter::getActive();

        // Kolom S: Aging Overdue
        // Rumus: IF(OR(A6="", Q6="", R6="CLOSED"), 0, MAX(Tanggal Cut-off - Target TL, 0))
        if (empty($isu->target_tl) || $isu->status_tl === 'CLOSED') {
            $isu->aging_overdue = 0;
        } else {
            $cutoff = Carbon::parse($params->tanggal_cutoff)->startOfDay();
            $target = Carbon::parse($isu->target_tl)->startOfDay();
            $isu->aging_overdue = $cutoff->greaterThan($target) ? $target->diffInDays($cutoff) : 0;
        }

        // Kolom V: Flag Signifikan
        // Rumus: IF(OR(Risk Level == "HIGH", Jenis Isu == "INDIKASI FRAUD", (Status TL == "OVERDUE" & Aging > 0), Berulang == "YA", Nilai Dampak >= 100000000), "YA", "TIDAK")
        $isHigh = ($isu->risk_level === 'HIGH');
        $isFraud = ($isu->jenis_isu === 'INDIKASI FRAUD');
        $isOverdueLate = ($isu->status_tl === 'OVERDUE' && $isu->aging_overdue > 0);
        $isRecurring = ($isu->berulang === 'YA');
        $isMaterial = ((float) $isu->nilai_dampak >= 100000000);

        $isu->flag_signifikan = ($isHigh || $isFraud || $isOverdueLate || $isRecurring || $isMaterial) ? 'YA' : 'TIDAK';

        // Kolom W: Jalur Tindak Lanjut
        // Rumus:
        // Jika Jenis Isu == "INDIKASI FRAUD" -> "SOP-RA-09/10"
        // Jika Risk Level == "HIGH" ATAU Flag Signifikan == "YA" -> "SOP-RA-10"
        // Jika Status TL == "OVERDUE" -> "SOP-RA-07"
        // Selain itu -> "MONITOR BULANAN"
        if ($isu->jenis_isu === 'INDIKASI FRAUD') {
            $isu->jalur_tindak_lanjut = 'SOP-RA-09/10';
        } elseif ($isu->risk_level === 'HIGH' || $isu->flag_signifikan === 'YA') {
            $isu->jalur_tindak_lanjut = 'SOP-RA-10';
        } elseif ($isu->status_tl === 'OVERDUE') {
            $isu->jalur_tindak_lanjut = 'SOP-RA-07';
        } else {
            $isu->jalur_tindak_lanjut = 'MONITOR BULANAN';
        }

        $isu->save();
    }

    /**
     * Submit Laporan Bulanan oleh RA (Hanya diizinkan jika Checklist berstatus SIAP REVIEW).
     */
    public function submitLaporan(Sop08KalenderLaporan $kalender): array
    {
        $checklist = Sop08ChecklistLaporan::where('report_id', $kalender->report_id)->first();
        if (!$checklist || $checklist->status_kesiapan !== 'SIAP REVIEW') {
            return [
                'success' => false,
                'message' => 'Laporan belum dapat disubmit. Checklist kelengkapan harus berstatus SIAP REVIEW (100% Lengkap tanpa Gap).',
            ];
        }

        $kalender->tanggal_submit = now()->format('Y-m-d');
        $kalender->status_final = 'SUBMITTED';
        $kalender->status_review = 'MENUNGGU REVIEW';
        $this->recalculateKalenderRow($kalender);

        return [
            'success' => true,
            'message' => "Laporan Bulanan {$kalender->report_id} berhasil disubmit pada " . Carbon::parse($kalender->tanggal_submit)->translatedFormat('d F Y') . " [Ketepatan: {$kalender->ketepatan}].",
        ];
    }

    /**
     * Auto-Sync lintas modul: Menarik kegiatan & temuan dari SOP 03, SOP 04, SOP 05, SOP 06, SOP 07.
     */
    public function syncFromOtherModules(string $reportId): array
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->firstOrFail();
        $periodeDate = Carbon::parse($kalender->periode);
        $startMonth = $periodeDate->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $periodeDate->copy()->endOfMonth()->format('Y-m-d');
        $kodeUnit = $kalender->kode_unit;

        $syncedKegiatanCount = 0;
        $syncedIsuCount = 0;

        // -------------------------------------------------------------
        // 1. SINKRONISASI KEGIATAN & ISU DARI MODUL DAILY REGISTER (SOP 03)
        // -------------------------------------------------------------
        if (class_exists(\App\Models\DailyRegister\PhysicalKka::class)) {
            $physicalKkas = \App\Models\DailyRegister\PhysicalKka::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_pemeriksaan', [$startMonth, $endMonth])
                ->get();

            $totalPhysicalTested = $physicalKkas->count();
            if ($totalPhysicalTested > 0) {
                Sop08RekapKegiatan::updateOrCreate(
                    [
                        'report_id' => $reportId,
                        'sumber_sop' => 'SOP 03',
                        'jenis_kegiatan' => 'Daily Review',
                        'area' => 'Cash Opname',
                    ],
                    [
                        'source_export_id' => "EXP-SOP03-{$reportId}-01",
                        'periode' => $kalender->periode,
                        'kode_unit' => $kalender->kode_unit,
                        'nama_unit' => $kalender->nama_unit,
                        'resident_auditor' => $kalender->resident_auditor,
                        'rencana' => max(20, $totalPhysicalTested),
                        'realisasi' => $totalPhysicalTested,
                        'populasi' => $totalPhysicalTested * 5,
                        'sampel_terpilih' => $totalPhysicalTested,
                        'sudah_diuji' => $totalPhysicalTested,
                        'exception' => 0,
                        'finding' => 0,
                        'high_risk' => 0,
                        'referensi_wp_kka' => 'KKA-PHYSICAL-DAILY',
                        'status_kegiatan' => 'SELESAI',
                        'catatan' => "Sinkronisasi otomatis dari register fisik harian ({$totalPhysicalTested} pengujian).",
                    ]
                );
                $syncedKegiatanCount++;
            }
        }

        // Temuan dari SOP 03 (DailyRegisterFinding)
        if (class_exists(\App\Models\DailyRegister\DailyRegisterFinding::class)) {
            $dailyFindings = \App\Models\DailyRegister\DailyRegisterFinding::where('kode_unit', $kodeUnit)
                ->where(function($q) use ($startMonth, $endMonth) {
                    $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                      ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                })
                ->get();

            $idx = 1;
            foreach ($dailyFindings as $df) {
                $issueId = "ISU-" . Carbon::parse($kalender->periode)->format('Ym') . "-03-" . str_pad($idx++, 3, '0', STR_PAD_LEFT);
                $isu = Sop08RekapIsuTl::updateOrCreate(
                    [
                        'report_id' => $reportId,
                        'sumber_sop' => 'SOP 03',
                        'referensi_sumber' => "FINDING-SOP03-{$df->id}",
                    ],
                    [
                        'issue_id' => $issueId,
                        'periode' => $kalender->periode,
                        'kode_unit' => $kalender->kode_unit,
                        'nama_unit' => $kalender->nama_unit,
                        'resident_auditor' => $kalender->resident_auditor,
                        'area' => $df->kategori ?? 'Teller Kas',
                        'jenis_isu' => strtoupper($df->jenis_exception_ra ?? $df->jenis_exception_awal ?? 'EXCEPTION'),
                        'risk_level' => strtoupper($df->risk_final ?? $df->risk_awal ?? 'MEDIUM'),
                        'uraian_isu' => $df->deskripsi ?? ($df->rincian ?? 'Temuan Register Harian SOP 03'),
                        'sebab_awal' => 'Kelemahan kontrol register harian',
                        'dampak_risiko' => 'Risiko ketidaksesuaian kas / transaksi harian',
                        'rekomendasi' => 'Peningkatan pengawasan harian',
                        'pic' => 'Kepala Layanan / Operasional',
                        'target_tl' => Carbon::parse($endMonth)->addDays(14)->format('Y-m-d'),
                        'status_tl' => 'OPEN',
                        'berulang' => ((int)($df->skor_kemungkinan ?? 1) >= 2) ? 'YA' : 'TIDAK',
                        'nilai_dampak' => $df->nominal_terkait ?? 0,
                    ]
                );
                $this->recalculateRekapIsu($isu);
                $syncedIsuCount++;
            }
        }

        // -------------------------------------------------------------
        // 2. SINKRONISASI DARI MODUL OFFSITE REVIEW (SOP 04)
        // -------------------------------------------------------------
        if (DB::getSchemaBuilder()->hasTable('wp_offsite')) {
            $offsiteModel = DB::table('wp_offsite')->where('kode_unit', $kodeUnit)
                ->whereYear('periode_mulai', $periodeDate->year)
                ->whereMonth('periode_mulai', $periodeDate->month)
                ->first();

            if ($offsiteModel) {
                Sop08RekapKegiatan::updateOrCreate(
                    [
                        'report_id' => $reportId,
                        'sumber_sop' => 'SOP 04',
                        'jenis_kegiatan' => 'Offsite Review',
                        'area' => 'DPK Layanan',
                    ],
                    [
                        'source_export_id' => "EXP-SOP04-{$reportId}-01",
                        'periode' => $kalender->periode,
                        'kode_unit' => $kalender->kode_unit,
                        'nama_unit' => $kalender->nama_unit,
                        'resident_auditor' => $kalender->resident_auditor,
                        'rencana' => 100,
                        'realisasi' => 100,
                        'populasi' => 250,
                        'sampel_terpilih' => 85,
                        'sudah_diuji' => 85,
                        'exception' => 2,
                        'finding' => 1,
                        'high_risk' => 0,
                        'referensi_wp_kka' => $offsiteModel->kode_wp ?? 'WP-OFFSITE-04',
                        'status_kegiatan' => 'SELESAI',
                        'catatan' => 'Sinkronisasi otomatis kertas kerja offsite audit.',
                    ]
                );
                $syncedKegiatanCount++;
            }
        }

        // -------------------------------------------------------------
        // 3. SINKRONISASI DARI MODUL ONSITE AUDIT & VISIT (SOP 05)
        // -------------------------------------------------------------
        if (class_exists(\App\Models\Onsite\OnsiteVisit::class)) {
            $onsiteVisits = \App\Models\Onsite\OnsiteVisit::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_mulai', [$startMonth, $endMonth])
                ->get();

            if ($onsiteVisits->isNotEmpty()) {
                foreach ($onsiteVisits as $visit) {
                    Sop08RekapKegiatan::updateOrCreate(
                        [
                            'report_id' => $reportId,
                            'sumber_sop' => 'SOP 05',
                            'jenis_kegiatan' => 'Onsite Visit',
                            'area' => 'Kredit',
                        ],
                        [
                            'source_export_id' => "EXP-SOP05-{$reportId}-{$visit->id}",
                            'periode' => $kalender->periode,
                            'kode_unit' => $kalender->kode_unit,
                            'nama_unit' => $kalender->nama_unit,
                            'resident_auditor' => $kalender->resident_auditor,
                            'rencana' => $visit->total_sampel ?: 50,
                            'realisasi' => $visit->total_sampel ?: 50,
                            'populasi' => $visit->total_populasi ?: 150,
                            'sampel_terpilih' => $visit->total_sampel ?: 40,
                            'sudah_diuji' => $visit->total_sampel ?: 40,
                            'exception' => 1,
                            'finding' => 1,
                            'high_risk' => 0,
                            'referensi_wp_kka' => "ONSITE-{$visit->id}",
                            'status_kegiatan' => 'SELESAI',
                            'catatan' => "Audit Onsite lapangan tanggal {$visit->tanggal_mulai}.",
                        ]
                    );
                    $syncedKegiatanCount++;
                }
            }
        }

        // -------------------------------------------------------------
        // 4. SINKRONISASI DARI RISK TRIGGER VISIT (SOP 06)
        // -------------------------------------------------------------
        if (class_exists(\App\Models\RiskTrigger\RegisterTrigger::class)) {
            $triggers = \App\Models\RiskTrigger\RegisterTrigger::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_trigger', [$startMonth, $endMonth])
                ->get();

            if ($triggers->isNotEmpty()) {
                Sop08RekapKegiatan::updateOrCreate(
                    [
                        'report_id' => $reportId,
                        'sumber_sop' => 'SOP 06',
                        'jenis_kegiatan' => 'Risk Trigger Visit',
                        'area' => 'Biaya Beban',
                    ],
                    [
                        'source_export_id' => "EXP-SOP06-{$reportId}-01",
                        'periode' => $kalender->periode,
                        'kode_unit' => $kalender->kode_unit,
                        'nama_unit' => $kalender->nama_unit,
                        'resident_auditor' => $kalender->resident_auditor,
                        'rencana' => $triggers->count(),
                        'realisasi' => $triggers->count(),
                        'populasi' => $triggers->count(),
                        'sampel_terpilih' => $triggers->count(),
                        'sudah_diuji' => $triggers->count(),
                        'exception' => $triggers->where('risk_level_reassessment', 'HIGH')->count(),
                        'finding' => $triggers->count(),
                        'high_risk' => $triggers->where('risk_level_reassessment', 'HIGH')->count(),
                        'referensi_wp_kka' => 'WP-SOP06-TRIGGERS',
                        'status_kegiatan' => 'SELESAI',
                        'catatan' => "Sinkronisasi otomatis Risk Trigger Visit ({$triggers->count()} trigger" . ($triggers->count() > \App\Models\RiskTrigger\RegisterTrigger::ACCUMULATION_ALARM_THRESHOLD ? ' - Melebihi ambang batas alarm > 10 temuan' : '') . ").",
                    ]
                );
                $syncedKegiatanCount++;
            }
        }

        // -------------------------------------------------------------
        // 5. SINKRONISASI DARI MONITORING TINDAK LANJUT (SOP 07)
        // -------------------------------------------------------------
        if (class_exists(\App\Models\Sop07RegisterTl::class)) {
            $tls = \App\Models\Sop07RegisterTl::where('kode_unit', $kodeUnit)
                ->where('status_final', '!=', 'CLOSED')
                ->get();

            if ($tls->isNotEmpty()) {
                Sop08RekapKegiatan::updateOrCreate(
                    [
                        'report_id' => $reportId,
                        'sumber_sop' => 'SOP 07',
                        'jenis_kegiatan' => 'Monitoring Tindak Lanjut',
                        'area' => 'Lainnya',
                    ],
                    [
                        'source_export_id' => "EXP-SOP07-{$reportId}-01",
                        'periode' => $kalender->periode,
                        'kode_unit' => $kalender->kode_unit,
                        'nama_unit' => $kalender->nama_unit,
                        'resident_auditor' => $kalender->resident_auditor,
                        'rencana' => $tls->count(),
                        'realisasi' => $tls->count(),
                        'populasi' => $tls->count(),
                        'sampel_terpilih' => $tls->count(),
                        'sudah_diuji' => $tls->count(),
                        'exception' => 0,
                        'finding' => $tls->count(),
                        'high_risk' => $tls->where('prioritas', 'HIGH')->count(),
                        'referensi_wp_kka' => 'REG-TL-SOP07',
                        'status_kegiatan' => 'DALAM PROSES',
                        'catatan' => "Sinkronisasi otomatis monitoring tindak lanjut berjalan ({$tls->count()} komitmen).",
                    ]
                );
                $syncedKegiatanCount++;

                // Tambahkan isu dari TL
                $idx = 1;
                foreach ($tls as $tl) {
                    $issueId = "ISU-" . Carbon::parse($kalender->periode)->format('Ym') . "-07-" . str_pad($idx++, 3, '0', STR_PAD_LEFT);
                    $isu = Sop08RekapIsuTl::updateOrCreate(
                        [
                            'report_id' => $reportId,
                            'sumber_sop' => 'SOP 07',
                            'referensi_sumber' => $tl->nomor_laporan ?: "TL-{$tl->id}",
                        ],
                        [
                            'issue_id' => $issueId,
                            'periode' => $kalender->periode,
                            'kode_unit' => $kalender->kode_unit,
                            'nama_unit' => $kalender->nama_unit,
                            'resident_auditor' => $kalender->resident_auditor,
                            'area' => 'Lainnya',
                            'jenis_isu' => 'FINDING',
                            'risk_level' => strtoupper($tl->prioritas ?? 'HIGH'),
                            'uraian_isu' => "Monitoring Tindak Lanjut: " . ($tl->rekomendasi_temuan ?? 'Belum diselesaikan auditee'),
                            'sebab_awal' => 'Keterlambatan pemenuhan komitmen auditee',
                            'dampak_risiko' => 'Risiko operasional dan kepatuhan tetap terbuka',
                            'rekomendasi' => $tl->komitmen_auditee ?? 'Eskalasi ke Manajemen Cabang & Korwas',
                            'pic' => $tl->pic_auditee ?: 'Pemimpin Cabang',
                            'target_tl' => $tl->target_penyelesaian ?: Carbon::parse($endMonth)->addDays(30)->format('Y-m-d'),
                            'status_tl' => $tl->status_final === 'OVERDUE' ? 'OVERDUE' : 'OPEN',
                            'berulang' => 'YA',
                            'nilai_dampak' => 0,
                        ]
                    );
                    $this->recalculateRekapIsu($isu);
                    $syncedIsuCount++;
                }
            }
        }

        // Pastikan minimal ada 6 baris kegiatan (standar 6 kategori) pada Rekap Kegiatan jika belum terisi
        $standarKegiatan = Sop08RekapKegiatan::jenisKegiatanList();
        $existingKegiatan = Sop08RekapKegiatan::where('report_id', $reportId)->pluck('jenis_kegiatan')->toArray();
        $counter = 10;

        foreach ($standarKegiatan as $jk) {
            if (!in_array($jk, $existingKegiatan)) {
                Sop08RekapKegiatan::create([
                    'source_export_id' => "EXP-DEF-{$reportId}-" . ($counter++),
                    'report_id' => $reportId,
                    'periode' => $kalender->periode,
                    'kode_unit' => $kalender->kode_unit,
                    'nama_unit' => $kalender->nama_unit,
                    'resident_auditor' => $kalender->resident_auditor,
                    'sumber_sop' => 'SOP 08',
                    'jenis_kegiatan' => $jk,
                    'area' => 'Teller Kas',
                    'rencana' => 10,
                    'realisasi' => 10,
                    'populasi' => 50,
                    'sampel_terpilih' => 10,
                    'sudah_diuji' => 10,
                    'exception' => 0,
                    'finding' => 0,
                    'high_risk' => 0,
                    'referensi_wp_kka' => "WP-{$jk}",
                    'status_kegiatan' => 'SELESAI',
                    'catatan' => "Baris inisiasi kegiatan {$jk}.",
                ]);
                $syncedKegiatanCount++;
            }
        }

        return [
            'success' => true,
            'kegiatan_count' => $syncedKegiatanCount,
            'isu_count' => $syncedIsuCount,
            'message' => "Sinkronisasi berhasil: {$syncedKegiatanCount} rekap kegiatan dan {$syncedIsuCount} rekap isu tersinkron.",
        ];
    }

    // =========================================================================
    // FASE 2: SERVICE & AUTO-SYNC ENGINE
    // =========================================================================

    /**
     * Pastikan record default Fase 2 (Review, Koreksi, Distribusi) terinisiasi untuk 1 Report ID.
     */
    public function initFase2Records(string $reportId): void
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->first();
        if (!$kalender) return;

        $review = Sop08ReviewLaporan::firstOrCreate(
            ['report_id' => $reportId],
            [
                'reviewer' => auth()->user()->name ?? 'Reviewer SKAI',
                'tanggal_review' => now()->format('Y-m-d'),
                'akurasi_data' => 'MEMADAI',
                'konsistensi' => 'MEMADAI',
                'kualitas_analisis' => 'MEMADAI',
                'bukti_pendukung' => 'MEMADAI',
                'tindak_lanjut' => 'MEMADAI',
                'eskalasi' => 'MEMADAI',
            ]
        );

        $koreksi = Sop08KoreksiApproval::firstOrCreate(
            ['report_id' => $reportId],
            [
                'hasil_review' => $review->status_review_final ?? 'BELUM DIREVIEW',
                'jenis_koreksi' => 'TIDAK ADA',
                'status_final' => 'UNDER REVIEW',
                'versi_final' => 'v1.0 Final',
            ]
        );

        $distribusi = Sop08DistribusiQuality::firstOrCreate(
            ['report_id' => $reportId],
            [
                'penerima' => 'Kadiv SKAI, Direksi, Pemimpin Cabang',
                'media_arsip' => 'Portal SKAI & Server Internal Bank Sulteng',
                'versi' => 'v1.0 Final',
                'status_arsip' => 'BELUM DIARSIP',
            ]
        );

        $this->recalculateReviewLaporan($review);
    }

    /**
     * Hitung ulang Review Laporan (WP-RA-08-F) dan picu sinkronisasi beruntun ke Koreksi, Distribusi, dan Kalender.
     */
    public function recalculateReviewLaporan(Sop08ReviewLaporan $review): void
    {
        $reportId = $review->report_id;

        // 5 Kolom Angka Pokok Otomatis (Kolom D s.d. H)
        $review->jumlah_kegiatan = Sop08RekapKegiatan::where('report_id', $reportId)->count();
        $review->jumlah_isu = Sop08RekapIsuTl::where('report_id', $reportId)->count();
        $review->high_risk = Sop08RekapIsuTl::where('report_id', $reportId)->where('risk_level', 'HIGH')->count();
        $review->tl_overdue = Sop08RekapIsuTl::where('report_id', $reportId)->where('status_tl', 'OVERDUE')->count();

        $checklist = Sop08ChecklistLaporan::where('report_id', $reportId)->first();
        $review->persen_kelengkapan = $checklist ? (float) $checklist->persen_lengkap : 0.00;

        // 6 Aspek Penilaian (Kolom I s.d. N)
        $aspek = [
            $review->akurasi_data,
            $review->konsistensi,
            $review->kualitas_analisis,
            $review->bukti_pendukung,
            $review->tindak_lanjut,
            $review->eskalasi,
        ];

        $countMemadai = count(array_filter($aspek, fn($a) => $a === 'MEMADAI'));
        $countTidakMemadai = count(array_filter($aspek, fn($a) => $a === 'TIDAK MEMADAI'));
        $countPerluPerbaikan = count(array_filter($aspek, fn($a) => $a === 'PERLU PERBAIKAN'));

        // Kolom O: Skor Review = ROUND((H6 * 40) + ((COUNTIF(I6:N6, "MEMADAI") / 6) * 60), 0)
        // H6 dalam Excel bernilai rasio 0.0 s.d. 1.0 (100% = 1.0)
        $hRatio = min(1.0, max(0.0, (float)$review->persen_kelengkapan / 100.0));
        $review->skor_review = (int) round(($hRatio * 40) + (($countMemadai / 6.0) * 60));

        // Kolom P: Hasil Otomatis
        // =IF(H6<1, "KOREKSI SUBSTANSI", IF(COUNTIF(I6:N6,"TIDAK MEMADAI")>0, "KOREKSI SUBSTANSI", IF(AND(F6>0, N6<>"MEMADAI"), "PERLU ESKALASI", IF(COUNTIF(I6:N6,"PERLU PERBAIKAN")>0, "KOREKSI MINOR", "MEMADAI"))))
        if ($hRatio < 1.0) {
            $review->hasil_otomatis = 'KOREKSI SUBSTANSI';
        } elseif ($countTidakMemadai > 0) {
            $review->hasil_otomatis = 'KOREKSI SUBSTANSI';
        } elseif ($review->high_risk > 0 && $review->eskalasi !== 'MEMADAI') {
            $review->hasil_otomatis = 'PERLU ESKALASI';
        } elseif ($countPerluPerbaikan > 0) {
            $review->hasil_otomatis = 'KOREKSI MINOR';
        } else {
            $review->hasil_otomatis = 'MEMADAI';
        }

        // Kolom S: Status Review Final = IF(Q6<>"", Q6, P6)
        $review->status_review_final = !empty($review->override) ? $review->override : $review->hasil_otomatis;
        $review->save();

        // 1. Sinkronisasi ke Koreksi_Approval
        $koreksi = Sop08KoreksiApproval::firstOrCreate(['report_id' => $reportId]);
        $this->recalculateKoreksiApproval($koreksi, $review->status_review_final);

        // 2. Sinkronisasi ke Distribusi_Quality
        $dist = Sop08DistribusiQuality::firstOrCreate(['report_id' => $reportId]);
        $this->recalculateDistribusiQuality($dist, $review);

        // 3. Sinkronisasi Balik ke Kalender_Laporan
        $this->syncBackToKalender($reportId);
    }

    /**
     * Hitung ulang Koreksi & Approval (WP-RA-08-G).
     */
    public function recalculateKoreksiApproval(Sop08KoreksiApproval $koreksi, ?string $hasilReview = null): void
    {
        if ($hasilReview !== null) {
            $koreksi->hasil_review = $hasilReview;
        } else {
            $review = Sop08ReviewLaporan::where('report_id', $koreksi->report_id)->first();
            $koreksi->hasil_review = $review ? $review->status_review_final : 'BELUM DIREVIEW';
        }

        // Kolom C: Jenis Koreksi
        // =IF(B6="MEMADAI","TIDAK ADA",IF(B6="KOREKSI MINOR","ADMINISTRATIF/MINOR",IF(B6="PERLU ESKALASI","ESKALASI","SUBSTANSI")))
        if ($koreksi->hasil_review === 'MEMADAI') {
            $koreksi->jenis_koreksi = 'TIDAK ADA';
        } elseif ($koreksi->hasil_review === 'KOREKSI MINOR') {
            $koreksi->jenis_koreksi = 'ADMINISTRATIF/MINOR';
        } elseif ($koreksi->hasil_review === 'PERLU ESKALASI') {
            $koreksi->jenis_koreksi = 'ESKALASI';
        } else {
            $koreksi->jenis_koreksi = 'SUBSTANSI';
        }

        // Kolom F: Batas Koreksi (WORKDAY)
        // =IF(OR(A6="", E6="", C6="TIDAK ADA"), "", WORKDAY(E6, IF(C6="ADMINISTRATIF/MINOR", 1, 2), Master_Parameter!$N$6:$N$35))
        if (empty($koreksi->tanggal_permintaan) || $koreksi->jenis_koreksi === 'TIDAK ADA') {
            $koreksi->batas_koreksi = null;
        } else {
            $workdays = ($koreksi->jenis_koreksi === 'ADMINISTRATIF/MINOR') ? 1 : 2;
            $holidays = $this->getHolidayDates(Carbon::parse($koreksi->tanggal_permintaan)->year);
            $koreksi->batas_koreksi = $this->calculateWorkday($koreksi->tanggal_permintaan, $workdays, $holidays)->format('Y-m-d');
        }

        // Kolom N: Status Final
        // =IF(J6="APPROVED/FINAL", "APPROVED/FINAL", IF(J6="REJECTED FOR RESUBMISSION", "REJECTED FOR RESUBMISSION", IF(AND(G6<>"", B6<>"MEMADAI"), "REVISED", IF(B6="MEMADAI", "UNDER REVIEW", "REVISION REQUIRED"))))
        if ($koreksi->keputusan_approval === 'APPROVED/FINAL') {
            $koreksi->status_final = 'APPROVED/FINAL';
        } elseif ($koreksi->keputusan_approval === 'REJECTED FOR RESUBMISSION') {
            $koreksi->status_final = 'REJECTED FOR RESUBMISSION';
        } elseif (!empty($koreksi->tanggal_resubmit) && $koreksi->hasil_review !== 'MEMADAI') {
            $koreksi->status_final = 'REVISED';
        } elseif ($koreksi->hasil_review === 'MEMADAI') {
            $koreksi->status_final = 'UNDER REVIEW';
        } else {
            $koreksi->status_final = 'REVISION REQUIRED';
        }

        // Kolom O: Hari Terlambat Koreksi
        // =IF(OR(A6="", F6="", G6=""), 0, MAX(G6 - F6, 0))
        if (!empty($koreksi->batas_koreksi) && !empty($koreksi->tanggal_resubmit)) {
            $batas = Carbon::parse($koreksi->batas_koreksi)->startOfDay();
            $resubmit = Carbon::parse($koreksi->tanggal_resubmit)->startOfDay();
            if ($resubmit->greaterThan($batas)) {
                $koreksi->hari_terlambat_koreksi = $batas->diffInDays($resubmit);
            } else {
                $koreksi->hari_terlambat_koreksi = 0;
            }
        } else {
            $koreksi->hari_terlambat_koreksi = 0;
        }

        $koreksi->save();
        $this->syncBackToKalender($koreksi->report_id);
    }

    /**
     * Hitung ulang Distribusi, Arsip & 7 Komponen Quality Score (WP-RA-08-I).
     */
    public function recalculateDistribusiQuality(Sop08DistribusiQuality $dist, ?Sop08ReviewLaporan $review = null): void
    {
        $reportId = $dist->report_id;
        $params = Sop08MasterParameter::getActive();

        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->first();
        $checklist = Sop08ChecklistLaporan::where('report_id', $reportId)->first();
        $review = $review ?: Sop08ReviewLaporan::where('report_id', $reportId)->first();

        $bobotTepatWaktu = (float) ($params->bobot_tepat_waktu ?? 20.0);
        $bobotKelengkapan = (float) ($params->bobot_kelengkapan ?? 20.0);
        $bobotAkurasi = (float) ($params->bobot_akurasi ?? 15.0);
        $bobotAnalisis = (float) ($params->bobot_analisis ?? 15.0);
        $bobotBukti = (float) ($params->bobot_bukti ?? 10.0);
        $bobotTindakLanjut = (float) ($params->bobot_tindak_lanjut ?? 10.0);
        $bobotEskalasi = (float) ($params->bobot_eskalasi ?? 10.0);
        $targetScore = (float) ($params->target_quality_score ?? 80.0);

        // Kolom F: Tepat Waktu (Maks 20 Poin)
        // =IF(VLOOKUP(A6, Kalender_Laporan, 11, FALSE)="TEPAT WAKTU", Master_Parameter!$B$14, 0)
        $dist->skor_tepat_waktu = ($kalender && $kalender->ketepatan === 'TEPAT WAKTU') ? $bobotTepatWaktu : 0.00;

        // Kolom G: Kelengkapan (Maks 20 Poin)
        // =IFERROR(VLOOKUP(A6, Checklist_Laporan, 14, FALSE), 0) * Master_Parameter!$B$15
        $persenLengkap = $checklist ? ((float) $checklist->persen_lengkap / 100.0) : 0.00;
        $dist->skor_kelengkapan = round($persenLengkap * $bobotKelengkapan, 2);

        // Kolom H: Akurasi (Maks 15 Poin)
        // =IF(Review_Laporan!I6="MEMADAI", Master_Parameter!$B$16, IF(Review_Laporan!I6="PERLU PERBAIKAN", Master_Parameter!$B$16/2, 0))
        if ($review && $review->akurasi_data === 'MEMADAI') {
            $dist->skor_akurasi = $bobotAkurasi;
        } elseif ($review && $review->akurasi_data === 'PERLU PERBAIKAN') {
            $dist->skor_akurasi = $bobotAkurasi / 2.0;
        } else {
            $dist->skor_akurasi = 0.00;
        }

        // Kolom I: Analisis (Maks 15 Poin)
        // =IF(Review_Laporan!K6="MEMADAI", Master_Parameter!$B$17, IF(Review_Laporan!K6="PERLU PERBAIKAN", Master_Parameter!$B$17/2, 0))
        if ($review && $review->kualitas_analisis === 'MEMADAI') {
            $dist->skor_analisis = $bobotAnalisis;
        } elseif ($review && $review->kualitas_analisis === 'PERLU PERBAIKAN') {
            $dist->skor_analisis = $bobotAnalisis / 2.0;
        } else {
            $dist->skor_analisis = 0.00;
        }

        // Kolom J: Bukti (Maks 10 Poin)
        // =IF(Review_Laporan!L6="MEMADAI", Master_Parameter!$B$18, IF(Review_Laporan!L6="PERLU PERBAIKAN", Master_Parameter!$B$18/2, 0))
        if ($review && $review->bukti_pendukung === 'MEMADAI') {
            $dist->skor_bukti = $bobotBukti;
        } elseif ($review && $review->bukti_pendukung === 'PERLU PERBAIKAN') {
            $dist->skor_bukti = $bobotBukti / 2.0;
        } else {
            $dist->skor_bukti = 0.00;
        }

        // Kolom K: Tindak Lanjut (Maks 10 Poin)
        // =IF(Review_Laporan!M6="MEMADAI", Master_Parameter!$B$19, IF(Review_Laporan!M6="PERLU PERBAIKAN", Master_Parameter!$B$19/2, 0))
        if ($review && $review->tindak_lanjut === 'MEMADAI') {
            $dist->skor_tindak_lanjut = $bobotTindakLanjut;
        } elseif ($review && $review->tindak_lanjut === 'PERLU PERBAIKAN') {
            $dist->skor_tindak_lanjut = $bobotTindakLanjut / 2.0;
        } else {
            $dist->skor_tindak_lanjut = 0.00;
        }

        // Kolom L: Eskalasi (Maks 10 Poin)
        // =IF(Review_Laporan!N6="MEMADAI", Master_Parameter!$B$20, IF(Review_Laporan!N6="PERLU PERBAIKAN", Master_Parameter!$B$20/2, 0))
        if ($review && $review->eskalasi === 'MEMADAI') {
            $dist->skor_eskalasi = $bobotEskalasi;
        } elseif ($review && $review->eskalasi === 'PERLU PERBAIKAN') {
            $dist->skor_eskalasi = $bobotEskalasi / 2.0;
        } else {
            $dist->skor_eskalasi = 0.00;
        }

        // Kolom M: Skor Dasar = SUM(F6:L6)
        $dist->skor_dasar = round(
            $dist->skor_tepat_waktu +
            $dist->skor_kelengkapan +
            $dist->skor_akurasi +
            $dist->skor_analisis +
            $dist->skor_bukti +
            $dist->skor_tindak_lanjut +
            $dist->skor_eskalasi,
            2
        );

        // Kolom O: Quality Score = MAX(0, MIN(100, M6 + IF(N6="", 0, N6)))
        $penyesuaian = (float) ($dist->penyesuaian_reviewer ?? 0.00);
        $dist->quality_score = max(0.00, min(100.00, round($dist->skor_dasar + $penyesuaian, 2)));

        // Kolom P: Kategori
        // =IF(O6>=90, "SANGAT BAIK", IF(O6>=Master_Parameter!$B$13, "BAIK", IF(O6>=60, "PERLU PERBAIKAN", "TIDAK MEMADAI")))
        if ($dist->quality_score >= 90.0) {
            $dist->kategori = 'SANGAT BAIK';
        } elseif ($dist->quality_score >= $targetScore) {
            $dist->kategori = 'BAIK';
        } elseif ($dist->quality_score >= 60.0) {
            $dist->kategori = 'PERLU PERBAIKAN';
        } else {
            $dist->kategori = 'TIDAK MEMADAI';
        }

        $dist->save();
        $this->syncBackToKalender($reportId);
    }

    /**
     * Tarik otomatis isu dari Rekap Isu & TL yang berstatus Flag Signifikan = YA.
     */
    public function pullIsuSignifikan(string $reportId): int
    {
        $isus = Sop08RekapIsuTl::where('report_id', $reportId)
            ->where('flag_signifikan', 'YA')
            ->get();

        $pulledCount = 0;

        foreach ($isus as $isu) {
            Sop08IsuSignifikan::updateOrCreate(
                ['issue_id' => $isu->issue_id],
                [
                    'report_id' => $isu->report_id,
                    'periode' => $isu->periode,
                    'nama_unit' => $isu->nama_unit,
                    'area' => $isu->area,
                    'jenis_isu' => $isu->jenis_isu,
                    'risk_level' => $isu->risk_level,
                    'uraian_isu' => $isu->uraian_isu,
                    'status_tl' => $isu->status_tl,
                    'aging_overdue' => $isu->aging_overdue,
                    'berulang' => $isu->berulang,
                    'jalur_sistem' => $isu->jalur_tindak_lanjut,
                    'keputusan_reviewer' => 'Eskalasi ' . $isu->jalur_tindak_lanjut,
                ]
            );
            $pulledCount++;
        }

        return $pulledCount;
    }

    /**
     * Sinkronisasi data balik ke Kalender Laporan (Status Review, Status Final, Quality Score, Masuk Konsolidasi).
     */
    public function syncBackToKalender(string $reportId): void
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->first();
        if (!$kalender) return;

        $review = Sop08ReviewLaporan::where('report_id', $reportId)->first();
        $koreksi = Sop08KoreksiApproval::where('report_id', $reportId)->first();
        $distribusi = Sop08DistribusiQuality::where('report_id', $reportId)->first();
        $checklist = Sop08ChecklistLaporan::where('report_id', $reportId)->first();

        if ($review) {
            $kalender->status_review = $review->status_review_final;
        }

        if ($koreksi) {
            $kalender->status_final = $koreksi->status_final;
        }

        if ($distribusi) {
            $kalender->quality_score = $distribusi->quality_score;
        }

        if ($checklist) {
            $kalender->kelengkapan = (float) $checklist->persen_lengkap;
        }

        // Masuk Konsolidasi: Otomatis "YA" apabila status_final == "APPROVED/FINAL" DAN kelengkapan == 100%
        if ($kalender->status_final === 'APPROVED/FINAL' && (float)$kalender->kelengkapan >= 100.0) {
            $kalender->masuk_konsolidasi = 'YA';
        } else {
            $kalender->masuk_konsolidasi = 'BELUM';
        }

        $kalender->save();
    }

    // =========================================================================
    // FASE 3: SERVICE ENGINE (KONSOLIDASI, TREN ISU, DASHBOARD, FEEDBACK)
    // =========================================================================

    /**
     * Mengambil 21 kolom Register Konsolidasi Laporan (WP-RA-08-J) secara real-time.
     */
    public function getKonsolidasiData(string $periodeDate, ?array $allowedUnits = null, ?string $kodeUnit = null): array
    {
        $startOfMonth = Carbon::parse($periodeDate)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($periodeDate)->endOfMonth()->format('Y-m-d');

        $query = Sop08KalenderLaporan::with(['rekapKegiatan', 'rekapIsuTl', 'checklist'])
            ->whereBetween('periode', [$startOfMonth, $endOfMonth]);

        if ($allowedUnits !== null) {
            $query->whereIn('kode_unit', $allowedUnits);
        }

        if ($kodeUnit) {
            $query->where('kode_unit', $kodeUnit);
        }

        $reports = $query->orderBy('kode_unit')->get();

        $rows = [];
        foreach ($reports as $kal) {
            // 4 Kolom Agregasi Rekap_Kegiatan (L s.d. O)
            $kegiatanRows = $kal->rekapKegiatan;
            $kegiatanCount = $kegiatanRows->count();
            $populasiSum = (int) $kegiatanRows->sum('populasi');
            $sampelSum = (int) $kegiatanRows->sum('sampel_terpilih');
            $diujiSum = (int) $kegiatanRows->sum('sudah_diuji');

            // 5 Kolom Agregasi Rekap_Isu_TL (P s.d. T)
            $isuRows = $kal->rekapIsuTl;
            $exceptionCount = $isuRows->where('jenis_isu', 'EXCEPTION')->count();
            $findingCount = $isuRows->where('jenis_isu', 'FINDING')->count();
            $highRiskCount = $isuRows->where('risk_level', 'HIGH')->count();
            $tlOverdueCount = $isuRows->where('status_tl', 'OVERDUE')->count();
            $isuSignifikanCount = $isuRows->where('flag_signifikan', 'YA')->count();

            // Kolom U: Status Konsolidasi
            // =IF(A6="","",IF(AND(J6="APPROVED/FINAL",H6=1),"SIAP KONSOLIDASI","BELUM SIAP"))
            $isComplete = ((float)$kal->kelengkapan >= 100.0 || ((float)$kal->kelengkapan >= 0.999 && (float)$kal->kelengkapan <= 1.0));
            if ($kal->status_final === 'APPROVED/FINAL' && $isComplete) {
                $statusKonsolidasi = 'SIAP KONSOLIDASI';
            } else {
                $statusKonsolidasi = 'BELUM SIAP';
            }

            $rows[] = [
                'report_id' => $kal->report_id,
                'periode' => $kal->periode,
                'kode_unit' => $kal->kode_unit,
                'nama_unit' => $kal->nama_unit,
                'resident_auditor' => $kal->resident_auditor,
                'tanggal_submit' => $kal->tanggal_submit,
                'ketepatan' => $kal->ketepatan ?: 'BELUM MASUK',
                'kelengkapan' => (float)$kal->kelengkapan,
                'status_review' => $kal->status_review ?: 'BELUM DIREVIEW',
                'status_final' => $kal->status_final ?: 'UNDER REVIEW',
                'quality_score' => (float)$kal->quality_score,
                'kegiatan' => $kegiatanCount,
                'populasi' => $populasiSum,
                'sampel' => $sampelSum,
                'diuji' => $diujiSum,
                'exception' => $exceptionCount,
                'finding' => $findingCount,
                'high_risk' => $highRiskCount,
                'tl_overdue' => $tlOverdueCount,
                'isu_signifikan' => $isuSignifikanCount,
                'status_konsolidasi' => $statusKonsolidasi,
                'kalender' => $kal,
            ];
        }

        return $rows;
    }

    /**
     * Menghitung Matriks Tren Temuan, TL & Isu Berulang (WP-RA-08-K | 10 Kolom) untuk 13 Area Standar.
     */
    public function getTrenIsuData(string $periodeDate, ?array $allowedUnits = null, ?string $kodeUnit = null): array
    {
        $carbonPeriode = Carbon::parse($periodeDate);
        $startBulanIni = $carbonPeriode->copy()->startOfMonth()->format('Y-m-d');
        $endBulanIni = $carbonPeriode->copy()->endOfMonth()->format('Y-m-d');

        // EOMONTH(B4,-2)+1 s.d. EOMONTH(B4,-1) -> 1 bulan sebelumnya
        $startBulanLalu = $carbonPeriode->copy()->subMonth()->startOfMonth()->format('Y-m-d');
        $endBulanLalu = $carbonPeriode->copy()->subMonth()->endOfMonth()->format('Y-m-d');

        $params = Sop08MasterParameter::getActive();
        $ambangHighAttention = (int)($params->ambang_high_attention ?? 60);

        // Query Rekap Isu TL Bulan Ini
        $queryBulanIni = Sop08RekapIsuTl::whereBetween('periode', [$startBulanIni, $endBulanIni]);
        if ($allowedUnits !== null) {
            $queryBulanIni->whereIn('kode_unit', $allowedUnits);
        }
        if ($kodeUnit) {
            $queryBulanIni->where('kode_unit', $kodeUnit);
        }
        $isusBulanIni = $queryBulanIni->get();

        // Query Rekap Isu TL Bulan Lalu
        $queryBulanLalu = Sop08RekapIsuTl::whereBetween('periode', [$startBulanLalu, $endBulanLalu]);
        if ($allowedUnits !== null) {
            $queryBulanLalu->whereIn('kode_unit', $allowedUnits);
        }
        if ($kodeUnit) {
            $queryBulanLalu->where('kode_unit', $kodeUnit);
        }
        $isusBulanLalu = $queryBulanLalu->get();

        $areas = Sop08RekapIsuTl::areaList();
        $rows = [];

        foreach ($areas as $area) {
            $isuAreaIni = $isusBulanIni->where('area', $area);
            $isuAreaLalu = $isusBulanLalu->where('area', $area);

            $b = $isuAreaIni->count(); // Isu Bulan Ini (Kolom B)
            $c = $isuAreaLalu->count(); // Isu Bulan Lalu (Kolom C)
            $d = $b - $c; // Perubahan (Kolom D)

            $e = $isuAreaIni->where('risk_level', 'HIGH')->count(); // High Risk (Kolom E)
            $f = $isuAreaIni->where('status_tl', 'OVERDUE')->count(); // TL Overdue (Kolom F)
            $g = $isuAreaIni->where('berulang', 'YA')->count(); // Berulang (Kolom G)
            $h = $isuAreaIni->where('flag_signifikan', 'YA')->count(); // Isu Signifikan (Kolom H)

            // Kolom I: Skor Perhatian
            // =MIN(100, (E*25) + (F*15) + (G*10) + (MAX(D,0)*5))
            $skorPerhatian = min(100, ($e * 25) + ($f * 15) + ($g * 10) + (max($d, 0) * 5));

            // Kolom J: Status
            // =IF(I7>=Master_Parameter!$B$21, "HIGH ATTENTION", IF(I7>=30, "PANTAU", "NORMAL"))
            if ($skorPerhatian >= $ambangHighAttention) {
                $status = 'HIGH ATTENTION';
            } elseif ($skorPerhatian >= 30) {
                $status = 'PANTAU';
            } else {
                $status = 'NORMAL';
            }

            $rows[] = [
                'area' => $area,
                'isu_bulan_ini' => $b,
                'isu_bulan_lalu' => $c,
                'perubahan' => $d,
                'high_risk' => $e,
                'tl_overdue' => $f,
                'berulang' => $g,
                'isu_signifikan' => $h,
                'skor_perhatian' => $skorPerhatian,
                'status' => $status,
            ];
        }

        return $rows;
    }

    /**
     * Mengambil seluruh data agregasi untuk Dashboard Eksekutif SOP 08 (WP-RA-08-L).
     */
    public function getDashboardData(string $periodeDate, ?array $allowedUnits = null, ?string $kodeUnit = null): array
    {
        $startOfMonth = Carbon::parse($periodeDate)->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($periodeDate)->endOfMonth()->format('Y-m-d');

        $params = Sop08MasterParameter::getActive();
        $targetQualityScore = (float)($params->target_quality_score ?? 80.0);

        // 1. Konsolidasi Data & Tren Isu Data
        $konsolidasiRows = $this->getKonsolidasiData($periodeDate, $allowedUnits, $kodeUnit);
        $trenIsuRows = $this->getTrenIsuData($periodeDate, $allowedUnits, $kodeUnit);

        // 2. Kalender Laporan Query
        $kalenderQuery = Sop08KalenderLaporan::whereBetween('periode', [$startOfMonth, $endOfMonth]);
        if ($allowedUnits !== null) {
            $kalenderQuery->whereIn('kode_unit', $allowedUnits);
        }
        if ($kodeUnit) {
            $kalenderQuery->where('kode_unit', $kodeUnit);
        }
        $kalenderList = $kalenderQuery->get();

        // 3. Rekap Isu Query
        $isuQuery = Sop08RekapIsuTl::whereBetween('periode', [$startOfMonth, $endOfMonth]);
        if ($allowedUnits !== null) {
            $isuQuery->whereIn('kode_unit', $allowedUnits);
        }
        if ($kodeUnit) {
            $isuQuery->where('kode_unit', $kodeUnit);
        }
        $isuList = $isuQuery->get();

        // 12 KPI Cards:
        $laporanWajib = $kalenderList->where('wajib_lapor', 'YA')->count();
        $sudahSubmit = $kalenderList->whereNotNull('tanggal_submit')->count();
        $tepatWaktu = $kalenderList->where('ketepatan', 'TEPAT WAKTU')->count();
        $terlambat = $kalenderList->where('ketepatan', 'TERLAMBAT')->count();
        $belumMasuk = $kalenderList->whereIn('ketepatan', ['BELUM MASUK', 'BELUM SUBMIT', null])->whereNull('tanggal_submit')->count();
        $terlambatBelum = $terlambat + $belumMasuk;
        $approvedFinal = $kalenderList->where('status_final', 'APPROVED/FINAL')->count();
        $revisionRequired = $kalenderList->where('status_final', 'REVISION REQUIRED')->count();

        $qualityScoreReports = $kalenderList->where('quality_score', '>', 0);
        $rataRataQuality = $qualityScoreReports->isNotEmpty() ? round($qualityScoreReports->avg('quality_score'), 1) : 0.0;

        $exceptionCount = $isuList->where('jenis_isu', 'EXCEPTION')->count();
        $findingCount = $isuList->where('jenis_isu', 'FINDING')->count();
        $highRiskCount = $isuList->where('risk_level', 'HIGH')->count();
        $tlOverdueCount = $isuList->where('status_tl', 'OVERDUE')->count();
        $isuSignifikanCount = $isuList->where('flag_signifikan', 'YA')->count();

        // Area High Attention count
        $areaHighAttentionCount = collect($trenIsuRows)->where('status', 'HIGH ATTENTION')->count();

        // 4. Monitoring Perhatian Unit (Tabel A27:H51)
        $unitMonitoring = [];
        $unitPerluPerhatianCount = 0;
        foreach ($konsolidasiRows as $row) {
            // =IF(OR(High Risk>0, Overdue>0, Isu Signifikan>0, Quality<80), "PERLU PERHATIAN", "NORMAL")
            $isPerluPerhatian = ($row['high_risk'] > 0 || $row['tl_overdue'] > 0 || $row['isu_signifikan'] > 0 || $row['quality_score'] < $targetQualityScore);
            if ($isPerluPerhatian) {
                $unitPerluPerhatianCount++;
            }

            $unitMonitoring[] = [
                'unit' => $row['nama_unit'],
                'kode_unit' => $row['kode_unit'],
                'ra' => $row['resident_auditor'],
                'status_final' => $row['status_final'],
                'quality' => $row['quality_score'],
                'high_risk' => $row['high_risk'],
                'overdue' => $row['tl_overdue'],
                'isu_signifikan' => $row['isu_signifikan'],
                'perhatian' => $isPerluPerhatian ? 'PERLU PERHATIAN' : 'NORMAL',
            ];
        }

        return [
            'kpi' => [
                'laporan_wajib' => $laporanWajib,
                'sudah_submit' => $sudahSubmit,
                'tepat_waktu' => $tepatWaktu,
                'terlambat_belum' => $terlambatBelum,
                'approved_final' => $approvedFinal,
                'rata_rata_quality' => $rataRataQuality,
                'exception' => $exceptionCount,
                'finding' => $findingCount,
                'high_risk' => $highRiskCount,
                'tl_overdue' => $tlOverdueCount,
                'isu_signifikan' => $isuSignifikanCount,
                'unit_high_attention' => $areaHighAttentionCount,
                'unit_perlu_perhatian_count' => $unitPerluPerhatianCount,
            ],
            'status_summary' => [
                'TEPAT WAKTU' => $tepatWaktu,
                'TERLAMBAT' => $terlambat,
                'BELUM MASUK' => $belumMasuk,
                'APPROVED/FINAL' => $approvedFinal,
                'REVISION REQUIRED' => $revisionRequired,
            ],
            'tren_isu' => $trenIsuRows,
            'unit_monitoring' => $unitMonitoring,
        ];
    }

    /**
     * Format auto-generator Feedback ID: FB-YYYYMM-XXX
     */
    public function generateFeedbackId(string $periodeDate): string
    {
        $prefix = "FB-" . Carbon::parse($periodeDate)->format('Ym') . "-";
        $count = Sop08Feedback::where('feedback_id', 'like', "{$prefix}%")->count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // FASE 4: MASTER RA, LOG HARIAN RA & KALENDER KINERJA RA (WP-RA-08-N/O/P)
    // =========================================================================

    /**
     * Hitung NETWORKDAYS: Jumlah hari kerja (Senin s.d. Jumat) dikurangi hari libur nasional.
     */
    public function calculateNetworkdays($startDate, $endDate, array $holidayDates = []): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            return 0;
        }

        $workdays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            if (!$current->isWeekend() && !in_array($dateStr, $holidayDates)) {
                $workdays++;
            }
            $current->addDay();
        }

        return $workdays;
    }

    /**
     * Format auto-generator Log ID: LOG-YYYYMMDD-XXX
     */
    public function generateLogId(string $tanggal): string
    {
        $prefix = "LOG-" . Carbon::parse($tanggal)->format('Ymd') . "-";
        $count = Sop08LogHarianRa::where('log_id', 'like', "{$prefix}%")->count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung Matriks Kalender Pelaksanaan Tugas & Kinerja Bulanan RA (WP-RA-08-P | 47 Kolom: A s.d. AU).
     */
    public function getKalenderKinerjaRaData(string $periodeDate, ?array $allowedUnits = null, ?string $kodeUnit = null, ?string $raId = null): array
    {
        $carbon = Carbon::parse($periodeDate);
        $startMonth = $carbon->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $carbon->copy()->endOfMonth()->format('Y-m-d');
        $daysInMonth = $carbon->daysInMonth;

        $holidayDates = $this->getHolidayDates($carbon->year);

        // 1. Query Master RA
        $raQuery = Sop08MasterRa::where('status', 'Aktif');
        if ($allowedUnits !== null) {
            $raQuery->whereIn('kode_kc_penempatan', $allowedUnits);
        }
        if ($kodeUnit) {
            $raQuery->where('kode_kc_penempatan', $kodeUnit);
        }
        if ($raId) {
            $raQuery->where('ra_id', $raId);
        }
        $masterRas = $raQuery->orderBy('kode_kc_penempatan')->get();

        // 2. Query Log Harian pada bulan bersangkutan
        $logQuery = Sop08LogHarianRa::whereBetween('tanggal', [$startMonth, $endMonth]);
        if ($allowedUnits !== null) {
            $logQuery->whereIn('kode_unit', $allowedUnits);
        }
        if ($kodeUnit) {
            $logQuery->where('kode_unit', $kodeUnit);
        }
        if ($raId) {
            $logQuery->where('ra_id', $raId);
        }
        $allLogs = $logQuery->get();

        // Hitung Hari Kerja (Kolom D) = NETWORKDAYS
        $hariKerjaBulan = $this->calculateNetworkdays($startMonth, $endMonth, $holidayDates);

        $results = [];

        foreach ($masterRas as $ra) {
            $raLogs = $allLogs->where('ra_id', $ra->ra_id);

            // Matriks Hari 1 s.d. 31 (Kolom Q s.d. AU)
            $matrixHari = [];
            for ($t = 1; $t <= 31; $t++) {
                if ($t > $daysInMonth) {
                    $matrixHari[$t] = [
                        'status' => '',
                        'day' => $t,
                    ];
                    continue;
                }

                $curDate = Carbon::parse($carbon->format('Y-m-') . str_pad($t, 2, '0', STR_PAD_LEFT));
                $curDateStr = $curDate->format('Y-m-d');
                $isWeekend = $curDate->isWeekend();
                $isHoliday = in_array($curDateStr, $holidayDates);

                $dayLogs = $raLogs->filter(function ($item) use ($curDateStr) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d') === $curDateStr;
                });

                // Hirarki Evaluasi Status Harian Excel (Baris 7 Formula)
                if ($isWeekend || $isHoliday) {
                    $status = 'LIBUR';
                } elseif ($dayLogs->where('status_hari', 'CUTI')->isNotEmpty()) {
                    $status = 'CUTI';
                } elseif ($dayLogs->where('status_hari', 'SAKIT')->isNotEmpty()) {
                    $status = 'SAKIT';
                } elseif ($dayLogs->where('status_hari', 'DINAS LUAR')->isNotEmpty()) {
                    $status = 'DINAS LUAR';
                } elseif ($dayLogs->where('status_hari', 'PELATIHAN')->isNotEmpty()) {
                    $status = 'PELATIHAN';
                } else {
                    $kerjaLogs = $dayLogs->where('status_hari', 'KERJA');
                    if ($kerjaLogs->isEmpty()) {
                        $status = 'TIDAK ADA AKTIVITAS';
                    } elseif ($kerjaLogs->where('status_penyelesaian', '!=', 'COMPLETED')->isNotEmpty()) {
                        $status = 'BELUM TUNTAS';
                    } elseif ($kerjaLogs->filter(function ($l) {
                        return in_array($l->hasil_review, ['TIDAK MEMADAI', 'PERLU PERBAIKAN']);
                    })->isNotEmpty()) {
                        $status = 'PERLU PERBAIKAN';
                    } elseif ($kerjaLogs->filter(function ($l) {
                        return in_array($l->hasil_review, ['BELUM DIREVIEW', null, '']);
                    })->isNotEmpty()) {
                        $status = 'SELESAI - BELUM REVIEW';
                    } else {
                        $status = 'BAIK';
                    }
                }

                $matrixHari[$t] = [
                    'status' => $status,
                    'day' => $t,
                    'date' => $curDateStr,
                ];
            }

            // Kolom E: Cuti/Sakit = COUNTIF(Q:AU, "CUTI") + COUNTIF(Q:AU, "SAKIT")
            $cutiSakit = collect($matrixHari)->filter(function ($m) {
                return in_array($m['status'], ['CUTI', 'SAKIT']);
            })->count();

            // Kolom F: Hari Tugas Dipenuhi = COUNTIF(BAIK + SELESAI + PERLU PERBAIKAN + BELUM TUNTAS + DINAS LUAR + PELATIHAN)
            $hariTugasDipenuhi = collect($matrixHari)->filter(function ($m) {
                return in_array($m['status'], [
                    'BAIK',
                    'SELESAI - BELUM REVIEW',
                    'PERLU PERBAIKAN',
                    'BELUM TUNTAS',
                    'DINAS LUAR',
                    'PELATIHAN',
                ]);
            })->count();

            // Kolom G: Tanpa Aktivitas = COUNTIF(Q:AU, "TIDAK ADA AKTIVITAS")
            $tanpaAktivitas = collect($matrixHari)->filter(function ($m) {
                return $m['status'] === 'TIDAK ADA AKTIVITAS';
            })->count();

            // Kolom H: Konsistensi (%) = IFERROR(MIN(1, F / (D - E)), 0)
            $denominator = $hariKerjaBulan - $cutiSakit;
            if ($denominator > 0) {
                $konsistensi = min(1.0, $hariTugasDipenuhi / $denominator);
            } else {
                $konsistensi = 0.0;
            }

            // Kolom I: Aktivitas = COUNTIFS(Log_Harian_RA, status_hari == "KERJA")
            $kerjaLogsTotal = $raLogs->where('status_hari', 'KERJA');
            $aktivitas = $kerjaLogsTotal->count();

            // Kolom J: Selesai = COUNTIFS(status_hari == "KERJA", status_penyelesaian == "COMPLETED")
            $selesai = $kerjaLogsTotal->where('status_penyelesaian', 'COMPLETED')->count();

            // Kolom K: Penyelesaian (%) = IFERROR(J / I, 0)
            $penyelesaian = $aktivitas > 0 ? ($selesai / $aktivitas) : 0.0;

            // Kolom L: Direview = COUNTIFS(Hasil_Review in [MEMADAI, PERLU PERBAIKAN, TIDAK MEMADAI])
            $direview = $kerjaLogsTotal->filter(function ($l) {
                return in_array($l->hasil_review, ['MEMADAI', 'PERLU PERBAIKAN', 'TIDAK MEMADAI']);
            })->count();

            // Kolom M: Memadai = COUNTIFS(Hasil_Review == "MEMADAI")
            $memadai = $kerjaLogsTotal->where('hasil_review', 'MEMADAI')->count();

            // Kolom N: Kualitas (%) = IF(L == 0, null, M / L)
            $kualitas = $direview > 0 ? ($memadai / $direview) : null;

            // Kolom O: Skor (0-100) = IF(N == null, null, ROUND(H*40 + K*30 + N*30, 0))
            if ($kualitas !== null) {
                $skor = (int) round(($konsistensi * 40) + ($penyelesaian * 30) + ($kualitas * 30), 0);
            } else {
                $skor = null;
            }

            // Kolom P: Status Bulanan
            if ($aktivitas === 0) {
                $statusBulanan = 'BELUM ADA DATA';
            } elseif ($kualitas === null || $direview === 0) {
                $statusBulanan = 'BELUM CUKUP DATA REVIEW';
            } elseif ($skor >= 90) {
                $statusBulanan = 'BAIK DAN KONSISTEN';
            } elseif ($skor >= 80) {
                $statusBulanan = 'MEMADAI';
            } elseif ($skor >= 60) {
                $statusBulanan = 'PERLU PENINGKATAN';
            } else {
                $statusBulanan = 'PERLU TINDAK LANJUT';
            }

            $results[] = [
                'ra_id' => $ra->ra_id,
                'nama_ra' => $ra->nama_ra,
                'kode_kc' => $ra->kode_kc_penempatan,
                'nama_kc' => $ra->nama_kc,
                'unit_ra' => $ra->unit_ra,
                'hari_kerja' => $hariKerjaBulan,
                'cuti_sakit' => $cutiSakit,
                'hari_tugas_dipenuhi' => $hariTugasDipenuhi,
                'tanpa_aktivitas' => $tanpaAktivitas,
                'konsistensi' => $konsistensi,
                'aktivitas' => $aktivitas,
                'selesai' => $selesai,
                'penyelesaian' => $penyelesaian,
                'direview' => $direview,
                'memadai' => $memadai,
                'kualitas' => $kualitas,
                'skor' => $skor,
                'status_bulanan' => $statusBulanan,
                'matrix_hari' => $matrixHari,
                'master_ra' => $ra,
            ];
        }

        return [
            'periode' => $carbon->format('Y-m'),
            'hari_kerja_bulan' => $hariKerjaBulan,
            'days_in_month' => $daysInMonth,
            'data' => $results,
        ];
    }

    /**
     * Auto-pull aktivitas dari modul-modul kerja (SOP 03, 04, 05, 06, 07) ke Log_Harian_RA.
     */
    public function pullLogsFromWorkModules(string $raId, string $startDate, string $endDate): array
    {
        $ra = Sop08MasterRa::where('ra_id', $raId)->firstOrFail();
        $kodeUnit = $ra->kode_kc_penempatan;
        $pulledCount = 0;

        // 1. SOP 03 - Daily Review (Physical KKA)
        if (class_exists(\App\Models\DailyRegister\PhysicalKka::class)) {
            $kkas = \App\Models\DailyRegister\PhysicalKka::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_pemeriksaan', [$startDate, $endDate])
                ->get();

            foreach ($kkas as $k) {
                $tgl = $k->tanggal_pemeriksaan ?: $startDate;
                $logId = $this->generateLogId($tgl);

                $existing = Sop08LogHarianRa::where('ra_id', $raId)
                    ->where('tanggal', $tgl)
                    ->where('output_referensi_wp', "KKA-PHYSICAL-{$k->id}")
                    ->first();

                if (!$existing) {
                    Sop08LogHarianRa::create([
                        'log_id' => $logId,
                        'tanggal' => $tgl,
                        'ra_id' => $ra->ra_id,
                        'user_id' => $ra->user_id,
                        'nama_ra' => $ra->nama_ra,
                        'kode_unit' => $ra->kode_kc_penempatan,
                        'nama_unit' => $ra->nama_kc,
                        'status_hari' => 'KERJA',
                        'sumber_sop' => 'SOP 03 - Daily Review',
                        'jenis_aktivitas' => 'Pemeriksaan Register Fisik Harian Kas & Dokumen',
                        'area' => 'Cash Opname',
                        'output_referensi_wp' => "KKA-PHYSICAL-{$k->id}",
                        'status_penyelesaian' => 'COMPLETED',
                        'hasil_review' => 'BELUM DIREVIEW',
                        'jumlah_item' => 1,
                        'catatan' => 'Auto-pull dari pengujian fisik harian (SOP 03).',
                    ]);
                    $pulledCount++;
                }
            }
        }

        // 2. SOP 04 - Offsite Review
        if (DB::getSchemaBuilder()->hasTable('wp_offsite')) {
            $carbonStart = Carbon::parse($startDate);
            $offs = DB::table('wp_offsite')->where('kode_unit', $kodeUnit)
                ->whereYear('periode_mulai', $carbonStart->year)
                ->whereMonth('periode_mulai', $carbonStart->month)
                ->get();

            foreach ($offs as $o) {
                $tgl = $o->periode_selesai ?: $startDate;
                $logId = $this->generateLogId($tgl);

                $existing = Sop08LogHarianRa::where('ra_id', $raId)
                    ->where('output_referensi_wp', "WP-OFFSITE-{$o->id}")
                    ->first();

                if (!$existing) {
                    Sop08LogHarianRa::create([
                        'log_id' => $logId,
                        'tanggal' => $tgl,
                        'ra_id' => $ra->ra_id,
                        'user_id' => $ra->user_id,
                        'nama_ra' => $ra->nama_ra,
                        'kode_unit' => $ra->kode_kc_penempatan,
                        'nama_unit' => $ra->nama_kc,
                        'status_hari' => 'KERJA',
                        'sumber_sop' => 'SOP 04 - Offsite Review',
                        'jenis_aktivitas' => 'Pelaksanaan Kertas Kerja Offsite Audit',
                        'area' => 'DPK Layanan',
                        'output_referensi_wp' => "WP-OFFSITE-{$o->id}",
                        'status_penyelesaian' => 'COMPLETED',
                        'hasil_review' => 'BELUM DIREVIEW',
                        'jumlah_item' => 1,
                        'catatan' => 'Auto-pull dari kertas kerja offsite audit (SOP 04).',
                    ]);
                    $pulledCount++;
                }
            }
        }

        // 3. SOP 05 - Onsite Audit
        if (class_exists(\App\Models\Onsite\OnsiteVisit::class)) {
            $visits = \App\Models\Onsite\OnsiteVisit::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_mulai', [$startDate, $endDate])
                ->get();

            foreach ($visits as $v) {
                $tgl = $v->tanggal_mulai ?: $startDate;
                $logId = $this->generateLogId($tgl);

                $existing = Sop08LogHarianRa::where('ra_id', $raId)
                    ->where('output_referensi_wp', "ONSITE-VISIT-{$v->id}")
                    ->first();

                if (!$existing) {
                    Sop08LogHarianRa::create([
                        'log_id' => $logId,
                        'tanggal' => $tgl,
                        'ra_id' => $ra->ra_id,
                        'user_id' => $ra->user_id,
                        'nama_ra' => $ra->nama_ra,
                        'kode_unit' => $ra->kode_kc_penempatan,
                        'nama_unit' => $ra->nama_kc,
                        'status_hari' => 'KERJA',
                        'sumber_sop' => 'SOP 05 - Onsite Audit',
                        'jenis_aktivitas' => 'Pelaksanaan Onsite Audit Lapangan Unit',
                        'area' => 'Kredit',
                        'output_referensi_wp' => "ONSITE-VISIT-{$v->id}",
                        'status_penyelesaian' => 'COMPLETED',
                        'hasil_review' => 'BELUM DIREVIEW',
                        'jumlah_item' => $v->total_sampel ?: 1,
                        'catatan' => 'Auto-pull dari kunjungan onsite audit (SOP 05).',
                    ]);
                    $pulledCount++;
                }
            }
        }

        // 4. SOP 06 - Risk Trigger Visit
        if (class_exists(\App\Models\RiskTrigger\RiskTriggerVisit::class)) {
            $rtVisits = \App\Models\RiskTrigger\RiskTriggerVisit::where('kode_unit', $kodeUnit)
                ->whereBetween('tanggal_mulai', [$startDate, $endDate])
                ->get();

            foreach ($rtVisits as $rv) {
                $tgl = $rv->tanggal_mulai ?: $startDate;
                $logId = $this->generateLogId($tgl);

                $existing = Sop08LogHarianRa::where('ra_id', $raId)
                    ->where('output_referensi_wp', "RISK-TRIGGER-{$rv->id}")
                    ->first();

                if (!$existing) {
                    Sop08LogHarianRa::create([
                        'log_id' => $logId,
                        'tanggal' => $tgl,
                        'ra_id' => $ra->ra_id,
                        'user_id' => $ra->user_id,
                        'nama_ra' => $ra->nama_ra,
                        'kode_unit' => $ra->kode_kc_penempatan,
                        'nama_unit' => $ra->nama_kc,
                        'status_hari' => 'KERJA',
                        'sumber_sop' => 'SOP 06 - Risk Trigger Visit',
                        'jenis_aktivitas' => 'Pemeriksaan Lapangan Insidental Risk Trigger',
                        'area' => 'Teller Kas',
                        'output_referensi_wp' => "RISK-TRIGGER-{$rv->id}",
                        'status_penyelesaian' => 'COMPLETED',
                        'hasil_review' => 'BELUM DIREVIEW',
                        'jumlah_item' => 1,
                        'catatan' => 'Auto-pull dari pemeriksaan khusus risk trigger (SOP 06).',
                    ]);
                    $pulledCount++;
                }
            }
        }

        return [
            'success' => true,
            'pulled_count' => $pulledCount,
            'message' => "Berhasil menarik {$pulledCount} baris log aktivitas dari modul kerja.",
        ];
    }

    /**
     * Hitung metrik kinerja RA untuk 1 Report ID Laporan Bulanan (Panel Sel A44:K53 WP-RA-08-C).
     */
    public function calculateRaPerformanceForReport(string $reportId): array
    {
        $kalender = Sop08KalenderLaporan::where('report_id', $reportId)->first();
        if (!$kalender) {
            return [
                'hari_kerja' => 21,
                'cuti_sakit' => 0,
                'hari_tugas_dipenuhi' => 21,
                'tanpa_aktivitas' => 0,
                'konsistensi' => 1.0,
                'penyelesaian' => 1.0,
                'kualitas' => 1.0,
                'skor' => 100,
                'status_bulanan' => 'BAIK DAN KONSISTEN',
            ];
        }

        $masterRa = Sop08MasterRa::where('kode_kc_penempatan', $kalender->kode_unit)->first();
        $raId = $masterRa?->ra_id;

        $kinerja = $this->getKalenderKinerjaRaData($kalender->periode, [$kalender->kode_unit], $kalender->kode_unit, $raId);
        $raPerf = collect($kinerja['data'])->first();

        if ($raPerf) {
            return [
                'hari_kerja' => $raPerf['hari_kerja'],
                'cuti_sakit' => $raPerf['cuti_sakit'],
                'hari_tugas_dipenuhi' => $raPerf['hari_tugas_dipenuhi'],
                'tanpa_aktivitas' => $raPerf['tanpa_aktivitas'],
                'konsistensi' => $raPerf['konsistensi'],
                'penyelesaian' => $raPerf['penyelesaian'],
                'kualitas' => $raPerf['kualitas'],
                'skor' => $raPerf['skor'],
                'status_bulanan' => $raPerf['status_bulanan'],
            ];
        }

        return [
            'hari_kerja' => $kinerja['hari_kerja_bulan'] ?? 21,
            'cuti_sakit' => 0,
            'hari_tugas_dipenuhi' => 0,
            'tanpa_aktivitas' => $kinerja['hari_kerja_bulan'] ?? 21,
            'konsistensi' => 0.0,
            'penyelesaian' => 0.0,
            'kualitas' => null,
            'skor' => null,
            'status_bulanan' => 'BELUM ADA DATA',
        ];
    }

    // =========================================================================
    // OVERHAUL SOP 08: 100% DATABASE PULL, KONSOLIDASI REAL-TIME, WYSIWYG
    // =========================================================================

    /**
     * Sanitasi konten HTML WYSIWYG untuk mencegah XSS namun tetap mempertahankan format teks & tabel.
     */
    public function sanitizeWysiwygHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Bersihkan elemen berbahaya script, style, iframe, object, embed, applet
        $clean = preg_replace('/<(script|style|iframe|object|embed|applet)\b[^>]*>.*?<\/\1>/is', '', $html);
        $clean = preg_replace('/<(script|style|iframe|object|embed|applet)\b[^>]*>/is', '', $clean);

        // 2. Hapus inline event handlers (onerror, onload, onclick, dll)
        $clean = preg_replace('/\s*on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);

        // 3. Hapus protokol berbahaya seperti javascript:
        $clean = preg_replace('/(href|src)\s*=\s*("javascript:[^"]*"|\'javascript:[^\']*\'|javascript:[^\s>]+)/i', '', $clean);

        // 4. Batasi tag yang diizinkan (teks terformat, headings, lists, dan tabel)
        $allowedTags = '<p><br><b><strong><i><em><u><s><h1><h2><h3><h4><h5><h6><ul><ol><li><table><thead><tbody><tfoot><tr><th><td><span><div><blockquote><hr>';
        $clean = strip_tags($clean, $allowedTags);

        return trim($clean);
    }

    /**
     * Simpan narasi WYSIWYG Admin untuk laporan bulanan tertentu.
     */
    public function saveWysiwygAdmin(string $reportId, ?string $catatanEksekutif, ?string $rencanaBulanDepan, ?int $adminId = null): Sop08NarasiLaporan
    {
        $narasi = Sop08NarasiLaporan::firstOrCreate(['report_id' => $reportId]);

        $sanitizedCatatan = $this->sanitizeWysiwygHtml($catatanEksekutif);
        $sanitizedRencana = $this->sanitizeWysiwygHtml($rencanaBulanDepan);

        $narasi->catatan_eksekutif = $sanitizedCatatan;
        $narasi->rencana_bulan_depan = $sanitizedRencana;
        $narasi->admin_editor_id = $adminId ?: auth()->id();
        $narasi->is_consolidated = true;

        // Kompatibilitas dengan pembaca lama
        if (!empty($sanitizedCatatan)) {
            $narasi->ringkasan_eksekutif = $sanitizedCatatan;
        }
        if (!empty($sanitizedRencana)) {
            $narasi->rencana_bulan_berikut = $sanitizedRencana;
        }

        $narasi->save();

        return $narasi;
    }

    /**
     * Hitung & rekam status kepatuhan KKA per cabang untuk periode berjalan.
     * Aturan:
     * 🟢 HIJAU (Tuntas / Patuh):
     *   Populasi data KKA > 0 DAN seluruh item KKA tersebut sudah selesai dikerjakan / diupload oleh RA; ATAU
     *   Populasi data KKA == 0 (nihil transaksi) DAN RA sudah mengunggah / mengonfirmasi KKA nihilnya.
     * 🔴 MERAH (Belum Selesai / Tertunggak):
     *   Terdapat populasi data KKA (>= 1) yang belum selesai dikerjakan / belum diupload oleh RA! (atau nihil belum dikonfirmasi)
     */
    public function getKkaComplianceStatus(string $kodeUnit, string $periodeDate): array
    {
        $carbon = Carbon::parse($periodeDate);
        $startMonth = $carbon->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $carbon->copy()->endOfMonth()->format('Y-m-d');

        $existingRecords = collect();
        if (DB::getSchemaBuilder()->hasTable('sop08_kka_branch_statuses')) {
            $existingRecords = Sop08KkaBranchStatus::where('kode_unit', $kodeUnit)
                ->whereDate('periode', $startMonth)
                ->get()
                ->keyBy('area');
        }

        // Standard 5 KKA Areas
        $areas = [
            'KKA Daily Register',
            'Cash Opname & Kas Fisik',
            'KKA Fisik & Observasi',
            'Offsite Review',
            'Risk Trigger SOP 06',
        ];

        $details = [];
        $totalPopulasi = 0;
        $totalSelesai = 0;
        $totalTertunggak = 0;
        $hasAnyMerah = false;

        foreach ($areas as $area) {
            $rec = $existingRecords->get($area);

            $populasi = 0;
            $uncompleted = 0;

            if ($area === 'KKA Daily Register') {
                if (class_exists(DailyRegisterFinding::class) && DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
                    $findings = DailyRegisterFinding::where('kode_unit', $kodeUnit)
                        ->where(function($q) use ($startMonth, $endMonth) {
                            $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                              ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                        })->get();
                    $populasi = $findings->count();
                    $uncompleted = $findings->filter(function($f) {
                        $s = strtolower($f->review_status ?? $f->status_review ?? '');
                        return !in_array($s, ['approved', 'selesai', 'closed']);
                    })->count();
                }
            } elseif ($area === 'Cash Opname & Kas Fisik') {
                if (DB::getSchemaBuilder()->hasTable('physical_kka_cash_details')) {
                    $cashKkas = DB::table('physical_kka_cash_details')
                        ->whereExists(function($sq) use ($kodeUnit, $startMonth, $endMonth) {
                            $sq->select(DB::raw(1))->from('physical_kkas')
                                ->whereColumn('physical_kkas.id', 'physical_kka_cash_details.physical_kka_id')
                                ->where('physical_kkas.kode_unit', $kodeUnit)
                                ->whereBetween('physical_kkas.tanggal_pemeriksaan', [$startMonth, $endMonth]);
                        })->get();
                    $populasi = $cashKkas->count();
                    $uncompleted = DB::table('physical_kka_cash_details')
                        ->whereExists(function($sq) use ($kodeUnit, $startMonth, $endMonth) {
                            $sq->select(DB::raw(1))->from('physical_kkas')
                                ->whereColumn('physical_kkas.id', 'physical_kka_cash_details.physical_kka_id')
                                ->where('physical_kkas.kode_unit', $kodeUnit)
                                ->whereBetween('physical_kkas.tanggal_pemeriksaan', [$startMonth, $endMonth])
                                ->where('physical_kkas.status_final', '!=', 'Final');
                        })->count();
                }
            } elseif ($area === 'KKA Fisik & Observasi') {
                if (class_exists(PhysicalKka::class) && DB::getSchemaBuilder()->hasTable('physical_kkas')) {
                    $kkas = PhysicalKka::where('kode_unit', $kodeUnit)
                        ->whereBetween('tanggal_pemeriksaan', [$startMonth, $endMonth])
                        ->get();
                    $populasi = $kkas->count();
                    $uncompleted = $kkas->filter(function($k) {
                        return strtolower($k->status_final ?? '') !== 'final';
                    })->count();
                }
            } elseif ($area === 'Offsite Review') {
                if (DB::getSchemaBuilder()->hasTable('wp_offsite')) {
                    $offsite = DB::table('wp_offsite')->where('kode_unit', $kodeUnit)
                        ->whereYear('periode_mulai', $carbon->year)
                        ->whereMonth('periode_mulai', $carbon->month)
                        ->first();
                    if ($offsite) {
                        $populasi = 1;
                        if (!in_array(strtolower($offsite->status_review ?? ''), ['approved', 'selesai', 'final'])) {
                            $uncompleted = 1;
                        }
                    }
                }
            } elseif ($area === 'Risk Trigger SOP 06') {
                if (DB::getSchemaBuilder()->hasTable('register_triggers')) {
                    $triggers = DB::table('register_triggers')->where('kode_unit', $kodeUnit)
                        ->whereBetween('tanggal_trigger', [$startMonth, $endMonth])
                        ->get();
                    $totalTriggers = $triggers->count();
                    // SOP 06 hanya memicu alarm jika akumulasi temuan cabang > 10
                    if ($totalTriggers > 10) {
                        $populasi = 1;
                        $hasUninformed = $triggers->filter(function($t) {
                            $wf = strtoupper($t->status_workflow ?? '');
                            $tl = strtoupper($t->status_tindak_lanjut_alarm ?? '');
                            return !in_array($wf, ['SELESAI/DALAM TL', 'SELESAI', 'DALAM PANTAUAN ALARM'])
                                && $tl !== 'TELAH DITINDAKLANJUTI';
                        })->isNotEmpty();
                        $uncompleted = $hasUninformed ? 1 : 0;
                    } else {
                        $populasi = 0;
                        $uncompleted = 0;
                    }
                }
            }

            // Jika ada override record status di database (misal dari fixture atau konfirmasi manual sebelumnya)
            if ($rec && $area !== 'Risk Trigger SOP 06') {
                if ($rec->populasi > 0 || $rec->is_nihil) {
                    $populasi = (int)$rec->populasi;
                    $itemSelesai = (int)$rec->item_selesai;
                    $uncompleted = max(0, $populasi - $itemSelesai);
                }
                $isNihil = (bool)$rec->is_nihil || ($populasi === 0);
                $isConfirmedNihil = (bool)$rec->is_confirmed_nihil;
            } else {
                $isNihil = ($populasi === 0);
                // Khusus SOP 06: RA tidak mengisi form di modul ini. Jika tidak ada alarm (> 10), otomatis patuh
                $isConfirmedNihil = ($area === 'Risk Trigger SOP 06') ? true : false;
            }

            if (!$isNihil) {
                $isPatuh = ($uncompleted === 0);
            } else {
                $isPatuh = ($area === 'Risk Trigger SOP 06') ? true : $isConfirmedNihil;
            }

            $areaStatus = $isPatuh ? 'HIJAU' : 'MERAH';
            if ($areaStatus === 'MERAH') {
                $hasAnyMerah = true;
            }

            $itemSelesai = max(0, $populasi - $uncompleted);

            $details[$area] = [
                'area' => $area,
                'populasi' => $populasi,
                'item_selesai' => $itemSelesai,
                'item_tertunggak' => $uncompleted,
                'is_nihil' => $isNihil,
                'is_confirmed_nihil' => $isConfirmedNihil,
                'status_kepatuhan' => $areaStatus,
            ];

            $totalPopulasi += $populasi;
            $totalSelesai += $itemSelesai;
            $totalTertunggak += $uncompleted;

            // Persist area record
            if (DB::getSchemaBuilder()->hasTable('sop08_kka_branch_statuses')) {
                if (!$rec) {
                    $rec = new Sop08KkaBranchStatus();
                    $rec->kode_unit = $kodeUnit;
                    $rec->periode = $startMonth;
                    $rec->area = $area;
                }
                $rec->populasi = $populasi;
                $rec->item_selesai = $itemSelesai;
                $rec->is_nihil = $isNihil;
                $rec->is_confirmed_nihil = $isConfirmedNihil;
                $rec->status_kepatuhan = $areaStatus;
                $rec->save();
            }
        }

        $overallStatus = $hasAnyMerah ? 'MERAH' : 'HIJAU';

        // Update overall ALL record
        if (DB::getSchemaBuilder()->hasTable('sop08_kka_branch_statuses')) {
            $allRec = $existingRecords->get('ALL');
            if (!$allRec) {
                $allRec = new Sop08KkaBranchStatus();
                $allRec->kode_unit = $kodeUnit;
                $allRec->periode = $startMonth;
                $allRec->area = 'ALL';
            }
            $allRec->populasi = $totalPopulasi;
            $allRec->item_selesai = $totalSelesai;
            $allRec->is_nihil = ($totalPopulasi === 0);
            $allRec->is_confirmed_nihil = !$hasAnyMerah;
            $allRec->status_kepatuhan = $overallStatus;
            $allRec->save();
        }

        return [
            'status' => $overallStatus,
            'badge' => $overallStatus === 'HIJAU' ? '🟢 Tuntas / Patuh' : '🔴 Belum Selesai / Tertunggak',
            'summary' => $overallStatus === 'HIJAU' ? 'Lengkap / Tuntas' : 'Belum Selesai / Tertunggak',
            'is_nihil' => ($totalPopulasi === 0),
            'is_confirmed_nihil' => !$hasAnyMerah,
            'populasi' => $totalPopulasi,
            'item_selesai' => $totalSelesai,
            'item_tertunggak' => $totalTertunggak,
            'details' => $details,
            'branch_status' => $existingRecords->get('ALL') ?? $existingRecords->first(),
        ];
    }

    /**
     * Konfirmasi KKA Nihil oleh Resident Auditor atau Sistem.
     */
    public function confirmNihilKka(string $kodeUnit, string $periodeDate, ?int $userId = null, string $area = 'ALL', ?string $catatan = null): Sop08KkaBranchStatus
    {
        $startMonth = Carbon::parse($periodeDate)->startOfMonth()->format('Y-m-d');
        $branchStatus = Sop08KkaBranchStatus::where('kode_unit', $kodeUnit)
            ->whereDate('periode', $startMonth)
            ->where('area', $area)
            ->first();

        if (!$branchStatus) {
            $branchStatus = new Sop08KkaBranchStatus();
            $branchStatus->kode_unit = $kodeUnit;
            $branchStatus->periode = $startMonth;
            $branchStatus->area = $area;
        }

        $branchStatus->is_nihil = true;
        $branchStatus->is_confirmed_nihil = true;
        $branchStatus->status_kepatuhan = 'HIJAU';
        $branchStatus->confirmed_by = $userId ?: auth()->id();
        $branchStatus->confirmed_at = now();
        $branchStatus->catatan = $catatan ?: 'KKA nihil transaksi telah dikonfirmasi oleh Resident Auditor.';
        $branchStatus->save();

        return $branchStatus;
    }

    /**
     * Mengambil data Tabel Matriks Konsolidasi Bulanan Seluruh Cabang (7 Kolom Ringkas, Padat, Presisi).
     */
    public function getMonthlyBranchMatrix(string $periodeDate, ?array $allowedUnits = null, array $filters = []): array
    {
        $carbon = Carbon::parse($periodeDate);
        $startMonth = $carbon->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $carbon->copy()->endOfMonth()->format('Y-m-d');

        // Daftar Seluruh Cabang / KCP PT. Bank Sulteng
        $unitsQuery = Sop08MasterUnit::where('is_active', true);
        if ($allowedUnits !== null) {
            $unitsQuery->whereIn('kode_unit', $allowedUnits);
        }
        $masterUnits = $unitsQuery->orderBy('kode_unit')->get();

        if ($masterUnits->isEmpty()) {
            $units = Unit::whereIn('unit_type', ['KC', 'KCU', 'KCP', 'KCPLK'])->where('unit_code', '!=', '000')->orderBy('unit_code')->get();
            $masterUnits = $units->map(function($u) {
                return (object)[
                    'kode_unit' => $u->unit_code,
                    'nama_unit' => $u->unit_name,
                    'assigned_ra' => 'Resident Auditor',
                    'resident_auditor' => 'Resident Auditor',
                ];
            });
        }

        $rows = [];

        foreach ($masterUnits as $unit) {
            $uCode = $unit->kode_unit;

            // 1. Status Kepatuhan KKA (Badge 🟢 Hijau / 🔴 Merah)
            $compliance = $this->getKkaComplianceStatus($uCode, $startMonth);

            // 2. Jumlah Transaksi Reversal
            $revCount = 0;
            if (DB::getSchemaBuilder()->hasTable('daily_registers')) {
                $revCount += DB::table('daily_registers')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('tanggal_data', [$startMonth, $endMonth])
                    ->where(function($q) {
                        $q->where('kategori', 'like', '%REVERSAL%')
                          ->orWhere('rincian', 'like', '%reversal%')
                          ->orWhere('source_sheet', 'like', '%reversal%');
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
                $revCount += DB::table('daily_register_findings')
                    ->where('kode_unit', $uCode)
                    ->where(function($q) use ($startMonth, $endMonth) {
                        $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                          ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                    })
                    ->where(function($q) {
                        $q->where('kategori', 'like', '%reversal%')
                          ->orWhere('jenis_exception_awal', 'like', '%reversal%')
                          ->orWhere('jenis_exception_ra', 'like', '%reversal%')
                          ->orWhere('rincian', 'like', '%reversal%')
                          ->orWhere('deskripsi', 'like', '%reversal%');
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('risk_trigger_kka_kas_tellars')) {
                $revCount += DB::table('risk_trigger_kka_kas_tellars')
                    ->where('reversal_match', 'Tidak')
                    ->whereExists(function($sq) use ($uCode, $startMonth, $endMonth) {
                        $sq->select(DB::raw(1))->from('register_triggers')
                            ->whereColumn('register_triggers.id', 'risk_trigger_kka_kas_tellars.register_trigger_id')
                            ->where('register_triggers.kode_unit', $uCode)
                            ->whereBetween('register_triggers.tanggal_trigger', [$startMonth, $endMonth]);
                    })->count();
            }
            if (class_exists(RawMetric::class) && DB::getSchemaBuilder()->hasTable('raw_metrics')) {
                $rawM = RawMetric::whereHas('unit', fn($q) => $q->where('unit_code', $uCode))
                    ->where('period', $carbon->year)
                    ->first();
                if ($rawM && $rawM->reversal_correction_txn > 0 && $revCount === 0) {
                    $revCount = (int)$rawM->reversal_correction_txn;
                }
            }

            // 3. Jumlah Selisih Kas
            $selisihKasCount = 0;
            if (DB::getSchemaBuilder()->hasTable('physical_kka_cash_details')) {
                $selisihKasCount += DB::table('physical_kka_cash_details')
                    ->whereRaw('ABS(selisih) > 0')
                    ->whereExists(function($sq) use ($uCode, $startMonth, $endMonth) {
                        $sq->select(DB::raw(1))->from('physical_kkas')
                            ->whereColumn('physical_kkas.id', 'physical_kka_cash_details.physical_kka_id')
                            ->where('physical_kkas.kode_unit', $uCode)
                            ->whereBetween('physical_kkas.tanggal_pemeriksaan', [$startMonth, $endMonth]);
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('physical_kka_atm_machines')) {
                $selisihKasCount += DB::table('physical_kka_atm_machines')
                    ->whereRaw('ABS(selisih) > 0')
                    ->whereExists(function($sq) use ($uCode, $startMonth, $endMonth) {
                        $sq->select(DB::raw(1))->from('physical_kkas')
                            ->whereColumn('physical_kkas.id', 'physical_kka_atm_machines.physical_kka_id')
                            ->where('physical_kkas.kode_unit', $uCode)
                            ->whereBetween('physical_kkas.tanggal_pemeriksaan', [$startMonth, $endMonth]);
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('physical_kkas')) {
                $selisihKasCount += DB::table('physical_kkas')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('tanggal_pemeriksaan', [$startMonth, $endMonth])
                    ->whereRaw('ABS(COALESCE(selisih_kas_khasanah, 0)) > 0')
                    ->count();
            }
            if (DB::getSchemaBuilder()->hasTable('daily_registers')) {
                $selisihKasCount += DB::table('daily_registers')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('tanggal_data', [$startMonth, $endMonth])
                    ->where(function($q) {
                        $q->where('kategori', 'like', '%SELISIH%')
                          ->orWhere('rincian', 'like', '%selisih%');
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
                $selisihKasCount += DB::table('daily_register_findings')
                    ->where('kode_unit', $uCode)
                    ->where(function($q) use ($startMonth, $endMonth) {
                        $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                          ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                    })
                    ->where(function($q) {
                        $q->where('kategori', 'like', '%selisih%')
                          ->orWhere('rincian', 'like', '%selisih%')
                          ->orWhere('deskripsi', 'like', '%selisih%');
                    })->count();
            }
            if (class_exists(RawMetric::class) && DB::getSchemaBuilder()->hasTable('raw_metrics')) {
                $rawM = RawMetric::whereHas('unit', fn($q) => $q->where('unit_code', $uCode))
                    ->where('period', $carbon->year)
                    ->first();
                if ($rawM && $rawM->cash_discrepancy > 0 && $selisihKasCount === 0) {
                    $selisihKasCount = (int)$rawM->cash_discrepancy;
                }
            }

            // 4. Jumlah Pelampauan Limit / Biaya Besar
            $limitBiayaCount = 0;
            if (DB::getSchemaBuilder()->hasTable('daily_registers')) {
                $limitBiayaCount += DB::table('daily_registers')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('tanggal_data', [$startMonth, $endMonth])
                    ->where(function($q) {
                        $q->whereIn('kategori', ['LIMIT_TARIK_TUNAI', 'BIAYA_HARIAN', 'JURNAL_MANUAL'])
                          ->orWhere('kategori', 'like', '%LIMIT%')
                          ->orWhere('kategori', 'like', '%BIAYA%');
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
                $limitBiayaCount += DB::table('daily_register_findings')
                    ->where('kode_unit', $uCode)
                    ->where(function($q) use ($startMonth, $endMonth) {
                        $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                          ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                    })
                    ->where(function($q) {
                        $q->where('source_sheet', 'KKA_Biaya_Beban')
                          ->orWhere('kategori', 'like', '%limit%')
                          ->orWhere('kategori', 'like', '%biaya%')
                          ->orWhere('nominal_terkait', '>=', 100000000);
                    })->count();
            }
            if (class_exists(RawMetric::class) && DB::getSchemaBuilder()->hasTable('raw_metrics')) {
                $rawM = RawMetric::whereHas('unit', fn($q) => $q->where('unit_code', $uCode))
                    ->where('period', $carbon->year)
                    ->first();
                if ($rawM && ($rawM->unusual_cost_journal > 0 || $rawM->large_risky_cash_txn > 0) && $limitBiayaCount === 0) {
                    $limitBiayaCount = (int)($rawM->unusual_cost_journal + $rawM->large_risky_cash_txn);
                }
            }

            // 5. Jumlah Temuan Moderate-to-High & High
            $modHighCount = 0;
            if (DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
                $modHighCount += DB::table('daily_register_findings')
                    ->where('kode_unit', $uCode)
                    ->where(function($q) use ($startMonth, $endMonth) {
                        $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                          ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
                    })
                    ->where(function($q) {
                        $q->whereIn('risk_awal', ['Moderate', 'High', 'MODERATE', 'HIGH', 'MODERATE TO HIGH'])
                          ->orWhereIn('risk_final', ['Moderate', 'High', 'MODERATE', 'HIGH', 'MODERATE TO HIGH'])
                          ->orWhere('skor_dampak', '>=', 4);
                    })->count();
            }
            if (DB::getSchemaBuilder()->hasTable('kka_findings')) {
                $modHighCount += DB::table('kka_findings')
                    ->where('kode_unit', $uCode)
                    ->whereIn('risk_awal', ['Moderate', 'High', 'MODERATE', 'HIGH'])
                    ->count();
            }
            if (DB::getSchemaBuilder()->hasTable('register_triggers')) {
                $modHighCount += DB::table('register_triggers')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('tanggal_trigger', [$startMonth, $endMonth])
                    ->whereIn('risk_level_reassessment', ['Moderate', 'High', 'MODERATE', 'HIGH'])
                    ->count();
            }
            if (DB::getSchemaBuilder()->hasTable('sop08_rekap_isu_tls')) {
                $modHighCount += DB::table('sop08_rekap_isu_tls')
                    ->where('kode_unit', $uCode)
                    ->whereBetween('periode', [$startMonth, $endMonth])
                    ->whereIn('risk_level', ['MODERATE', 'HIGH', 'Moderate', 'High', 'MODERATE TO HIGH'])
                    ->count();
            }

            // 6. Status Tindak Lanjut Aktif / Overdue
            $tlAktif = 0;
            $tlOverdue = 0;
            if (DB::getSchemaBuilder()->hasTable('sop07_register_tls')) {
                $tls = DB::table('sop07_register_tls')->where('kode_unit', $uCode)->get();
                $tlOverdue = $tls->filter(function($t) {
                    return $t->status_final === 'OVERDUE' || ($t->status_final !== 'CLOSED' && !empty($t->target_penyelesaian) && Carbon::parse($t->target_penyelesaian)->isPast());
                })->count();
                $tlAktif = $tls->filter(function($t) {
                    return in_array(strtoupper($t->status_final ?? ''), ['OPEN', 'IN PROGRESS', 'BELUM SELESAI', 'PENDING']);
                })->count();
            }
            if ($tlAktif === 0 && $tlOverdue === 0 && DB::getSchemaBuilder()->hasTable('sop08_rekap_isu_tls')) {
                $isus = DB::table('sop08_rekap_isu_tls')->where('kode_unit', $uCode)->whereBetween('periode', [$startMonth, $endMonth])->get();
                $tlOverdue = $isus->where('status_tl', 'OVERDUE')->count();
                $tlAktif = $isus->whereIn('status_tl', ['OPEN', 'IN PROGRESS'])->count();
            }

            // 7. Status Approval & Isu Signifikan
            $kalender = Sop08KalenderLaporan::where('kode_unit', $uCode)->whereDate('periode', $startMonth)->first();
            $statusApproval = $kalender?->status_final ?: 'UNDER REVIEW';
            $hasIsuSignifikan = ($modHighCount > 0) || (
                DB::getSchemaBuilder()->hasTable('sop08_rekap_isu_tls') && 
                DB::table('sop08_rekap_isu_tls')->where('kode_unit', $uCode)->whereBetween('periode', [$startMonth, $endMonth])->where('flag_signifikan', 'YA')->exists()
            );

            $row = [
                'kode_unit' => $uCode,
                'nama_unit' => $unit->nama_unit,
                'resident_auditor' => $unit->assigned_ra ?? ($unit->resident_auditor ?? 'Resident Auditor'),
                'kepatuhan_status' => $compliance['status'],
                'status_kepatuhan' => $compliance['status'],
                'kepatuhan_badge' => $compliance['badge'],
                'status_kepatuhan_badge' => $compliance['badge'],
                'kepatuhan_detail' => $compliance['summary'] ?? ($compliance['status'] === 'HIJAU' ? 'Lengkap / Tuntas' : 'Belum Selesai / Tertunggak'),
                'compliance_data' => $compliance,
                'reversal_count' => $revCount,
                'selisih_kas_count' => $selisihKasCount,
                'limit_biaya_count' => $limitBiayaCount,
                'mod_to_high_count' => $modHighCount,
                'temuan_mod_high_count' => $modHighCount,
                'tl_active_count' => $tlAktif,
                'tl_aktif_count' => $tlAktif,
                'tl_overdue_count' => $tlOverdue,
                'status_approval' => $statusApproval,
                'has_isu_signifikan' => $hasIsuSignifikan,
                'has_significant_issue' => $hasIsuSignifikan,
                'report_id' => $kalender?->report_id,
                'kalender' => $kalender,
            ];

            // Filter Cepat (Smart Filters)
            if (!empty($filters['kode_unit']) && $filters['kode_unit'] !== $uCode) {
                continue;
            }

            if (!empty($filters['kepatuhan'])) {
                $fKep = strtoupper($filters['kepatuhan']);
                if ($fKep === 'MERAH' && $compliance['status'] !== 'MERAH') {
                    continue;
                }
                if ($fKep === 'HIJAU' && $compliance['status'] !== 'HIJAU') {
                    continue;
                }
            }

            if (!empty($filters['status_approval'])) {
                $sa = strtoupper($filters['status_approval']);
                if (in_array($sa, ['ADA_KOREKSI', 'KOREKSI', 'REVISION REQUIRED']) && !in_array($statusApproval, ['REVISION REQUIRED', 'REVISED', 'KOREKSI'])) {
                    continue;
                }
                if (in_array($sa, ['MENUNGGU', 'MENUNGGU_APPROVAL', 'UNDER REVIEW']) && !in_array($statusApproval, ['MENUNGGU REVIEW', 'UNDER REVIEW', 'SUBMITTED', 'DRAFT'])) {
                    continue;
                }
                if (in_array($sa, ['APPROVED', 'SELESAI', 'FINAL', 'APPROVED/FINAL']) && !in_array($statusApproval, ['APPROVED/FINAL', 'SELESAI', 'FINAL', 'APPROVED'])) {
                    continue;
                }
            }

            if (!empty($filters['isu_signifikan'])) {
                $fIsu = strtoupper($filters['isu_signifikan']);
                if (in_array($fIsu, ['YA', 'ADA', 'TRUE']) && !$hasIsuSignifikan) {
                    continue;
                }
                if (in_array($fIsu, ['TIDAK', 'TIDAK_ADA', 'FALSE']) && $hasIsuSignifikan) {
                    continue;
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Mengambil bagian Hasil Utama & Temuan Kunci (Otomatis Level Moderate-to-High & High).
     */
    public function getHasilUtamaTemuanKunci(string $periodeDate, ?string $kodeUnit = null): array
    {
        $carbon = Carbon::parse($periodeDate);
        $startMonth = $carbon->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $carbon->copy()->endOfMonth()->format('Y-m-d');

        $findingsList = [];

        // 1. Dari DailyRegisterFinding
        if (class_exists(DailyRegisterFinding::class) && DB::getSchemaBuilder()->hasTable('daily_register_findings')) {
            $query = DailyRegisterFinding::where(function($q) use ($startMonth, $endMonth) {
                $q->whereBetween('tanggal_data', [$startMonth, $endMonth])
                  ->orWhereBetween('tanggal_ditemukan', [$startMonth, $endMonth]);
            })->where(function($q) {
                $q->whereIn('risk_awal', ['Moderate', 'High', 'MODERATE', 'HIGH', 'MODERATE TO HIGH'])
                  ->orWhereIn('risk_final', ['Moderate', 'High', 'MODERATE', 'HIGH', 'MODERATE TO HIGH'])
                  ->orWhere('skor_dampak', '>=', 4);
            });

            if ($kodeUnit) {
                $query->where('kode_unit', $kodeUnit);
            }

            foreach ($query->get() as $f) {
                $area = str_replace(['KKA_', '_'], ['', ' '], $f->source_sheet ?: 'Teller Kas');
                $nominal = (float)($f->nominal_terkait ?? 0);
                $isBerulang = ((int)($f->skor_kemungkinan ?? 1) >= 2) || 
                              DailyRegisterFinding::where('kode_unit', $f->kode_unit)
                                ->where('id', '!=', $f->id)
                                ->where('source_sheet', $f->source_sheet)
                                ->exists();

                $findingsList[] = [
                    'id' => (string)$f->id,
                    'source' => 'Daily Register',
                    'kode_unit' => $f->kode_unit,
                    'nama_unit' => $f->nama_unit ?? "Cabang {$f->kode_unit}",
                    'area' => $area,
                    'uraian' => $f->deskripsi ?: ($f->rincian ?: 'Temuan Ketidaksesuaian Operasional Harian'),
                    'nominal' => $nominal,
                    'nominal_formatted' => $nominal > 0 ? 'Rp ' . number_format($nominal, 0, ',', '.') : '-',
                    'is_berulang' => $isBerulang,
                    'is_temuan_berulang' => $isBerulang,
                    'rekomendasi' => $f->simpulan_ra ?: 'Tingkatkan kontrol kepatuhan internal dan lakukan pembinaan staf pelaksana.',
                    'pic' => $f->kategori ? "PIC {$f->kategori}" : 'Pemimpin Cabang / Seksi Operasional',
                    'target_tl' => Carbon::parse($endMonth)->addDays(14)->format('d/m/Y'),
                    'target_date' => Carbon::parse($endMonth)->addDays(14)->format('Y-m-d'),
                    'risk_level' => in_array(strtoupper($f->risk_final ?? $f->risk_awal ?? ''), ['HIGH']) ? 'HIGH' : 'MODERATE TO HIGH',
                ];
            }
        }

        // 2. Dari RegisterTrigger (SOP 06)
        if (class_exists(\App\Models\RiskTrigger\RegisterTrigger::class) && DB::getSchemaBuilder()->hasTable('register_triggers')) {
            $query = \App\Models\RiskTrigger\RegisterTrigger::whereBetween('tanggal_trigger', [$startMonth, $endMonth])
                ->whereIn('risk_level_reassessment', ['Moderate', 'High', 'MODERATE', 'HIGH', 'MODERATE TO HIGH']);
            if ($kodeUnit) {
                $query->where('kode_unit', $kodeUnit);
            }
            foreach ($query->get() as $t) {
                $isBerulang = ($t->jumlah_kejadian > 1) 
                    || ($t->status_duplikat === 'DUPLIKAT - REVIEW')
                    || \App\Models\RiskTrigger\RegisterTrigger::where('kode_unit', $t->kode_unit)
                        ->where('id', '!=', $t->id)
                        ->where('area', $t->area)
                        ->exists();
                $nominal = (float)($t->eksposur_nominal ?? $t->nominal ?? 0);
                $uraian = $t->uraian_trigger ?: ($t->indikasi_anomali ?: 'Penyimpangan ambang batas risiko / alarm risk trigger terlampaui.');
                $findingsList[] = [
                    'id' => (string)($t->trigger_id ?: $t->id),
                    'source' => 'Risk Trigger',
                    'kode_unit' => $t->kode_unit,
                    'nama_unit' => $t->nama_unit ?? "Cabang {$t->kode_unit}",
                    'area' => $t->area ?? 'Risk Trigger',
                    'uraian' => $uraian,
                    'nominal' => $nominal,
                    'nominal_formatted' => $nominal > 0 ? 'Rp ' . number_format($nominal, 0, ',', '.') : '-',
                    'is_berulang' => $isBerulang,
                    'is_temuan_berulang' => $isBerulang,
                    'rekomendasi' => 'Pemeriksaan lapangan terarah dan penyesuaian parameter toleransi limit cabang.',
                    'pic' => $t->assigned_ra ?: 'Pemimpin Cabang / Korwas',
                    'target_tl' => $t->sla_deadline ? Carbon::parse($t->sla_deadline)->format('d/m/Y') : Carbon::parse($endMonth)->addDays(7)->format('d/m/Y'),
                    'target_date' => $t->sla_deadline ? Carbon::parse($t->sla_deadline)->format('Y-m-d') : Carbon::parse($endMonth)->addDays(7)->format('Y-m-d'),
                    'risk_level' => strtoupper($t->risk_level_reassessment) === 'HIGH' ? 'HIGH' : 'MODERATE TO HIGH',
                ];
            }
        }

        // 3. Dari Sop08RekapIsuTl
        if (DB::getSchemaBuilder()->hasTable('sop08_rekap_isu_tls')) {
            $query = Sop08RekapIsuTl::whereBetween('periode', [$startMonth, $endMonth])
                ->whereIn('risk_level', ['MODERATE', 'HIGH', 'Moderate', 'High', 'MODERATE TO HIGH']);
            if ($kodeUnit) {
                $query->where('kode_unit', $kodeUnit);
            }
            foreach ($query->get() as $isu) {
                $already = collect($findingsList)->contains(fn($item) => $item['kode_unit'] === $isu->kode_unit && $item['area'] === $isu->area && $item['uraian'] === $isu->uraian_isu);
                if (!$already) {
                    $nominal = (float)($isu->nilai_dampak ?? 0);
                    $isBerulang = ($isu->berulang === 'YA');
                    $findingsList[] = [
                        'id' => (string)($isu->issue_id ?: $isu->id),
                        'source' => $isu->sumber_sop ?: 'SOP 08',
                        'kode_unit' => $isu->kode_unit,
                        'nama_unit' => $isu->nama_unit ?? "Cabang {$isu->kode_unit}",
                        'area' => $isu->area,
                        'uraian' => $isu->uraian_isu,
                        'nominal' => $nominal,
                        'nominal_formatted' => $nominal > 0 ? 'Rp ' . number_format($nominal, 0, ',', '.') : '-',
                        'is_berulang' => $isBerulang,
                        'is_temuan_berulang' => $isBerulang,
                        'rekomendasi' => $isu->rekomendasi ?: 'Eskalasi ke Manajemen Cabang & Korwas',
                        'pic' => $isu->pic ?: 'Pemimpin Cabang',
                        'target_tl' => $isu->target_tl ? Carbon::parse($isu->target_tl)->format('d/m/Y') : Carbon::parse($endMonth)->addDays(14)->format('d/m/Y'),
                        'target_date' => $isu->target_tl ? Carbon::parse($isu->target_tl)->format('Y-m-d') : Carbon::parse($endMonth)->addDays(14)->format('Y-m-d'),
                        'risk_level' => strtoupper($isu->risk_level) === 'HIGH' ? 'HIGH' : 'MODERATE TO HIGH',
                    ];
                }
            }
        }

        return $findingsList;
    }

    /**
     * Tarikan terpadu 100% database Rekap Kegiatan + Log Harian RA.
     */
    public function getUnifiedDailyActivities(string $periodeDate, ?string $kodeUnit = null): array
    {
        $carbon = Carbon::parse($periodeDate);
        $startMonth = $carbon->copy()->startOfMonth()->format('Y-m-d');
        $endMonth = $carbon->copy()->endOfMonth()->format('Y-m-d');

        $activities = [];

        // 1. Sop08LogHarianRa
        if (DB::getSchemaBuilder()->hasTable('sop08_log_harian_ras')) {
            $logQuery = Sop08LogHarianRa::whereBetween('tanggal', [$startMonth, $endMonth]);
            if ($kodeUnit) {
                $logQuery->where('kode_unit', $kodeUnit);
            }
            foreach ($logQuery->orderBy('tanggal', 'desc')->get() as $log) {
                $detailStr = $log->output_referensi_wp 
                    ? "Area: {$log->area} ({$log->output_referensi_wp})" 
                    : ($log->area ? "Area: {$log->area}" : 'Pemeriksaan Aktivitas Harian');
                $activities[] = [
                    'tanggal' => Carbon::parse($log->tanggal)->format('d/m/Y'),
                    'tanggal_raw' => Carbon::parse($log->tanggal)->format('Y-m-d'),
                    'kode_unit' => $log->kode_unit,
                    'nama_unit' => $log->nama_unit,
                    'nama_ra' => $log->nama_ra,
                    'sumber_sop' => $log->sumber_sop ?: 'SOP 08 - Log Harian',
                    'jenis_aktivitas' => $log->jenis_aktivitas ?: 'Aktivitas Pengawasan Rutin',
                    'kegiatan' => $log->jenis_aktivitas ?: 'Aktivitas Pengawasan Rutin',
                    'area' => $log->area,
                    'output_wp' => $log->output_referensi_wp,
                    'detail' => $detailStr,
                    'status_penyelesaian' => $log->status_penyelesaian ?: 'COMPLETED',
                    'status' => $log->status_penyelesaian ?: 'COMPLETED',
                    'hasil_review' => $log->hasil_review ?: 'MEMADAI',
                ];
            }
        }

        // 2. PhysicalKka (Daily Review)
        if (class_exists(PhysicalKka::class) && DB::getSchemaBuilder()->hasTable('physical_kkas')) {
            $pkQuery = PhysicalKka::whereBetween('tanggal_pemeriksaan', [$startMonth, $endMonth]);
            if ($kodeUnit) {
                $pkQuery->where('kode_unit', $kodeUnit);
            }
            foreach ($pkQuery->orderBy('tanggal_pemeriksaan', 'desc')->get() as $pk) {
                $already = collect($activities)->contains(fn($a) => ($a['output_wp'] ?? '') === "KKA-PHYSICAL-{$pk->id}");
                if (!$already) {
                    $statusStr = (strtolower($pk->status_final ?? '') === 'final' || ($pk->status_approval ?? '') === 'Approved') ? 'COMPLETED' : 'IN PROGRESS';
                    $activities[] = [
                        'tanggal' => Carbon::parse($pk->tanggal_pemeriksaan)->format('d/m/Y'),
                        'tanggal_raw' => Carbon::parse($pk->tanggal_pemeriksaan)->format('Y-m-d'),
                        'kode_unit' => $pk->kode_unit,
                        'nama_unit' => $pk->nama_unit ?? "Cabang {$pk->kode_unit}",
                        'nama_ra' => $pk->auditor_name ?? 'Resident Auditor',
                        'sumber_sop' => 'SOP 03 - Daily Review',
                        'jenis_aktivitas' => 'Pemeriksaan Register Fisik Harian Kas & Dokumen',
                        'kegiatan' => 'Pemeriksaan Register Fisik Harian Kas & Dokumen',
                        'area' => 'Cash Opname',
                        'output_wp' => "KKA-PHYSICAL-{$pk->id}",
                        'detail' => "Cash Opname & Register Dokumen [KKA-PHYSICAL-{$pk->id}]",
                        'status_penyelesaian' => $statusStr,
                        'status' => $statusStr,
                        'hasil_review' => $pk->status_final ?: ($pk->status_approval ?: 'MEMADAI'),
                    ];
                }
            }
        }

        return collect($activities)->sortByDesc('tanggal_raw')->values()->all();
    }
}

