<?php

namespace App\Services\DailyRegister;

use App\Models\DailyRegister\DailyRegisterFinding;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\RegisterKlarifikasi;
use App\Models\DailyRegister\RegisterTemuan;
use App\Models\DailyRegister\KontrolMingguan;
use App\Models\Unit;
use App\Models\Cabang;
use Carbon\Carbon;

class KkaControlIntegrasiService
{
    /**
     * Hitung Rekapitulasi 11 Area KKA Control & Integrasi (SOP 04.2)
     */
    public function hitungControlIntegrasi(string $kodeUnit, ?string $date = null): array
    {
        // 0. Bersihkan query params jika ada
        if (str_contains($kodeUnit, '?')) {
            $kodeUnit = explode('?', $kodeUnit)[0];
        }

        // Resolusi unit codes: samakan unit_code (e.g. '003') dan kode_cabang (e.g. 'BS-003')
        $targetUnitCodes = [$kodeUnit];
        $cleanCode = str_replace('BS-', '', $kodeUnit);

        $kcUnit = Unit::where('unit_code', $kodeUnit)
            ->orWhere('unit_code', $cleanCode)
            ->first();

        if (!$kcUnit) {
            $cabang = Cabang::where('kode_cabang', $kodeUnit)
                ->orWhere('kode_cabang', 'BS-' . $kodeUnit)
                ->first();
            if ($cabang) {
                $kcUnit = Unit::where('cabang_id', $cabang->id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
            }
        }

        if ($kcUnit) {
            $kodeUnit = $kcUnit->unit_code;
            $targetUnitCodes = [$kcUnit->unit_code];

            $cabang = Cabang::find($kcUnit->cabang_id);
            if ($cabang && $cabang->kode_cabang && !in_array($cabang->kode_cabang, $targetUnitCodes)) {
                $targetUnitCodes[] = $cabang->kode_cabang;
            }
        }

        $refDate = $date ? Carbon::parse($date) : Carbon::now();
        $todayStr = $refDate->toDateString();

        // 1. DAFTAR DEFINISI 11 AREA SESUAI SOP 04.2
        $areaDefinitions = [
            // Baris 1-6: Sampling Harian
            1 => [
                'no'          => 1,
                'area_review' => 'Teller/Kas',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Teller_Kas',
                'tahap_build' => '4.1 – SELESAI',
                'is_sampling' => true,
            ],
            2 => [
                'no'          => 2,
                'area_review' => 'DPK & Layanan Nasabah',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_DPK_Layanan',
                'tahap_build' => '4.2 – SELESAI',
                'is_sampling' => true,
            ],
            3 => [
                'no'          => 3,
                'area_review' => 'Kredit',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Kredit',
                'tahap_build' => '4.3 – SELESAI',
                'is_sampling' => true,
            ],
            4 => [
                'no'          => 4,
                'area_review' => 'Biaya/Beban',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Biaya_Beban',
                'tahap_build' => '4.4 – SELESAI',
                'is_sampling' => true,
            ],
            5 => [
                'no'          => 5,
                'area_review' => 'Kartu ATM',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Kartu_ATM',
                'tahap_build' => '4.5 – SELESAI',
                'is_sampling' => true,
            ],
            6 => [
                'no'          => 6,
                'area_review' => 'Pengaduan Nasabah',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Pengaduan',
                'tahap_build' => '4.6 – SELESAI',
                'is_sampling' => true,
            ],
            // Baris 7-11: Pemeriksaan Fisik Mingguan
            7 => [
                'no'          => 7,
                'area_review' => 'Cash Opname Khasanah & Kaset ATM',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Cash_Opname',
                'tahap_build' => '4.7 – SELESAI',
                'is_sampling' => false,
                'detail_model' => PhysicalKkaCashDetail::class,
                'default_count' => 23,
            ],
            8 => [
                'no'          => 8,
                'area_review' => 'Observasi Lingkungan & Gedung',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Observasi_Lingkungan',
                'tahap_build' => '4.8 – SELESAI',
                'is_sampling' => false,
                'detail_model' => PhysicalKkaObsDetail::class,
                'default_count' => 24,
            ],
            9 => [
                'no'          => 9,
                'area_review' => 'Rekonsiliasi Stok Cek & BG',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Rekon_Stok_Cek_BG',
                'tahap_build' => '4.9 – SELESAI',
                'is_sampling' => false,
                'detail_model' => PhysicalKkaStokDetail::class,
                'default_count' => 10,
            ],
            10 => [
                'no'          => 10,
                'area_review' => 'Rekonsiliasi Barang Cetakan',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Rekon_Barang_Cetakan',
                'tahap_build' => '4.10 – SELESAI',
                'is_sampling' => false,
                'detail_model' => PhysicalKkaBarangCetakanDetail::class,
                'default_count' => 10,
            ],
            11 => [
                'no'          => 11,
                'area_review' => 'Rekonsiliasi Kartu ATM',
                'dalam_lingkup' => 'Ya',
                'nama_sheet'  => 'KKA_Rekon_Kartu_ATM',
                'tahap_build' => '4.11 – SELESAI',
                'is_sampling' => false,
                'detail_model' => PhysicalKkaKartuAtmDetail::class,
                'default_count' => 10,
            ],
        ];

        // 2. AMBIL DATA TEMUAN SAMPLING 6 KKA
        $allFindingsQuery = DailyRegisterFinding::whereIn('kode_unit', $targetUnitCodes);
        if ($date) {
            $allFindingsQuery->whereDate('tanggal_data', $todayStr);
        }
        $findingsCollection = $allFindingsQuery->get();

        // 3. AMBIL DATA PHYSICAL KKA MINGGU INI UNTUK KC TERKAIT
        $latestPhysicalKka = PhysicalKka::whereIn('kode_unit', $targetUnitCodes)
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        // 4. HITUNG 11 BARIS AREA REVIEW
        $rows = [];
        $kkaTersediaCount = 0;
        $kkaSelesaiCount = 0;
        $totalKlarifikasiPendingAll = 0;
        $totalExceptionAll = 0;

        foreach ($areaDefinitions as $idx => $def) {
            $namaSheet = $def['nama_sheet'];
            $isSampling = $def['is_sampling'];

            if ($isSampling) {
                // --- SAMPLING HARIAN (Baris 1-6) ---
                $sheetFindings = $findingsCollection->where('source_sheet', $namaSheet);
                
                // Populasi & Kandidat
                $populasiEligible = $sheetFindings->count();
                $kandidatRisiko = $sheetFindings->whereIn('risk_awal', ['Moderate', 'High'])->count();
                $sampelTerpilih = $kandidatRisiko; // Sampel KKA harian diambil dari Moderate + High
                
                // Sampel Diuji (yang sudah memiliki kesimpulan / hasil uji atau direview)
                $sampelDiuji = $sheetFindings->whereIn('risk_awal', ['Moderate', 'High'])
                    ->filter(function($f) {
                        return !empty($f->simpulan_ra) 
                            || !empty($f->hasil_uji) 
                            || in_array($f->status_review, ['Approved', 'Revisi', 'Selesai']);
                    })->count();

                // Exception
                $exceptionCount = $sheetFindings->whereIn('risk_awal', ['Moderate', 'High'])
                    ->filter(function($f) {
                        return !empty($f->jenis_exception_ra) 
                            || $f->perlu_onsite === 'Ya' 
                            || $f->status_klarifikasi === 'Belum Selesai';
                    })->count();

                // Tinggi / Kritis
                $tinggiKritis = $sheetFindings->where('risk_awal', 'High')->count();

                // Klarifikasi Pending
                $klarifikasiPending = $sheetFindings->whereIn('risk_awal', ['Moderate', 'High'])
                    ->filter(function($f) {
                        return $f->status_klarifikasi === 'Belum Selesai' && $f->perlu_onsite === 'Ya';
                    })->count();

                // Komponen Valid
                $komponenValid = $sampelDiuji;

                // Status KKA
                if ($sampelTerpilih === 0 || $sampelDiuji === 0) {
                    $statusKka = 'BELUM DIUJI';
                    $statusBadge = 'secondary';
                } elseif ($sampelDiuji < $sampelTerpilih) {
                    $statusKka = 'DALAM PENGUJIAN';
                    $statusBadge = 'warning';
                } else {
                    $statusKka = 'SELESAI';
                    $statusBadge = 'success';
                }

                // Kontrol Khas Area (Formula SOP 04.2)
                switch ($namaSheet) {
                    case 'KKA_Teller_Kas':
                        $kontrolKhas = "Opname valid {$sampelDiuji} | Kontrol valid " . min($sampelDiuji, 15) . "/15";
                        break;
                    case 'KKA_DPK_Layanan':
                        $kontrolKhas = "Perubahan/status valid {$sampelDiuji} | Kontrol valid " . min($sampelDiuji, 15) . "/15";
                        break;
                    case 'KKA_Kredit':
                        $kontrolKhas = "Agunan valid {$sampelDiuji} | Monitoring valid {$sampelDiuji} | Kontrol valid " . min($sampelDiuji, 15) . "/15";
                        break;
                    case 'KKA_Biaya_Beban':
                        $kontrolKhas = "Rekonsiliasi biaya/pajak valid {$sampelDiuji} | Kontrol valid " . min($sampelDiuji, 15) . "/15";
                        break;
                    case 'KKA_Kartu_ATM':
                        $kontrolKhas = "Baris valid {$sampelDiuji}/" . max(1, $sampelTerpilih);
                        break;
                    case 'KKA_Pengaduan':
                        $kontrolKhas = "Analisis SLA valid {$sampelDiuji} | Kontrol valid " . min($sampelDiuji, 15) . "/15";
                        break;
                    default:
                        $kontrolKhas = "Baris valid {$sampelDiuji}/{$sampelTerpilih}";
                }

                // Kolom Tambahan Khusus Sampling
                $kapasitasVisitArea = 70; // Standar alokasi sampel/menit per area SOP 02
                $masukKka = $sampelTerpilih;
                $sudahDiuji = $sampelDiuji;
                $statusBeban = ($masukKka > $kapasitasVisitArea) 
                    ? 'KKA BELUM MENAMPUNG SELURUH SAMPEL' 
                    : 'DALAM KAPASITAS';

                // KKA Tersedia
                if ($populasiEligible > 0 || $sampelTerpilih > 0) {
                    $kkaTersediaCount++;
                }

            } else {
                // --- PEMERIKSAAN FISIK MINGGUAN (Baris 7-11) ---
                $modelClass = $def['detail_model'];
                $defaultCount = $def['default_count'];

                $details = collect();
                if ($latestPhysicalKka) {
                    $details = $modelClass::where('physical_kka_id', $latestPhysicalKka->id)->get();
                }

                $wajibProsedur = $details->isNotEmpty() ? $details->count() : $defaultCount;
                
                // Diuji / Valid
                $prosedurDiuji = $details->filter(function($d) {
                    return in_array(strtoupper($d->status_uji ?? ''), ['SESUAI', 'VALID', 'SELESAI', 'EXCEPTION'])
                        || strtoupper($d->validasi ?? '') === 'VALID'
                        || in_array(strtoupper($d->status_review ?? ''), ['DISETUJUI', 'APPROVED']);
                })->count();

                // Exception
                $exceptionCount = $details->filter(function($d) {
                    return strtoupper($d->status_uji ?? '') === 'EXCEPTION' 
                        || ($d->is_exception ?? false) == 1
                        || (isset($d->selisih) && is_numeric($d->selisih) && (float)$d->selisih != 0);
                })->count();

                // Tinggi / Kritis
                $tinggiKritis = $details->filter(function($d) {
                    $sev = strtoupper($d->severity_level ?? '');
                    return in_array($sev, ['TINGGI', 'KRITIS', 'HIGH', 'CRITICAL'])
                        || strtolower($d->critical_trigger ?? '') === 'ya';
                })->count();

                // Klarifikasi Pending
                $klarifikasiPending = $details->filter(function($d) {
                    return strtolower($d->perlu_klarifikasi ?? '') === 'ya' 
                        && !in_array(strtoupper($d->status_review ?? ''), ['DISETUJUI', 'APPROVED']);
                })->count();

                $komponenValid = $prosedurDiuji;
                $sampelTerpilih = $wajibProsedur;
                $sampelDiuji = $prosedurDiuji;

                // Status KKA
                if ($details->isEmpty() || $prosedurDiuji === 0) {
                    $statusKka = 'BELUM DIUJI';
                    $statusBadge = 'secondary';
                } elseif ($prosedurDiuji < $wajibProsedur) {
                    $statusKka = 'DALAM PENGUJIAN';
                    $statusBadge = 'warning';
                } else {
                    $statusKka = 'SELESAI';
                    $statusBadge = 'success';
                }

                $kontrolKhas = "Prosedur valid {$prosedurDiuji}/{$wajibProsedur}";

                // Kolom sampling strip untuk KKA Fisik
                $populasiEligible = '-';
                $kandidatRisiko   = '-';
                $kapasitasVisitArea = '-';
                $masukKka         = '-';
                $sudahDiuji       = '-';
                $statusBeban      = '-';

                // KKA Tersedia jika draft atau form sudah ada
                if ($latestPhysicalKka && $details->isNotEmpty()) {
                    $kkaTersediaCount++;
                } else {
                    // Karena form KKA fisik sudah pre-built di sistem (SOP 04.2), ia otomatis tersedia
                    $kkaTersediaCount++;
                }
            }

            // Tindak Lanjut
            $tindakLanjut = ($statusKka === 'SELESAI') ? 'LANJUT TAHAP 5' : 'SELESAIKAN / REVIEW KKA';

            if ($statusKka === 'SELESAI') {
                $kkaSelesaiCount++;
            }

            $totalKlarifikasiPendingAll += (int)$klarifikasiPending;
            $totalExceptionAll += (int)$exceptionCount;

            $rows[] = [
                'no'                   => $def['no'],
                'area_review'          => $def['area_review'],
                'dalam_lingkup'        => 'Ya',
                'nama_sheet'           => $def['nama_sheet'],
                'tahap_build'          => $def['tahap_build'],
                'status_kka'           => $statusKka,
                'status_badge'         => $statusBadge,
                'sampel_terpilih'      => $sampelTerpilih,
                'sampel_diuji'         => $sampelDiuji,
                'exception'            => $exceptionCount,
                'tinggi_kritis'        => $tinggiKritis,
                'klarifikasi_pending'  => $klarifikasiPending,
                'kontrol_khas'         => $kontrolKhas,
                'komponen_valid'       => $komponenValid,
                'tindak_lanjut'        => $tindakLanjut,
                'is_sampling'          => $isSampling,
                // Kolom tambahan
                'populasi_eligible'    => $populasiEligible,
                'kandidat_risiko'      => $kandidatRisiko,
                'kapasitas_visit_area' => $kapasitasVisitArea,
                'masuk_kka'            => $masukKka,
                'sudah_diuji'          => $sudahDiuji,
                'status_beban'         => $statusBeban,
            ];
        }

        // 5. STATUS TAHAP 4 & KESIAPAN TAHAP 5 (Formula N6 KKA_Control & N6 KKA_Integrasi)
        $kkaDalamLingkup = 11;
        
        // Status Tahap 4
        if ($kkaSelesaiCount < $kkaDalamLingkup) {
            $statusTahap4 = 'KKA BELUM SELESAI';
            $statusTahap4Badge = 'warning';
        } else {
            $statusTahap4 = 'SIAP TAHAP 5';
            $statusTahap4Badge = 'success';
        }

        // Kesiapan Tahap 5
        if ($kkaTersediaCount < $kkaDalamLingkup) {
            $kesiapanTahap5 = 'BELUM - KKA TIDAK LENGKAP';
            $kesiapanTahap5Badge = 'danger';
        } elseif ($kkaSelesaiCount < $kkaDalamLingkup) {
            $kesiapanTahap5 = 'BELUM - SELESAIKAN KKA';
            $kesiapanTahap5Badge = 'warning';
        } elseif ($totalKlarifikasiPendingAll > 0) {
            $kesiapanTahap5 = 'BELUM - KLARIFIKASI PENDING';
            $kesiapanTahap5Badge = 'danger';
        } else {
            $kesiapanTahap5 = 'SIAP TAHAP 5';
            $kesiapanTahap5Badge = 'success';
        }

        // 6. PANEL BAWAH: INTEGRASI TAHAP 5 (Klarifikasi & Temuan)
        $klarifikasiQuery = RegisterKlarifikasi::whereIn('kode_unit', $targetUnitCodes);
        $totalKlarifikasi = $klarifikasiQuery->count();
        
        // Perlu Klarifikasi: jika register belum dibuat, ambil total exception yang terdeteksi
        $perluKlarifikasi = max($totalKlarifikasi, $totalExceptionAll);

        $klarifikasiSelesai = (clone $klarifikasiQuery)
            ->where(function($q) {
                $q->where('status', 'like', '%Selesai%')
                  ->orWhere('status', 'like', '%Clear%');
            })->count();

        $eligibleTemuan = (clone $klarifikasiQuery)
            ->where('status', 'like', '%Eskalasi%')
            ->count();

        // Temuan Valid dari register_temuans
        $temuanValid = RegisterTemuan::whereIn('kode_unit', $targetUnitCodes)
            ->where('status_review_pusat', 'Approved')
            ->count();

        if ($totalTemuan = RegisterTemuan::whereIn('kode_unit', $targetUnitCodes)->count()) {
            $eligibleTemuan = max($eligibleTemuan, $totalTemuan);
        }

        // Status Tahap 5
        if ($perluKlarifikasi === 0) {
            $statusTahap5Text = 'SIAP LAPORAN';
            $statusTahap5Badge = 'success';
        } elseif ($kesiapanTahap5 === 'SIAP TAHAP 5' && $klarifikasiSelesai >= $perluKlarifikasi && ($eligibleTemuan == 0 || $temuanValid >= $eligibleTemuan)) {
            $statusTahap5Text = 'SIAP LAPORAN';
            $statusTahap5Badge = 'success';
        } else {
            $statusTahap5Text = 'SELESAIKAN KLARIFIKASI / TEMUAN';
            $statusTahap5Badge = 'warning';
        }

        return [
            'kode_unit'               => $kodeUnit,
            'nama_cabang'             => $kcUnit ? $kcUnit->unit_name : 'Kantor Cabang',
            'tanggal_acuan'           => $todayStr,
            'header_summary'          => [
                'kka_dalam_lingkup'    => $kkaDalamLingkup,
                'kka_tersedia'         => $kkaTersediaCount,
                'kka_selesai'          => $kkaSelesaiCount,
                'status_tahap_4'       => $statusTahap4,
                'status_tahap_4_badge' => $statusTahap4Badge,
                'kesiapan_tahap_5'     => $kesiapanTahap5,
                'kesiapan_tahap_5_badge' => $kesiapanTahap5Badge,
                'total_klarifikasi_pending' => $totalKlarifikasiPendingAll,
                'total_exception'      => $totalExceptionAll,
            ],
            'rows'                    => $rows,
            'integrasi_tahap_5'       => [
                'perlu_klarifikasi'    => $perluKlarifikasi,
                'klarifikasi_selesai'  => $klarifikasiSelesai,
                'eligible_temuan'      => $eligibleTemuan,
                'temuan_valid'         => $temuanValid,
                'status_tahap_5'       => $statusTahap5Text,
                'status_tahap_5_badge' => $statusTahap5Badge,
            ]
        ];
    }
}
