<?php

namespace App\Http\Controllers\DailyRegister;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyRegister\KontrolMingguan;
use App\Services\DailyRegister\KapasitasHarianService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class KontrolMingguanController extends Controller
{
    protected KapasitasHarianService $kapasitasService;

    public function __construct(KapasitasHarianService $kapasitasService)
    {
        $this->kapasitasService = $kapasitasService;
    }

    /**
     * Mengambil data kapasitas harian dan jadwal kontrol mingguan (JSON)
     */
    public function getKapasitas(Request $request)
    {
        $user = Auth::user();
        $kodeUnit = $request->input('kode_unit');

        if ($kodeUnit && str_contains($kodeUnit, '?')) {
            $kodeUnit = explode('?', $kodeUnit)[0];
        }

        if (!$kodeUnit) {
            $cabangParam = $request->input('cabang_id') ?: $request->input('cabang');
            if ($cabangParam) {
                $kcUnit = \App\Models\Unit::where('cabang_id', $cabangParam)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                }
            } elseif ($user && $user->cabang_id && strtolower($user->role) === 'ra') {
                $kcUnit = \App\Models\Unit::where('cabang_id', $user->cabang_id)
                    ->whereIn('unit_type', ['KC', 'KCU', 'KP'])
                    ->first();
                if ($kcUnit) {
                    $kodeUnit = $kcUnit->unit_code;
                } elseif ($user->cabang && $user->cabang->kode_cabang) {
                    $kodeUnit = str_replace('BS-', '', $user->cabang->kode_cabang);
                }
            } elseif ($user && !empty($user->unit_code) && strtolower($user->role) === 'ra') {
                $kodeUnit = $user->unit_code;
            } else {
                // Cari KC yang memiliki data temuan aktif terlebih dahulu (misal KC Poso 003)
                $kcWithData = \App\Models\DailyRegister\DailyRegisterFinding::whereIn('risk_awal', ['Moderate', 'High'])
                    ->whereNotNull('kode_unit')
                    ->value('kode_unit');
                if ($kcWithData) {
                    $kodeUnit = $kcWithData;
                } else {
                    $firstKc = \App\Models\Unit::whereIn('unit_type', ['KC', 'KCU', 'KP'])->first();
                    $kodeUnit = $firstKc ? $firstKc->unit_code : '003';
                }
            }
        }

        if ($kodeUnit && str_starts_with($kodeUnit, 'BS-')) {
            $clean = str_replace('BS-', '', $kodeUnit);
            $kcUnit = \App\Models\Unit::where('unit_code', $clean)->first();
            if ($kcUnit) {
                $kodeUnit = $kcUnit->unit_code;
            }
        }

        $tanggal = $request->filled('tanggal') ? $request->input('tanggal') : null;
        $data = $this->kapasitasService->hitungKapasitas($kodeUnit, $tanggal);

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Memperbarui jadwal kontrol mingguan (Hari Rencana, Override Tanggal, atau Jumlah Auditor)
     */
    public function updateSchedule(Request $request)
    {
        $user = Auth::user();
        $isAdmin = in_array(strtolower($user->role), ['admin', 'kabag_ra', 'kadiv_skai']);

        $request->validate([
            'id'               => 'required|exists:kontrol_mingguans,id',
            'hari_rencana'     => 'nullable|integer|between:1,5',
            'override_tanggal' => 'nullable|date',
            'jumlah_auditor'   => 'nullable|integer|min:1',
            'catatan'          => 'nullable|string',
        ]);

        $item = KontrolMingguan::findOrFail($request->id);

        // Validasi: Override tanggal harus berada dalam rentang Senin s.d. Jumat pada minggu yang sama
        if ($request->filled('override_tanggal')) {
            $override = Carbon::parse($request->override_tanggal);
            $startDate = Carbon::parse($item->start_date)->startOfDay();
            $endDate = Carbon::parse($item->end_date)->endOfDay();

            if ($override->lt($startDate) || $override->gt($endDate)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Override tanggal harus berada dalam rentang hari kerja minggu berjalan (' . $startDate->format('d M Y') . ' s.d. ' . $endDate->format('d M Y') . ').',
                ], 422);
            }

            // Validasi hari kerja (Senin s.d. Jumat, bukan Sabtu/Minggu)
            if ($override->isWeekend()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tanggal override harus hari kerja (Senin s.d. Jumat).',
                ], 422);
            }
        }

        // Hitung ulang tanggal rencana efektif
        $hari = $request->filled('hari_rencana') ? (int)$request->hari_rencana : $item->hari_rencana;
        $tglEfektif = $request->filled('override_tanggal')
            ? $request->override_tanggal
            : Carbon::parse($item->start_date)->addDays($hari - 1)->toDateString();

        $updateData = [
            'hari_rencana'            => $hari,
            'override_tanggal'        => $request->override_tanggal ?: null,
            'tanggal_rencana_efektif' => $tglEfektif,
            'catatan'                 => $request->catatan ?: $item->catatan,
        ];

        if ($request->filled('jumlah_auditor')) {
            // Update jumlah auditor untuk semua KKA di minggu tersebut untuk unit yang sama
            KontrolMingguan::where('kode_unit', $item->kode_unit)
                ->where('start_date', $item->start_date)
                ->update(['jumlah_auditor' => (int)$request->jumlah_auditor]);
        }

        $item->update($updateData);

        // Ambil data kapasitas terbaru untuk dikembalikan ke UI
        $tanggalParam = $request->filled('tanggal') ? $request->input('tanggal') : null;
        $freshData = $this->kapasitasService->hitungKapasitas($item->kode_unit, $tanggalParam);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal Kontrol Mingguan ' . $item->nama_area . ' berhasil diperbarui.',
            'data'    => $freshData,
        ]);
    }
}
