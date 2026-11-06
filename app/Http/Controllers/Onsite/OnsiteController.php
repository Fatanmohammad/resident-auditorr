<?php

namespace App\Http\Controllers\Onsite;

use App\Http\Controllers\Controller;
use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsiteKkaFinding;
use App\Models\Onsite\OnsiteKkaChecklistAnswer;
use App\Models\DailyRegister\DailyRegisterChecklist;
use App\Models\ScheduledVisit;
use App\Models\Unit;
use App\Services\Onsite\CbsOnsiteParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OnsiteController extends Controller
{
    /**
     * Daftar kunjungan Onsite
     */
    public function index()
    {
        $user = auth()->user();

        $query = OnsiteVisit::orderByDesc('tanggal_mulai');

        if (strtolower($user->role) === 'ra') {
            // RA hanya lihat kunjungan unit di wilayahnya
            $unitCodes = Unit::where(function ($q) use ($user) {
                if ($user->cabang_id) {
                    $q->where('cabang_id', $user->cabang_id)
                      ->orWhereIn('cabang_id', function ($sub) use ($user) {
                          $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                      });
                }
            })->pluck('unit_code');

            $query->whereIn('kode_unit', $unitCodes);
        }

        $visits = $query->paginate(20);

        return view('onsite.index', compact('visits'));
    }

    /**
     * Form buat kunjungan baru
     */
    public function create()
    {
        $user = auth()->user();

        // Hanya tampilkan unit KCP dan KCPLK
        $query = Unit::whereIn('unit_type', ['KCP', 'KCPLK'])->orderBy('unit_name');

        if (strtolower($user->role) === 'ra') {
            $query->where(function ($q) use ($user) {
                if ($user->cabang_id) {
                    $q->where('cabang_id', $user->cabang_id)
                      ->orWhereIn('cabang_id', function ($sub) use ($user) {
                          $sub->select('id')->from('cabangs')->where('parent_id', $user->cabang_id);
                      });
                }
            });
        }

        $units = $query->get();

        return view('onsite.create', compact('units'));
    }

    /**
     * Simpan kunjungan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_unit'       => 'required|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $unit = Unit::where('unit_code', $request->kode_unit)->first();

        $visit = OnsiteVisit::create([
            'kode_unit'       => $request->kode_unit,
            'nama_unit'       => $unit?->unit_name,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'ra_id'           => auth()->id(),
            'status'          => 'Persiapan',
            'periode'         => date('Y-m', strtotime($request->tanggal_mulai)),
        ]);

        return redirect()->route('onsite.upload', $visit->id)
            ->with('success', 'Kunjungan berhasil dibuat. Silakan upload data CBS populasi.');
    }

    /**
     * Form upload CBS untuk kunjungan
     */
    public function uploadForm(OnsiteVisit $visit)
    {
        return view('onsite.upload', compact('visit'));
    }

    /**
     * Proses upload CBS
     */
    public function uploadProcess(Request $request, OnsiteVisit $visit, CbsOnsiteParser $parser)
    {
        $request->validate([
            'file_cbs' => 'required|file|mimes:csv,txt|max:20480',
            'periode'  => 'nullable|string',
        ]);

        $file     = $request->file('file_cbs');
        $filePath = $file->storeAs('onsite_staging', time() . '_' . $file->getClientOriginalName(), 'local');
        $fullPath = Storage::disk('local')->path($filePath);

        DB::beginTransaction();
        try {
            $result = $parser->parse($fullPath, $visit);

            $updateData = ['status' => 'Berlangsung'];
            if ($request->filled('periode')) {
                $updateData['periode'] = $request->periode;
            }
            $visit->update($updateData);

            DB::commit();

            if (file_exists($fullPath)) unlink($fullPath);

            return redirect()->route('onsite.index')
                ->with('success', "Upload CBS berhasil. {$result['total_populasi']} populasi diproses, {$result['total_sampel']} sampel dipilih otomatis.");

        } catch (\Exception $e) {
            DB::rollBack();
            if (file_exists($fullPath)) unlink($fullPath);

            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * KKA Onsite — halaman UI
     */
    public function kka(OnsiteVisit $visit)
    {
        return view('onsite.kka', compact('visit'));
    }

    /**
     * KKA Onsite — data JSON untuk AJAX
     */
    public function kkaData(Request $request, OnsiteVisit $visit)
    {
        $query = OnsiteKkaFinding::where('onsite_visit_id', $visit->id)
            ->orderBy('prioritas')
            ->orderBy('tgl_tx');

        if ($request->filled('jenis_sampling')) {
            $query->where('jenis_sampling', $request->jenis_sampling);
        }

        $findings = $query->get();

        return response()->json(['status' => 'success', 'data' => $findings]);
    }

    /**
     * Ambil data detail review KKA Onsite (Finding + Master Checklist + Existing Answers + Recurring Count)
     */
    public function getReviewData($id)
    {
        $finding = OnsiteKkaFinding::with('checklistAnswers')->findOrFail($id);

        if (DailyRegisterChecklist::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\DailyRegisterChecklistSeeder',
                '--force' => true,
            ]);
        }

        $sheet = request('source_sheet');
        if (!$sheet) {
            $ket = strtoupper($finding->ket_tx ?? '');
            if (preg_match('/(KREDIT|PENCAIRAN|ANGSURAN|PINJAMAN|AKAD|DEBITUR|KUR)/i', $ket)) {
                $sheet = 'KKA_Kredit';
            } elseif (preg_match('/(TABUNGAN|GIRO|DEPOSITO|DPK|DORMANT)/i', $ket)) {
                $sheet = 'KKA_DPK_Layanan';
            } elseif (preg_match('/(BIAYA|BEBAN|SEWA|PROMOSI|LISTRIK|ATK|HONOR)/i', $ket)) {
                $sheet = 'KKA_Biaya_Beban';
            } elseif (preg_match('/(ATM|KARTU|PIN)/i', $ket)) {
                $sheet = 'KKA_Kartu_ATM';
            } elseif (preg_match('/(PENGADUAN|KLAIM|SENGKETA|KOMPLAIN)/i', $ket)) {
                $sheet = 'KKA_Pengaduan';
            } else {
                $sheet = 'KKA_Teller_Kas';
            }
        }

        $checklists = DailyRegisterChecklist::where('source_sheet', $sheet)
            ->orderBy('sort_order')
            ->get();

        $recurringCount = $this->calculateRecurringCount($finding);

        return response()->json([
            'status'           => 'success',
            'finding'          => $finding,
            'checklists'       => $checklists,
            'existing_answers' => $finding->checklistAnswers,
            'recurring_count'  => $recurringCount,
        ]);
    }

    private function calculateRecurringCount(OnsiteKkaFinding $finding): int
    {
        $count = OnsiteKkaFinding::where('kode_unit', $finding->kode_unit)
            ->where('id', '!=', $finding->id)
            ->where(function ($q) {
                $q->whereNotNull('hasil_uji')->orWhere('risk_final', '!=', 'Low');
            })
            ->count();

        return max(1, $count + 1);
    }

    /**
     * Update input RA pada satu sampel KKA
     */
    public function updateKka(Request $request, $id)
    {
        $finding = OnsiteKkaFinding::findOrFail($id);

        if ($finding->status_review === 'Approved') {
            return response()->json(['status' => 'error', 'message' => 'KKA sudah Approved dan tidak dapat diubah.'], 403);
        }

        // Checklist answers handling
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

        $rules = [
            'bukti_referensi'   => 'nullable|string',
            'critical_trigger'  => 'nullable|in:Ya,Tidak',
            'tanggal_ditemukan' => 'nullable|date',
            'file_bukti'        => 'nullable|file|mimes:pdf|max:5120',
        ];

        if ($tidakCount >= 1) {
            $rules['jenis_exception_ra'] = 'required|string';
            $rules['hasil_uji']          = 'required|string';
            $rules['klarifikasi_unit']   = 'required|string';
        }

        $request->validate($rules);

        // Upload file bukti jika ada
        if ($request->hasFile('file_bukti')) {
            if ($finding->file_bukti && Storage::disk('public')->exists($finding->file_bukti)) {
                Storage::disk('public')->delete($finding->file_bukti);
            }
            $filePath = $request->file('file_bukti')->store('onsite_kka_bukti', 'public');
            $finding->file_bukti = $filePath;
        }

        // Update checklist answers
        if (is_array($rawChecklist) && count($rawChecklist) > 0) {
            foreach ($rawChecklist as $ans) {
                if (isset($ans['checklist_id'])) {
                    OnsiteKkaChecklistAnswer::updateOrCreate(
                        [
                            'onsite_kka_finding_id' => $finding->id,
                            'checklist_id'          => $ans['checklist_id']
                        ],
                        [
                            'jawaban'    => $ans['jawaban'] ?? 'Ya',
                            'keterangan' => $ans['keterangan'] ?? null,
                        ]
                    );
                }
            }
        }

        // Otomasi Skor Risiko 5x5
        $nominal = (float)($finding->jumlah_tx ?? 0);
        $critical = $request->input('critical_trigger', 'Tidak');
        $recurringCount = $this->calculateRecurringCount($finding);

        if ($tidakCount === 0 && $critical === 'Tidak') {
            $skorDampak = 1;
            $skorKemungkinan = 1;
            $skorFinal = 1;
            $riskFinal = 'Low';
        } else {
            if ($critical === 'Ya' || $nominal >= 500000000) {
                $skorDampak = 5;
            } else {
                $chkScore = 1;
                if ($tidakCount >= 5) $chkScore = 4;
                elseif ($tidakCount >= 3) $chkScore = 3;
                elseif ($tidakCount >= 2) $chkScore = 2;
                elseif ($tidakCount === 1) $chkScore = ($nominal > 0) ? 1 : 2;

                $nomScore = 0;
                if ($nominal > 0) {
                    if ($nominal >= 100000000) $nomScore = 4;
                    elseif ($nominal >= 50000000) $nomScore = 3;
                    elseif ($nominal >= 10000000) $nomScore = 2;
                    else $nomScore = 1;
                }

                $skorDampak = max($chkScore, $nomScore);
                if ($nominal <= 0 && $tidakCount >= 1 && $skorDampak < 2) {
                    $skorDampak = 2;
                }
                $skorDampak = min(max($skorDampak, 1), 5);
            }

            if ($recurringCount >= 7) {
                $skorKemungkinan = 5;
            } elseif ($recurringCount >= 5) {
                $skorKemungkinan = 4;
            } elseif ($recurringCount >= 3) {
                $skorKemungkinan = 3;
            } elseif ($recurringCount === 2) {
                $skorKemungkinan = 2;
            } else {
                $skorKemungkinan = ($tidakCount >= 2) ? 2 : 1;
            }

            $skorFinal = $skorDampak * $skorKemungkinan;

            if ($critical === 'Ya') {
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
        }

        $finding->bukti_referensi    = $request->input('bukti_referensi');
        $finding->critical_trigger   = $critical;
        $finding->jenis_exception_ra = $tidakCount >= 1 ? $request->input('jenis_exception_ra') : null;
        $finding->hasil_uji          = $tidakCount >= 1 ? $request->input('hasil_uji') : null;
        $finding->klarifikasi_unit   = $tidakCount >= 1 ? $request->input('klarifikasi_unit') : null;
        $finding->skor_dampak        = $skorDampak;
        $finding->skor_kemungkinan   = $skorKemungkinan;
        $finding->skor_final         = $skorFinal;
        $finding->risk_final         = $riskFinal;
        $finding->risk_level         = $riskFinal;
        $finding->tanggal_ditemukan  = $request->input('tanggal_ditemukan', now()->toDateString());
        $finding->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Review KKA Onsite RA berhasil disimpan.',
            'data'    => $finding
        ]);
    }

    /**
     * Review KKA oleh Admin/Kabag/Kadiv
     */
    public function reviewKka(Request $request, $id)
    {
        $finding = OnsiteKkaFinding::findOrFail($id);

        $request->validate([
            'status_review'      => 'required|in:Belum Direview,Revisi,Approved',
            'catatan_reviewer'   => 'nullable|string',
            'status_klarifikasi' => 'nullable|string',
            'keputusan_eskalasi' => 'nullable|string',
        ]);

        $finding->update($request->only([
            'status_review',
            'catatan_reviewer',
            'status_klarifikasi',
            'keputusan_eskalasi',
        ]));

        return response()->json(['status' => 'success', 'message' => 'Review KKA berhasil disimpan.', 'data' => $finding]);
    }
}
