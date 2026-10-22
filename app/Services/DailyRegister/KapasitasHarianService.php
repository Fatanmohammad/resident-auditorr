<?php

namespace App\Services\DailyRegister;

use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\KontrolMingguan;
use Carbon\Carbon;

class KapasitasHarianService
{
    /**
     * Bobot Menit per Sampel Transaksi sesuai SOP 04.2 / Parameter_Sampling_SOP02 Baris 33
     */
    public const BOBOT_MENIT = [
        'KKA_Teller_Kas'   => 12, // 12 menit / sampel
        'KKA_DPK_Layanan'  => 15, // 15 menit / sampel
        'KKA_Kredit'       => 40, // 40 menit / sampel
        'KKA_Biaya_Beban'  => 25, // 25 menit / sampel
        'KKA_Kartu_ATM'    => 12, // 12 menit / sampel
        'KKA_Pengaduan'    => 30, // 30 menit / sampel
    ];

    /**
     * Hitung Kapasitas Menit Harian Auditor & Pemeriksaan Mingguan
     */
    public function hitungKapasitas(string $kodeUnit, $date = null): array
    {
        // 0. Bersihkan query params jika ada (misal '003?cabang_id=6')
        if (str_contains($kodeUnit, '?')) {
            $kodeUnit = explode('?', $kodeUnit)[0];
        }

        // Resolusi unit codes: cari apakah kodeUnit ini adalah unit_code (e.g. '003') atau kode_cabang (e.g. 'BS-003')
        $targetUnitCodes = [$kodeUnit];
        $cleanCode = str_replace('BS-', '', $kodeUnit);

        $kcUnit = \App\Models\Unit::where('unit_code', $kodeUnit)
            ->orWhere('unit_code', $cleanCode)
            ->first();

        if (!$kcUnit) {
            $cabang = \App\Models\Cabang::where('kode_cabang', $kodeUnit)
                ->orWhere('kode_cabang', 'BS-' . $kodeUnit)
                ->first();
            if ($cabang) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $cabang->id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
            }
        }

        if ($kcUnit) {
            $kodeUnit = $kcUnit->unit_code; // Selalu gunakan unit_code KC standar (e.g. '003')
            $targetUnitCodes = [$kcUnit->unit_code];

            // Sertakan juga kode cabang jika berbeda (e.g. 'BS-003')
            $cabang = \App\Models\Cabang::find($kcUnit->cabang_id);
            if ($cabang && $cabang->kode_cabang && !in_array($cabang->kode_cabang, $targetUnitCodes)) {
                $targetUnitCodes[] = $cabang->kode_cabang;
            }
        }

        $refDate = $date ? Carbon::parse($date) : Carbon::now();
        $todayStr = Carbon::now()->toDateString();
        $reviewDateStr = $refDate->toDateString();

        // 1. Ambil data Kontrol Mingguan minggu ini untuk KC tersebut
        $weeklySchedules = KontrolMingguan::getOrCreateForUnit($kodeUnit, $refDate);

        // Ambil jumlah auditor yang diset untuk unit ini
        $firstItem = $weeklySchedules->first();
        $jumlahAuditor = $firstItem ? (int)$firstItem->jumlah_auditor : 1;
        if ($jumlahAuditor < 1) $jumlahAuditor = 1;

        $menitEfektifPerAuditor = 420; // 7 jam kerja efektif
        $totalMenitHarian = $menitEfektifPerAuditor * $jumlahAuditor;

        // 2. Menit Pemeriksaan Mingguan Hari Ini:
        // Jumlahkan Menit Rencana yang Tanggal Rencana Efektif-nya jatuh pada hari ini
        $scheduledToday = $weeklySchedules->filter(function($item) use ($todayStr, $date) {
            $compareDate = $date ? Carbon::parse($date)->toDateString() : $todayStr;
            $tgl = $item->override_tanggal 
                ? Carbon::parse($item->override_tanggal)->toDateString() 
                : Carbon::parse($item->tanggal_rencana_efektif)->toDateString();
            return $tgl === $compareDate;
        });

        $menitMingguanHariIni = $scheduledToday->sum('menit_rencana');
        $areaMingguanHariIni = $scheduledToday->pluck('nama_area')->toArray();

        // 3. Menit Tersedia untuk Sampling Harian
        $menitTersediaSampling = max(0, $totalMenitHarian - $menitMingguanHariIni);

        // 4. Hitung Sampel Terpilih pada 6 KKA Harian
        // Ambil temuan Moderate & High yang aktif pada tanggal acuan (jika ditentukan)
        $findingsQuery = DailyRegisterFinding::whereIn('kode_unit', $targetUnitCodes)
            ->whereIn('risk_awal', ['Moderate', 'High']);

        if (!empty($date)) {
            $findingsQuery->whereDate('tanggal_data', Carbon::parse($date)->toDateString());
        }

        $sampleCounts = [
            'KKA_Teller_Kas'  => (clone $findingsQuery)->where('source_sheet', 'KKA_Teller_Kas')->count(),
            'KKA_DPK_Layanan' => (clone $findingsQuery)->where('source_sheet', 'KKA_DPK_Layanan')->count(),
            'KKA_Kredit'      => (clone $findingsQuery)->where('source_sheet', 'KKA_Kredit')->count(),
            'KKA_Biaya_Beban' => (clone $findingsQuery)->where('source_sheet', 'KKA_Biaya_Beban')->count(),
            'KKA_Kartu_ATM'   => (clone $findingsQuery)->where('source_sheet', 'KKA_Kartu_ATM')->count(),
            'KKA_Pengaduan'   => (clone $findingsQuery)->where('source_sheet', 'KKA_Pengaduan')->count(),
        ];

        // 5. Estimasi Menit per KKA & Total Estimasi
        $estimasiMenitPerArea = [];
        $totalEstimasiMenitSampel = 0;
        $totalJumlahSampel = 0;

        foreach (self::BOBOT_MENIT as $sheet => $bobot) {
            $count = $sampleCounts[$sheet] ?? 0;
            $menitArea = $count * $bobot;
            $estimasiMenitPerArea[$sheet] = [
                'jumlah_sampel' => $count,
                'bobot_menit'   => $bobot,
                'total_menit'   => $menitArea,
            ];
            $totalEstimasiMenitSampel += $menitArea;
            $totalJumlahSampel += $count;
        }

        // 6. Sisa / (Lebih) Kapasitas
        $sisaKapasitas = $menitTersediaSampling - $totalEstimasiMenitSampel;

        // 7. Status Kapasitas
        if ($sisaKapasitas < 0) {
            $statusKapasitas = 'OVER CAPACITY - TAMBAH KAPASITAS/ESKALASI';
            $statusBadgeClass = 'danger';
            $statusBadgeColor = '#ef4444';
        } else {
            $statusKapasitas = 'DALAM KAPASITAS';
            $statusBadgeClass = 'success';
            $statusBadgeColor = '#10b981';
        }

        return [
            'kode_unit'                   => $kodeUnit,
            'nama_cabang'                 => $kcUnit ? $kcUnit->unit_name : $kodeUnit,
            'tanggal_review'              => $todayStr,
            'menit_per_auditor'           => $menitEfektifPerAuditor,
            'jumlah_auditor'              => $jumlahAuditor,
            'total_menit_harian'          => $totalMenitHarian,
            'menit_mingguan_hari_ini'     => $menitMingguanHariIni,
            'area_mingguan_hari_ini'      => $areaMingguanHariIni,
            'menit_tersedia_sampling'     => $menitTersediaSampling,
            'total_jumlah_sampel'         => $totalJumlahSampel,
            'estimasi_menit_sampel'       => $totalEstimasiMenitSampel,
            'estimasi_menit_per_area'     => $estimasiMenitPerArea,
            'sisa_kapasitas'              => $sisaKapasitas,
            'status_kapasitas'            => $statusKapasitas,
            'status_badge_class'          => $statusBadgeClass,
            'status_badge_color'          => $statusBadgeColor,
            'schedules'                   => $weeklySchedules,
            'start_date'                  => $weeklySchedules->first()?->start_date?->format('Y-m-d'),
            'end_date'                    => $weeklySchedules->first()?->end_date?->format('Y-m-d'),
        ];
    }
}
