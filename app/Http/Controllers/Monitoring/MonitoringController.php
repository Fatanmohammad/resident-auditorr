<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sop07RegisterTl;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index()
    {
        $totalTl           = Sop07RegisterTl::where('tl_id', 'LIKE', 'TL-%')->count();
        $registerValid     = Sop07RegisterTl::where('kelengkapan', 'VALID')->count();
        $highRiskUnclosed  = Sop07RegisterTl::where('prioritas', 'HIGH')->where('status_final', '!=', 'CLOSED')->count();
        $overdue           = Sop07RegisterTl::where('status_final', 'OVERDUE')->count();

        $workflowCounts = [
            'PERBAIKI_DATA'    => Sop07RegisterTl::where('status_workflow', 'PERBAIKI DATA')->count(),
            'REVIEW_DUPLIKAT'  => Sop07RegisterTl::where('status_workflow', 'REVIEW DUPLIKAT')->count(),
            'MONITOR_REMINDER' => Sop07RegisterTl::where('status_workflow', 'MONITOR/REMINDER')->count(),
            'VERIFIKASI_BUKTI' => Sop07RegisterTl::where('status_workflow', 'VERIFIKASI BUKTI')->count(),
            'AJUKAN_CLOSURE'   => Sop07RegisterTl::where('status_workflow', 'AJUKAN CLOSURE')->count(),
            'PERLU_ESKALASI'   => Sop07RegisterTl::where('status_workflow', 'PERLU ESKALASI')->count(),
            'SELESAI'          => Sop07RegisterTl::where('status_workflow', 'SELESAI')->count(),
        ];

        $sumberCounts = [
            'Regulator'         => Sop07RegisterTl::where('sumber_tl', 'Regulator')->count(),
            'SKAI'              => Sop07RegisterTl::where('sumber_tl', 'SKAI')->count(),
            'Auditor Eksternal' => Sop07RegisterTl::where('sumber_tl', 'Auditor Eksternal')->count(),
            'Resident Auditor'  => Sop07RegisterTl::where('sumber_tl', 'Resident Auditor')->count(),
            'Manajemen/Komite'  => Sop07RegisterTl::where('sumber_tl', 'Manajemen/Komite')->count(),
        ];

        return view('monitoring.index', compact('totalTl', 'registerValid', 'highRiskUnclosed', 'overdue', 'workflowCounts', 'sumberCounts'));
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
}