<?php

namespace App\Services\RiskTrigger;

use App\Models\RiskTrigger\RegisterTrigger;
use App\Models\RiskTrigger\AssignmentScope;
use App\Models\RiskTrigger\ProgramKerjaData;
use App\Models\RiskTrigger\MasterParameter;
use App\Models\DailyRegister\RegisterKlarifikasi;
use App\Models\DailyRegister\PhysicalKkaCashDetail;
use App\Models\DailyRegister\PhysicalKkaObsDetail;
use App\Models\DailyRegister\PhysicalKkaStokDetail;
use App\Models\DailyRegister\PhysicalKkaKartuAtmDetail;
use App\Models\DailyRegister\PhysicalKkaBarangCetakanDetail;
use App\Models\DailyRegister\PhysicalKka;
use App\Models\RiskTrigger\Kka\RiskTriggerKkaInduk;
use App\Models\RiskTrigger\Kka\KkaTellerKas;
use App\Models\RiskTrigger\Kka\KkaDpkLayanan;
use App\Models\RiskTrigger\Kka\KkaKredit;
use App\Models\RiskTrigger\Kka\KkaBiayaBeban;
use App\Models\RiskTrigger\Kka\KkaAtm;
use App\Models\RiskTrigger\Kka\KkaPengaduan;
use App\Models\RiskTrigger\Kka\KkaFisik;
use App\Models\RiskTrigger\Kka\KkaObservasi;
use App\Models\RiskTrigger\KlarifikasiBukti;
use App\Models\RiskTrigger\SimpulanTl;
use App\Models\Cabang;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RiskTriggerService
{
    /**
     * Map source sheet name to SOP 06 standard 13 area
     */
    public static function mapSourceSheetToArea(?string $sheet): string
    {
        $s = strtolower(trim((string)$sheet));
        if (str_contains($s, 'teller') || str_contains($s, 'kas')) {
            return RegisterTrigger::AREA_TELLER_KAS;
        }
        if (str_contains($s, 'dpk') || str_contains($s, 'layanan')) {
            return RegisterTrigger::AREA_DPK_LAYANAN;
        }
        if (str_contains($s, 'kredit') || str_contains($s, 'credit')) {
            return RegisterTrigger::AREA_KREDIT;
        }
        if (str_contains($s, 'biaya') || str_contains($s, 'beban')) {
            return RegisterTrigger::AREA_BIAYA_BEBAN;
        }
        if (str_contains($s, 'atm') && !str_contains($s, 'rekon')) {
            return RegisterTrigger::AREA_KARTU_ATM;
        }
        if (str_contains($s, 'rekon_kartu') || str_contains($s, 'rekon atm')) {
            return RegisterTrigger::AREA_REKON_KARTU_ATM;
        }
        if (str_contains($s, 'pengaduan') || str_contains($s, 'aduan')) {
            return RegisterTrigger::AREA_PENGADUAN;
        }
        if (str_contains($s, 'cash') || str_contains($s, 'opname')) {
            return RegisterTrigger::AREA_CASH_OPNAME;
        }
        if (str_contains($s, 'observasi') || str_contains($s, 'lingkungan')) {
            return RegisterTrigger::AREA_OBSERVASI_LINGKUNGAN;
        }
        if (str_contains($s, 'cek') || str_contains($s, 'bg') || str_contains($s, 'stok')) {
            return RegisterTrigger::AREA_REKON_CEK_BG;
        }
        if (str_contains($s, 'cetakan') || str_contains($s, 'barang')) {
            return RegisterTrigger::AREA_REKON_BARANG_CETAKAN;
        }
        if (str_contains($s, 'aset') || str_contains($s, 'asset') || str_contains($s, 'inventaris')) {
            return RegisterTrigger::AREA_ASET;
        }

        return RegisterTrigger::AREA_UMUM_LAINNYA;
    }

    /**
     * Parse risk score & level from raw values (handles string labels like 'High', 'Moderate', or numeric)
     */
    public static function parseRiskScoreAndLevel($rawScore, $dampak = null, $kemungkinan = null): array
    {
        if (is_numeric($rawScore) && (float)$rawScore > 0) {
            $score = (float)$rawScore;
        } elseif (is_numeric($dampak) && is_numeric($kemungkinan) && ((float)$dampak * (float)$kemungkinan) > 0) {
            $score = (float)$dampak * (float)$kemungkinan;
        } else {
            $str = strtolower(trim((string)$rawScore));
            $score = match ($str) {
                'critical', 'kritis' => 16.0,
                'high', 'tinggi' => 10.0,
                'moderate', 'moderate to high', 'sedang' => 5.0,
                default => 2.0,
            };
        }

        $level = match (true) {
            $score >= 16 => 'KRITIS',
            $score >= 10 => 'TINGGI',
            $score >= 5 => 'SEDANG',
            default => 'RENDAH',
        };

        return [$score, $level];
    }

    /**
     * Dapatkan daftar kode unit yang dapat diakses oleh user (terutama role RA)
     * Mengembalikan null jika user adalah Admin / Reviewer (akses global)
     */
    public static function getAccessibleUnitCodes(?User $user = null): ?array
    {
        if (!$user) {
            return null;
        }

        if (strtolower($user->role) === 'ra') {
            $allowedCabangIds = method_exists($user, 'cabangIdYangDapatDiakses')
                ? $user->cabangIdYangDapatDiakses()
                : [$user->cabang_id];

            $allowedUnits = Unit::whereIn('cabang_id', $allowedCabangIds)->pluck('unit_code')->toArray();
            $allowedCabangCodes = Cabang::whereIn('id', $allowedCabangIds)->pluck('kode_cabang')->toArray();

            $targetCodes = array_unique(array_filter(array_merge($allowedUnits, $allowedCabangCodes)));
            $expanded = [];
            foreach ($targetCodes as $code) {
                $clean = str_replace('BS-', '', (string)$code);
                $expanded[] = $code;
                $expanded[] = $clean;
                $expanded[] = 'BS-' . $clean;
                if (is_numeric($clean)) {
                    $expanded[] = str_pad($clean, 3, '0', STR_PAD_LEFT);
                }
            }

            return array_values(array_unique(array_filter($expanded)));
        }

        return null;
    }

    /**
     * Ekspansi kode unit agar cocok dengan format variasi (misal: '003', '3', 'BS-003')
     */
    public static function expandUnitCode(?string $kodeUnit): array
    {
        if (empty($kodeUnit)) {
            return [];
        }
        $clean = str_replace('BS-', '', trim($kodeUnit));
        $expanded = [$kodeUnit, $clean, 'BS-' . $clean];
        if (is_numeric($clean)) {
            $expanded[] = str_pad($clean, 3, '0', STR_PAD_LEFT);
        }
        return array_values(array_unique(array_filter($expanded)));
    }

    /**
     * Sinkronisasi data pemicu dari semua modul:
     * 1. Daily Register (6 KKA Harian & 5 KKA Fisik)
     * 2. Offsite
     * 3. Onsite (slot future-proof)
     */
    public function syncAll(?User $user = null, ?string $filterKodeUnit = null): array
    {
        $stats = [
            'daily_register' => 0,
            'physical_kka' => 0,
            'offsite' => 0,
            'onsite' => 0,
            'total_new' => 0,
            'total_updated' => 0,
        ];

        // Tentukan batasan kode unit berdasarkan role atau filter Admin
        $targetUnits = null;
        if ($user && strtolower($user->role) === 'ra') {
            $targetUnits = static::getAccessibleUnitCodes($user);
        } elseif (!empty($filterKodeUnit)) {
            $targetUnits = static::expandUnitCode($filterKodeUnit);
        }

        // Preload existing WP IDs for lightning-fast O(1) checks
        $existingMap = RegisterTrigger::pluck('id', 'source_wp_id')->toArray();

        DB::beginTransaction();
        try {
            // 1. Sync Daily Register Findings
            $drStats = $this->syncDailyRegisterFindings($user, $existingMap, $targetUnits);
            $stats['daily_register'] += $drStats['new'];
            $stats['total_new'] += $drStats['new'];
            $stats['total_updated'] += $drStats['updated'];

            // 2. Sync Physical KKA Exceptions
            $physStats = $this->syncPhysicalKkaExceptions($user, $existingMap, $targetUnits);
            $stats['physical_kka'] += $physStats['new'];
            $stats['total_new'] += $physStats['new'];
            $stats['total_updated'] += $physStats['updated'];

            // 3. Sync Offsite Findings
            $offStats = $this->syncOffsiteFindings($user, $existingMap, $targetUnits);
            $stats['offsite'] += $offStats['new'];
            $stats['total_new'] += $offStats['new'];
            $stats['total_updated'] += $offStats['updated'];

            // 4. Sync Onsite Findings (Future-proof slot)
            $onStats = $this->syncOnsiteFindings($user, $existingMap, $targetUnits);
            $stats['onsite'] += $onStats['new'];
            $stats['total_new'] += $onStats['new'];
            $stats['total_updated'] += $onStats['updated'];

            // 5. Update Status Duplikasi & Alarm Akumulasi (> 5 Temuan)
            $this->recalculateDuplicatesAndAlarms();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }

    /**
     * Sync from Daily Register 6 KKA Harian (daily_register_findings)
     */
    protected function syncDailyRegisterFindings(?User $user = null, array &$existingMap = [], ?array $targetUnits = null): array
    {
        $newCount = 0;
        $updatedCount = 0;

        if (!Schema::hasTable('daily_register_findings')) {
            return ['new' => 0, 'updated' => 0];
        }

        $query = DB::table('daily_register_findings');

        // Isolasi Cabang jika targetUnits didefinisikan (khusus RA atau filter Admin)
        if (!empty($targetUnits)) {
            $query->whereIn('kode_unit', $targetUnits);
        }

        // Tarik temuan dengan risk score >= 5 atau severity Sedang/Tinggi/Kritis
        $query->where(function ($q) {
            $q->whereIn(DB::raw('LOWER(COALESCE(risk_awal, ""))'), ['high', 'critical', 'tinggi', 'kritis', 'moderate', 'moderate to high', 'sedang'])
                ->orWhere('risk_awal', '>=', 5)
                ->orWhereRaw('(COALESCE(skor_dampak, 1) * COALESCE(skor_kemungkinan, 1)) >= 5')
                ->orWhereIn(DB::raw('LOWER(COALESCE(kategori, ""))'), ['kritis', 'tinggi', 'sedang', 'critical', 'high', 'moderate']);
        });

        $findings = $query->latest('id')->take(300)->get();

        foreach ($findings as $f) {
            $sourceWpId = "DRF-{$f->id}";
            $isExisting = isset($existingMap[$sourceWpId]);

            $tanggal = $f->tanggal_data ?: ($f->tanggal_ditemukan ?: Carbon::today()->toDateString());
            $area = static::mapSourceSheetToArea($f->source_sheet ?? $f->kategori ?? '');
            [$riskScore, $riskLevel] = static::parseRiskScoreAndLevel($f->risk_awal, $f->skor_dampak, $f->skor_kemungkinan);

            $deskripsi = $f->rincian ?: ($f->deskripsi ?: $f->jenis_exception_awal ?: 'Temuan Daily Register');
            $fraudIndicator = str_contains(strtolower($deskripsi), 'fraud') || str_contains(strtolower($f->kategori ?? ''), 'fraud') ? 'Ya' : 'Tidak';
            $perluLap = (!empty($f->perlu_onsite) && in_array(strtolower((string)$f->perlu_onsite), ['1', 'ya', 'true', 'yes'])) ? 'Ya' : 'Tidak';

            if (!$isExisting) {
                $trigger = new RegisterTrigger();
                $trigger->trigger_id = RegisterTrigger::generateNextTriggerId($tanggal);
                $trigger->sumber = 'Daily Review';
                $trigger->source_wp_id = $sourceWpId;
                $trigger->tanggal_trigger = $tanggal;
                $trigger->kode_unit = $f->kode_unit ?: '000';
                $trigger->nama_unit = $f->nama_unit ?: $this->resolveUnitName($f->kode_unit);
                $trigger->area = $area;
                $trigger->objek_key = substr($f->jenis_exception_awal ?: $f->rincian ?: "ID-{$f->id}", 0, 250);
                $trigger->periode_mulai = $f->tanggal_data ?: $tanggal;
                $trigger->periode_selesai = $f->tanggal_data ?: $tanggal;
                $trigger->uraian_trigger = $deskripsi;
                $trigger->bukti_awal = $f->bukti_referensi ?: 'KKA Daily Register & Log Transaksi';
                $trigger->risk_score_sumber = $riskScore;
                $trigger->risk_level_sumber = $riskLevel;
                $trigger->jumlah_kejadian = 1;
                $trigger->eksposur_nominal = (float) ($f->nominal_terkait ?: 0);
                $trigger->perlu_verifikasi_lapangan = $perluLap;
                $trigger->indikasi_fraud = $fraudIndicator;
                $trigger->objek_surprise_fisik = 'Tidak';
                $trigger->user_id = $user?->id;

                $trigger->calculateDerivedAttributes();
                $trigger->save();

                $existingMap[$sourceWpId] = $trigger->id;
                $newCount++;
            }
        }

        return ['new' => $newCount, 'updated' => $updatedCount];
    }

    /**
     * Sync from 5 KKA Fisik Exceptions (register_klarifikasis & physical_kka_*_details)
     */
    protected function syncPhysicalKkaExceptions(?User $user = null, array &$existingMap = [], ?array $targetUnits = null): array
    {
        $newCount = 0;
        $updatedCount = 0;

        if (!Schema::hasTable('register_klarifikasis')) {
            return ['new' => 0, 'updated' => 0];
        }

        $query = RegisterKlarifikasi::with('source');
        if (!empty($targetUnits)) {
            $query->whereIn('kode_unit', $targetUnits);
        }
        $klarifikasis = $query->get();

        foreach ($klarifikasis as $k) {
            $source = $k->source;
            if (!$source) {
                continue;
            }

            $sourceWpId = "PKKA-KLARIFIKASI-{$k->id}";
            $isExisting = isset($existingMap[$sourceWpId]);

            $tanggal = $k->created_at ? $k->created_at->toDateString() : Carbon::today()->toDateString();
            $kodeUnit = $k->kode_unit ?: '000';
            $namaUnit = $this->resolveUnitName($kodeUnit);

            $area = RegisterTrigger::AREA_CASH_OPNAME;
            $objekKey = 'Fisik Exception';
            $selisih = 0;
            $penyebab = '';
            $riskScore = 5.0;

            if (str_contains($k->source_type, 'CashDetail')) {
                $area = RegisterTrigger::AREA_CASH_OPNAME;
                $objekKey = ($source->objek_persediaan ?? 'Kas') . ' (' . ($source->pecahan ?? '-') . ')';
                $selisih = abs((float)($source->selisih ?? 0));
                $penyebab = $source->penyebab ?? 'Selisih fisik kas opname';
                $riskScore = (float)($source->risk_score ?? 10);
            } elseif (str_contains($k->source_type, 'ObsDetail')) {
                $area = RegisterTrigger::AREA_OBSERVASI_LINGKUNGAN;
                $objekKey = $source->area_observasi ?? 'Observasi Fisik';
                $penyebab = $source->kondisi_aktual ?? ($source->penyebab ?? 'Kondisi fisik tidak sesuai kriteria');
                $riskScore = (float)($source->risk_score ?? 8);
            } elseif (str_contains($k->source_type, 'StokDetail')) {
                $area = RegisterTrigger::AREA_REKON_CEK_BG;
                $objekKey = ($source->sub_area ?? 'Stok') . ' - ' . ($source->kondisi_objek ?? '');
                $selisih = abs((float)($source->selisih ?? 0));
                $penyebab = $source->penyebab ?? 'Selisih persediaan fisik formulir/cek/BG';
                $riskScore = (float)($source->risk_score ?? 8);
            } elseif (str_contains($k->source_type, 'KartuAtmDetail')) {
                $area = RegisterTrigger::AREA_REKON_KARTU_ATM;
                $objekKey = ($source->sub_area ?? 'ATM') . ' - ' . ($source->jenis_kartu ?? '');
                $selisih = is_numeric($source->selisih ?? null) ? abs((float)$source->selisih) : 0;
                $penyebab = $source->penyebab ?? ($source->kondisi ?? 'Selisih kartu fisik ATM');
                $riskScore = (float)($source->risk_score ?? 8);
            } elseif (str_contains($k->source_type, 'BarangCetakanDetail')) {
                $area = RegisterTrigger::AREA_REKON_BARANG_CETAKAN;
                $objekKey = ($source->kategori ?? 'Cetakan') . ' - ' . ($source->nama_barang ?? '');
                $selisih = abs((float)($source->selisih ?? 0));
                $penyebab = $source->penyebab ?? ($source->kondisi ?? 'Selisih barang cetakan');
                $riskScore = (float)($source->risk_score ?? 6);
            }

            if ($riskScore < 5) {
                $riskScore = 5;
            }

            $riskLevel = match (true) {
                $riskScore >= 16 => 'KRITIS',
                $riskScore >= 10 => 'TINGGI',
                $riskScore >= 5 => 'SEDANG',
                default => 'RENDAH',
            };

            $fraudIndicator = str_contains(strtolower($penyebab), 'fraud') ? 'Ya' : 'Tidak';

            if (!$isExisting) {
                $trigger = new RegisterTrigger();
                $trigger->trigger_id = RegisterTrigger::generateNextTriggerId($tanggal);
                $trigger->sumber = 'Daily Review';
                $trigger->source_wp_id = $sourceWpId;
                $trigger->tanggal_trigger = $tanggal;
                $trigger->kode_unit = $kodeUnit;
                $trigger->nama_unit = $namaUnit;
                $trigger->area = $area;
                $trigger->objek_key = substr($objekKey, 0, 250);
                $trigger->periode_mulai = $tanggal;
                $trigger->periode_selesai = $tanggal;
                $trigger->uraian_trigger = $penyebab ?: "Exception fisik pada {$objekKey}";
                $trigger->bukti_awal = $source->bukti_path ? "Lampiran Bukti Fisik ({$source->bukti_path})" : 'Berita Acara Hitung Fisik & KKA Fisik';
                $trigger->risk_score_sumber = $riskScore;
                $trigger->risk_level_sumber = $riskLevel;
                $trigger->jumlah_kejadian = 1;
                $trigger->eksposur_nominal = $selisih;
                $trigger->perlu_verifikasi_lapangan = 'Ya'; // Temuan fisik otomatis butuh verifikasi lapangan
                $trigger->indikasi_fraud = $fraudIndicator;
                $trigger->objek_surprise_fisik = 'Ya';
                $trigger->user_id = $user?->id;

                $trigger->calculateDerivedAttributes();
                $trigger->save();

                $existingMap[$sourceWpId] = $trigger->id;
                $newCount++;
            }
        }

        return ['new' => $newCount, 'updated' => $updatedCount];
    }

    /**
     * Sync from Offsite Findings (kka_findings)
     */
    protected function syncOffsiteFindings(?User $user = null, array &$existingMap = [], ?array $targetUnits = null): array
    {
        $newCount = 0;
        $updatedCount = 0;

        if (!Schema::hasTable('kka_findings')) {
            return ['new' => 0, 'updated' => 0];
        }

        $query = DB::table('kka_findings')
            ->where(function ($q) {
                $q->whereIn(DB::raw('LOWER(COALESCE(risk_awal, ""))'), ['high', 'critical', 'tinggi', 'kritis', 'moderate', 'moderate to high', 'sedang'])
                    ->orWhere('risk_awal', '>=', 5)
                    ->orWhereRaw('(COALESCE(skor_dampak, 1) * COALESCE(skor_kemungkinan, 1)) >= 5');
            });

        // Isolasi Cabang jika targetUnits didefinisikan (khusus RA atau filter Admin)
        if (!empty($targetUnits)) {
            $query->whereIn('kode_unit', $targetUnits);
        }

        // Ambil 300 temuan terbaru dari Offsite agar proses sinkronisasi cepat dan responsif
        $findings = $query->latest('id')->take(300)->get();

        foreach ($findings as $f) {
            $sourceWpId = "OFF-{$f->id}";
            $isExisting = isset($existingMap[$sourceWpId]);

            $tanggal = $f->tanggal_data ?: ($f->tanggal_ditemukan ?: Carbon::today()->toDateString());
            $area = static::mapSourceSheetToArea($f->source_sheet ?? '');
            [$riskScore, $riskLevel] = static::parseRiskScoreAndLevel($f->risk_awal, $f->skor_dampak, $f->skor_kemungkinan);

            $deskripsi = $f->deskripsi ?: ($f->jenis_exception_awal ?: 'Temuan Analisis Offsite');
            $fraudIndicator = str_contains(strtolower($deskripsi), 'fraud') ? 'Ya' : 'Tidak';
            $perluLap = (!empty($f->perlu_onsite) && in_array(strtolower((string)$f->perlu_onsite), ['1', 'ya', 'true', 'yes'])) ? 'Ya' : 'Tidak';

            if (!$isExisting) {
                $trigger = new RegisterTrigger();
                $trigger->trigger_id = RegisterTrigger::generateNextTriggerId($tanggal);
                $trigger->sumber = 'Offsite';
                $trigger->source_wp_id = $sourceWpId;
                $trigger->tanggal_trigger = $tanggal;
                $trigger->kode_unit = $f->kode_unit ?: '000';
                $trigger->nama_unit = $f->nama_unit ?: $this->resolveUnitName($f->kode_unit);
                $trigger->area = $area;
                $trigger->objek_key = substr($f->jenis_exception_awal ?: "OFF-ID-{$f->id}", 0, 250);
                $trigger->periode_mulai = $f->tanggal_data ?: $tanggal;
                $trigger->periode_selesai = $f->tanggal_data ?: $tanggal;
                $trigger->uraian_trigger = $deskripsi;
                $trigger->bukti_awal = $f->bukti_referensi ?: 'Data Dump CBS & Laporan Offsite';
                $trigger->risk_score_sumber = $riskScore;
                $trigger->risk_level_sumber = $riskLevel;
                $trigger->jumlah_kejadian = 1;
                $trigger->eksposur_nominal = (float) ($f->nominal_terkait ?: 0);
                $trigger->perlu_verifikasi_lapangan = $perluLap;
                $trigger->indikasi_fraud = $fraudIndicator;
                $trigger->objek_surprise_fisik = 'Tidak';
                $trigger->user_id = $user?->id;

                $trigger->calculateDerivedAttributes();
                $trigger->save();

                $existingMap[$sourceWpId] = $trigger->id;
                $newCount++;
            }
        }

        return ['new' => $newCount, 'updated' => $updatedCount];
    }

    /**
     * Future-proof Slot: Sync from Onsite Findings (onsite_kka_findings)
     */
    protected function syncOnsiteFindings(?User $user = null, array &$existingMap = [], ?array $targetUnits = null): array
    {
        if (!Schema::hasTable('onsite_kka_findings')) {
            return ['new' => 0, 'updated' => 0];
        }

        $newCount = 0;
        $updatedCount = 0;

        try {
            $query = DB::table('onsite_kka_findings')
                ->where(function ($q) {
                    $q->whereIn(DB::raw('LOWER(COALESCE(risk_level, ""))'), ['high', 'critical', 'tinggi', 'kritis', 'moderate', 'moderate to high', 'sedang'])
                        ->orWhere('stable_score', '>=', 5)
                        ->orWhereRaw('(COALESCE(skor_dampak, 1) * COALESCE(skor_kemungkinan, 1)) >= 5');
                });

            if (!empty($targetUnits)) {
                $query->whereIn('kode_unit', $targetUnits);
            }

            $findings = $query->latest('id')
                ->take(300)
                ->get();

            foreach ($findings as $f) {
                $sourceWpId = "ONSITE-{$f->id}";
                $isExisting = isset($existingMap[$sourceWpId]);

                $tanggal = $f->tgl_tx ?: ($f->tanggal_ditemukan ?: Carbon::today()->toDateString());
                [$riskScore, $riskLevel] = static::parseRiskScoreAndLevel($f->stable_score ?? $f->risk_level, $f->skor_dampak, $f->skor_kemungkinan);

                $deskripsi = $f->ket_tx ?: ($f->hasil_uji ?: 'Temuan Pemeriksaan Onsite');
                $fraudIndicator = str_contains(strtolower($deskripsi), 'fraud') ? 'Ya' : 'Tidak';

                if (!$isExisting) {
                    $trigger = new RegisterTrigger();
                    $trigger->trigger_id = RegisterTrigger::generateNextTriggerId($tanggal);
                    $trigger->sumber = 'Onsite';
                    $trigger->source_wp_id = $sourceWpId;
                    $trigger->tanggal_trigger = $tanggal;
                    $trigger->kode_unit = $f->kode_unit ?: '000';
                    $trigger->nama_unit = $this->resolveUnitName($f->kode_unit);
                    $trigger->area = RegisterTrigger::AREA_UMUM_LAINNYA;
                    $trigger->objek_key = substr($f->no_arsip ?: "TX-{$f->id}", 0, 250);
                    $trigger->periode_mulai = $tanggal;
                    $trigger->periode_selesai = $tanggal;
                    $trigger->uraian_trigger = $deskripsi;
                    $trigger->bukti_awal = $f->bukti_referensi ?: 'KKA Onsite Sampling';
                    $trigger->risk_score_sumber = $riskScore;
                    $trigger->risk_level_sumber = $riskLevel;
                    $trigger->jumlah_kejadian = (int) ($f->jumlah_tx ?: 1);
                    $trigger->eksposur_nominal = 0;
                    $trigger->perlu_verifikasi_lapangan = 'Ya';
                    $trigger->indikasi_fraud = $fraudIndicator;
                    $trigger->objek_surprise_fisik = 'Tidak';
                    $trigger->user_id = $user?->id;

                    $trigger->calculateDerivedAttributes();
                    $trigger->save();

                    $existingMap[$sourceWpId] = $trigger->id;
                    $newCount++;
                }
            }
        } catch (\Exception $e) {
            report($e);
        }

        return ['new' => $newCount, 'updated' => $updatedCount];
    }

    /**
     * Input / Import Manual Trigger
     */
    public function createManualTrigger(array $data, ?User $user = null): RegisterTrigger
    {
        $tanggal = $data['tanggal_trigger'] ?? Carbon::today()->toDateString();

        $trigger = new RegisterTrigger();
        $trigger->trigger_id = RegisterTrigger::generateNextTriggerId($tanggal);
        $trigger->sumber = $data['sumber'] ?? 'Surprise';
        $trigger->source_wp_id = $data['source_wp_id'] ?? ('MANUAL-' . strtoupper(uniqid()));
        $trigger->tanggal_trigger = $tanggal;
        $trigger->kode_unit = $data['kode_unit'] ?? '000';
        $trigger->nama_unit = $data['nama_unit'] ?? $this->resolveUnitName($data['kode_unit'] ?? '000');
        $trigger->area = $data['area'] ?? RegisterTrigger::AREA_UMUM_LAINNYA;
        $trigger->objek_key = $data['objek_key'] ?? '-';
        $trigger->periode_mulai = $data['periode_mulai'] ?? $tanggal;
        $trigger->periode_selesai = $data['periode_selesai'] ?? $tanggal;
        $trigger->uraian_trigger = $data['uraian_trigger'] ?? '';
        $trigger->bukti_awal = $data['bukti_awal'] ?? '';
        $trigger->risk_score_sumber = (float) ($data['risk_score_sumber'] ?? 5);

        $score = $trigger->risk_score_sumber;
        $trigger->risk_level_sumber = match (true) {
            $score >= 16 => 'KRITIS',
            $score >= 10 => 'TINGGI',
            $score >= 5 => 'SEDANG',
            default => 'RENDAH',
        };

        $trigger->jumlah_kejadian = (int) ($data['jumlah_kejadian'] ?? 1);
        $trigger->eksposur_nominal = (float) ($data['eksposur_nominal'] ?? 0);
        $trigger->perlu_verifikasi_lapangan = ($data['perlu_verifikasi_lapangan'] ?? 'Tidak') === 'Ya' ? 'Ya' : 'Tidak';
        $trigger->indikasi_fraud = ($data['indikasi_fraud'] ?? 'Tidak') === 'Ya' ? 'Ya' : 'Tidak';
        $trigger->objek_surprise_fisik = ($data['objek_surprise_fisik'] ?? 'Tidak') === 'Ya' ? 'Ya' : 'Tidak';
        $trigger->user_id = $user?->id;

        $trigger->calculateDerivedAttributes();
        $trigger->save();

        // Refresh duplikasi & alarm
        $this->recalculateDuplicatesAndAlarms();

        return $trigger;
    }

    /**
     * Hitung ulang Status Duplikasi & Notifikasi Alarm Akumulasi (> 5 Temuan)
     * Mengikuti rumus Excel COUNTIF($U$6:U6, U6) > 1 (Kejadian pertama = UNIK, repetisi berikutnya = DUPLIKAT - REVIEW)
     */
    public function recalculateDuplicatesAndAlarms(): array
    {
        $allTriggers = RegisterTrigger::orderBy('id', 'asc')->get();

        // 1. Hitung akumulasi per Unit + Area risiko (skor >= 5 Moderate-to-High / High)
        $unitAreaHighCounts = [];
        foreach ($allTriggers as $t) {
            if ($t->risk_score_reassessment >= 5) {
                $compositeKey = "{$t->kode_unit}|{$t->area}";
                $unitAreaHighCounts[$compositeKey] = ($unitAreaHighCounts[$compositeKey] ?? 0) + 1;
            }
        }

        $triggeredAlarms = [];
        $seenKeys = [];

        foreach ($allTriggers as $t) {
            $changed = false;

            // Status Duplikat: Muncul pertama kali = UNIK, repetisi berikutnya = DUPLIKAT - REVIEW
            $dupKey = $t->duplicate_key;
            if ($dupKey && isset($seenKeys[$dupKey])) {
                $newDupStatus = 'DUPLIKAT - REVIEW';
            } else {
                $newDupStatus = 'UNIK';
                if ($dupKey) {
                    $seenKeys[$dupKey] = true;
                }
            }

            if ($t->status_duplikat !== $newDupStatus) {
                $t->status_duplikat = $newDupStatus;
                $changed = true;
            }

            // Alarm Akumulasi > 5 Temuan
            $compositeKey = "{$t->kode_unit}|{$t->area}";
            $countInGroup = $unitAreaHighCounts[$compositeKey] ?? 0;
            $alarmActive = $countInGroup > 5;

            if ($alarmActive) {
                if (!isset($triggeredAlarms[$compositeKey])) {
                    $triggeredAlarms[$compositeKey] = [
                        'kode_unit' => $t->kode_unit,
                        'nama_unit' => $t->nama_unit ?: $this->resolveUnitName($t->kode_unit),
                        'area' => $t->area,
                        'count' => $countInGroup,
                        'message' => "PERINGATAN RISK TRIGGER: Area {$t->area} pada " . ($t->nama_unit ?: $t->kode_unit) . " memiliki {$countInGroup} akumulasi temuan Moderate-to-High / High (> 5). Segera lakukan pendalaman kembali / pemeriksaan Risk Trigger Visit!",
                    ];
                }

                // Otomatis tandai Perlu Verifikasi Lapangan = "Ya"
                if ($t->perlu_verifikasi_lapangan !== 'Ya') {
                    $t->perlu_verifikasi_lapangan = 'Ya';
                    $changed = true;
                }
            }

            // Recalculate derived attributes
            $t->calculateDerivedAttributes($alarmActive);
            $t->save();
        }

        return array_values($triggeredAlarms);
    }

    /**
     * Dapatkan daftar aktif Notifikasi Alarm (> 5 Temuan per Unit & Area)
     */
    public function getActiveAlarms(?User $user = null, ?string $filterKodeUnit = null): array
    {
        $query = RegisterTrigger::where('risk_score_reassessment', '>=', 5);

        if ($user && strtolower($user->role) === 'ra') {
            $query->byBranch($user);
        } elseif (!empty($filterKodeUnit)) {
            $expanded = static::expandUnitCode($filterKodeUnit);
            $query->whereIn('kode_unit', $expanded);
        }

        $grouped = $query->select('kode_unit', 'nama_unit', 'area', DB::raw('count(*) as total'))
            ->groupBy('kode_unit', 'nama_unit', 'area')
            ->having('total', '>', 5)
            ->get();

        $alarms = [];
        foreach ($grouped as $row) {
            $namaUnit = $row->nama_unit ?: $this->resolveUnitName($row->kode_unit);
            $alarms[] = [
                'kode_unit' => $row->kode_unit,
                'nama_unit' => $namaUnit,
                'area' => $row->area,
                'count' => $row->total,
                'message' => "PERINGATAN RISK TRIGGER: Area {$row->area} pada {$namaUnit} memiliki {$row->total} akumulasi temuan Moderate-to-High / High (> 5). Segera lakukan pendalaman kembali / pemeriksaan Risk Trigger Visit!",
            ];
        }

        return $alarms;
    }

    /**
     * Ringkasan Kontrol Statistics untuk Dashboard SOP 06
     */
    public function getRingkasanControlStats(?User $user = null, ?string $filterKodeUnit = null): array
    {
        $query = RegisterTrigger::query();
        if ($user && strtolower($user->role) === 'ra') {
            $query->byBranch($user);
        } elseif (!empty($filterKodeUnit)) {
            $expanded = static::expandUnitCode($filterKodeUnit);
            $query->whereIn('kode_unit', $expanded);
        }

        $all = $query->get();
        $today = Carbon::today();

        // Hitung Assignment Siap Dilaksanakan
        $asQuery = AssignmentScope::where('status_assignment', 'SIAP DILAKSANAKAN');
        if ($user && strtolower($user->role) === 'ra') {
            $asQuery->byBranch($user);
        } elseif (!empty($filterKodeUnit)) {
            $asQuery->whereIn('kode_unit', static::expandUnitCode($filterKodeUnit));
        }
        $assignmentSiapCount = $asQuery->count();

        // Query Simpulan TL untuk sinkronisasi Fase 4 (Sel B14, B15, dan Matrix Area Kolom K)
        $simTlQuery = SimpulanTl::query();
        if ($user && strtolower($user->role) === 'ra') {
            $simTlQuery->byBranch($user);
        } elseif (!empty($filterKodeUnit)) {
            $simTlQuery->whereIn('kode_unit', static::expandUnitCode($filterKodeUnit));
        }
        $simTlAll = $simTlQuery->get();

        // Indikator Simpulan Tersedia (Sel B14): Menghitung jumlah baris di Simpulan_TL yang memiliki Visit ID (RTV-*)
        $simpulanTersediaCount = $simTlAll->filter(function ($s) {
            return !empty($s->visit_id);
        })->count();

        // Indikator Masih Open/Belum Ditutup (Sel B15): Menghitung jumlah baris di Simpulan_TL yang Status Closure-nya bernilai "BELUM DITUTUP", "TL BELUM LENGKAP", atau "KLARIFIKASI BELUM SELESAI"
        $masihOpenCount = $simTlAll->whereIn('status_closure', [
            'BELUM DITUTUP',
            'TL BELUM LENGKAP',
            'KLARIFIKASI BELUM SELESAI'
        ])->count();

        // 1. 12 Kartu Indikator Status
        $kpi = [
            'total_trigger' => $all->count(),
            'data_valid' => $all->where('kelengkapan_data', 'VALID')->count(),
            'risk_kritis' => $all->where('risk_level_reassessment', 'KRITIS')->count(),
            'risk_tinggi' => $all->where('risk_level_reassessment', 'TINGGI')->count(),
            'keputusan_visit' => $all->where('keputusan_final', RegisterTrigger::KEPUTUSAN_RISK_TRIGGER_VISIT)->count(),
            'menunggu_assignment' => $all->where('status_workflow', 'MENUNGGU ASSIGNMENT')->count(),
            'assignment_siap' => $assignmentSiapCount,
            'visit_dalam_proses' => $all->where('status_workflow', 'VISIT DALAM PROSES')->count(),
            'simpulan_tersedia' => $simpulanTersediaCount,
            'masih_open' => $masihOpenCount,
            'overdue_sla' => $all->filter(function ($t) use ($today) {
                return $t->sla_deadline && $t->sla_deadline->lt($today) && $t->status_workflow !== 'SELESAI/DALAM TL';
            })->count(),
            'indikasi_fraud' => $all->filter(function ($t) {
                return strtoupper((string)$t->indikasi_fraud) === 'YA' || $t->keputusan_final === RegisterTrigger::KEPUTUSAN_FRAUD;
            })->count(),
        ];

        // 2. Ringkasan 8 Keputusan Final
        $keputusanSummary = [];
        foreach (RegisterTrigger::$STANDAR_KEPUTUSAN as $kep) {
            $keputusanSummary[$kep] = $all->where('keputusan_final', $kep)->count();
        }

        // 3. Matrix 13 Area Risiko
        $matrixArea = [];
        $activeAlarms = $this->getActiveAlarms($user, $filterKodeUnit);
        $alarmAreaMap = [];
        foreach ($activeAlarms as $a) {
            $alarmAreaMap[$a['area']] = true;
        }

        foreach (RegisterTrigger::$STANDAR_AREAS as $areaName) {
            $inArea = $all->where('area', $areaName);
            $totalTrigger = $inArea->count();
            $totalVisit = $inArea->where('keputusan_final', RegisterTrigger::KEPUTUSAN_RISK_TRIGGER_VISIT)->count();
            // Tabel Matriks 13 Area Risiko (Kolom K - Exception): Menghitung jumlah visit pada area tersebut di Simpulan_TL yang memiliki Jumlah Exception (G) > 0 (=COUNTIFS(Simpulan_TL!D6:D205, Area, Simpulan_TL!G6:G205, ">0"))
            $totalException = $simTlAll->where('area', $areaName)->where('jumlah_exception', '>', 0)->count();

            $matrixArea[] = [
                'area' => $areaName,
                'kka_tujuan' => RegisterTrigger::mapAreaToKkaTujuan($areaName),
                'total_trigger' => $totalTrigger,
                'total_visit' => $totalVisit,
                'total_exception' => $totalException,
                'is_alarm' => isset($alarmAreaMap[$areaName]) || $totalTrigger > 5,
            ];
        }

        return [
            'kpi' => $kpi,
            'keputusan_summary' => $keputusanSummary,
            'matrix_area' => $matrixArea,
            'alarms' => $activeAlarms,
        ];
    }

    /**
     * Resolving unit name from code
     */
    protected function resolveUnitName(?string $kodeUnit): string
    {
        if (empty($kodeUnit)) {
            return 'Unit Tidak Dikenal';
        }

        $clean = str_replace('BS-', '', $kodeUnit);
        $unit = Unit::where('unit_code', $kodeUnit)->orWhere('unit_code', $clean)->first();
        if ($unit) {
            return $unit->unit_name;
        }

        $cabang = Cabang::where('kode_cabang', $kodeUnit)->orWhere('kode_cabang', 'BS-' . $clean)->first();
        if ($cabang) {
            return $cabang->nama_cabang;
        }

        return "Kantor Cabang {$kodeUnit}";
    }

    /**
     * Resolving assigned RA name for a branch code
     */
    public function resolveAssignedRaForBranch(?string $kodeUnit): string
    {
        if (empty($kodeUnit)) {
            return 'Resident Auditor';
        }

        $clean = str_replace('BS-', '', $kodeUnit);
        $cabang = Cabang::where('kode_cabang', $kodeUnit)->orWhere('kode_cabang', 'BS-' . $clean)->first();
        if (!$cabang && is_numeric($clean)) {
            $cabang = Cabang::where('kode_cabang', str_pad($clean, 3, '0', STR_PAD_LEFT))->first();
        }

        if ($cabang) {
            $raUser = User::where('cabang_id', $cabang->id)->whereRaw('LOWER(role) = ?', ['ra'])->first();
            if ($raUser) {
                return $raUser->name;
            }
        }

        // Try via Unit
        $unit = Unit::where('unit_code', $kodeUnit)->orWhere('unit_code', $clean)->first();
        if ($unit && $unit->cabang_id) {
            $raUser = User::where('cabang_id', $unit->cabang_id)->whereRaw('LOWER(role) = ?', ['ra'])->first();
            if ($raUser) {
                return $raUser->name;
            }
        }

        return 'Resident Auditor';
    }

    /**
     * Buat Assignment Scope dari Trigger dan generate Program Kerja & Data (Fase 2)
     */
    public function createAssignmentFromTrigger(RegisterTrigger $trigger, array $data, User $actor): AssignmentScope
    {
        if ($trigger->keputusan_final !== RegisterTrigger::KEPUTUSAN_RISK_TRIGGER_VISIT) {
            throw new \InvalidArgumentException("Trigger {$trigger->trigger_id} tidak berstatus 'RISK TRIGGER VISIT'. Keputusan saat ini: {$trigger->keputusan_final}");
        }

        // Cek jika sudah memiliki assignment aktif
        $existing = AssignmentScope::where('register_trigger_id', $trigger->id)->first();
        if ($existing) {
            throw new \InvalidArgumentException("Trigger {$trigger->trigger_id} sudah memiliki Assignment Visit: {$existing->visit_id}");
        }

        // Format Periode/Populasi: {dd/mm/yyyy} s.d. {dd/mm/yyyy} | {Objek/Key}
        $start = $trigger->periode_mulai ? Carbon::parse($trigger->periode_mulai)->format('d/m/Y') : '-';
        $end = $trigger->periode_selesai ? Carbon::parse($trigger->periode_selesai)->format('d/m/Y') : '-';
        $objek = $trigger->objek_key ?: '-';
        $periodePopulasi = "{$start} s.d. {$end} | {$objek}";

        $tanggalVisit = !empty($data['tanggal_visit']) ? Carbon::parse($data['tanggal_visit'])->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $visitId = AssignmentScope::generateNextVisitId($trigger->kode_unit, $tanggalVisit);

        $assignedRaSumber = $trigger->assigned_ra ?: $this->resolveAssignedRaForBranch($trigger->kode_unit);

        // Buat Assignment Scope
        $assignment = new AssignmentScope();
        $assignment->visit_id = $visitId;
        $assignment->register_trigger_id = $trigger->id;
        $assignment->trigger_id = $trigger->trigger_id;
        $assignment->kode_unit = $trigger->kode_unit;
        $assignment->nama_unit = $trigger->nama_unit ?: $this->resolveUnitName($trigger->kode_unit);
        $assignment->area = $trigger->area;
        $assignment->risk_level = $trigger->risk_level_reassessment;
        $assignment->keputusan_final = $trigger->keputusan_final;
        $assignment->kka_tujuan = $trigger->kka_tujuan;
        $assignment->scope_tujuan_awal = $trigger->uraian_trigger;
        $assignment->periode_populasi = $periodePopulasi;
        $assignment->tanggal_visit = $tanggalVisit;
        $assignment->mulai_aktual = !empty($data['mulai_aktual']) ? Carbon::parse($data['mulai_aktual'])->format('Y-m-d') : null;
        $assignment->selesai_aktual = !empty($data['selesai_aktual']) ? Carbon::parse($data['selesai_aktual'])->format('Y-m-d') : null;
        $assignment->assigned_ra_sumber = $assignedRaSumber;
        $assignment->ra_pelaksana = !empty($data['ra_pelaksana']) ? trim($data['ra_pelaksana']) : $assignedRaSumber;
        $assignment->reviewer = !empty($data['reviewer']) ? trim($data['reviewer']) : $actor->name;
        $assignment->referensi_otorisasi = !empty($data['referensi_otorisasi']) ? trim($data['referensi_otorisasi']) : null;
        $assignment->klasifikasi = !empty($data['klasifikasi']) ? trim($data['klasifikasi']) : 'Risk Trigger Visit Terarah';
        $assignment->batas_scope = !empty($data['batas_scope']) ? trim($data['batas_scope']) : null;
        $assignment->kriteria_selesai = !empty($data['kriteria_selesai']) ? trim($data['kriteria_selesai']) : null;
        $assignment->created_by = $actor->id;

        $assignment->calculateDerivedAttributes();
        $assignment->save();

        // 1. SINKRONISASI BALIK: Ubah status workflow pada RegisterTrigger menjadi "VISIT DALAM PROSES"
        $trigger->status_workflow = 'VISIT DALAM PROSES';
        $trigger->save();

        // 2. OTOMASI PROGRAM KERJA DATA: Generate 1 baris ProgramKerjaData
        $param = MasterParameter::getForArea($trigger->area);

        // Rumus Sampel Pemicu: MAX(1, Jumlah Kejadian)
        $sampelPemicu = max(1, (int)($trigger->jumlah_kejadian ?: 1));
        // Rumus Sampel Pembanding: MAX(1, ROUNDUP(SQRT(J6), 0))
        $sampelPembanding = max(1, (int)ceil(sqrt($sampelPemicu)));

        $cutoffData = $trigger->periode_selesai ?: ($trigger->tanggal_trigger ?: Carbon::today());

        $pk = new ProgramKerjaData();
        $pk->assignment_scope_id = $assignment->id;
        $pk->register_trigger_id = $trigger->id;
        $pk->kode_unit = $trigger->kode_unit;
        $pk->visit_id = $assignment->visit_id;
        $pk->trigger_id = $trigger->trigger_id;
        $pk->area = $trigger->area;
        $pk->kka_tujuan = $param?->kka_tujuan ?: $trigger->kka_tujuan;
        $pk->tujuan_uji = $param?->tujuan_uji ?: 'Membuktikan transaksi/selisih/reversal/otorisasi pemicu';
        $pk->kriteria_utama = $param?->kriteria_utama ?: 'Kebijakan dan SOP relevan';
        $pk->data_bukti_minimum = $param?->data_bukti_minimum ?: ($trigger->bukti_awal ?: 'Daftar transaksi pemicu dan voucher');
        $pk->metode_uji = $param?->metode_uji ?: 'Tracing, vouching, rekonsiliasi dan klarifikasi';
        $pk->metode_sampling = $param?->metode_sampling ?: 'Targeted + pembanding SOP 02';
        $pk->sampel_pemicu = $sampelPemicu;
        $pk->sampel_pembanding = $sampelPembanding;
        $pk->cutoff_data = $cutoffData;
        $pk->referensi_bukti_awal = $trigger->bukti_awal ?: 'Berita Acara / Laporan Temuan Modul Asal';
        $pk->langkah_tambahan_ra = !empty($data['langkah_tambahan_ra']) ? trim($data['langkah_tambahan_ra']) : null;
        $pk->tanggal_permintaan = null;
        $pk->tanggal_diterima = null;
        $pk->catatan_akses_data = null;

        $pk->calculateStatusKesiapan($assignment->perlu_data_unit);
        $pk->save();

        // 3. OTOMASI KKA RISK TRIGGER (KKA INDUK - FASE 3): Generate 1 baris KKA Induk
        $this->ensureKkaInduk($assignment, $trigger, $pk);

        // 4. OTOMASI SIMPULAN & TINDAK LANJUT (FASE 4): Generate 1 baris Simpulan TL
        $this->ensureSimpulanTl($assignment, $trigger);

        return $assignment;
    }

    /**
     * Update Tanggal Mulai dan Selesai Aktual Lapangan (Role RA / Admin)
     */
    public function updateAssignmentActualDates(AssignmentScope $assignment, array $dates, User $actor): AssignmentScope
    {
        if (array_key_exists('mulai_aktual', $dates)) {
            $assignment->mulai_aktual = !empty($dates['mulai_aktual']) ? Carbon::parse($dates['mulai_aktual'])->format('Y-m-d') : null;
        }
        if (array_key_exists('selesai_aktual', $dates)) {
            $assignment->selesai_aktual = !empty($dates['selesai_aktual']) ? Carbon::parse($dates['selesai_aktual'])->format('Y-m-d') : null;
        }

        $assignment->calculateDerivedAttributes();
        $assignment->save();

        return $assignment;
    }

    /**
     * Update Data Pelaksanaan Lapangan Program Kerja (Role RA / Admin)
     */
    public function updateProgramKerjaData(ProgramKerjaData $pk, array $data, User $actor): ProgramKerjaData
    {
        if (array_key_exists('langkah_tambahan_ra', $data)) {
            $pk->langkah_tambahan_ra = trim($data['langkah_tambahan_ra']) ?: null;
        }
        if (array_key_exists('tanggal_permintaan', $data)) {
            $pk->tanggal_permintaan = !empty($data['tanggal_permintaan']) ? Carbon::parse($data['tanggal_permintaan'])->format('Y-m-d') : null;
        }
        if (array_key_exists('tanggal_diterima', $data)) {
            $pk->tanggal_diterima = !empty($data['tanggal_diterima']) ? Carbon::parse($data['tanggal_diterima'])->format('Y-m-d') : null;
        }
        if (array_key_exists('catatan_akses_data', $data)) {
            $pk->catatan_akses_data = trim($data['catatan_akses_data']) ?: null;
        }

        $pk->calculateStatusKesiapan();
        $pk->save();

        return $pk;
    }

    /**
     * Pastikan 1 baris KKA Induk (KKA_Risk_Trigger) ter-generate untuk Visit ID ini
     */
    public function ensureKkaInduk(AssignmentScope $assignment, ?RegisterTrigger $trigger = null, ?ProgramKerjaData $pk = null): RiskTriggerKkaInduk
    {
        $kka = RiskTriggerKkaInduk::where('visit_id', $assignment->visit_id)->first();
        if ($kka) {
            return $kka;
        }

        if (!$trigger) {
            $trigger = $assignment->registerTrigger ?: RegisterTrigger::where('trigger_id', $assignment->trigger_id)->first();
        }

        if (!$pk) {
            $pk = ProgramKerjaData::where('visit_id', $assignment->visit_id)->first();
        }

        $area = $assignment->area ?: ($trigger?->area ?: 'Area Risiko');
        $pertanyaanAudit = "Apakah risk trigger pada " . $area . " terbukti, berulang, material, atau memerlukan tindak lanjut/perluasan?";
        $kondisiAwal = $trigger?->uraian_trigger ?: ($assignment->scope_tujuan_awal ?: '-');
        $kriteria = $pk?->kriteria_utama ?: 'Kebijakan dan SOP relevan';
        $prosedurUtama = $pk?->metode_uji ?: 'Tracing, vouching, rekonsiliasi dan klarifikasi';
        $kkaArea = $assignment->kka_tujuan ?: ($trigger?->kka_tujuan ?: 'KKA_Risk_Trigger');

        $kka = new RiskTriggerKkaInduk();
        $kka->assignment_scope_id = $assignment->id;
        $kka->register_trigger_id = $assignment->register_trigger_id ?: $trigger?->id;
        $kka->visit_id = $assignment->visit_id;
        $kka->trigger_id = $assignment->trigger_id;
        $kka->kode_unit = $assignment->kode_unit;
        $kka->unit_kantor = $assignment->nama_unit ?: ($trigger?->nama_unit ?: 'Kantor Cabang');
        $kka->area = $area;
        $kka->risk_level = $assignment->risk_level ?: 'HIGH';
        $kka->pertanyaan_audit = $pertanyaanAudit;
        $kka->kondisi_awal = $kondisiAwal;
        $kka->kriteria = $kriteria;
        $kka->prosedur_utama = $prosedurUtama;
        $kka->ringkasan_bukti = null;
        $kka->hasil_analisis = null;
        $kka->trigger_terbukti = null;
        $kka->perlu_perluasan = 'Tidak';
        $kka->alasan_perluasan = null;
        $kka->kka_area = $kkaArea;
        $kka->exception_area = 0;
        $kka->simpulan_ra = null;
        $kka->ra_pelaksana = $assignment->ra_pelaksana;
        $kka->reviewer = $assignment->reviewer;
        $kka->status_review = 'DRAFT / BELUM DIREVIEW';
        $kka->save();

        $this->recalculateExceptionArea($assignment->visit_id);
        $kka->refresh();

        return $kka;
    }

    /**
     * Sinkronisasi seluruh KKA Induk untuk semua Assignment Scope yang ada
     */
    public function syncAllKkaInduk(): int
    {
        $assignments = AssignmentScope::all();
        $created = 0;
        foreach ($assignments as $a) {
            $existing = RiskTriggerKkaInduk::where('visit_id', $a->visit_id)->exists();
            if (!$existing) {
                $this->ensureKkaInduk($a);
                $created++;
            } else {
                $this->recalculateExceptionArea($a->visit_id);
            }
        }
        return $created;
    }

    /**
     * Hitung Exception Area lintas 8 sheet KKA Area secara real-time dan update ke KKA Induk
     */
    public function recalculateExceptionArea(string $visitId): int
    {
        $totalExceptions = 
            KkaTellerKas::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaDpkLayanan::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaKredit::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaBiayaBeban::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaAtm::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaPengaduan::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaFisik::where('visit_id', $visitId)->where('exception', 'Ya')->count() +
            KkaObservasi::where('visit_id', $visitId)->where('exception', 'Ya')->count();

        RiskTriggerKkaInduk::where('visit_id', $visitId)->update(['exception_area' => $totalExceptions]);

        // Real-time update Simpulan TL Kolom G (Jumlah Exception) & recalculate derived attributes
        $simTl = SimpulanTl::where('visit_id', $visitId)->first();
        if ($simTl) {
            $simTl->jumlah_exception = $totalExceptions;
            $simTl->calculateDerivedAttributes();
            $simTl->saveQuietly();
        }

        return $totalExceptions;
    }

    /**
     * Cek apakah user berhak mengubah KKA Induk / KKA Area
     */
    public function canModifyKka(RiskTriggerKkaInduk $kka, User $user): bool
    {
        $role = strtolower((string)$user->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if ($isAdmin) {
            return true;
        }

        // Role RA: hanya cabang sendiri dan status review belum FINAL
        $allowed = RiskTriggerService::getAccessibleUnitCodes($user);
        if ($allowed && !in_array($kka->kode_unit, $allowed)) {
            return false;
        }

        if ($kka->status_review === 'FINAL') {
            return false;
        }

        return true;
    }

    /**
     * Update Kolom Input KKA Induk (RA & Reviewer)
     */
    public function updateKkaInduk(RiskTriggerKkaInduk $kka, array $data, User $actor): RiskTriggerKkaInduk
    {
        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);

        if (!$isAdmin) {
            // Isolasi Cabang
            $allowed = RiskTriggerService::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($kka->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat mengakses KKA cabang Anda sendiri.');
            }
            // Kunci jika status FINAL
            if ($kka->status_review === 'FINAL') {
                abort(403, 'KKA Induk ini sudah FINAL dan telah dikunci oleh Reviewer.');
            }
        }

        // Input Pengujian Profesional RA (Kolom J s.d. N, Kolom Q)
        if (array_key_exists('ringkasan_bukti', $data)) {
            $kka->ringkasan_bukti = trim($data['ringkasan_bukti']) ?: null;
        }
        if (array_key_exists('hasil_analisis', $data)) {
            $kka->hasil_analisis = trim($data['hasil_analisis']) ?: null;
        }
        if (array_key_exists('trigger_terbukti', $data)) {
            $kka->trigger_terbukti = trim($data['trigger_terbukti']) ?: null;
        }
        if (array_key_exists('perlu_perluasan', $data)) {
            $kka->perlu_perluasan = trim($data['perlu_perluasan']) ?: 'Tidak';
            if ($kka->perlu_perluasan === 'Ya' && !empty($data['alasan_perluasan'])) {
                $kka->alasan_perluasan = trim($data['alasan_perluasan']);
            } elseif ($kka->perlu_perluasan !== 'Ya') {
                $kka->alasan_perluasan = null;
            }
        }
        if (array_key_exists('alasan_perluasan', $data) && $kka->perlu_perluasan === 'Ya') {
            $kka->alasan_perluasan = trim($data['alasan_perluasan']) ?: null;
        }
        if (array_key_exists('simpulan_ra', $data)) {
            $kka->simpulan_ra = trim($data['simpulan_ra']) ?: null;
        }

        // Status Review (Kolom T): Khusus Admin / Reviewer
        if ($isAdmin && array_key_exists('status_review', $data)) {
            $validStatuses = ['DRAFT / BELUM DIREVIEW', 'REVISI', 'FINAL'];
            if (in_array(trim($data['status_review']), $validStatuses)) {
                $kka->status_review = trim($data['status_review']);
            }
        }

        $kka->save();

        // Hitung ulang exception area
        $kka->exception_area = $this->recalculateExceptionArea($kka->visit_id);
        $kka->save();

        // Sinkronisasi otomatis ke Simpulan & Tindak Lanjut (Fase 4)
        $this->syncSimpulanTlFromKka($kka);

        return $kka;
    }

    /**
     * Resolusi Model Class untuk 8 KKA Area
     */
    public static function getAreaModelClass(?string $kkaArea): ?string
    {
        $map = [
            'KKA_Teller_Kas' => KkaTellerKas::class,
            'KKA_DPK_Layanan' => KkaDpkLayanan::class,
            'KKA_Kredit' => KkaKredit::class,
            'KKA_Biaya_Beban' => KkaBiayaBeban::class,
            'KKA_ATM' => KkaAtm::class,
            'KKA_Pengaduan' => KkaPengaduan::class,
            'KKA_Fisik' => KkaFisik::class,
            'KKA_Observasi' => KkaObservasi::class,
        ];

        return $map[$kkaArea] ?? null;
    }

    /**
     * Ambil data baris pengujian untuk suatu KKA Area & Visit ID
     */
    public function getKkaAreaRows(string $kkaArea, string $visitId)
    {
        $modelClass = static::getAreaModelClass($kkaArea);
        if (!$modelClass) {
            return collect();
        }

        return $modelClass::where('visit_id', $visitId)->orderBy('id', 'asc')->get();
    }

    /**
     * Simpan baris sampel baru ke KKA Area terkait
     */
    public function storeAreaRow(string $kkaArea, string $visitId, array $data, User $actor)
    {
        $modelClass = static::getAreaModelClass($kkaArea);
        if (!$modelClass) {
            throw new \InvalidArgumentException("KKA Area '{$kkaArea}' tidak valid.");
        }

        $assignment = AssignmentScope::where('visit_id', $visitId)->firstOrFail();
        $kkaInduk = $this->ensureKkaInduk($assignment);

        if (!$this->canModifyKka($kkaInduk, $actor)) {
            abort(403, 'Akses Ditolak: KKA telah dikunci (FINAL) atau bukan wewenang cabang Anda.');
        }

        $row = new $modelClass();
        $row->assignment_scope_id = $assignment->id;
        $row->visit_id = $assignment->visit_id;
        $row->trigger_id = $assignment->trigger_id;
        $row->kode_unit = $assignment->kode_unit;
        $row->unit_kantor = $assignment->nama_unit ?: 'Kantor Cabang';
        $row->area = $assignment->area;
        $row->created_by = $actor->id;

        // Fill remaining input fields
        $row->fill($data);
        $row->save();

        // Real-time update exception area pada KKA Induk
        $this->recalculateExceptionArea($visitId);

        return $row;
    }

    /**
     * Update baris sampel pada KKA Area terkait
     */
    public function updateAreaRow(string $kkaArea, int $id, array $data, User $actor)
    {
        $modelClass = static::getAreaModelClass($kkaArea);
        if (!$modelClass) {
            throw new \InvalidArgumentException("KKA Area '{$kkaArea}' tidak valid.");
        }

        $row = $modelClass::findOrFail($id);
        $kkaInduk = RiskTriggerKkaInduk::where('visit_id', $row->visit_id)->first();
        if ($kkaInduk && !$this->canModifyKka($kkaInduk, $actor)) {
            abort(403, 'Akses Ditolak: KKA telah dikunci (FINAL) atau bukan wewenang cabang Anda.');
        }

        $row->fill($data);
        $row->updated_by = $actor->id;
        $row->save();

        // Real-time update exception area pada KKA Induk
        $this->recalculateExceptionArea($row->visit_id);

        return $row;
    }

    /**
     * Hapus baris sampel pada KKA Area terkait
     */
    public function deleteAreaRow(string $kkaArea, int $id, User $actor)
    {
        $modelClass = static::getAreaModelClass($kkaArea);
        if (!$modelClass) {
            throw new \InvalidArgumentException("KKA Area '{$kkaArea}' tidak valid.");
        }

        $row = $modelClass::findOrFail($id);
        $visitId = $row->visit_id;
        $kkaInduk = RiskTriggerKkaInduk::where('visit_id', $visitId)->first();
        if ($kkaInduk && !$this->canModifyKka($kkaInduk, $actor)) {
            abort(403, 'Akses Ditolak: KKA telah dikunci (FINAL) atau bukan wewenang cabang Anda.');
        }

        $row->delete();

        // Real-time update exception area pada KKA Induk
        $this->recalculateExceptionArea($visitId);

        return true;
    }

    /* =========================================================================
     * FASE 4: SIMPULAN, TINDAK LANJUT & CLOSURE (Simpulan_TL) & KLARIFIKASI BUKTI
     * ========================================================================= */

    /**
     * Pastikan 1 baris Simpulan & Tindak Lanjut (Simpulan_TL) ter-generate untuk Visit ID ini
     */
    public function ensureSimpulanTl(AssignmentScope $assignment, ?RegisterTrigger $trigger = null, ?RiskTriggerKkaInduk $kka = null): SimpulanTl
    {
        $simTl = SimpulanTl::where('visit_id', $assignment->visit_id)->first();
        if ($simTl) {
            return $simTl;
        }

        if (!$trigger) {
            $trigger = $assignment->registerTrigger ?: RegisterTrigger::where('trigger_id', $assignment->trigger_id)->first();
        }

        if (!$kka) {
            $kka = RiskTriggerKkaInduk::where('visit_id', $assignment->visit_id)->first();
        }

        $area = $assignment->area ?: ($trigger?->area ?: 'Area Risiko');
        $kkaTujuan = $assignment->kka_tujuan ?: ($trigger?->kka_tujuan ?: 'KKA_Risk_Trigger');

        $simTl = new SimpulanTl();
        $simTl->assignment_scope_id = $assignment->id;
        $simTl->register_trigger_id = $assignment->register_trigger_id ?: $trigger?->id;
        $simTl->kode_unit = $assignment->kode_unit;
        $simTl->unit_kantor = $assignment->nama_unit ?: ($trigger?->nama_unit ?: 'Kantor Cabang');
        $simTl->visit_id = $assignment->visit_id;
        $simTl->trigger_id = $assignment->trigger_id;
        $simTl->area = $area;
        $simTl->risk_level_awal = $assignment->risk_level ?: 'HIGH';
        $simTl->trigger_terbukti = $kka?->trigger_terbukti;
        $simTl->jumlah_exception = $kka?->exception_area ?? 0;
        $simTl->simpulan_akhir = $kka?->simpulan_ra;
        $simTl->klasifikasi_hasil = null;
        $simTl->perlu_perluasan = $kka?->perlu_perluasan ?? 'Tidak';
        $simTl->scope_perluasan = $kka?->alasan_perluasan;
        $simTl->tindak_lanjut_awal = null;
        $simTl->pic_unit = null;
        $simTl->due_date = null;
        $simTl->level_eskalasi = 'Tidak Perlu';
        $simTl->tujuan_eskalasi = null;
        $simTl->laporan = null;
        $simTl->referensi_kka_bukti = "{$kkaTujuan} | Klarifikasi_Bukti";
        $simTl->ra_pelaksana = $assignment->ra_pelaksana;
        $simTl->reviewer = $assignment->reviewer;
        $simTl->status_review = 'DRAFT';

        $simTl->calculateDerivedAttributes();
        $simTl->save();

        // Update status workflow di Register Trigger jika ada record aktif di Simpulan TL
        RegisterTrigger::where('trigger_id', $assignment->trigger_id)->update([
            'status_workflow' => 'SELESAI/DALAM TL'
        ]);

        return $simTl;
    }

    /**
     * Sinkronisasi data dari KKA Induk ke Simpulan TL
     */
    public function syncSimpulanTlFromKka(RiskTriggerKkaInduk $kka): void
    {
        $simTl = SimpulanTl::where('visit_id', $kka->visit_id)->first();
        if ($simTl) {
            $simTl->trigger_terbukti = $kka->trigger_terbukti;
            $simTl->perlu_perluasan = $kka->perlu_perluasan;
            $simTl->scope_perluasan = $kka->alasan_perluasan;
            if (empty($simTl->simpulan_akhir) && !empty($kka->simpulan_ra)) {
                $simTl->simpulan_akhir = $kka->simpulan_ra;
            }
            $simTl->calculateDerivedAttributes();
            $simTl->save();
        }
    }

    /**
     * Sinkronisasi seluruh Simpulan TL untuk semua Assignment Scope yang ada
     */
    public function syncAllSimpulanTl(): int
    {
        $assignments = AssignmentScope::all();
        $created = 0;
        foreach ($assignments as $a) {
            $existing = SimpulanTl::where('visit_id', $a->visit_id)->exists();
            if (!$existing) {
                $this->ensureSimpulanTl($a);
                $created++;
            } else {
                $simTl = SimpulanTl::where('visit_id', $a->visit_id)->first();
                if ($simTl) {
                    $simTl->calculateDerivedAttributes();
                    $simTl->save();
                }
            }
        }
        return $created;
    }

    /**
     * Hitung ulang status klarifikasi dan status closure pada Simpulan TL
     */
    public function recalculateSimpulanTl(string $visitId): void
    {
        $simTl = SimpulanTl::where('visit_id', $visitId)->first();
        if ($simTl) {
            $simTl->calculateDerivedAttributes();
            $simTl->save();
        }
    }

    /**
     * Cek apakah user berhak mengubah Simpulan TL
     */
    public function canModifySimpulanTl(SimpulanTl $simpulan, User $user): bool
    {
        $role = strtolower((string)$user->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if ($isAdmin) {
            return true;
        }

        // RA: Periksa isolasi cabang dan status kunci
        $allowed = static::getAccessibleUnitCodes($user);
        if ($allowed && !in_array($simpulan->kode_unit, $allowed)) {
            return false;
        }

        // Kunci jika status FINAL & CLOSED
        if ($simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
            return false;
        }

        return true;
    }

    /**
     * Update Simpulan & Tindak Lanjut (Kolom J, M s.d. R, V)
     */
    public function updateSimpulanTl(SimpulanTl $simpulan, array $data, User $actor): SimpulanTl
    {
        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);

        if (!$isAdmin) {
            // Isolasi Cabang
            $allowed = static::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($simpulan->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat mengakses Simpulan TL cabang Anda sendiri.');
            }
            // Kunci jika status review sudah FINAL dan CLOSED
            if ($simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
                abort(403, 'Simpulan & Tindak Lanjut ini sudah FINAL dan telah DITUTUP. Akses terkunci.');
            }
        }

        // RA & Admin dapat mengupdate usulan penyelesaian & tindak lanjut
        if (array_key_exists('simpulan_akhir', $data)) {
            $simpulan->simpulan_akhir = trim($data['simpulan_akhir']) ?: null;
        }
        if (array_key_exists('klasifikasi_hasil', $data)) {
            $simpulan->klasifikasi_hasil = trim($data['klasifikasi_hasil']) ?: null;
        }
        if (array_key_exists('tindak_lanjut_awal', $data)) {
            $simpulan->tindak_lanjut_awal = trim($data['tindak_lanjut_awal']) ?: null;
        }
        if (array_key_exists('pic_unit', $data)) {
            $simpulan->pic_unit = trim($data['pic_unit']) ?: null;
        }
        if (array_key_exists('due_date', $data)) {
            $simpulan->due_date = !empty($data['due_date']) ? Carbon::parse($data['due_date'])->format('Y-m-d') : null;
        }
        if (array_key_exists('level_eskalasi', $data)) {
            $simpulan->level_eskalasi = trim($data['level_eskalasi']) ?: 'Tidak Perlu';
        }
        if (array_key_exists('tujuan_eskalasi', $data)) {
            $simpulan->tujuan_eskalasi = trim($data['tujuan_eskalasi']) ?: null;
        }
        if (array_key_exists('laporan', $data)) {
            $simpulan->laporan = trim($data['laporan']) ?: null;
        }

        // Khusus Admin / Reviewer: Status Review (Kolom V: DRAFT, REVISI, FINAL)
        if ($isAdmin && array_key_exists('status_review', $data)) {
            $validStatusReviews = ['DRAFT', 'REVISI', 'FINAL'];
            if (in_array(trim($data['status_review']), $validStatusReviews)) {
                $simpulan->status_review = trim($data['status_review']);
            }
        }

        $simpulan->updated_by = $actor->id;
        $simpulan->calculateDerivedAttributes();
        $simpulan->save();

        // Update status workflow di Register Trigger
        RegisterTrigger::where('trigger_id', $simpulan->trigger_id)->update([
            'status_workflow' => 'SELESAI/DALAM TL'
        ]);

        return $simpulan;
    }

    /**
     * Ambil daftar klarifikasi bukti untuk Visit ID tertentu
     */
    public function getKlarifikasiList(string $visitId, ?User $user = null)
    {
        $query = KlarifikasiBukti::where('visit_id', $visitId)->orderBy('id', 'asc');
        if ($user && strtolower($user->role) === 'ra') {
            $query->byBranch($user);
        }
        return $query->get();
    }

    /**
     * Tambah baris Klarifikasi & Bukti baru (Multi-row per Visit ID)
     */
    public function storeKlarifikasi(array $data, User $actor, ?\Illuminate\Http\UploadedFile $file = null): KlarifikasiBukti
    {
        $assignment = AssignmentScope::where('visit_id', $data['visit_id'])->firstOrFail();

        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if (!$isAdmin) {
            $allowed = static::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($assignment->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat menambahkan klarifikasi untuk cabang Anda sendiri.');
            }
        }

        // Handle file upload if present
        $filePath = null;
        if ($file && $file->isValid()) {
            $filePath = $file->store('risk_trigger_klarifikasi', 'public');
        }

        $row = new KlarifikasiBukti();
        $row->assignment_scope_id = $assignment->id;
        $row->register_trigger_id = $assignment->register_trigger_id;
        $row->kode_unit = $assignment->kode_unit;
        $row->unit_kantor = $assignment->nama_unit ?: 'Kantor Cabang';
        $row->visit_id = $assignment->visit_id;
        $row->trigger_id = $assignment->trigger_id;
        $row->area = $assignment->area;

        // Input oleh RA (Kolom D s.d. N)
        $row->isu_exception = trim($data['isu_exception'] ?? '');
        $row->pihak_diklarifikasi = trim($data['pihak_diklarifikasi'] ?? '') ?: null;
        $row->tanggal_permintaan = !empty($data['tanggal_permintaan']) ? Carbon::parse($data['tanggal_permintaan'])->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $row->pertanyaan_klarifikasi = trim($data['pertanyaan_klarifikasi'] ?? '') ?: null;
        $row->jawaban_auditee = trim($data['jawaban_auditee'] ?? '') ?: null;
        $row->bukti_diminta = trim($data['bukti_diminta'] ?? '') ?: null;
        $row->bukti_diterima = trim($data['bukti_diterima'] ?? '') ?: null;
        $row->lampiran_file = $filePath;
        $row->tanggal_diterima = !empty($data['tanggal_diterima']) ? Carbon::parse($data['tanggal_diterima'])->format('Y-m-d') : null;
        $row->kecukupan_bukti = in_array($data['kecukupan_bukti'] ?? '', ['Memadai', 'Kurang Memadai', 'Tidak Ada Bukti']) ? $data['kecukupan_bukti'] : 'Kurang Memadai';
        $row->analisis_ra = trim($data['analisis_ra'] ?? '') ?: null;
        $row->status_klarifikasi = in_array($data['status_klarifikasi'] ?? '', ['Open', 'Dalam Proses', 'Closed']) ? $data['status_klarifikasi'] : 'Open';

        // Kolom O & P: Reviewer & Catatan Review
        $row->reviewer = $assignment->reviewer ?: ($isAdmin ? $actor->name : null);
        if ($isAdmin && !empty($data['catatan_review'])) {
            $row->catatan_review = trim($data['catatan_review']);
        }

        $row->created_by = $actor->id;
        $row->save();

        return $row;
    }

    /**
     * Update baris Klarifikasi & Bukti
     */
    public function updateKlarifikasi(KlarifikasiBukti $row, array $data, User $actor, ?\Illuminate\Http\UploadedFile $file = null): KlarifikasiBukti
    {
        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if (!$isAdmin) {
            $allowed = static::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($row->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat mengedit klarifikasi cabang Anda sendiri.');
            }
            // Check if locked
            $simpulan = SimpulanTl::where('visit_id', $row->visit_id)->first();
            if ($simpulan && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
                abort(403, 'Klarifikasi telah terkunci karena status Simpulan TL sudah FINAL & CLOSED.');
            }
        }

        if ($file && $file->isValid()) {
            $row->lampiran_file = $file->store('risk_trigger_klarifikasi', 'public');
        }

        // RA & Admin can edit D-N
        if (array_key_exists('isu_exception', $data)) {
            $row->isu_exception = trim($data['isu_exception']);
        }
        if (array_key_exists('pihak_diklarifikasi', $data)) {
            $row->pihak_diklarifikasi = trim($data['pihak_diklarifikasi']) ?: null;
        }
        if (array_key_exists('tanggal_permintaan', $data)) {
            $row->tanggal_permintaan = !empty($data['tanggal_permintaan']) ? Carbon::parse($data['tanggal_permintaan'])->format('Y-m-d') : null;
        }
        if (array_key_exists('pertanyaan_klarifikasi', $data)) {
            $row->pertanyaan_klarifikasi = trim($data['pertanyaan_klarifikasi']) ?: null;
        }
        if (array_key_exists('jawaban_auditee', $data)) {
            $row->jawaban_auditee = trim($data['jawaban_auditee']) ?: null;
        }
        if (array_key_exists('bukti_diminta', $data)) {
            $row->bukti_diminta = trim($data['bukti_diminta']) ?: null;
        }
        if (array_key_exists('bukti_diterima', $data)) {
            $row->bukti_diterima = trim($data['bukti_diterima']) ?: null;
        }
        if (array_key_exists('tanggal_diterima', $data)) {
            $row->tanggal_diterima = !empty($data['tanggal_diterima']) ? Carbon::parse($data['tanggal_diterima'])->format('Y-m-d') : null;
        }
        if (array_key_exists('kecukupan_bukti', $data) && in_array($data['kecukupan_bukti'], ['Memadai', 'Kurang Memadai', 'Tidak Ada Bukti'])) {
            $row->kecukupan_bukti = $data['kecukupan_bukti'];
        }
        if (array_key_exists('analisis_ra', $data)) {
            $row->analisis_ra = trim($data['analisis_ra']) ?: null;
        }
        if (array_key_exists('status_klarifikasi', $data) && in_array($data['status_klarifikasi'], ['Open', 'Dalam Proses', 'Closed'])) {
            $row->status_klarifikasi = $data['status_klarifikasi'];
        }

        // Catatan Review (Kolom P): Admin/Reviewer Only!
        if ($isAdmin && array_key_exists('catatan_review', $data)) {
            $row->catatan_review = trim($data['catatan_review']) ?: null;
            $row->reviewer = $actor->name;
        }

        $row->updated_by = $actor->id;
        $row->save();

        return $row;
    }

    /**
     * Hapus baris Klarifikasi & Bukti
     */
    public function deleteKlarifikasi(KlarifikasiBukti $row, User $actor): bool
    {
        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if (!$isAdmin) {
            $allowed = static::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($row->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat menghapus klarifikasi cabang Anda sendiri.');
            }
            $simpulan = SimpulanTl::where('visit_id', $row->visit_id)->first();
            if ($simpulan && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
                abort(403, 'Klarifikasi telah terkunci karena status Simpulan TL sudah FINAL & CLOSED.');
            }
        }

        $row->delete();
        return true;
    }

    /**
     * Tarik Cepat daftar baris berstatus EXCEPTION dari 8 KKA Area ke Klarifikasi & Bukti
     */
    public function pullExceptionsToKlarifikasi(string $visitId, User $actor): int
    {
        $assignment = AssignmentScope::where('visit_id', $visitId)->firstOrFail();

        $role = strtolower((string)$actor->role);
        $isAdmin = in_array($role, ['admin', 'superadmin', 'administrator', 'reviewer', 'head']);
        if (!$isAdmin) {
            $allowed = static::getAccessibleUnitCodes($actor);
            if ($allowed && !in_array($assignment->kode_unit, $allowed)) {
                abort(403, 'Akses Ditolak: Anda hanya dapat menarik exception untuk cabang Anda sendiri.');
            }
            $simpulan = SimpulanTl::where('visit_id', $visitId)->first();
            if ($simpulan && $simpulan->status_review === 'FINAL' && $simpulan->status_closure === 'CLOSED/DITERUSKAN') {
                abort(403, 'Simpulan TL sudah FINAL & CLOSED. Data terkunci.');
            }
        }

        $exceptionItems = [];

        // 1. Teller Kas
        $rows = KkaTellerKas::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Teller Kas',
                'desc' => "[KKA_Teller_Kas] Ref: " . ($r->nomor_referensi ?: '-') . " | Selisih: Rp " . number_format((float)($r->selisih_nominal ?? 0), 0, ',', '.') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Ditemukan exception pada pengujian teller kas'),
            ];
        }

        // 2. DPK Layanan
        $rows = KkaDpkLayanan::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'DPK & Layanan',
                'desc' => "[KKA_DPK_Layanan] Rek: " . ($r->nomor_rekening ?: '-') . " (" . ($r->nama_nasabah ?: '-') . ") | Uraian: " . ($r->catatan_pemeriksaan ?: 'Exception kelengkapan dokumen/otorisasi DPK'),
            ];
        }

        // 3. Kredit
        $rows = KkaKredit::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Kredit',
                'desc' => "[KKA_Kredit] Debitur: " . ($r->nama_debitur ?: '-') . " (No Rek/PK: " . ($r->nomor_rekening ?: '-') . ") | Baki Debet: Rp " . number_format((float)($r->baki_debet ?? 0), 0, ',', '.') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Penyimpangan kriteria kredit'),
            ];
        }

        // 4. Biaya & Beban
        $rows = KkaBiayaBeban::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Biaya & Beban',
                'desc' => "[KKA_Biaya_Beban] No Bukti: " . ($r->nomor_bukti ?: '-') . " | Pos: " . ($r->pos_biaya ?: '-') . " | Nominal: Rp " . number_format((float)($r->nominal ?? 0), 0, ',', '.') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Penyimpangan pos biaya/beban'),
            ];
        }

        // 5. ATM
        $rows = KkaAtm::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Kartu ATM',
                'desc' => "[KKA_ATM] Mesin: " . ($r->id_atm ?: '-') . " (" . ($r->lokasi_atm ?: '-') . ") | Selisih Fisik: Rp " . number_format((float)($r->selisih_fisik ?? 0), 0, ',', '.') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Exception operasional/kas ATM'),
            ];
        }

        // 6. Pengaduan
        $rows = KkaPengaduan::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Pengaduan',
                'desc' => "[KKA_Pengaduan] Tiket: " . ($r->nomor_tiket ?: '-') . " | Pelapor: " . ($r->nama_pelapor ?: '-') . " | Penyelesaian: " . ($r->status_penyelesaian ?: '-') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Penyimpangan penanganan aduan'),
            ];
        }

        // 7. Fisik
        $rows = KkaFisik::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Fisik & Kas Opname',
                'desc' => "[KKA_Fisik] Objek: " . ($r->objek_fisik ?: '-') . " | Selisih: Rp " . number_format((float)($r->selisih_fisik ?? 0), 0, ',', '.') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Exception pemeriksaan fisik/kas'),
            ];
        }

        // 8. Observasi
        $rows = KkaObservasi::where('visit_id', $visitId)->where('exception', 'Ya')->get();
        foreach ($rows as $r) {
            $exceptionItems[] = [
                'area' => 'Observasi Operasional',
                'desc' => "[KKA_Observasi] Aspek: " . ($r->aspek_observasi ?: '-') . " | Kondisi: " . ($r->kondisi_ditemukan ?: '-') . " | Catatan: " . ($r->catatan_pemeriksaan ?: 'Penyimpangan kontrol operasional'),
            ];
        }

        $pulledCount = 0;
        foreach ($exceptionItems as $item) {
            $exists = KlarifikasiBukti::where('visit_id', $visitId)
                ->where('isu_exception', $item['desc'])
                ->exists();

            if (!$exists) {
                $klar = new KlarifikasiBukti();
                $klar->assignment_scope_id = $assignment->id;
                $klar->register_trigger_id = $assignment->register_trigger_id;
                $klar->kode_unit = $assignment->kode_unit;
                $klar->unit_kantor = $assignment->nama_unit ?: 'Kantor Cabang';
                $klar->visit_id = $assignment->visit_id;
                $klar->trigger_id = $assignment->trigger_id;
                $klar->area = $assignment->area ?: $item['area'];
                $klar->isu_exception = $item['desc'];
                $klar->pihak_diklarifikasi = null;
                $klar->tanggal_permintaan = Carbon::today()->format('Y-m-d');
                $klar->pertanyaan_klarifikasi = "Mohon penjelasan/klarifikasi dan bukti pendukung terkait exception berikut: " . $item['desc'];
                $klar->jawaban_auditee = null;
                $klar->bukti_diminta = "Dokumen pendukung / bukti transaksi / penjelasan resmi";
                $klar->bukti_diterima = null;
                $klar->lampiran_file = null;
                $klar->tanggal_diterima = null;
                $klar->kecukupan_bukti = 'Kurang Memadai';
                $klar->analisis_ra = 'Perlu klarifikasi auditee untuk membuktikan keabsahan atau penyebab exception';
                $klar->status_klarifikasi = 'Open';
                $klar->reviewer = $assignment->reviewer;
                $klar->catatan_review = null;
                $klar->created_by = $actor->id;
                $klar->save();

                $pulledCount++;
            }
        }

        // Recalculate Simpulan TL derived attributes
        $simTl = SimpulanTl::where('visit_id', $visitId)->first();
        if ($simTl) {
            $simTl->calculateDerivedAttributes();
            $simTl->saveQuietly();
        }

        return $pulledCount;
    }
}

