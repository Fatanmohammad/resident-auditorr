<?php

namespace App\Models\DailyRegister;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KontrolMingguan extends Model
{
    use HasFactory;

    protected $table = 'kontrol_mingguans';
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'override_tanggal' => 'date',
        'tanggal_rencana_efektif' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Definisi default 5 KKA Fisik Mingguan sesuai SOP Excel
     */
    public static function defaultAreas(): array
    {
        return [
            [
                'area_key'      => 'rekon_kartu_atm',
                'sheet_kka'     => 'KKA_Rekon_Kartu_ATM',
                'nama_area'     => 'Rekonsiliasi Persediaan Kartu ATM',
                'hari_rencana'  => 1, // Senin
                'menit_rencana' => 60,
            ],
            [
                'area_key'      => 'cash_opname',
                'sheet_kka'     => 'KKA_Cash_Opname',
                'nama_area'     => 'Cash Opname Khasanah & Kaset ATM',
                'hari_rencana'  => 2, // Selasa
                'menit_rencana' => 120,
            ],
            [
                'area_key'      => 'observasi_lingkungan',
                'sheet_kka'     => 'KKA_Observasi_Lingkungan',
                'nama_area'     => 'Observasi Lingkungan & Pengamanan',
                'hari_rencana'  => 3, // Rabu
                'menit_rencana' => 90,
            ],
            [
                'area_key'      => 'rekon_stok_cek_bg',
                'sheet_kka'     => 'KKA_Rekon_Stok_Cek_BG',
                'nama_area'     => 'Rekonsiliasi Stok Cek & Bilyet Giro',
                'hari_rencana'  => 4, // Kamis
                'menit_rencana' => 45,
            ],
            [
                'area_key'      => 'rekon_barang_cetakan',
                'sheet_kka'     => 'KKA_Rekon_Barang_Cetakan',
                'nama_area'     => 'Rekonsiliasi Barang Cetakan Bernilai',
                'hari_rencana'  => 5, // Jumat
                'menit_rencana' => 45,
            ],
        ];
    }

    /**
     * Mendapatkan atau membuat jadwal kontrol mingguan untuk unit & tanggal tertentu
     */
    public static function getOrCreateForUnit(string $kodeUnit, $date = null)
    {
        $refDate = $date ? Carbon::parse($date) : Carbon::now();
        $monday = $refDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $friday = $refDate->copy()->startOfWeek(Carbon::MONDAY)->addDays(4)->toDateString();
        $year = (int) $refDate->format('Y');
        $weekNumber = (int) $refDate->isoWeek();

        $existing = self::where('kode_unit', $kodeUnit)
            ->where('start_date', $monday)
            ->orderBy('hari_rencana', 'asc')
            ->get();

        if ($existing->count() < 5) {
            foreach (self::defaultAreas() as $def) {
                $item = self::where('kode_unit', $kodeUnit)
                    ->where('start_date', $monday)
                    ->where('area_key', $def['area_key'])
                    ->first();

                if (!$item) {
                    $tglEfektif = Carbon::parse($monday)->addDays($def['hari_rencana'] - 1)->toDateString();
                    self::create([
                        'kode_unit'               => $kodeUnit,
                        'tahun'                   => $year,
                        'minggu_ke'               => $weekNumber,
                        'start_date'              => $monday,
                        'end_date'                => $friday,
                        'area_key'                => $def['area_key'],
                        'sheet_kka'               => $def['sheet_kka'],
                        'nama_area'               => $def['nama_area'],
                        'hari_rencana'            => $def['hari_rencana'],
                        'menit_rencana'           => $def['menit_rencana'],
                        'override_tanggal'        => null,
                        'tanggal_rencana_efektif' => $tglEfektif,
                        'jumlah_auditor'          => 1,
                    ]);
                }
            }

            $existing = self::where('kode_unit', $kodeUnit)
                ->where('start_date', $monday)
                ->orderBy('hari_rencana', 'asc')
                ->get();
        }

        // Sinkronisasi status pengerjaan mingguan dari data PhysicalKka yang sudah diisi
        return self::evaluateWeeklyStatuses($existing, $kodeUnit, $monday, $friday);
    }

    /**
     * Evaluasi status pengerjaan mingguan (Selesai Minggu Ini, Terlambat, Wajib Hari Ini, Terjadwal)
     */
    public static function evaluateWeeklyStatuses($schedules, string $kodeUnit, string $monday, string $friday)
    {
        $today = Carbon::now()->toDateString();

        // Cari PhysicalKka yang disubmit / diedit pada minggu kalender yang sama
        $weeklyKka = PhysicalKka::with(['cashDetails', 'obsDetails', 'stokDetails', 'kartuAtmDetails', 'barangCetakanDetails'])
            ->where('kode_unit', $kodeUnit)
            ->whereBetween('tanggal_pemeriksaan', [$monday, $friday])
            ->latest('updated_at')
            ->first();

        foreach ($schedules as $sched) {
            $isCompleted = false;
            $completedDate = null;

            if ($weeklyKka) {
                switch ($sched->area_key) {
                    case 'cash_opname':
                        // Dianggap selesai jika ada detail cash yang diisi
                        $cashTested = $weeklyKka->cashDetails->whereNotNull('hasil_fisik')->where('hasil_fisik', '!=', '')->count();
                        if ($cashTested > 0) {
                            $isCompleted = true;
                            $completedDate = $weeklyKka->cashDetails->max('tanggal_uji') ?? $weeklyKka->tanggal_pemeriksaan;
                        }
                        break;
                    case 'observasi_lingkungan':
                        $obsTested = $weeklyKka->obsDetails->whereNotNull('hasil_observasi')->where('hasil_observasi', '!=', '')->count();
                        if ($obsTested > 0) {
                            $isCompleted = true;
                            $completedDate = $weeklyKka->obsDetails->max('tanggal_uji') ?? $weeklyKka->tanggal_pemeriksaan;
                        }
                        break;
                    case 'rekon_stok_cek_bg':
                        $stokTested = $weeklyKka->stokDetails->whereNotNull('hasil_fisik')->where('hasil_fisik', '!=', '')->count();
                        if ($stokTested > 0) {
                            $isCompleted = true;
                            $completedDate = $weeklyKka->stokDetails->max('tanggal_uji') ?? $weeklyKka->tanggal_pemeriksaan;
                        }
                        break;
                    case 'rekon_kartu_atm':
                        $atmTested = $weeklyKka->kartuAtmDetails->whereNotNull('hasil_fisik')->where('hasil_fisik', '!=', '')->count();
                        if ($atmTested > 0) {
                            $isCompleted = true;
                            $completedDate = $weeklyKka->kartuAtmDetails->max('tanggal_uji') ?? $weeklyKka->tanggal_pemeriksaan;
                        }
                        break;
                    case 'rekon_barang_cetakan':
                        $bctTested = $weeklyKka->barangCetakanDetails->whereNotNull('hasil_fisik')->where('hasil_fisik', '!=', '')->count();
                        if ($bctTested > 0) {
                            $isCompleted = true;
                            $completedDate = $weeklyKka->barangCetakanDetails->max('tanggal_uji') ?? $weeklyKka->tanggal_pemeriksaan;
                        }
                        break;
                }
            }

            $tglEfektif = $sched->override_tanggal 
                ? Carbon::parse($sched->override_tanggal)->toDateString() 
                : Carbon::parse($sched->tanggal_rencana_efektif)->toDateString();

            // LOGIKA STATUS MINGGU INI SESUAI EXCEL
            // 1. Jika sudah selesai
            if ($isCompleted) {
                $status = 'SELESAI MINGGU INI';
                $badgeClass = 'success';
                $badgeColor = '#10b981';
            } elseif ($today > $tglEfektif) {
                // 2. Belum selesai & Lewat tanggal rencana
                $status = 'TERLAMBAT';
                $badgeClass = 'danger';
                $badgeColor = '#ef4444';
            } elseif ($today === $tglEfektif) {
                // 3. Belum selesai & Hari ini adalah tanggal rencana
                $status = 'WAJIB HARI INI';
                $badgeClass = 'warning';
                $badgeColor = '#f59e0b';
            } else {
                // 4. Belum selesai & Tanggal rencana di hari mendatang
                $status = 'TERJADWAL';
                $badgeClass = 'info';
                $badgeColor = '#3b82f6';
            }

            // Update status di database jika berubah
            if ($sched->status_minggu_ini !== $status) {
                self::where('id', $sched->id)->update([
                    'status_minggu_ini' => $status,
                    'tanggal_selesai' => $completedDate,
                ]);
                $sched->status_minggu_ini = $status;
                $sched->tanggal_selesai = $completedDate;
            }

            $sched->calculated_status = $status;
            $sched->badge_class = $badgeClass;
            $sched->badge_color = $badgeColor;
            $sched->is_completed = $isCompleted;
            $sched->tanggal_selesai_efektif = $completedDate;
        }

        return $schedules;
    }
}
