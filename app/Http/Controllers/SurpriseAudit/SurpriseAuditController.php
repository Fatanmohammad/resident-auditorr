<?php

namespace App\Http\Controllers\SurpriseAudit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SurpriseAuditAssignment;
use App\Models\Sop05KkaKasDetail;
use App\Models\Sop05KkaAtmDetail;
use App\Models\Sop05KkaKartuDetail;
use App\Models\Sop05KkaDokumenBerhargaDetail;
use App\Models\Sop05KkaBarangCetakanDetail;
use App\Models\Sop05KkaAgunanDetail;
use App\Models\Sop05KkaAsetDetail;
use App\Models\Sop05KkaLingkunganDetail;
use Carbon\Carbon;

class SurpriseAuditController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $assignments = SurpriseAuditAssignment::with(['raUser', 'creator'])->latest()->get();
        $totalCount = $assignments->count();
        $siapCount = $assignments->filter(function($a) {
            $st = strtoupper(trim($a->status_assignment ?? $a->status ?? ''));
            return $st === 'SIAP DILAKSANAKAN' || empty($st);
        })->count();
        $prosesCount = $assignments->filter(function($a) {
            return in_array(strtoupper(trim($a->status_assignment ?? $a->status ?? '')), ['DALAM PROSES', 'SEDANG BERJALAN']);
        })->count();
        $selesaiCount = $assignments->filter(function($a) {
            return in_array(strtoupper(trim($a->status_assignment ?? $a->status ?? '')), ['SELESAI', 'SELESAI LENGKAP']);
        })->count();

        return view('surprise-audit.index', compact('assignments', 'totalCount', 'siapCount', 'prosesCount', 'selesaiCount', 'user'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'kabag_ra', 'kadiv_skai'])) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Admin / Supervisor yang berwenang mengubah status penugasan.');
        }

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $request->validate([
            'status_assignment' => 'required|string|in:SIAP DILAKSANAKAN,DALAM PROSES,SELESAI',
        ]);

        $assignment->update([
            'status_assignment' => $request->status_assignment,
        ]);

        return redirect()->back()->with('success', 'Status penugasan ' . $assignment->visit_id . ' berhasil diperbarui menjadi: ' . $request->status_assignment);
    }

    public function create()
    {
        $units = \App\Models\Unit::orderBy('unit_code')->get();
        $raUsers = \App\Models\User::where('role', 'ra')->orderBy('name')->get();
        $reviewers = \App\Models\User::whereIn('role', ['kabag_ra', 'kadiv_skai', 'admin'])->orderBy('name')->get();

        return view('surprise-audit.create', compact('units', 'raUsers', 'reviewers'));
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'kode_unit' => 'required',
            'nama_unit' => 'required',
            'tanggal_rencana' => 'required',
            'ruang_lingkup' => 'required|array',
            'sample_data_file' => 'nullable|file|mimes:xlsx,xls|max:10240',
        ]);

        $raUserId = $request->ra_user_id ?: auth()->id();
        $residentAuditorName = $request->resident_auditor;
        if ($raUserId) {
            $raUser = \App\Models\User::find($raUserId);
            if ($raUser) {
                $residentAuditorName = $raUser->name;
            }
        }

        $reviewerName = $request->reviewer;
        if ($request->reviewer_user_id) {
            $revUser = \App\Models\User::find($request->reviewer_user_id);
            if ($revUser) {
                $reviewerName = $revUser->name;
            }
        }

        // Handle Upload Sample Data Excel (Poin 6)
        $sampleFilePath = null;
        if ($request->hasFile('sample_data_file')) {
            $file = $request->file('sample_data_file');
            $fileName = 'sample_' . time() . '_' . $file->getClientOriginalName();
            $sampleFilePath = $file->storeAs('surprise_audit_samples', $fileName, 'public');
        }

        $tanggalFormat = date('Ymd', strtotime($request->tanggal_rencana));
        $visitId = 'SA-' . $tanggalFormat . '-' . $request->kode_unit;

        // Auto hitung nama hari jika belum ada
        $namaHari = $request->tanggal_hari;
        if (!$namaHari) {
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $namaHari = $days[date('w', strtotime($request->tanggal_rencana))] ?? 'Hari';
        }

        SurpriseAuditAssignment::create([
            'visit_id' => $visitId,
            'kode_unit' => $request->kode_unit,
            'nama_unit' => $request->nama_unit,
            'tanggal_rencana' => $request->tanggal_rencana,
            'tanggal_hari' => $namaHari,
            'tanggal_mulai_aktual' => $request->tanggal_mulai_aktual ?? null,
            'tanggal_selesai_aktual' => $request->tanggal_selesai_aktual ?? null,
            'resident_auditor' => $residentAuditorName ?? auth()->user()->name,
            'reviewer' => $reviewerName ?? 'Reviewer SKAI',
            'ra_user_id' => $raUserId,
            'created_by' => auth()->id(),
            'dasar_penugasan' => $request->dasar_penugasan ?? 'Informasi Khusus',
            'nomor_assignment' => $request->nomor_assignment ?: ('ST-RA/SA/' . date('Ym') . '/' . $request->kode_unit),
            'nomor_surat_tugas' => $request->nomor_assignment ?? null,
            'pemberi_tugas' => $request->pemberi_tugas ?? 'Pimpinan SKAI',
            'tujuan' => $request->tujuan ?? 'Memastikan kesesuaian saldo buku dengan kas fisik khasanah dan kas ATM secara mendadak.',
            'klasifikasi_kerahasiaan' => 'Rahasia - Need to Know',
            'ruang_lingkup_area' => $request->ruang_lingkup,
            'status_assignment' => 'SIAP DILAKSANAKAN',
            'sample_data_file' => $sampleFilePath,
        ]);

        return redirect()->route('surprise_audit.index')->with('success', 'Penugasan Surprise Audit berhasil dibuat oleh Resident Auditor.');
    }

    public function show($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $areas = [
            ['nama' => 'Kas Khasanah', 'kode' => 'kas', 'kka' => 'KKA_Kas', 'model' => Sop05KkaKasDetail::class],
            ['nama' => 'Kas ATM', 'kode' => 'kas_atm', 'kka' => 'KKA_Kas_ATM', 'model' => Sop05KkaAtmDetail::class],
            ['nama' => 'Kartu ATM/PIN', 'kode' => 'atm_pin', 'kka' => 'KKA_ATM_PIN', 'model' => Sop05KkaKartuDetail::class],
            ['nama' => 'Persediaan Barang Cetakan', 'kode' => 'pencetakan', 'kka' => 'KKA_Kartu_Dok', 'model' => Sop05KkaBarangCetakanDetail::class],
            ['nama' => 'Dokumen Agunan', 'kode' => 'agunan', 'kka' => 'KKA_Agunan', 'model' => Sop05KkaAgunanDetail::class],
            ['nama' => 'Aset', 'kode' => 'aset', 'kka' => 'KKA_Aset', 'model' => Sop05KkaAsetDetail::class],
            ['nama' => 'Lingkungan', 'kode' => 'lingkungan', 'kka' => 'KKA_Lingkungan', 'model' => Sop05KkaLingkunganDetail::class],
            ['nama' => 'Dokumen Berharga', 'kode' => 'dok_berharga', 'kka' => 'KKA_Dok_Berharga', 'model' => Sop05KkaDokumenBerhargaDetail::class],
        ];

        $controlData = [];
        $ruangLingkup = $assignment->ruang_lingkup_area ?? [];

        foreach ($areas as $area) {
            $isInScope = in_array($area['kode'], $ruangLingkup) ? 'Ya' : 'Tidak';
            
            $objekCount = 0;
            $exceptionCount = 0;

            if ($isInScope === 'Ya' && class_exists($area['model'])) {
                $query = $area['model']::where('surprise_audit_assignment_id', $assignment->id);
                $objekCount = $query->count();
                $exceptionCount = (clone $query)->where('exception_aktif', 'Ya')->count();
            }

            // Tentukan status KKA & Area berdasarkan pengisian data
            if ($isInScope === 'Tidak') {
                $statusKka = 'TIDAK DALAM LINGKUP';
                $statusArea = 'TIDAK DALAM LINGKUP';
            } else {
                $statusKka = $objekCount > 0 ? 'LENGKAP' : 'BELUM LENGKAP';
                $statusArea = $objekCount > 0 ? 'KKA SELESAI' : 'KKA BELUM SELESAI';
            }

            // Tentukan klasifikasi hasil berdasarkan temuan exception
            $klasifikasiHasil = $exceptionCount > 0 ? 'Temuan' : 'Normal';

            $controlData[] = [
                'area' => $area['nama'],
                'kode' => $area['kode'],
                'dalam_lingkup' => $isInScope,
                'status_kka' => $statusKka,
                'objek_diuji' => $objekCount,
                'exception' => $exceptionCount,
                'klasifikasi_hasil' => $klasifikasiHasil,
                'status_area' => $statusArea,
            ];
        }

        // Kumpulkan Seluruh Berkas Bukti & Sample yang Diunggah RA untuk Admin
        $uploadedBukti = [];

        if ($assignment->sample_data_file) {
            $uploadedBukti[] = [
                'area' => 'Sample Data Master',
                'objek' => 'Populasi / Sample Pemeriksaan Fisik',
                'file_name' => basename($assignment->sample_data_file),
                'file_path' => $assignment->sample_data_file,
                'type' => 'sample',
                'id' => $assignment->id,
                'status_tl' => 'TERSEDIA',
                'komitmen_tanggal' => null,
                'klarifikasi' => 'File lampiran data sample awal penugasan (Excel).',
            ];
        }

        $kasBukti = Sop05KkaKasDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->whereNotNull('file_bukti')
            ->where('file_bukti', '!=', '')
            ->get();
        foreach ($kasBukti as $kb) {
            $uploadedBukti[] = [
                'area' => 'Kas Khasanah & Teller',
                'objek' => $kb->objek_pemeriksaan,
                'file_name' => basename($kb->file_bukti),
                'file_path' => $kb->file_bukti,
                'type' => 'kas',
                'id' => $kb->id,
                'status_tl' => $kb->status_tindak_lanjut ?? 'SUDAH SELESAI',
                'komitmen_tanggal' => $kb->komitmen_tanggal,
                'klarifikasi' => $kb->klarifikasi ?? 'Bukti fisik/penyesuaian kas cabang.',
            ];
        }

        $kartuBukti = Sop05KkaKartuDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->whereNotNull('file_bukti')
            ->where('file_bukti', '!=', '')
            ->get();
        foreach ($kartuBukti as $krb) {
            $uploadedBukti[] = [
                'area' => 'Kartu ATM & PIN Mailer',
                'objek' => 'KARTU ' . strtoupper($krb->jenis_kartu),
                'file_name' => basename($krb->file_bukti),
                'file_path' => $krb->file_bukti,
                'type' => 'kartu',
                'id' => $krb->id,
                'status_tl' => $krb->status_tindak_lanjut ?? 'SUDAH SELESAI',
                'komitmen_tanggal' => $krb->komitmen_tanggal,
                'klarifikasi' => $krb->klarifikasi ?? 'Bukti/surat klarifikasi fisik kartu dan PIN.',
            ];
        }

        $atmBukti = Sop05KkaAtmDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->whereNotNull('file_bukti')
            ->where('file_bukti', '!=', '')
            ->get();
        foreach ($atmBukti as $atmb) {
            $uploadedBukti[] = [
                'area' => 'Kas ATM',
                'objek' => $atmb->objek_lokasi_atm,
                'file_name' => basename($atmb->file_bukti),
                'file_path' => $atmb->file_bukti,
                'type' => 'atm',
                'id' => $atmb->id,
                'status_tl' => $atmb->status_tindak_lanjut ?? 'SUDAH SELESAI',
                'komitmen_tanggal' => $atmb->komitmen_tanggal,
                'klarifikasi' => $atmb->klarifikasi ?? 'Bukti fisik cash opname ATM / counter printout.',
            ];
        }

        return view('surprise-audit.show', compact('assignment', 'controlData', 'uploadedBukti'));
    }

    public function viewBukti($type, $id, Request $request)
    {
        $filePath = null;
        $fileName = 'bukti';
        $relative = null;

        if ($type === 'kas') {
            $detail = Sop05KkaKasDetail::findOrFail($id);
            $relative = $detail->file_bukti;
            $fileName = 'bukti_kas_' . $id . '_' . basename($relative);
        } elseif ($type === 'kartu') {
            $detail = Sop05KkaKartuDetail::findOrFail($id);
            $relative = $detail->file_bukti;
            $fileName = 'bukti_kartu_' . $id . '_' . basename($relative);
        } elseif ($type === 'atm') {
            $detail = Sop05KkaAtmDetail::findOrFail($id);
            $relative = $detail->file_bukti;
            $fileName = 'bukti_atm_' . $id . '_' . basename($relative);
        } elseif ($type === 'sample') {
            $assignment = SurpriseAuditAssignment::where('visit_id', $id)->orWhere('id', $id)->firstOrFail();
            $relative = $assignment->sample_data_file;
            $fileName = 'sample_' . basename($relative);
        } else {
            abort(404, 'Tipe bukti tidak valid.');
        }

        if (!$relative) {
            abort(404, 'File bukti belum diunggah.');
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if ($disk->exists($relative)) {
            $filePath = $disk->path($relative);
        } elseif (file_exists(storage_path('app/public/' . $relative))) {
            $filePath = storage_path('app/public/' . $relative);
        } elseif (file_exists(public_path('storage/' . $relative))) {
            $filePath = public_path('storage/' . $relative);
        } else {
            abort(404, 'File fisik bukti tidak ditemukan di penyimpanan server.');
        }

        // Jika user minta download langsung
        if ($request->has('download') || $request->input('action') === 'download' || str_ends_with(strtolower($filePath), '.xlsx') || str_ends_with(strtolower($filePath), '.xls')) {
            return response()->download($filePath, $fileName);
        }

        $mime = mime_content_type($filePath);
        $fileData = file_get_contents($filePath);
        $base64 = base64_encode($fileData);
        $downloadUrl = route('surprise_audit.view_bukti', ['type' => $type, 'id' => $id, 'download' => 1]);

        if (str_contains($mime, 'pdf')) {
            return response(<<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Surprise Audit - {$fileName}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #1e293b; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .topbar { background: #0f172a; color: #fff; padding: 0.6rem 1.2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; }
        .topbar-title { font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
        .btn-download { background: #2563eb; color: #fff; text-decoration: none; padding: 0.35rem 0.9rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; transition: background 0.2s; }
        .btn-download:hover { background: #1d4ed8; }
        .viewer-container { flex: 1; width: 100%; height: calc(100vh - 48px); background: #334155; }
        embed, iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-title">
            <span>📄 Dokumen Bukti Surprise Audit: {$fileName}</span>
        </div>
        <a href="{$downloadUrl}" class="btn-download">
            ⬇️ Unduh PDF Asli
        </a>
    </div>
    <div class="viewer-container">
        <embed src="data:application/pdf;base64,{$base64}" type="application/pdf" />
    </div>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        } else {
            return response(<<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Surprise Audit - {$fileName}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #1e293b; color: #fff; padding: 0.6rem 1.2rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155; }
        .topbar-title { font-size: 0.9rem; font-weight: 600; }
        .btn-download { background: #2563eb; color: #fff; text-decoration: none; padding: 0.35rem 0.9rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .img-container { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        img { max-width: 95%; max-height: 85vh; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-title">
            <span>🖼️ Bukti Foto / Dokumen: {$fileName}</span>
        </div>
        <a href="{$downloadUrl}" class="btn-download">
            ⬇️ Unduh File Asli
        </a>
    </div>
    <div class="img-container">
        <img src="data:{$mime};base64,{$base64}" alt="Bukti Surprise Audit" />
    </div>
</body>
</html>
HTML, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }
    }

    public static function tanggalTerbilangIndonesia($dateStr)
    {
        $carbon = Carbon::parse($dateStr);
        $hariMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari = $hariMap[$carbon->format('l')] ?? 'Hari';
        
        $angka = [
            1 => 'Satu', 2 => 'Dua', 3 => 'Tiga', 4 => 'Empat', 5 => 'Lima', 6 => 'Enam', 7 => 'Tujuh', 8 => 'Delapan', 9 => 'Sembilan', 10 => 'Sepuluh', 11 => 'Sebelas',
            12 => 'Dua Belas', 13 => 'Tiga Belas', 14 => 'Empat Belas', 15 => 'Lima Belas', 16 => 'Enam Belas', 17 => 'Tujuh Belas', 18 => 'Delapan Belas', 19 => 'Sembilan Belas',
            20 => 'Dua Puluh', 21 => 'Dua Puluh Satu', 22 => 'Dua Puluh Dua', 23 => 'Dua Puluh Tiga', 24 => 'Dua Puluh Empat', 25 => 'Dua Puluh Lima', 26 => 'Dua Puluh Enam', 27 => 'Dua Puluh Tujuh', 28 => 'Dua Puluh Delapan', 29 => 'Dua Puluh Sembilan', 30 => 'Tiga Puluh', 31 => 'Tiga Puluh Satu'
        ];
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tglTeks = $angka[(int)$carbon->format('d')] ?? $carbon->format('d');
        $blnTeks = $bulan[(int)$carbon->format('m')] ?? $carbon->format('F');
        $thn = (int)$carbon->format('Y');
        $thnTeks = 'Dua Ribu ' . ($angka[$thn - 2000] ?? $thn);

        return [
            'hari' => $hari,
            'terbilang' => "Tanggal {$tglTeks} Bulan {$blnTeks} Tahun {$thnTeks}",
            'angka' => $carbon->format('d-m-Y'),
            'bulan' => $blnTeks,
            'tahun' => $thn,
        ];
    }

    public function showKas($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $kasData = $assignment->kasDetails;

        $totalSistem = $kasData->sum('nilai_sistem');
        $totalFisik = $kasData->sum('nilai_fisik');
        $totalSelisih = $totalFisik - $totalSistem;
        $exceptionAktif = $kasData->where('exception_aktif', 'Ya')->count();

        // Default nama pemeriksa & saksi jika belum ada
        if (!$assignment->pemeriksa_1) {
            $assignment->pemeriksa_1 = $assignment->resident_auditor ?? auth()->user()->name;
        }
        if (!$assignment->pemeriksa_2) {
            $assignment->pemeriksa_2 = 'Rostika Warda';
        }
        if (!$assignment->saksi_1) {
            $assignment->saksi_1 = 'Hajara Harun (Pemimpin Seksi Pelayanan)';
        }
        if (!$assignment->saksi_2) {
            $assignment->saksi_2 = 'Jasmawati T. Tolabo (Teller)';
        }
        if (!$assignment->pejabat_mengetahui) {
            $assignment->pejabat_mengetahui = 'ADE IRMA S. MAILILI (Pemimpin Cabang Pembantu)';
        }

        return view('surprise-audit.kas', compact(
            'assignment', 
            'kasData', 
            'totalSistem', 
            'totalFisik', 
            'totalSelisih', 
            'exceptionAktif'
        ));
    }

    public function storeKas(Request $request, $id)
    {
        $request->validate([
            'objek_pemeriksaan' => 'required',
            'nilai_sistem' => 'required|numeric',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // 1. Hitung Uang Layak Edar (ULE) Kertas
        $totalUle = (($request->ule_100k ?? 0) * 100000) +
                    (($request->ule_75k ?? 0) * 75000) +
                    (($request->ule_50k ?? 0) * 50000) +
                    (($request->ule_20k ?? 0) * 20000) +
                    (($request->ule_10k ?? 0) * 10000) +
                    (($request->ule_5k ?? 0) * 5000) +
                    (($request->ule_2k ?? 0) * 2000) +
                    (($request->ule_1k ?? 0) * 1000);

        // 2. Hitung Uang Tidak Layak Edar (UTLE / Uang Lusuh)
        $totalUtle = (($request->utle_100k ?? 0) * 100000) +
                     (($request->utle_75k ?? 0) * 75000) +
                     (($request->utle_50k ?? 0) * 50000) +
                     (($request->utle_20k ?? 0) * 20000) +
                     (($request->utle_10k ?? 0) * 10000) +
                     (($request->utle_5k ?? 0) * 5000) +
                     (($request->utle_2k ?? 0) * 2000) +
                     (($request->utle_1k ?? 0) * 1000);

        // 3. Hitung Logam (Keping)
        $totalLogam = (($request->logam_1000 ?? 0) * 1000) +
                      (($request->logam_500 ?? 0) * 500) +
                      (($request->logam_200 ?? 0) * 200) +
                      (($request->logam_100 ?? 0) * 100) +
                      (($request->logam_50 ?? 0) * 50) +
                      (($request->logam_25 ?? 0) * 25);

        $nilaiFisik = $totalUle + $totalUtle + $totalLogam;
        $nilaiSistem = (float) $request->nilai_sistem;
        $selisih = $nilaiFisik - $nilaiSistem;
        $exceptionAktif = $selisih != 0 ? 'Ya' : 'Tidak';

        // Upload bukti dokumen / surat alasan jika ada (Poin 7)
        $fileBuktiPath = null;
        if ($request->hasFile('file_bukti')) {
            $file = $request->file('file_bukti');
            $fileName = 'bukti_kas_' . time() . '_' . $file->getClientOriginalName();
            $fileBuktiPath = $file->storeAs('surprise_audit_bukti', $fileName, 'public');
        }

        $assignment->kasDetails()->create([
            'objek_pemeriksaan' => $request->objek_pemeriksaan,
            'nilai_sistem' => $nilaiSistem,
            'ule_100k' => $request->ule_100k ?? 0,
            'ule_75k'  => $request->ule_75k ?? 0,
            'ule_50k'  => $request->ule_50k ?? 0,
            'ule_20k'  => $request->ule_20k ?? 0,
            'ule_10k'  => $request->ule_10k ?? 0,
            'ule_5k'   => $request->ule_5k ?? 0,
            'ule_2k'   => $request->ule_2k ?? 0,
            'ule_1k'   => $request->ule_1k ?? 0,
            'utle_100k' => $request->utle_100k ?? 0,
            'utle_75k'  => $request->utle_75k ?? 0,
            'utle_50k'  => $request->utle_50k ?? 0,
            'utle_20k'  => $request->utle_20k ?? 0,
            'utle_10k'  => $request->utle_10k ?? 0,
            'utle_5k'   => $request->utle_5k ?? 0,
            'utle_2k'   => $request->utle_2k ?? 0,
            'utle_1k'   => $request->utle_1k ?? 0,
            'logam_1000' => $request->logam_1000 ?? 0,
            'logam_500'  => $request->logam_500 ?? 0,
            'logam_200'  => $request->logam_200 ?? 0,
            'logam_100'  => $request->logam_100 ?? 0,
            'logam_50'   => $request->logam_50 ?? 0,
            'logam_25'   => $request->logam_25 ?? 0,
            'total_ule' => $totalUle,
            'total_utle' => $totalUtle,
            'total_logam' => $totalLogam,
            'nilai_fisik' => $nilaiFisik,
            'selisih' => $selisih,
            'status_uji' => 'SIAP DIREVIU',
            'catatan_pemeriksa' => $request->catatan_pemeriksa,
            'exception_aktif' => $exceptionAktif,
            'status_tindak_lanjut' => $request->status_tindak_lanjut ?? 'SUDAH SELESAI',
            'komitmen_tanggal' => $request->komitmen_tanggal ?? null,
            'file_bukti' => $fileBuktiPath,
            'klarifikasi' => $request->klarifikasi ?? null,
        ]);

        // Simpan / update data Berita Acara pada Assignment
        $assignment->update([
            'jam_mulai_opname' => $request->jam_mulai_opname ?? $assignment->jam_mulai_opname ?? '16.30 WITA',
            'jam_selesai_opname' => $request->jam_selesai_opname ?? $assignment->jam_selesai_opname ?? '18.45 WITA',
            'kondisi_opname' => $request->kondisi_opname ?? $assignment->kondisi_opname ?? 'sebelum Kas Harian dilimpahkan Kepada Teller',
            'pemeriksa_1' => $request->pemeriksa_1 ?? $assignment->pemeriksa_1 ?? $assignment->resident_auditor,
            'pemeriksa_2' => $request->pemeriksa_2 ?? $assignment->pemeriksa_2 ?? 'Rostika Warda',
            'saksi_1' => $request->saksi_1 ?? $assignment->saksi_1 ?? 'Hajara Harun',
            'saksi_2' => $request->saksi_2 ?? $assignment->saksi_2 ?? 'Jasmawati T. Tolabo',
            'saksi_list' => $request->saksi_list ? (is_array($request->saksi_list) ? $request->saksi_list : explode("\n", str_replace("\r", "", $request->saksi_list))) : $assignment->saksi_list,
            'pejabat_mengetahui' => $request->pejabat_mengetahui ?? $assignment->pejabat_mengetahui ?? 'ADE IRMA S. MAILILI',
            'keterangan_selisih_kas' => $request->keterangan_selisih_kas ?? $assignment->keterangan_selisih_kas,
        ]);

        // Poin 8: Otomasi Status Lifecycle (Ketika RA sedang mengerjakan KKA -> otomatis DALAM PROSES)
        if ($assignment->status_assignment === 'SIAP DILAKSANAKAN' || empty($assignment->status_assignment)) {
            $assignment->update(['status_assignment' => 'DALAM PROSES']);
        }

        return redirect()->route('surprise_audit.kas', $assignment->visit_id)
            ->with('success', 'Data Opname Keadaan Kas berhasil disimpan!');
    }

    public function beritaAcaraKas($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $kasData = $assignment->kasDetails;
        $totalSistem = $kasData->sum('nilai_sistem');
        $totalFisik = $kasData->sum('nilai_fisik');
        $selisih = $totalFisik - $totalSistem;

        $tglInfo = self::tanggalTerbilangIndonesia($assignment->tanggal_rencana);

        // Rekapitulasi per nominal dari seluruh pos kas
        $denominasiKertas = [
            100000 => $kasData->sum('ule_100k'),
            75000  => $kasData->sum('ule_75k'),
            50000  => $kasData->sum('ule_50k'),
            20000  => $kasData->sum('ule_20k'),
            10000  => $kasData->sum('ule_10k'),
            5000   => $kasData->sum('ule_5k'),
            2000   => $kasData->sum('ule_2k'),
            1000   => $kasData->sum('ule_1k'),
        ];

        $denominasiLogam = [
            1000 => $kasData->sum('logam_1000'),
            500  => $kasData->sum('logam_500'),
            200  => $kasData->sum('logam_200'),
            100  => $kasData->sum('logam_100'),
            50   => $kasData->sum('logam_50'),
            25   => $kasData->sum('logam_25'),
        ];

        $denominasiLusuh = [
            100000 => $kasData->sum('utle_100k'),
            75000  => $kasData->sum('utle_75k'),
            50000  => $kasData->sum('utle_50k'),
            20000  => $kasData->sum('utle_20k'),
            10000  => $kasData->sum('utle_10k'),
            5000   => $kasData->sum('utle_5k'),
            2000   => $kasData->sum('utle_2k'),
            1000   => $kasData->sum('utle_1k'),
        ];

        $subtotalKertas = $kasData->sum('total_ule');
        $subtotalLogam = $kasData->sum('total_logam');
        $subtotalLusuh = $kasData->sum('total_utle');

        // Daftar saksi
        $saksiList = $assignment->saksi_list ?: [
            'Hajara Harun',
            'Jasmawati T. Tolabo',
            'Fahri Patrayudha',
            'Aulya Setiasi, SE',
            'Alber Max Iskandar',
            'Fibriana R. Mastura',
            'Mutiara R. Pusadan',
            'Andee Refo Kani'
        ];

        return view('surprise-audit.berita-acara-kas', compact(
            'assignment',
            'tglInfo',
            'kasData',
            'totalSistem',
            'totalFisik',
            'selisih',
            'denominasiKertas',
            'denominasiLogam',
            'denominasiLusuh',
            'subtotalKertas',
            'subtotalLogam',
            'subtotalLusuh',
            'saksiList'
        ));
    }

    public function atm($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $atmData = Sop05KkaAtmDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        $saldoBukuTotal = $atmData->sum('saldo_buku');
        $hasilFisikTotal = $atmData->sum('hasil_fisik_total');
        $selisihTotal = $hasilFisikTotal - $saldoBukuTotal;
        $exceptionAktifCount = $atmData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.atm', compact(
            'assignment', 
            'atmData', 
            'saldoBukuTotal', 
            'hasilFisikTotal', 
            'selisihTotal', 
            'exceptionAktifCount'
        ));
    }

    public function storeAtm(Request $request, $id)
    {
        $request->validate([
            'objek_lokasi_atm' => 'required',
            'saldo_buku' => 'required|numeric',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $k1_pecahan = (float) ($request->kaset1_pecahan ?? 100000);
        $k1_lembar = (int) ($request->kaset1_lembar ?? 0);
        $k1_nilai = $k1_pecahan * $k1_lembar;

        $k2_pecahan = (float) ($request->kaset2_pecahan ?? 50000);
        $k2_lembar = (int) ($request->kaset2_lembar ?? 0);
        $k2_nilai = $k2_pecahan * $k2_lembar;

        $k3_pecahan = (float) ($request->kaset3_pecahan ?? 100000);
        $k3_lembar = (int) ($request->kaset3_lembar ?? 0);
        $k3_nilai = $k3_pecahan * $k3_lembar;

        $k4_pecahan = (float) ($request->kaset4_pecahan ?? 50000);
        $k4_lembar = (int) ($request->kaset4_lembar ?? 0);
        $k4_nilai = $k4_pecahan * $k4_lembar;

        $hasilFisikTotal = $k1_nilai + $k2_nilai + $k3_nilai + $k4_nilai;
        $selisih = $hasilFisikTotal - (float) $request->saldo_buku;
        $exceptionAktif = $selisih != 0 ? 'Ya' : 'Tidak';

        $fileBuktiPath = null;
        if ($request->hasFile('file_bukti')) {
            $fileBuktiPath = $request->file('file_bukti')->store('uploads/surprise_audit/bukti', 'public');
        }

        Sop05KkaAtmDetail::create([
            'surprise_audit_assignment_id' => $assignment->id,
            'area_kka' => 'atm',
            'objek_lokasi_atm' => $request->objek_lokasi_atm,
            'saldo_buku' => $request->saldo_buku,
            'kaset1_pecahan' => $k1_pecahan,
            'kaset1_lembar' => $k1_lembar,
            'kaset1_nilai' => $k1_nilai,
            'kaset2_pecahan' => $k2_pecahan,
            'kaset2_lembar' => $k2_lembar,
            'kaset2_nilai' => $k2_nilai,
            'kaset3_pecahan' => $k3_pecahan,
            'kaset3_lembar' => $k3_lembar,
            'kaset3_nilai' => $k3_nilai,
            'kaset4_pecahan' => $k4_pecahan,
            'kaset4_lembar' => $k4_lembar,
            'kaset4_nilai' => $k4_nilai,
            'hasil_fisik_total' => $hasilFisikTotal,
            'selisih' => $selisih,
            'status_uji' => $exceptionAktif === 'Ya' ? 'EXCEPTION' : 'SESUAI',
            'sebab' => $request->sebab,
            'dampak' => $request->dampak,
            'referensi_bukti' => $request->referensi_bukti,
            'validasi' => 'LENGKAP',
            'exception_aktif' => $exceptionAktif,
            'status_tindak_lanjut' => $request->status_tindak_lanjut ?? 'SUDAH SELESAI',
            'komitmen_tanggal' => $request->komitmen_tanggal ?? null,
            'file_bukti' => $fileBuktiPath,
            'klarifikasi' => $request->klarifikasi ?? null,
        ]);

        // Poin 8: Otomasi Status Lifecycle (Ketika RA sedang mengerjakan KKA -> otomatis DALAM PROSES)
        if ($assignment->status_assignment === 'SIAP DILAKSANAKAN' || empty($assignment->status_assignment)) {
            $assignment->update(['status_assignment' => 'DALAM PROSES']);
        }

        return redirect()->route('surprise_audit.atm', $assignment->visit_id)
            ->with('success', 'Data Opname KKA Kas ATM berhasil disimpan.');
    }

    public function destroyAtm($id, $detailId)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        Sop05KkaAtmDetail::where('id', $detailId)
            ->where('surprise_audit_assignment_id', $assignment->id)
            ->delete();

        return redirect()->route('surprise_audit.atm', $assignment->visit_id)
            ->with('success', 'Data Opname ATM berhasil dihapus.');
    }

    // ==========================================
    // MODUL: KKA KARTU ATM & PIN MAILER (WP-RA-05-E)
    // ==========================================

    public function showKartu($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // 3 Tipe Kartu Resmi Bank Sulteng dengan Harga Modal Standar
        $masterKartu = [
            'Silver'   => 42180,
            'Gold'     => 68820,
            'Platinum' => 68820,
        ];

        foreach ($masterKartu as $jenis => $hargaModal) {
            Sop05KkaKartuDetail::firstOrCreate([
                'surprise_audit_assignment_id' => $assignment->id,
                'jenis_kartu' => $jenis,
            ], [
                'lokasi_penyimpanan' => 'Brankas Utama',
                'harga_per_kartu' => $hargaModal,
                'jumlah_fisik_kartu' => ($jenis === 'Silver' ? 365 : ($jenis === 'Platinum' ? 65 : 0)),
                'jumlah_pin_mailer'  => ($jenis === 'Silver' ? 367 : 0),
                'saldo_persediaan_gl' => 22855910,
                'cardman_qty' => 431,
                'cardman_nominal' => 19911180,
                'periode_audit' => 'SEMESTER 2 ' . date('Y'),
                'periode_data' => 'SEMESTER 1 ' . date('Y'),
                'posisi_rekonsiliasi' => date('Y-m-d'),
            ]);
        }

        $kartuData = Sop05KkaKartuDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->orderByRaw("FIELD(jenis_kartu, 'Silver', 'Gold', 'Platinum')")
            ->get();

        $totalFisikQty = $kartuData->sum('jumlah_fisik_kartu');
        $totalNominalFisik = $kartuData->sum('nilai_fisik_kartu');
        $saldoPersediaanGl = $kartuData->first()->saldo_persediaan_gl ?? 22855910;
        $selisihPersediaanGl = $totalNominalFisik - $saldoPersediaanGl;
        $totalPinMailer = $kartuData->sum('jumlah_pin_mailer');
        $selisihTotalKartuPin = $totalFisikQty - $totalPinMailer;

        $cardmanQty = $kartuData->first()->cardman_qty ?? 431;
        $cardmanNominal = $kartuData->first()->cardman_nominal ?? 19911180;

        $exceptionAktifCount = $kartuData->where('exception_aktif', 'Ya')->count();

        // Default Findings otomatis jika belum diedit user
        $defaultFindings1 = "Ditemukan bahwa posisi antara fisik, saldo persediaan dan sistem cardman berbeda; dimana fisik mencatat sebanyak {$totalFisikQty} pcs atau Rp " . number_format($totalNominalFisik, 0, ',', '.') . "; rekening persediaan Rp " . number_format($saldoPersediaanGl, 0, ',', '.') . " dan sistem mencatat {$cardmanQty} pcs atau Rp " . number_format($cardmanNominal, 0, ',', '.') . ".";
        
        $findingsTabel1 = $kartuData->first()->findings_tabel1 ?: $defaultFindings1;
        $findingsTabel2 = $kartuData->first()->findings_tabel2 ?: "Pin Mailer KC {$assignment->nama_unit} untuk Kartu Silver diketahui kelebihan dimana salah satunya dikarenakan kesalahan petugas dalam memberikan PIN Mailer pada saat aktivasi kartu ATM; untuk Kartu Platinum tidak disertai dengan PIN Mailer.";

        return view('surprise-audit.kartu', compact(
            'assignment',
            'kartuData',
            'totalFisikQty',
            'totalNominalFisik',
            'saldoPersediaanGl',
            'selisihPersediaanGl',
            'totalPinMailer',
            'selisihTotalKartuPin',
            'cardmanQty',
            'cardmanNominal',
            'findingsTabel1',
            'findingsTabel2',
            'exceptionAktifCount'
        ));
    }

    public function storeKartu(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $items = $request->input('items', []);
        $saldoGl = (float) ($request->input('saldo_persediaan_gl', 22855910));
        $cardmanQty = (int) ($request->input('cardman_qty', 431));
        $cardmanNominal = (float) ($request->input('cardman_nominal', 19911180));
        $findings1 = $request->input('findings_tabel1');
        $findings2 = $request->input('findings_tabel2');

        foreach ($items as $detailId => $data) {
            $harga = (float) ($data['harga_per_kartu'] ?? 0);
            $fisikKartu = (int) ($data['jumlah_fisik_kartu'] ?? 0);
            $pinMailer = (int) ($data['jumlah_pin_mailer'] ?? 0);

            $nilaiFisik = $fisikKartu * $harga;
            $selisihPinFisik = $fisikKartu - $pinMailer; // Kartu - PIN Mailer (positif = kartu lebih, negatif = pin lebih)

            // Upload bukti per item jika ada (Poin 7)
            $fileBuktiPath = null;
            if ($request->hasFile("items.{$detailId}.file_bukti")) {
                $file = $request->file("items.{$detailId}.file_bukti");
                $fileName = 'bukti_kartu_' . time() . '_' . $file->getClientOriginalName();
                $fileBuktiPath = $file->storeAs('surprise_audit_bukti', $fileName, 'public');
            }

            $updateData = [
                'lokasi_penyimpanan' => $data['lokasi_penyimpanan'] ?? 'Brankas Utama',
                'harga_per_kartu' => $harga,
                'jumlah_fisik_kartu' => $fisikKartu,
                'jumlah_pin_mailer' => $pinMailer,
                'nilai_fisik_kartu' => $nilaiFisik,
                'selisih_pin_fisik' => $selisihPinFisik,
                'saldo_persediaan_gl' => $saldoGl,
                'cardman_qty' => $cardmanQty,
                'cardman_nominal' => $cardmanNominal,
                'findings_tabel1' => $findings1,
                'findings_tabel2' => $findings2,
                'status_tindak_lanjut' => $data['status_tindak_lanjut'] ?? 'SUDAH SELESAI',
                'komitmen_tanggal' => $data['komitmen_tanggal'] ?? null,
                'klarifikasi' => $data['klarifikasi'] ?? null,
                'exception_aktif' => ($selisihPinFisik != 0 || $nilaiFisik == 0) ? 'Ya' : 'Tidak',
                'status_rekonsiliasi' => ($selisihPinFisik == 0) ? 'SESUAI' : 'SELISIH',
            ];

            if ($fileBuktiPath) {
                $updateData['file_bukti'] = $fileBuktiPath;
            }

            Sop05KkaKartuDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update($updateData);
        }

        // Poin 8: Otomasi Status Lifecycle
        if ($assignment->status_assignment === 'SIAP DILAKSANAKAN' || empty($assignment->status_assignment)) {
            $assignment->update(['status_assignment' => 'DALAM PROSES']);
        }

        return redirect()->route('surprise_audit.kartu', $assignment->visit_id)
            ->with('success', 'Data Rekonsiliasi Kartu ATM & PIN Mailer berhasil disimpan.');
    }

    public function beritaAcaraKartu($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $kartuData = Sop05KkaKartuDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->orderByRaw("FIELD(jenis_kartu, 'Silver', 'Gold', 'Platinum')")
            ->get();

        $totalFisikQty = $kartuData->sum('jumlah_fisik_kartu');
        $totalNominalFisik = $kartuData->sum('nilai_fisik_kartu');
        $saldoPersediaanGl = $kartuData->first()->saldo_persediaan_gl ?? 22855910;
        $selisihPersediaanGl = $totalNominalFisik - $saldoPersediaanGl;
        $totalPinMailer = $kartuData->sum('jumlah_pin_mailer');
        $selisihTotalKartuPin = $totalFisikQty - $totalPinMailer;

        $cardmanQty = $kartuData->first()->cardman_qty ?? 431;
        $cardmanNominal = $kartuData->first()->cardman_nominal ?? 19911180;

        $findings1 = $kartuData->first()->findings_tabel1 ?? '';
        $findings2 = $kartuData->first()->findings_tabel2 ?? '';

        $tglInfo = self::tanggalTerbilangIndonesia($assignment->tanggal_rencana);

        return view('surprise-audit.berita-acara-kartu', compact(
            'assignment',
            'tglInfo',
            'kartuData',
            'totalFisikQty',
            'totalNominalFisik',
            'saldoPersediaanGl',
            'selisihPersediaanGl',
            'totalPinMailer',
            'selisihTotalKartuPin',
            'cardmanQty',
            'cardmanNominal',
            'findings1',
            'findings2'
        ));
    }

    // Poin 9: Admin Review (Approve & Reject)
    public function adminReview(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'kabag_ra', 'kadiv_skai'])) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Admin / Reviewer yang berwenang melakukan approval/reject KKA.');
        }

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $request->validate([
            'action' => 'required|in:approve,reject',
            'catatan_review_admin' => 'nullable|string',
            'nomor_surat_tugas' => 'nullable|string',
        ]);

        if ($request->action === 'approve') {
            $assignment->update([
                'status_review_admin' => 'DISETUJUI',
                'status_assignment' => 'SELESAI',
                'catatan_review_admin' => $request->catatan_review_admin ?? 'Disetujui oleh Reviewer SKAI/Admin.',
                'nomor_surat_tugas' => $request->nomor_surat_tugas ?: $assignment->nomor_assignment,
                'nomor_assignment' => $request->nomor_surat_tugas ?: $assignment->nomor_assignment,
            ]);
            return redirect()->back()->with('success', 'KKA Fisik berhasil di-APPROVE. RA sekarang dapat melakukan tindak lanjut klarifikasi ke cabang.');
        } else {
            if (!$request->catatan_review_admin) {
                return redirect()->back()->with('error', 'Catatan reject wajib diisi agar RA mengetahui bagian mana yang perlu diperbaiki.');
            }
            $assignment->update([
                'status_review_admin' => 'PERLU PERBAIKAN',
                'status_assignment' => 'DALAM PROSES',
                'catatan_review_admin' => $request->catatan_review_admin,
            ]);
            return redirect()->back()->with('error', 'KKA Fisik telah di-REJECT. RA wajib memperbaiki KKA sesuai catatan review.');
        }
    }

    // Poin 7: RA Meminta Bukti Tambahan ke Cabang
    public function mintaBuktiCabang(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $detailType = $request->detail_type; // 'kas' atau 'kartu'
        $detailId = $request->detail_id;

        if ($detailType === 'kas') {
            Sop05KkaKasDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update(['minta_bukti_cabang' => true]);
        } elseif ($detailType === 'kartu') {
            Sop05KkaKartuDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update(['minta_bukti_cabang' => true]);
        }

        return redirect()->back()->with('success', 'Permintaan bukti tambahan telah dicatat dan diteruskan ke cabang.');
    }

    // ==========================================
    // MODUL: KKA PERSEDIAAN BARANG CETAKAN (WP-RA-05-F)
    // ==========================================

    public function showBarangCetakan($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $cetakanDefault = [
            'Buku Tabungan Utama / Pcs',
            'Bilyet Deposito Kosong / Lembar',
            'Slip Setoran / Penarikan / Lembar',
            'Formulir Pembukaan Rekening / Lembar',
            'Kwitansi Bermaterai / Buku'
        ];

        foreach ($cetakanDefault as $jenis) {
            Sop05KkaBarangCetakanDetail::firstOrCreate([
                'surprise_audit_assignment_id' => $assignment->id,
                'jenis_barang_cetakan' => $jenis,
            ]);
        }

        $cetakanData = Sop05KkaBarangCetakanDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        $catatanTotal = $cetakanData->sum('catatan_petugas');
        $fisikTotal = $cetakanData->sum('hasil_fisik');
        $selisihTotal = $fisikTotal - $catatanTotal;
        $exceptionAktifCount = $cetakanData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.barang-cetakan', compact(
            'assignment',
            'cetakanData',
            'catatanTotal',
            'fisikTotal',
            'selisihTotal',
            'exceptionAktifCount'
        ));
    }

    public function storeBarangCetakan(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $items = $request->input('items', []);

        foreach ($items as $detailId => $data) {
            $catatan = (int) ($data['catatan_petugas'] ?? 0);
            $fisik = (int) ($data['hasil_fisik'] ?? 0);
            $kondisi = $data['kondisi_penyimpanan'] ?? 'Aman/Terkunci';

            $selisih = $fisik - $catatan;
            $hasException = ($selisih != 0) || ($kondisi === 'Tidak Sesuai');

            $statusUji = $hasException ? 'EXCEPTION' : 'SESUAI';
            $exceptionAktif = $hasException ? 'Ya' : 'Tidak';

            Sop05KkaBarangCetakanDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'jenis_barang_cetakan' => $data['jenis_barang_cetakan'] ?? 'Barang Cetakan',
                    'catatan_petugas' => $catatan,
                    'hasil_fisik' => $fisik,
                    'selisih' => $selisih,
                    'kondisi_penyimpanan' => $kondisi,
                    'status_uji' => $statusUji,
                    'sebab' => $data['sebab'] ?? null,
                    'dampak' => $data['dampak'] ?? null,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'validasi' => $hasException ? 'ANALISIS BELUM LENGKAP' : 'VALID',
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.barang_cetakan', $assignment->visit_id)
            ->with('success', 'Data KKA Persediaan Barang Cetakan (WP-RA-05-F) berhasil diperbarui!');
    }

    // ==========================================
    // MODUL: KKA DOKUMEN AGUNAN (WP-RA-05-H)
    // ==========================================

    public function showAgunan($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $agunanData = Sop05KkaAgunanDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        $totalDokumen = $agunanData->count();
        $exceptionAktifCount = $agunanData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.agunan', compact(
            'assignment',
            'agunanData',
            'totalDokumen',
            'exceptionAktifCount'
        ));
    }

    public function storeAgunan(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        if ($request->has('add_item')) {
            Sop05KkaAgunanDetail::create([
                'surprise_audit_assignment_id' => $assignment->id,
                'cif_debitur' => $request->cif_debitur,
                'no_kredit' => $request->no_kredit,
                'jenis_agunan' => $request->jenis_agunan ?? 'Sertifikat (SHM/SHGB)',
                'dokumen_menurut_register' => $request->dokumen_menurut_register ?? 'Sertifikat Hak Milik',
                'bentuk_disyaratkan' => $request->bentuk_disyaratkan ?? 'Asli',
                'dokumen_ditemukan' => 'Ya',
                'bentuk_ditemukan' => $request->bentuk_disyaratkan ?? 'Asli',
                'identitas_sesuai' => 'Ya',
                'nomor_dokumen' => $request->nomor_dokumen,
                'tanggal_kedaluwarsa' => $request->tanggal_kedaluwarsa,
                'akses_custodian' => 'Sesuai',
                'register_mutakhir' => 'Ya',
                'metode_pemilihan' => 'SOP 02 - System Driven',
            ]);

            return redirect()->route('surprise_audit.agunan', $assignment->visit_id)
                ->with('success', 'Sampel Dokumen Agunan berhasil ditambahkan.');
        }

        $items = $request->input('items', []);
        $tanggalAudit = Carbon::parse($assignment->tanggal_rencana ?? now());

        foreach ($items as $detailId => $data) {
            $tglExp = !empty($data['tanggal_kedaluwarsa']) ? Carbon::parse($data['tanggal_kedaluwarsa']) : null;
            $hariKeKedaluwarsa = $tglExp ? $tanggalAudit->diffInDays($tglExp, false) : null;

            $ditemukan = $data['dokumen_ditemukan'] ?? 'Ya';
            $bentukDisyaratkan = $data['bentuk_disyaratkan'] ?? 'Asli';
            $bentukDitemukan = $data['bentuk_ditemukan'] ?? 'Asli';
            $identitasSesuai = $data['identitas_sesuai'] ?? 'Ya';
            $custodian = $data['akses_custodian'] ?? 'Sesuai';
            $regMutakhir = $data['register_mutakhir'] ?? 'Ya';

            $hasException = ($ditemukan === 'Tidak') ||
                             ($bentukDisyaratkan !== $bentukDitemukan) ||
                             ($identitasSesuai === 'Tidak') ||
                             ($hariKeKedaluwarsa !== null && $hariKeKedaluwarsa < 0) ||
                             ($custodian === 'Tidak Sesuai') ||
                             ($regMutakhir === 'Tidak');

            $statusUji = $hasException ? 'EXCEPTION' : 'SESUAI';
            $exceptionAktif = $hasException ? 'Ya' : 'Tidak';

            Sop05KkaAgunanDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'cif_debitur' => $data['cif_debitur'] ?? null,
                    'no_kredit' => $data['no_kredit'] ?? null,
                    'jenis_agunan' => $data['jenis_agunan'] ?? null,
                    'dokumen_menurut_register' => $data['dokumen_menurut_register'] ?? null,
                    'bentuk_disyaratkan' => $bentukDisyaratkan,
                    'dokumen_ditemukan' => $ditemukan,
                    'bentuk_ditemukan' => $bentukDitemukan,
                    'identitas_sesuai' => $identitasSesuai,
                    'nomor_dokumen' => $data['nomor_dokumen'] ?? null,
                    'tanggal_kedaluwarsa' => $data['tanggal_kedaluwarsa'] ?? null,
                    'hari_ke_kedaluwarsa' => $hariKeKedaluwarsa,
                    'akses_custodian' => $custodian,
                    'register_mutakhir' => $regMutakhir,
                    'sebab' => $data['sebab'] ?? null,
                    'dampak' => $data['dampak'] ?? null,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'status_uji' => $statusUji,
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.agunan', $assignment->visit_id)
            ->with('success', 'Data KKA Dokumen Agunan (WP-RA-05-H) berhasil diperbarui!');
    }

    // ==========================================
    // MODUL: KKA INVENTARISASI ASET (WP-RA-05-I)
    // ==========================================

    public function showAset($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $asetData = Sop05KkaAsetDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        $qtyRegisterTotal = $asetData->sum('qty_register');
        $qtyFisikTotal = $asetData->sum('qty_fisik');
        $selisihTotal = $qtyFisikTotal - $qtyRegisterTotal;
        $exceptionAktifCount = $asetData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.aset', compact(
            'assignment',
            'asetData',
            'qtyRegisterTotal',
            'qtyFisikTotal',
            'selisihTotal',
            'exceptionAktifCount'
        ));
    }

    public function storeAset(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        if ($request->has('add_item')) {
            Sop05KkaAsetDetail::create([
                'surprise_audit_assignment_id' => $assignment->id,
                'id_tag_aset' => $request->id_tag_aset,
                'jenis_nama_aset' => $request->jenis_nama_aset,
                'lokasi_register' => $request->lokasi_register ?? 'Kantor Cabang',
                'lokasi_fisik' => $request->lokasi_fisik ?? 'Kantor Cabang',
                'qty_register' => (int) ($request->qty_register ?? 1),
                'qty_fisik' => (int) ($request->qty_fisik ?? 1),
                'kondisi_fisik' => $request->kondisi_fisik ?? 'Baik',
                'label_inventaris' => $request->label_inventaris ?? 'Ya',
            ]);

            return redirect()->route('surprise_audit.aset', $assignment->visit_id)
                ->with('success', 'Sampel Aset berhasil ditambahkan.');
        }

        $items = $request->input('items', []);

        foreach ($items as $detailId => $data) {
            $qtyReg = (int) ($data['qty_register'] ?? 0);
            $qtyFisik = (int) ($data['qty_fisik'] ?? 0);
            $lokasiReg = $data['lokasi_register'] ?? '';
            $lokasiFisik = $data['lokasi_fisik'] ?? '';
            $kondisi = $data['kondisi_fisik'] ?? 'Baik';
            $label = $data['label_inventaris'] ?? 'Ya';

            $selisihQty = $qtyFisik - $qtyReg;
            $hasException = ($selisihQty != 0) ||
                             ($lokasiReg !== $lokasiFisik) ||
                             ($kondisi !== 'Baik') ||
                             ($label === 'Tidak');

            $statusUji = $hasException ? 'EXCEPTION' : 'SESUAI';
            $exceptionAktif = $hasException ? 'Ya' : 'Tidak';

            Sop05KkaAsetDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'id_tag_aset' => $data['id_tag_aset'] ?? null,
                    'jenis_nama_aset' => $data['jenis_nama_aset'] ?? null,
                    'lokasi_register' => $lokasiReg,
                    'lokasi_fisik' => $lokasiFisik,
                    'qty_register' => $qtyReg,
                    'qty_fisik' => $qtyFisik,
                    'selisih_qty' => $selisihQty,
                    'kondisi_fisik' => $kondisi,
                    'label_inventaris' => $label,
                    'sebab' => $data['sebab'] ?? null,
                    'dampak' => $data['dampak'] ?? null,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'status_uji' => $statusUji,
                    'validasi' => $hasException ? 'ANALISIS BELUM LENGKAP' : 'VALID',
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.aset', $assignment->visit_id)
            ->with('success', 'Data KKA Inventarisasi Aset (WP-RA-05-I) berhasil diperbarui!');
    }

    // ==========================================
    // MODUL: KKA LINGKUNGAN DAN CCTV (WP-RA-05-J)
    // Sheet Master Excel: KKA_Lingkungan
    // ==========================================

    public function showLingkungan($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        // Data Bawaan KKA Lingkungan (10 Kriteria Standar SOP 05 Master Excel)
        $defaultKriteria = [
            ['kategori' => 'Kelayakan', 'area' => 'Area Layanan Nasabah', 'kriteria' => 'Area aman, layak digunakan, akses dan fasilitas utama berfungsi', 'playback' => 'N.A.'],
            ['kategori' => 'Kelayakan', 'area' => 'Area Kerja Teller/Back Office', 'kriteria' => 'Tata ruang, akses, penerangan, dan sarana kerja layak', 'playback' => 'N.A.'],
            ['kategori' => 'Kelayakan', 'area' => 'Khasanah/Ruang Terbatas', 'kriteria' => 'Akses terbatas, pintu/pengamanan, dan kondisi ruang layak', 'playback' => 'N.A.'],
            ['kategori' => 'Kebersihan', 'area' => 'Area Layanan Nasabah', 'kriteria' => 'Lantai, meja, kursi, dan fasilitas nasabah bersih dan tertata', 'playback' => 'N.A.'],
            ['kategori' => 'Kebersihan', 'area' => 'Area Kerja dan Penyimpanan', 'kriteria' => 'Area kerja, arsip, dan penyimpanan bersih serta tidak menghambat akses', 'playback' => 'N.A.'],
            ['kategori' => 'Kebersihan', 'area' => 'ATM dan Perimeter', 'kriteria' => 'Area ATM, akses masuk, dan perimeter bersih serta terawat', 'playback' => 'N.A.'],
            ['kategori' => 'CCTV', 'area' => 'Area Layanan Nasabah', 'kriteria' => 'Kamera aktif, sudut pandang memadai, waktu sesuai, dan playback dapat dilakukan', 'playback' => 'Aktif/Terbaca'],
            ['kategori' => 'CCTV', 'area' => 'Teller/Kas/Khasanah', 'kriteria' => 'Area kritis tercakup tanpa blind spot material dan rekaman dapat diputar', 'playback' => 'Aktif/Terbaca'],
            ['kategori' => 'CCTV', 'area' => 'ATM/Perimeter', 'kriteria' => 'Kamera aktif, objek utama terlihat, dan kualitas rekaman memadai', 'playback' => 'Aktif/Terbaca'],
            ['kategori' => 'CCTV', 'area' => 'NVR/DVR/Monitoring', 'kriteria' => 'Penyimpanan, tanggal/jam, akses, retensi, dan playback berfungsi', 'playback' => 'Berfungsi/Akses Sesuai'],
        ];

        foreach ($defaultKriteria as $item) {
            Sop05KkaLingkunganDetail::firstOrCreate([
                'surprise_audit_assignment_id' => $assignment->id,
                'kategori' => $item['kategori'],
                'area_perangkat' => $item['area'],
            ], [
                'kriteria_unik' => $item['kriteria'],
                'hasil_uji_playback' => $item['playback'],
            ]);
        }

        $lingkunganData = Sop05KkaLingkunganDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        $totalItem = $lingkunganData->count();
        $exceptionAktifCount = $lingkunganData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.lingkungan', compact(
            'assignment',
            'lingkunganData',
            'totalItem',
            'exceptionAktifCount'
        ));
    }

    public function storeLingkungan(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $items = $request->input('items', []);

        foreach ($items as $detailId => $data) {
            $observasi = $data['hasil_observasi_ra'] ?? '';
            $playback = $data['hasil_uji_playback'] ?? 'N.A.';
            $kesimpulan = $data['kesimpulan_uji'] ?? 'Sesuai';
            $sebab = $data['sebab'] ?? '';
            $dampak = $data['dampak'] ?? '';

            // Rumus Logika Master Excel KKA_Lingkungan
            $isBelumLengkap = empty(trim($observasi)) || (empty(trim($playback)) && empty(trim($kesimpulan)));
            
            if ($isBelumLengkap) {
                $validasi = 'BELUM LENGKAP';
            } elseif ($kesimpulan === 'Tidak Sesuai' && (empty(trim($sebab)) || empty(trim($dampak)))) {
                $validasi = 'ANALISIS BELUM LENGKAP';
            } else {
                $validasi = 'VALID';
            }

            $exceptionAktif = ($kesimpulan === 'Tidak Sesuai') ? 'Ya' : 'Tidak';

            Sop05KkaLingkunganDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'hasil_observasi_ra' => $observasi,
                    'hasil_uji_playback' => $playback,
                    'kesimpulan_uji' => $kesimpulan,
                    'sebab' => $sebab,
                    'dampak' => $dampak,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'status_uji' => $kesimpulan === 'Tidak Sesuai' ? 'EXCEPTION' : 'SESUAI',
                    'validasi' => $validasi,
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.lingkungan', $assignment->visit_id)
            ->with('success', 'Data KKA Lingkungan dan CCTV (WP-RA-05-J) berhasil diperbarui!');
    }

    // ==========================================
    // MODUL: KKA DOKUMEN BERHARGA (WP-RA-05-G)
    // ==========================================

    public function showDokumenBerharga($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $dokumenData = Sop05KkaDokumenBerhargaDetail::where('surprise_audit_assignment_id', $assignment->id)->get();

        if ($dokumenData->isEmpty()) {
            $defaultItems = [
                ['jenis_dokumen' => 'Cek', 'harga_per_lembar' => 5000],
                ['jenis_dokumen' => 'Bilyet Giro', 'harga_per_lembar' => 5000],
                ['jenis_dokumen' => 'Bilyet Deposito', 'harga_per_lembar' => 0],
            ];
            foreach ($defaultItems as $item) {
                Sop05KkaDokumenBerhargaDetail::create([
                    'surprise_audit_assignment_id' => $assignment->id,
                    'jenis_dokumen' => $item['jenis_dokumen'],
                    'persediaan_sistem_qty' => 0,
                    'catatan_petugas_qty' => 0,
                    'hasil_fisik_qty' => 0,
                    'harga_per_lembar' => $item['harga_per_lembar'],
                    'nilai_sistem' => 0,
                    'nilai_fisik' => 0,
                    'selisih_sistem_catatan' => 0,
                    'selisih_sistem_fisik' => 0,
                    'selisih_catatan_fisik' => 0,
                    'selisih_nilai' => 0,
                    'status_uji' => 'SESUAI',
                    'validasi' => 'VALID',
                    'exception_aktif' => 'Tidak',
                ]);
            }
            $dokumenData = Sop05KkaDokumenBerhargaDetail::where('surprise_audit_assignment_id', $assignment->id)->get();
        }

        $qtySistemTotal = $dokumenData->sum('persediaan_sistem_qty');
        $qtyCatatanTotal = $dokumenData->sum('catatan_petugas_qty');
        $qtyFisikTotal = $dokumenData->sum('hasil_fisik_qty');
        $exceptionAktifCount = $dokumenData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.dokumen-berharga', compact(
            'assignment',
            'dokumenData',
            'qtySistemTotal',
            'qtyCatatanTotal',
            'qtyFisikTotal',
            'exceptionAktifCount'
        ));
    }

    public function storeDokumenBerharga(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        foreach ($request->items ?? [] as $detailId => $data) {
            $sistemQty = (int)($data['persediaan_sistem_qty'] ?? 0);
            $catatanQty = (int)($data['catatan_petugas_qty'] ?? 0);
            $fisikQty = (int)($data['hasil_fisik_qty'] ?? 0);
            $harga = (float)($data['harga_per_lembar'] ?? 0);

            $nilaiSistem = $sistemQty * $harga;
            $nilaiFisik = $fisikQty * $harga;
            $selisihSistemCatatan = $sistemQty - $catatanQty;
            $selisihSistemFisik = $sistemQty - $fisikQty;
            $selisihCatatanFisik = $catatanQty - $fisikQty;
            $selisihNilai = $nilaiSistem - $nilaiFisik;

            $statusUji = ($selisihSistemFisik == 0 && empty($data['nomor_hilang_lompat'])) ? 'SESUAI' : 'EXCEPTION';
            $exceptionAktif = ($statusUji === 'EXCEPTION') ? 'Ya' : 'Tidak';

            $validasi = 'VALID';
            if ($exceptionAktif === 'Ya' && (empty($data['sebab']) || empty($data['dampak']))) {
                $validasi = 'ANALISIS BELUM LENGKAP';
            }

            Sop05KkaDokumenBerhargaDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'persediaan_sistem_qty' => $sistemQty,
                    'catatan_petugas_qty' => $catatanQty,
                    'hasil_fisik_qty' => $fisikQty,
                    'harga_per_lembar' => $harga,
                    'nilai_sistem' => $nilaiSistem,
                    'nilai_fisik' => $nilaiFisik,
                    'selisih_sistem_catatan' => $selisihSistemCatatan,
                    'selisih_sistem_fisik' => $selisihSistemFisik,
                    'selisih_catatan_fisik' => $selisihCatatanFisik,
                    'selisih_nilai' => $selisihNilai,
                    'range_nomor_seri' => $data['range_nomor_seri'] ?? null,
                    'nomor_hilang_lompat' => $data['nomor_hilang_lompat'] ?? null,
                    'status_uji' => $statusUji,
                    'sebab' => $data['sebab'] ?? null,
                    'dampak' => $data['dampak'] ?? null,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'validasi' => $validasi,
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.dokumen_berharga', $assignment->visit_id)
            ->with('success', 'Data KKA Dokumen Berharga (WP-RA-05-G) berhasil diperbarui!');
    }
}
