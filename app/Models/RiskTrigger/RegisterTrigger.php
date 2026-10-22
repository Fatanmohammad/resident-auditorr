<?php

namespace App\Models\RiskTrigger;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Cabang;
use App\Models\Unit;
use Carbon\Carbon;

class RegisterTrigger extends Model
{
    use HasFactory;

    protected $table = 'register_triggers';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_trigger' => 'date',
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'sla_deadline' => 'date',
        'risk_score_sumber' => 'float',
        'eksposur_nominal' => 'float',
        'jumlah_kejadian' => 'integer',
        'likelihood' => 'integer',
        'impact' => 'integer',
        'risk_score_reassessment' => 'float',
    ];

    // 13 Standar Area SOP 06
    public const AREA_TELLER_KAS = 'Teller Kas';
    public const AREA_DPK_LAYANAN = 'DPK Layanan';
    public const AREA_KREDIT = 'Kredit';
    public const AREA_BIAYA_BEBAN = 'Biaya Beban';
    public const AREA_KARTU_ATM = 'Kartu ATM';
    public const AREA_PENGADUAN = 'Pengaduan';
    public const AREA_REKON_KARTU_ATM = 'Rekon Kartu ATM';
    public const AREA_CASH_OPNAME = 'Cash Opname';
    public const AREA_OBSERVASI_LINGKUNGAN = 'Observasi Lingkungan';
    public const AREA_REKON_CEK_BG = 'Rekon Cek BG';
    public const AREA_REKON_BARANG_CETAKAN = 'Rekon Barang Cetakan';
    public const AREA_ASET = 'Aset';
    public const AREA_UMUM_LAINNYA = 'Umum/Lainnya';

    public static array $STANDAR_AREAS = [
        self::AREA_TELLER_KAS,
        self::AREA_DPK_LAYANAN,
        self::AREA_KREDIT,
        self::AREA_BIAYA_BEBAN,
        self::AREA_KARTU_ATM,
        self::AREA_PENGADUAN,
        self::AREA_REKON_KARTU_ATM,
        self::AREA_CASH_OPNAME,
        self::AREA_OBSERVASI_LINGKUNGAN,
        self::AREA_REKON_CEK_BG,
        self::AREA_REKON_BARANG_CETAKAN,
        self::AREA_ASET,
        self::AREA_UMUM_LAINNYA,
    ];

    // 8 Standar Keputusan Final SOP 06
    public const KEPUTUSAN_RISK_TRIGGER_VISIT = 'RISK TRIGGER VISIT';
    public const KEPUTUSAN_KLARIFIKASI_OFFSITE = 'KLARIFIKASI OFFSITE';
    public const KEPUTUSAN_MONITORING = 'MONITORING';
    public const KEPUTUSAN_SURPRISE_AUDIT = 'SOP 05 - SURPRISE AUDIT';
    public const KEPUTUSAN_MONITORING_TL = 'SOP 07 - MONITORING TL';
    public const KEPUTUSAN_FRAUD = 'SOP 09 - PEMERIKSAAN AWAL FRAUD';
    public const KEPUTUSAN_ESKALASI_LANGSUNG = 'ESKALASI LANGSUNG';
    public const KEPUTUSAN_TUTUP_TIDAK_RELEVAN = 'TUTUP - TIDAK RELEVAN';

    public static array $STANDAR_KEPUTUSAN = [
        self::KEPUTUSAN_RISK_TRIGGER_VISIT,
        self::KEPUTUSAN_KLARIFIKASI_OFFSITE,
        self::KEPUTUSAN_MONITORING,
        self::KEPUTUSAN_SURPRISE_AUDIT,
        self::KEPUTUSAN_MONITORING_TL,
        self::KEPUTUSAN_FRAUD,
        self::KEPUTUSAN_ESKALASI_LANGSUNG,
        self::KEPUTUSAN_TUTUP_TIDAK_RELEVAN,
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignmentScope()
    {
        return $this->hasOne(AssignmentScope::class, 'register_trigger_id');
    }

    public function programKerjaData()
    {
        return $this->hasOne(ProgramKerjaData::class, 'register_trigger_id');
    }

    /**
     * Helper to generate sequential Trigger ID: RT-{YYYYMMDD}-{0001}
     */
    public static function generateNextTriggerId($tanggal = null): string
    {
        $dateObj = $tanggal ? Carbon::parse($tanggal) : Carbon::today();
        $dateStr = $dateObj->format('Ymd');
        $prefix = "RT-{$dateStr}-";

        $lastRecord = static::where('trigger_id', 'like', "{$prefix}%")
            ->orderBy('trigger_id', 'desc')
            ->first();

        if ($lastRecord) {
            $lastSeq = (int) substr($lastRecord->trigger_id, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung penambahan Hari Kerja (Senin s.d. Jumat, mengabaikan Sabtu-Minggu)
     * Mengimplementasikan rumus Excel WORKDAY(Tanggal, Hari)
     */
    public static function addWorkdays($startDate, int $days): Carbon
    {
        $date = Carbon::parse($startDate)->copy();
        $added = 0;
        while ($added < $days) {
            $date->addDay();
            if (!$date->isWeekend()) {
                $added++;
            }
        }
        return $date;
    }

    /**
     * Mapping 13 Area ke KKA Tujuan (SOP 06 Master_Parameter)
     */
    public static function mapAreaToKkaTujuan(?string $area): string
    {
        return match ($area) {
            self::AREA_TELLER_KAS => 'KKA_Teller_Kas',
            self::AREA_DPK_LAYANAN => 'KKA_DPK_Layanan',
            self::AREA_KREDIT => 'KKA_Kredit',
            self::AREA_BIAYA_BEBAN => 'KKA_Biaya_Beban',
            self::AREA_KARTU_ATM, self::AREA_REKON_KARTU_ATM => 'KKA_ATM',
            self::AREA_PENGADUAN => 'KKA_Pengaduan',
            self::AREA_CASH_OPNAME, self::AREA_REKON_CEK_BG, self::AREA_REKON_BARANG_CETAKAN, self::AREA_ASET => 'KKA_Fisik',
            self::AREA_OBSERVASI_LINGKUNGAN => 'KKA_Observasi',
            default => 'KKA_Risk_Trigger',
        };
    }

    /**
     * Kalkulasi seluruh formula turunan otomatis (Persis formula Excel Register_Trigger)
     */
    public function calculateDerivedAttributes(bool $alarmAccumulationActive = false): void
    {
        // 20. Kelengkapan Data: jika Sumber, Source WP ID, Tanggal, Kode Unit, Area, Uraian, atau Bukti Awal kosong -> BELUM LENGKAP
        $isValid = !empty($this->sumber)
            && !empty($this->source_wp_id)
            && !empty($this->tanggal_trigger)
            && !empty($this->kode_unit)
            && !empty($this->area)
            && !empty($this->uraian_trigger)
            && !empty($this->bukti_awal);

        $this->kelengkapan_data = $isValid ? 'VALID' : 'BELUM LENGKAP';

        // 21. Duplicate Key: {Kode_Unit}|{Area}|{Objek_Key}|{YYYYMMDD_Mulai}|{YYYYMMDD_Selesai}
        $startStr = $this->periode_mulai ? Carbon::parse($this->periode_mulai)->format('Ymd') : '';
        $endStr = $this->periode_selesai ? Carbon::parse($this->periode_selesai)->format('Ymd') : '';
        $this->duplicate_key = "{$this->kode_unit}|{$this->area}|{$this->objek_key}|{$startStr}|{$endStr}";

        // 22. Status Duplikat: dihitung di level service / bulk query (default: UNIK jika belum tersimpan)

        // 23. Likelihood:
        // Jumlah Kejadian >= 10 -> 5; >= 5 -> 4; >= 3 -> 3; >= 2 -> 2; else 1
        $kejadian = (int) ($this->jumlah_kejadian ?: 1);
        $this->likelihood = match (true) {
            $kejadian >= 10 => 5,
            $kejadian >= 5 => 4,
            $kejadian >= 3 => 3,
            $kejadian >= 2 => 2,
            default => 1,
        };

        // 24. Impact:
        // Jika Indikasi Fraud == 'Ya' ATAU Eksposur >= 1.000.000.000 -> 5
        // Jika Eksposur >= 100.000.000 -> 4
        // Jika Eksposur >= 10.000.000 -> 3
        // Jika Eksposur > 0 -> 2
        // Jika Eksposur == 0 (Non-nominal): jika Risk Score Sumber >= 16 -> 4; >= 10 -> 3; else 2
        $fraud = strtoupper(trim((string)$this->indikasi_fraud)) === 'YA';
        $nominal = (float) ($this->eksposur_nominal ?: 0);
        $sourceScore = (float) ($this->risk_score_sumber ?: 0);

        if ($fraud || $nominal >= 1000000000) {
            $this->impact = 5;
        } elseif ($nominal >= 100000000) {
            $this->impact = 4;
        } elseif ($nominal >= 10000000) {
            $this->impact = 3;
        } elseif ($nominal > 0) {
            $this->impact = 2;
        } else {
            if ($sourceScore >= 16) {
                $this->impact = 4;
            } elseif ($sourceScore >= 10) {
                $this->impact = 3;
            } else {
                $this->impact = 2;
            }
        }

        // 25. Risk Score Reassessment: MAX(Risk Score Sumber, Likelihood * Impact)
        $reassessedScore = max($sourceScore, (float)($this->likelihood * $this->impact));
        $this->risk_score_reassessment = $reassessedScore;

        // 26. Risk Level Reassessment:
        // >= 16 -> KRITIS; >= 10 -> TINGGI; >= 5 -> SEDANG; else RENDAH
        $this->risk_level_reassessment = match (true) {
            $reassessedScore >= 16 => 'KRITIS',
            $reassessedScore >= 10 => 'TINGGI',
            $reassessedScore >= 5 => 'SEDANG',
            default => 'RENDAH',
        };

        // Jika ada alarm akumulasi (> 5 temuan High/Moderate di area yang sama), otomatis tandai Perlu Verifikasi Lapangan = "Ya"
        if ($alarmAccumulationActive) {
            $this->perlu_verifikasi_lapangan = 'Ya';
        }

        // 27. Keputusan Otomatis (Routing Otomatis SOP 06):
        $perluLap = strtoupper(trim((string)$this->perlu_verifikasi_lapangan)) === 'YA';
        $surpriseFisik = strtoupper(trim((string)$this->objek_surprise_fisik)) === 'YA';
        $level = $this->risk_level_reassessment;

        if ($this->kelengkapan_data !== 'VALID') {
            $this->keputusan_otomatis = 'LENGKAPI DATA';
        } elseif ($fraud) {
            $this->keputusan_otomatis = 'SOP 09 - PEMERIKSAAN AWAL FRAUD';
        } elseif ($surpriseFisik && in_array($level, ['KRITIS', 'TINGGI'])) {
            $this->keputusan_otomatis = 'SOP 05 - SURPRISE AUDIT';
        } elseif ($this->sumber === 'Monitoring TL') {
            $this->keputusan_otomatis = 'SOP 07 - MONITORING TL';
        } elseif ((in_array($level, ['KRITIS', 'TINGGI']) && $perluLap) || $alarmAccumulationActive) {
            $this->keputusan_otomatis = 'RISK TRIGGER VISIT';
        } elseif ($level === 'SEDANG' && $kejadian >= 3 && $perluLap) {
            $this->keputusan_otomatis = 'RISK TRIGGER VISIT';
        } elseif ($level === 'RENDAH') {
            $this->keputusan_otomatis = 'MONITORING';
        } else {
            $this->keputusan_otomatis = 'KLARIFIKASI OFFSITE';
        }

        // 30. Keputusan Final:
        // Jika Override Keputusan kosong -> gunakan Keputusan Otomatis
        // Jika Override diisi tapi Alasan Override kosong -> "OVERRIDE BELUM BERALASAN"
        // Selain itu gunakan Override Keputusan (Catatan: jika Fraud == Ya, override turun tidak diizinkan)
        $override = trim((string)$this->override_keputusan);
        $alasan = trim((string)$this->alasan_override);

        if (empty($override)) {
            $this->keputusan_final = $this->keputusan_otomatis;
        } elseif (empty($alasan)) {
            $this->keputusan_final = 'OVERRIDE BELUM BERALASAN';
        } else {
            if ($fraud && $override !== 'SOP 09 - PEMERIKSAAN AWAL FRAUD') {
                $this->keputusan_final = 'SOP 09 - PEMERIKSAAN AWAL FRAUD'; // Proteksi fraud tidak boleh diturunkan
            } else {
                $this->keputusan_final = $override;
            }
        }

        // 31. SLA / Deadline (Hari Kerja sejak Tanggal Trigger):
        // KRITIS = +1 Hari Kerja, TINGGI = +3, SEDANG = +5, RENDAH = +10
        if ($this->tanggal_trigger) {
            $daysToAdd = match ($this->risk_level_reassessment) {
                'KRITIS' => 1,
                'TINGGI' => 3,
                'SEDANG' => 5,
                default => 10,
            };
            $this->sla_deadline = static::addWorkdays($this->tanggal_trigger, $daysToAdd);
        }

        // 33. KKA Tujuan (Mapping Area)
        $this->kka_tujuan = static::mapAreaToKkaTujuan($this->area);

        // 34. Prioritas Visit:
        if ($this->keputusan_final !== 'RISK TRIGGER VISIT') {
            $this->prioritas_visit = 'n.a.';
        } else {
            $this->prioritas_visit = match ($this->risk_level_reassessment) {
                'KRITIS' => 'SEGERA/H+1',
                'TINGGI' => 'TINGGI - <=3 HARI KERJA',
                default => 'TERJADWAL - <=5 HARI KERJA',
            };
        }

        // 32. Status Workflow (Persis Formula Excel Kolom AF):
        if ($this->status_duplikat === 'DUPLIKAT - REVIEW') {
            $this->status_workflow = 'REVIEW DUPLIKAT';
        } elseif ($this->keputusan_final === 'RISK TRIGGER VISIT') {
            // Jika belum di-assign atau baru masuk status visit
            if (empty($this->status_workflow) || in_array($this->status_workflow, ['BARU', 'REVIEW DUPLIKAT', 'KLARIFIKASI OFFSITE', 'MONITORING', 'MENUNGGU ASSIGNMENT'])) {
                $this->status_workflow = 'MENUNGGU ASSIGNMENT';
            }
        } else {
            $this->status_workflow = $this->keputusan_final;
        }
    }

    /**
     * Scope akses per cabang untuk akun RA
     */
    public function scopeByBranch($query, ?User $user = null)
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return $query;
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

            return $query->whereIn('kode_unit', array_values(array_unique(array_filter($expanded))));
        }

        return $query;
    }
}
