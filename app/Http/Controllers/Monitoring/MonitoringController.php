<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sop07RegisterTl;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Revisi Poin 11: Mengarahkan menu utama langsung ke Halaman Tunggal Terpadu Monitoring
        return $this->monitoringTerpadu($request);
    }

    public function registerTl() 
    {
        $registers = Sop07RegisterTl::orderBy('id', 'desc')->get();
        return view('monitoring.register_tl', compact('registers'));
    }

    public function storeRegisterTl(Request $request)
    {
        $request->validate([
            'tl_id' => 'required|string|max:255|unique:sop07_register_tls,tl_id',
            'no_laporan' => 'required|string|max:255',
            'sumber_tl' => 'required|string|max:255',
            'nama_unit_sumber' => 'required|string|max:255',
        ]);

        Sop07RegisterTl::create([
            'tl_id'               => $request->tl_id,
            'nomor_laporan'       => $request->no_laporan,
            'tanggal_laporan'     => date('Y-m-d'),
            'sumber_tl'           => $request->sumber_tl,
            'nama_unit'           => $request->nama_unit_sumber,
            'kode_unit'           => 'KODE-01',
            'rekomendasi_temuan'  => '-',
            'status_final'        => 'OPEN',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->back()->with('success', 'Item Register TL berhasil ditambahkan.');
    }

    public function rencanaReminder() 
    {
        $plans = Sop07RegisterTl::orderBy('id', 'desc')->get();
        return view('monitoring.rencana_reminder', compact('plans'));
    }

    public function logBukti() 
    {
        $logs = DB::table('sop07_log_reminder_buktis')->orderBy('id', 'desc')->get();
        return view('monitoring.log_bukti', compact('logs'));
    }

    public function kkaVerifikasi() 
    {
        $kkas = DB::table('sop07_kka_verifikasis')->orderBy('id', 'desc')->get();
        return view('monitoring.kka_verifikasi', compact('kkas'));
    }

    public function closureReview() 
    {
        $closures = DB::table('sop07_closure_reviews')->orderBy('id', 'desc')->get();
        return view('monitoring.closure_review', compact('closures'));
    }

    public function eskalasi() 
    {
        $eskalasis = DB::table('sop07_perpanjangan_eskalasis')->orderBy('id', 'desc')->get();
        return view('monitoring.eskalasi', compact('eskalasis'));
    }

    public function statusAging() 
    {
        $agings = Sop07RegisterTl::orderBy('id', 'desc')->get();
        return view('monitoring.status_aging', compact('agings'));
    }

    public function kpiRa() 
    {
        return view('monitoring.kpi_ra');
    }

    public function ringkasanBulanan() 
    {
        return view('monitoring.ringkasan_bulanan');
    }

    public function storeLogBukti(Request $request)
    {
        DB::table('sop07_log_reminder_buktis')->insert([
            'log_id'               => 'LOG-' . time(),
            'tl_id'                => $request->tl_id,
            'unit_kantor'          => $request->unit_kantor ?? '-',
            'tanggal_komunikasi'   => $request->tanggal_komunikasi,
            'jenis_komunikasi'     => $request->jenis_komunikasi,
            'saluran'              => $request->saluran ?? '-',
            'ringkasan_komunikasi' => $request->ringkasan_komunikasi,
            'bukti_diterima'       => $request->bukti_diterima ?? 'TIDAK',
            'referensi_bukti'      => $request->referensi_bukti,
            'status_log'           => 'SUBMITTED',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return redirect()->back()->with('success', 'Log bukti berhasil disimpan');
    }

    public function storeKkaVerifikasi(Request $request)
    {
        DB::table('sop07_kka_verifikasis')->insert([
            'kka_id'             => 'KKA-' . time(),
            'no_kka'             => $request->no_kka,
            'tl_id'              => $request->tl_id,
            'unit_kantor'        => $request->unit_kantor ?? '-',
            'bukti_diharapkan'   => $request->bukti_diharapkan,
            'closure_authority'  => $request->closure_authority,
            'relevan'            => $request->relevan ?? 'YA',
            'lengkap'            => $request->lengkap ?? 'YA',
            // 'valid'           => Dihapus karena kolom tidak ada di tabel database
            'korektif_realisasi' => $request->korektif ?? 'YA',
            'dapat_diuji'        => $request->dapat_diuji ?? 'YA',
            'populasi_terdampak' => $request->populasi_terdampak ?? 0,
            'objek_diuji'        => $request->objek_diuji ?? 0,
            'coverage'           => $request->coverage ?? '100.0%',
            'efektif'            => $request->efektif ?? 'EFEKTIF',
            'exception'          => $request->exception ?? '-',
            'analisis_ra'        => $request->analisis_ra,
            'hasil_verifikasi'   => $request->hasil_verifikasi,
            'bukti_tambahan'     => $request->bukti_tambahan ?? '-',
            'perlu_lapangan'     => $request->perlu_lapangan ?? 'TIDAK',
            'usulan_status'      => $request->usulan_status,
            'ra_pemantau'        => $request->ra_pemantau ?? 'oawja',
            'reviewer'           => 'Reviewer SKAI',
            'status_review'      => 'REVIEWED',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->back()->with('success', 'KKA verifikasi berhasil disimpan');
    }

    public function storeClosureReview(Request $request)
    {
        DB::table('sop07_closure_reviews')->insert([
            'closure_review_id'         => 'CR-' . time(),
            'tl_id'                     => $request->tl_id,
            'unit_kantor'               => $request->unit_kantor ?? '-',
            'sumber_tl'                 => $request->sumber_tl ?? '-',
            'closure_authority'         => $request->closure_authority,
            'hasil_verifikasi_terakhir' => 'VALID',
            'usulan_status'             => $request->usulan_status,
            'tanggal_pengajuan'         => $request->tanggal_pengajuan,
            'referensi_paket_bukti'     => $request->referensi_paket_bukti,
            'keputusan_authority'       => 'PENDING',
            'status_efektif'            => 'PROPOSED',
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        return redirect()->back()->with('success', 'Closure review berhasil diajukan');
    }

    public function storeEskalasi(Request $request)
    {
        DB::table('sop07_perpanjangan_eskalasis')->insert([
            'action_id'               => 'ACT-' . time(),
            'tl_id'                   => $request->tl_id,
            'unit_kantor'             => $request->unit_kantor ?? '-',
            'risk_level'              => 'HIGH',
            'jenis_aksi'              => $request->jenis_aksi,
            'tanggal_aksi_permintaan' => $request->tanggal_aksi_permintaan,
            'alasan'                  => $request->alasan,
            'target_baru_diminta'     => $request->target_baru_diminta,
            'level_persetujuan'       => 'KADIV SKAI',
            'keputusan'               => 'PENDING',
            'status_aksi'             => 'PROCESSED',
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        return redirect()->back()->with('success', 'Aksi eskalasi berhasil diajukan');
    }

    public function exportExcel($sheet)
    {
        return redirect()->back()->with('info', 'Proses export sheet ' . $sheet . ' berhasil.');
    }

    // =========================================================================
    // REVISI POIN 10 & 11: HALAMAN TERPADU MONITORING TL & UPLOAD EXCEL
    // =========================================================================

    public function monitoringTerpadu(Request $request)
    {
        // Poin 7, 8, 10, 11: Otomatis sinkronisasi temuan fisik Surprise Audit & seeder Regulator
        self::autoSyncSurpriseAuditFindings();

        $currentTab = $request->get('tab', 'internal'); // 'internal' atau 'regulator'
        $statusFilter = $request->get('status', 'all'); // 'all', 'belum_tl', 'sudah_tl'
        $unitFilter = $request->get('unit', '');
        $search = $request->get('search', '');
        $tahunFilter = $request->get('tahun', '');

        // Query Dasar Temuan Internal
        $queryInternal = Sop07RegisterTl::whereIn('sumber_tl', ['SKAI', 'Resident Auditor', 'Internal', 'Surprise Audit', 'Offsite', 'Onsite']);
        // Query Dasar Temuan Regulator
        $queryRegulator = Sop07RegisterTl::whereIn('sumber_tl', ['Regulator', 'OJK', 'Bank Indonesia', 'BI', 'BPK', 'Auditor Eksternal']);

        // Hitung Statistik Internal
        $totalInternal = (clone $queryInternal)->count();
        $belumTlInternal = (clone $queryInternal)->whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE'])->count();
        $sudahTlInternal = (clone $queryInternal)->whereIn('status_final', ['CLOSED', 'PROPOSED CLOSED'])->count();

        // Hitung Statistik Regulator
        $totalRegulator = (clone $queryRegulator)->count();
        $belumTlRegulator = (clone $queryRegulator)->whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE'])->count();
        $sudahTlRegulator = (clone $queryRegulator)->whereIn('status_final', ['CLOSED', 'PROPOSED CLOSED'])->count();

        // Terapkan filter ke tab yang sedang aktif
        $activeQuery = ($currentTab === 'regulator') ? $queryRegulator : $queryInternal;

        if ($statusFilter === 'belum_tl') {
            $activeQuery->whereIn('status_final', ['OPEN', 'UNDER REVIEW', 'INADEQUATE', 'OVERDUE']);
        } elseif ($statusFilter === 'sudah_tl') {
            $activeQuery->whereIn('status_final', ['CLOSED', 'PROPOSED CLOSED']);
        }

        if (!empty($unitFilter)) {
            $activeQuery->where('nama_unit', 'LIKE', "%{$unitFilter}%");
        }

        if (!empty($tahunFilter)) {
            $activeQuery->whereYear('tanggal_laporan', $tahunFilter);
        }

        if (!empty($search)) {
            $activeQuery->where(function($q) use ($search) {
                $q->where('tl_id', 'LIKE', "%{$search}%")
                  ->orWhere('nomor_laporan', 'LIKE', "%{$search}%")
                  ->orWhere('rekomendasi_temuan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_unit', 'LIKE', "%{$search}%");
            });
        }

        $items = $activeQuery->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $units = \App\Models\Unit::orderBy('unit_name')->get();

        return view('monitoring.terpadu', compact(
            'items',
            'currentTab',
            'statusFilter',
            'unitFilter',
            'search',
            'tahunFilter',
            'units',
            'totalInternal',
            'belumTlInternal',
            'sudahTlInternal',
            'totalRegulator',
            'belumTlRegulator',
            'sudahTlRegulator'
        ));
    }

    public function uploadRegulatorExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $imported = 0;
            // Baris 1 biasanya header, mulai dari indeks 1
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[0]) && empty($row[1])) continue;

                $tlId = !empty($row[0]) ? trim($row[0]) : ('REG-' . date('Ymd') . '-' . rand(100, 999));
                $sumber = !empty($row[1]) ? trim($row[1]) : 'OJK';
                $unit = !empty($row[2]) ? trim($row[2]) : 'Bank Sulteng';
                $noLap = !empty($row[3]) ? trim($row[3]) : 'Laporan Pengawasan Regulator';
                $tglLap = !empty($row[4]) ? date('Y-m-d', strtotime($row[4])) : date('Y-m-d');
                $temuan = !empty($row[5]) ? trim($row[5]) : '-';
                $komitmen = !empty($row[6]) ? trim($row[6]) : '-';
                $target = !empty($row[7]) ? date('Y-m-d', strtotime($row[7])) : date('Y-m-d', strtotime('+30 days'));
                $prioritas = !empty($row[8]) ? strtoupper(trim($row[8])) : 'HIGH';
                $statusFinal = !empty($row[9]) ? strtoupper(trim($row[9])) : 'OPEN';

                Sop07RegisterTl::updateOrCreate([
                    'tl_id' => $tlId,
                ], [
                    'sumber_tl' => $sumber,
                    'nama_unit' => $unit,
                    'nomor_laporan' => $noLap,
                    'tanggal_laporan' => $tglLap,
                    'rekomendasi_temuan' => $temuan,
                    'komitmen_auditee' => $komitmen,
                    'target_penyelesaian' => $target,
                    'prioritas' => $prioritas,
                    'status_final' => $statusFinal,
                    'kelengkapan' => 'VALID',
                ]);
                $imported++;
            }

            return redirect()->route('monitoring.terpadu', ['tab' => 'regulator'])
                ->with('success', "Berhasil mengimpor {$imported} data temuan Regulator dari Excel.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function uploadInternalExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $imported = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[0]) && empty($row[1])) continue;

                $tlId = !empty($row[0]) ? trim($row[0]) : ('INT-' . date('Ymd') . '-' . rand(100, 999));
                $sumber = !empty($row[1]) ? trim($row[1]) : 'SKAI';
                $unit = !empty($row[2]) ? trim($row[2]) : 'Bank Sulteng';
                $noLap = !empty($row[3]) ? trim($row[3]) : 'Laporan Hasil Audit Internal';
                $tglLap = !empty($row[4]) ? date('Y-m-d', strtotime($row[4])) : date('Y-m-d');
                $temuan = !empty($row[5]) ? trim($row[5]) : '-';
                $komitmen = !empty($row[6]) ? trim($row[6]) : '-';
                $target = !empty($row[7]) ? date('Y-m-d', strtotime($row[7])) : date('Y-m-d', strtotime('+30 days'));
                $prioritas = !empty($row[8]) ? strtoupper(trim($row[8])) : 'MEDIUM';
                $statusFinal = !empty($row[9]) ? strtoupper(trim($row[9])) : 'OPEN';

                Sop07RegisterTl::updateOrCreate([
                    'tl_id' => $tlId,
                ], [
                    'sumber_tl' => $sumber,
                    'nama_unit' => $unit,
                    'nomor_laporan' => $noLap,
                    'tanggal_laporan' => $tglLap,
                    'rekomendasi_temuan' => $temuan,
                    'komitmen_auditee' => $komitmen,
                    'target_penyelesaian' => $target,
                    'prioritas' => $prioritas,
                    'status_final' => $statusFinal,
                    'kelengkapan' => 'VALID',
                ]);
                $imported++;
            }

            return redirect()->route('monitoring.terpadu', ['tab' => 'internal'])
                ->with('success', "Berhasil mengimpor {$imported} data temuan Internal dari Excel.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }

    public function downloadTemplateExcel($type)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"template_import_{$type}_tl.csv\"",
        ];

        $callback = function () use ($type) {
            $handle = fopen('php://output', 'w');
            // Header CSV
            fputcsv($handle, [
                'Nomor Temuan / TL ID',
                'Sumber Temuan',
                'Nama Unit Cabang',
                'Nomor Laporan / Surat',
                'Tanggal Laporan (YYYY-MM-DD)',
                'Uraian Temuan & Rekomendasi',
                'Komitmen Tindak Lanjut Auditee',
                'Target Penyelesaian (YYYY-MM-DD)',
                'Prioritas (HIGH/MEDIUM/LOW)',
                'Status (OPEN/CLOSED/INADEQUATE/OVERDUE)',
            ]);

            // Sample Row
            if ($type === 'regulator') {
                fputcsv($handle, [
                    'REG-2025-001',
                    'OJK',
                    'Kantor Cabang Utama Palu',
                    'S-142/KO.04/2025',
                    '2025-09-15',
                    'Penyempurnaan SOP Pengelolaan Kas ATM dan rekonsiliasi PIN Mailer',
                    'Menyusun revisi panduan operasional teknis khasanah',
                    '2025-11-30',
                    'HIGH',
                    'OPEN',
                ]);
            } else {
                fputcsv($handle, [
                    'INT-2025-001',
                    'Resident Auditor',
                    'KC Kolonodale',
                    'ST-RA/SA/202509/001',
                    '2025-09-30',
                    'Selisih persediaan kartu ATM Silver vs rekening persediaan GL dan kelebihan PIN Mailer',
                    'Melakukan penyesuaian rekening GL dan pemusnahan PIN Mailer rusak',
                    '2025-10-31',
                    'MEDIUM',
                    'OPEN',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // UPDATE STATUS TINDAK LANJUT LANGSUNG DARI MONITORING (Poin 7 & 8)
    // =========================================================================

    public function updateTindakLanjut(Request $request, $id)
    {
        $tl = Sop07RegisterTl::findOrFail($id);

        $request->validate([
            'status_tindak_lanjut' => 'required|in:SUDAH SELESAI,DALAM PROSES,TIDAK BISA',
            'target_penyelesaian' => 'nullable|date',
            'klarifikasi' => 'nullable|string',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $statusTl = $request->status_tindak_lanjut;
        $isClosed = ($statusTl === 'SUDAH SELESAI');
        $statusFinal = $isClosed ? 'CLOSED' : ($statusTl === 'TIDAK BISA' ? 'INADEQUATE' : 'OPEN');

        $dataUpdate = [
            'status_tindak_lanjut' => $statusTl,
            'status_final' => $statusFinal,
            'target_penyelesaian' => $request->target_penyelesaian ?: $tl->target_penyelesaian,
            'komitmen_auditee' => $request->klarifikasi ?: $tl->komitmen_auditee,
            'klarifikasi' => $request->klarifikasi ?: $tl->klarifikasi,
        ];

        if ($request->hasFile('file_bukti')) {
            $path = $request->file('file_bukti')->store('uploads/monitoring/bukti', 'public');
            $dataUpdate['bukti_terakhir'] = $path;
        }

        $tl->update($dataUpdate);

        return redirect()->back()->with('success', "Status tindak lanjut temuan {$tl->tl_id} berhasil diperbarui.");
    }

    public function viewBukti($id)
    {
        $tl = Sop07RegisterTl::findOrFail($id);
        $relative = $tl->bukti_terakhir;
        if (!$relative) {
            abort(404, 'Bukti tindak lanjut belum diunggah.');
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($disk->exists($relative)) {
            $filePath = $disk->path($relative);
        } elseif (file_exists(storage_path('app/public/' . $relative))) {
            $filePath = storage_path('app/public/' . $relative);
        } elseif (file_exists(public_path('storage/' . $relative))) {
            $filePath = public_path('storage/' . $relative);
        } else {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        $mime = mime_content_type($filePath);
        $fileData = file_get_contents($filePath);
        $base64 = base64_encode($fileData);
        $fileName = basename($filePath);

        if (str_contains($mime, 'pdf')) {
            return response(<<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Tindak Lanjut - {$fileName}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #1e293b; color: #fff; padding: 0.6rem 1.2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; }
        .topbar-title { font-size: 0.9rem; font-weight: 600; }
        .btn-download { background: #10b981; color: #fff; text-decoration: none; padding: 0.35rem 0.9rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        iframe { flex: 1; width: 100%; border: none; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-title">📄 Dokumen Bukti Tindak Lanjut: {$fileName}</div>
        <a href="data:application/pdf;base64,{$base64}" download="{$fileName}" class="btn-download">⬇️ Unduh PDF</a>
    </div>
    <iframe src="data:application/pdf;base64,{$base64}#toolbar=1"></iframe>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        } else {
            return response(<<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Tindak Lanjut - {$fileName}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #1e293b; color: #fff; padding: 0.6rem 1.2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; }
        .topbar-title { font-size: 0.9rem; font-weight: 600; }
        .btn-download { background: #10b981; color: #fff; text-decoration: none; padding: 0.35rem 0.9rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .img-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        img { max-width: 95%; max-height: 85vh; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-title">🖼️ Foto/Bukti Tindak Lanjut: {$fileName}</div>
        <a href="data:{$mime};base64,{$base64}" download="{$fileName}" class="btn-download">⬇️ Unduh Gambar</a>
    </div>
    <div class="img-container">
        <img src="data:{$mime};base64,{$base64}" alt="Bukti Tindak Lanjut" />
    </div>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }
    }

    // =========================================================================
    // OTOMATIS SINKRONISASI TEMUAN DARI KKA SURPRISE AUDIT & DEMO REGULATOR
    // =========================================================================

    public static function autoSyncSurpriseAuditFindings()
    {
        // 1. Sync Kas Findings
        $kasList = \App\Models\Sop05KkaKasDetail::with('assignment')->get();
        foreach ($kasList as $k) {
            $assignment = $k->assignment;
            if (!$assignment) continue;
            
            $isClosed = ($k->status_tindak_lanjut === 'SUDAH SELESAI');
            $statusFinal = $isClosed ? 'CLOSED' : ($k->status_tindak_lanjut === 'TIDAK BISA' ? 'INADEQUATE' : 'OPEN');
            $nominalSelisih = number_format(abs($k->selisih), 0, ',', '.');
            $uraianTemuan = "Pemeriksaan Fisik Kas ({$k->objek_pemeriksaan}): " . 
                ($k->selisih != 0 ? "Terdapat selisih kas fisik senilai Rp {$nominalSelisih}. " : "Pencocokan saldo fisik kas. ") .
                ($assignment->keterangan_selisih_kas ?: '');

            Sop07RegisterTl::updateOrCreate([
                'tl_id' => 'SA-KAS-' . $k->id,
            ], [
                'sumber_tl' => 'Surprise Audit',
                'nama_unit' => $assignment->nama_unit ?? 'Bank Sulteng',
                'nomor_laporan' => $assignment->nomor_assignment ?: $assignment->visit_id,
                'tanggal_laporan' => $assignment->tanggal_rencana ?: date('Y-m-d'),
                'rekomendasi_temuan' => $uraianTemuan,
                'komitmen_auditee' => $k->klarifikasi ?: 'Penyesuaian rekening kas operasional cabang.',
                'target_penyelesaian' => $k->komitmen_tanggal ?: date('Y-m-d', strtotime('+14 days')),
                'prioritas' => abs($k->selisih) > 1000000 ? 'HIGH' : 'MEDIUM',
                'status_final' => $statusFinal,
                'status_tindak_lanjut' => $k->status_tindak_lanjut ?: ($isClosed ? 'SUDAH SELESAI' : 'DALAM PROSES'),
                'bukti_terakhir' => $k->file_bukti,
                'klarifikasi' => $k->klarifikasi,
                'kelengkapan' => 'VALID',
            ]);
        }

        // 2. Sync Kartu Findings
        $kartuList = \App\Models\Sop05KkaKartuDetail::with('assignment')->get();
        foreach ($kartuList as $kr) {
            $assignment = $kr->assignment;
            if (!$assignment) continue;

            $isClosed = ($kr->status_tindak_lanjut === 'SUDAH SELESAI');
            $statusFinal = $isClosed ? 'CLOSED' : ($kr->status_tindak_lanjut === 'TIDAK BISA' ? 'INADEQUATE' : 'OPEN');
            $uraianTemuan = "Rekonsiliasi Persediaan Kartu ATM ({$kr->jenis_kartu}): " . 
                ($kr->findings_tabel1 ?: "Pencocokan fisik kartu ({$kr->jumlah_fisik_kartu} pcs), saldo GL persediaan, dan sistem cardman.");

            Sop07RegisterTl::updateOrCreate([
                'tl_id' => 'SA-KARTU-' . $kr->id,
            ], [
                'sumber_tl' => 'Surprise Audit',
                'nama_unit' => $assignment->nama_unit ?? 'Bank Sulteng',
                'nomor_laporan' => $assignment->nomor_assignment ?: $assignment->visit_id,
                'tanggal_laporan' => $assignment->tanggal_rencana ?: date('Y-m-d'),
                'rekomendasi_temuan' => $uraianTemuan,
                'komitmen_auditee' => $kr->klarifikasi ?: ($kr->findings_tabel2 ?: 'Penyelarasan stok fisik PIN Mailer dan persediaan buku.'),
                'target_penyelesaian' => $kr->komitmen_tanggal ?: date('Y-m-d', strtotime('+30 days')),
                'prioritas' => 'MEDIUM',
                'status_final' => $statusFinal,
                'status_tindak_lanjut' => $kr->status_tindak_lanjut ?: ($isClosed ? 'SUDAH SELESAI' : 'DALAM PROSES'),
                'bukti_terakhir' => $kr->file_bukti,
                'klarifikasi' => $kr->klarifikasi,
                'kelengkapan' => 'VALID',
            ]);
        }

        // 3. Sync ATM Findings
        $atmList = \App\Models\Sop05KkaAtmDetail::with('assignment')->get();
        foreach ($atmList as $at) {
            $assignment = $at->assignment;
            if (!$assignment) continue;

            $isClosed = ($at->status_tindak_lanjut === 'SUDAH SELESAI');
            $statusFinal = $isClosed ? 'CLOSED' : ($at->status_tindak_lanjut === 'TIDAK BISA' ? 'INADEQUATE' : 'OPEN');
            $nominalSelisih = number_format(abs($at->selisih), 0, ',', '.');
            $uraianTemuan = "Cash Opname Kas ATM ({$at->objek_lokasi_atm}): " . 
                ($at->selisih != 0 ? "Terdapat selisih fisik kas kaset vs CBS Olibs senilai Rp {$nominalSelisih}." : "Pemeriksaan 4 kaset ATM fisik selesai.");

            Sop07RegisterTl::updateOrCreate([
                'tl_id' => 'SA-ATM-' . $at->id,
            ], [
                'sumber_tl' => 'Surprise Audit',
                'nama_unit' => $assignment->nama_unit ?? 'Bank Sulteng',
                'nomor_laporan' => $assignment->nomor_assignment ?: $assignment->visit_id,
                'tanggal_laporan' => $assignment->tanggal_rencana ?: date('Y-m-d'),
                'rekomendasi_temuan' => $uraianTemuan,
                'komitmen_auditee' => $at->klarifikasi ?: 'Penyesuaian jurnal selisih kaset ATM.',
                'target_penyelesaian' => $at->komitmen_tanggal ?: date('Y-m-d', strtotime('+14 days')),
                'prioritas' => abs($at->selisih) > 5000000 ? 'HIGH' : 'MEDIUM',
                'status_final' => $statusFinal,
                'status_tindak_lanjut' => $at->status_tindak_lanjut ?: ($isClosed ? 'SUDAH SELESAI' : 'DALAM PROSES'),
                'bukti_terakhir' => $at->file_bukti,
                'klarifikasi' => $at->klarifikasi,
                'kelengkapan' => 'VALID',
            ]);
        }

        // 4. Seeder Demo Temuan Regulator Jika Masih Kosong
        if (Sop07RegisterTl::whereIn('sumber_tl', ['OJK', 'Regulator', 'Bank Indonesia', 'BI'])->count() === 0) {
            $demoRegulator = [
                [
                    'tl_id' => 'REG-2025-001',
                    'sumber_tl' => 'OJK',
                    'nama_unit' => 'Kantor Cabang Utama Palu',
                    'nomor_laporan' => 'S-142/KO.04/2025',
                    'tanggal_laporan' => '2025-08-15',
                    'rekomendasi_temuan' => 'Penyempurnaan Kebijakan Manajemen Risiko Operasional Khasanah Kas dan Dual Control Kunci Brankas.',
                    'komitmen_auditee' => 'Menyusun revisi SK Direksi tentang pedoman pengamanan kas dan rotasi pemegang kunci.',
                    'target_penyelesaian' => '2025-11-30',
                    'prioritas' => 'HIGH',
                    'status_final' => 'OPEN',
                    'status_tindak_lanjut' => 'DALAM PROSES',
                    'klarifikasi' => 'Sedang dalam tahap review oleh Bagian Hukum dan Kepatuhan.',
                    'kelengkapan' => 'VALID',
                ],
                [
                    'tl_id' => 'REG-2025-002',
                    'sumber_tl' => 'Bank Indonesia',
                    'nama_unit' => 'Kantor Cabang Tolitoli',
                    'nomor_laporan' => 'BI/PLW/SP/2025/088',
                    'tanggal_laporan' => '2025-09-02',
                    'rekomendasi_temuan' => 'Kepatuhan Penyampaian Laporan Berkala Sistem Pembayaran & Pengelolaan Uang Rupiah (PUR) Layak Edar.',
                    'komitmen_auditee' => 'Telah dilakukan perbaikan sistem rekonsiliasi sortir uang lusuh dan pengiriman laporan tepat waktu.',
                    'target_penyelesaian' => '2025-09-25',
                    'prioritas' => 'MEDIUM',
                    'status_final' => 'CLOSED',
                    'status_tindak_lanjut' => 'SUDAH SELESAI',
                    'klarifikasi' => 'Telah dikirimkan surat tanda terima dari Kantor Perwakilan BI Palu.',
                    'kelengkapan' => 'VALID',
                ],
                [
                    'tl_id' => 'REG-2025-003',
                    'sumber_tl' => 'OJK',
                    'nama_unit' => 'Kantor Cabang Luwuk',
                    'nomor_laporan' => 'S-189/KO.04/2025',
                    'tanggal_laporan' => '2025-09-18',
                    'rekomendasi_temuan' => 'Penerapan Standar Keamanan Fisik Ruang Server Cabang dan Kesiapan Disaster Recovery Plan (DRP) Operasional.',
                    'komitmen_auditee' => 'Pengadaan perangkat APAR FM-200 dan implementasi access control biometrik server.',
                    'target_penyelesaian' => '2025-12-15',
                    'prioritas' => 'HIGH',
                    'status_final' => 'OPEN',
                    'status_tindak_lanjut' => 'DALAM PROSES',
                    'klarifikasi' => 'Proses pengadaan perangkat pengaman fisik oleh Divisi Umum.',
                    'kelengkapan' => 'VALID',
                ],
                [
                    'tl_id' => 'REG-2025-004',
                    'sumber_tl' => 'OJK',
                    'nama_unit' => 'Kantor Cabang Kolonodale',
                    'nomor_laporan' => 'S-205/KO.04/2025',
                    'tanggal_laporan' => '2025-07-10',
                    'rekomendasi_temuan' => 'Penyelarasan Batas Maksimum Pemberian Kredit (BMPK) dan Kelengkapan Dokumentasi Sertifikat Agunan Pokok.',
                    'komitmen_auditee' => 'Seluruh berkas dokumen sertifikat agunan kredit telah di-register dan disimpan di brankas khasanah khusus.',
                    'target_penyelesaian' => '2025-08-30',
                    'prioritas' => 'HIGH',
                    'status_final' => 'CLOSED',
                    'status_tindak_lanjut' => 'SUDAH SELESAI',
                    'klarifikasi' => 'Berita acara serah terima dokumen agunan telah lengkap diverifikasi SKAI.',
                    'kelengkapan' => 'VALID',
                ],
            ];

            foreach ($demoRegulator as $dreg) {
                Sop07RegisterTl::updateOrCreate(['tl_id' => $dreg['tl_id']], $dreg);
            }
        }
    }
}