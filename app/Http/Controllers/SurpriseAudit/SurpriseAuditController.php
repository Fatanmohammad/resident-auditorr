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
        $assignments = SurpriseAuditAssignment::latest()->get();
        return view('surprise-audit.index', compact('assignments'));
    }

    public function create()
    {
        return view('surprise-audit.create');
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'kode_unit' => 'required',
            'nama_unit' => 'required',
            'nomor_assignment' => 'required',
            'tanggal_rencana' => 'required',
            'resident_auditor' => 'required',
            'reviewer' => 'required',
            'ruang_lingkup' => 'required|array',
        ]);

        $tanggalFormat = date('Ymd', strtotime($request->tanggal_rencana));
        $visitId = 'SA-' . $tanggalFormat . '-' . $request->kode_unit;

        SurpriseAuditAssignment::create([
            'visit_id' => $visitId,
            'kode_unit' => $request->kode_unit,
            'nama_unit' => $request->nama_unit,
            'tanggal_rencana' => $request->tanggal_rencana,
            'tanggal_mulai_aktual' => $request->tanggal_mulai_aktual ?? null,
            'tanggal_selesai_aktual' => $request->tanggal_selesai_aktual ?? null,
            'resident_auditor' => $request->resident_auditor,
            'reviewer' => $request->reviewer,
            'dasar_penugasan' => 'Informasi Khusus',
            'nomor_assignment' => $request->nomor_assignment,
            'pemberi_tugas' => 'Pimpinan SKAI',
            'tujuan' => $request->tujuan ?? 'Memastikan kesesuaian saldo buku dengan kas fisik khasanah dan kas ATM secara mendadak.',
            'klasifikasi_kerahasiaan' => 'Rahasia - Need to Know',
            'ruang_lingkup_area' => $request->ruang_lingkup,
            'status_assignment' => 'SIAP DILAKSANAKAN',
        ]);

        return redirect()->route('surprise_audit.index')->with('success', 'Penugasan Surprise Audit berhasil dibuat.');
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

        return view('surprise-audit.show', compact('assignment', 'controlData'));
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
        ]);

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $totalUle = (($request->ule_100k ?? 0) * 100000) +
                    (($request->ule_50k ?? 0) * 50000) +
                    (($request->ule_20k ?? 0) * 20000) +
                    (($request->ule_10k ?? 0) * 10000) +
                    (($request->ule_5k ?? 0) * 5000) +
                    (($request->ule_2k ?? 0) * 2000) +
                    (($request->ule_1k ?? 0) * 1000);

        $totalUtle = (($request->utle_100k ?? 0) * 100000) +
                     (($request->utle_50k ?? 0) * 50000) +
                     (($request->utle_20k ?? 0) * 20000) +
                     (($request->utle_10k ?? 0) * 10000) +
                     (($request->utle_5k ?? 0) * 5000) +
                     (($request->utle_2k ?? 0) * 2000) +
                     (($request->utle_1k ?? 0) * 1000);

        $totalLogam = (($request->logam_1000 ?? 0) * 1000) +
                      (($request->logam_500 ?? 0) * 500) +
                      (($request->logam_200 ?? 0) * 200) +
                      (($request->logam_100 ?? 0) * 100);

        $nilaiFisik = $totalUle + $totalUtle + $totalLogam;
        $nilaiSistem = $request->nilai_sistem;
        $selisih = $nilaiFisik - $nilaiSistem;
        $exceptionAktif = $selisih != 0 ? 'Ya' : 'Tidak';

        $assignment->kasDetails()->create([
            'objek_pemeriksaan' => $request->objek_pemeriksaan,
            'nilai_sistem' => $nilaiSistem,
            'ule_100k' => $request->ule_100k ?? 0,
            'ule_50k'  => $request->ule_50k ?? 0,
            'ule_20k'  => $request->ule_20k ?? 0,
            'ule_10k'  => $request->ule_10k ?? 0,
            'ule_5k'   => $request->ule_5k ?? 0,
            'ule_2k'   => $request->ule_2k ?? 0,
            'ule_1k'   => $request->ule_1k ?? 0,
            'utle_100k' => $request->utle_100k ?? 0,
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
            'total_ule' => $totalUle,
            'total_utle' => $totalUtle,
            'total_logam' => $totalLogam,
            'nilai_fisik' => $nilaiFisik,
            'selisih' => $selisih,
            'status_uji' => 'SIAP DIREVIU',
            'catatan_pemeriksa' => $request->catatan_pemeriksa,
            'exception_aktif' => $exceptionAktif,
        ]);

        return redirect()->route('surprise_audit.kas', $assignment->visit_id)
            ->with('success', 'Data Opname Rincian Pecahan Kas berhasil disimpan!');
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
        ]);

        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $k1_nilai = ($request->kaset1_pecahan ?? 100000) * ($request->kaset1_lembar ?? 0);
        $k2_nilai = ($request->kaset2_pecahan ?? 50000) * ($request->kaset2_lembar ?? 0);
        $k3_nilai = ($request->kaset3_pecahan ?? 100000) * ($request->kaset3_lembar ?? 0);
        $k4_nilai = ($request->kaset4_pecahan ?? 50000) * ($request->kaset4_lembar ?? 0);

        $hasilFisikTotal = $k1_nilai + $k2_nilai + $k3_nilai + $k4_nilai;
        $selisih = $hasilFisikTotal - $request->saldo_buku;
        $exceptionAktif = $selisih != 0 ? 'Ya' : 'Tidak';

        Sop05KkaAtmDetail::create([
            'surprise_audit_assignment_id' => $assignment->id,
            'area_kka' => 'atm',
            'objek_lokasi_atm' => $request->objek_lokasi_atm,
            'saldo_buku' => $request->saldo_buku,
            'kaset1_pecahan' => $request->kaset1_pecahan ?? 100000,
            'kaset1_lembar' => $request->kaset1_lembar ?? 0,
            'kaset1_nilai' => $k1_nilai,
            'kaset2_pecahan' => $request->kaset2_pecahan ?? 50000,
            'kaset2_lembar' => $request->kaset2_lembar ?? 0,
            'kaset2_nilai' => $k2_nilai,
            'kaset3_pecahan' => $request->kaset3_pecahan ?? 100000,
            'kaset3_lembar' => $request->kaset3_lembar ?? 0,
            'kaset3_nilai' => $k3_nilai,
            'kaset4_pecahan' => $request->kaset4_pecahan ?? 50000,
            'kaset4_lembar' => $request->kaset4_lembar ?? 0,
            'kaset4_nilai' => $k4_nilai,
            'hasil_fisik_total' => $hasilFisikTotal,
            'selisih' => $selisih,
            'status_uji' => $request->status_uji ?? 'LENGKAP',
            'sebab' => $request->sebab,
            'dampak' => $request->dampak,
            'referensi_bukti' => $request->referensi_bukti,
            'validasi' => 'LENGKAP',
            'exception_aktif' => $exceptionAktif,
        ]);

        return redirect()->route('surprise_audit.atm', $assignment->visit_id)
            ->with('success', 'Data Opname KKA Kas ATM berhasil disimpan.');
    }

    // ==========================================
    // MODUL: KKA KARTU ATM & PIN MAILER (WP-RA-05-E)
    // ==========================================

    public function showKartu($id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $jenisKartuTetap = ['Platinum', 'Gold', 'Silver'];
        foreach ($jenisKartuTetap as $jenis) {
            Sop05KkaKartuDetail::firstOrCreate([
                'surprise_audit_assignment_id' => $assignment->id,
                'jenis_kartu' => $jenis,
            ], [
                'lokasi_penyimpanan' => 'Brankas Utama',
                'harga_per_kartu' => 15000,
            ]);
        }

        $kartuData = Sop05KkaKartuDetail::where('surprise_audit_assignment_id', $assignment->id)
            ->orderByRaw("FIELD(jenis_kartu, 'Platinum', 'Gold', 'Silver')")
            ->get();

        $saldoBukuTotal = $kartuData->sum('saldo_persediaan_buku');
        $nilaiFisikTotal = $kartuData->sum('nilai_fisik_kartu');
        $selisihNilaiTotal = $nilaiFisikTotal - $saldoBukuTotal;
        $exceptionAktifCount = $kartuData->where('exception_aktif', 'Ya')->count();

        return view('surprise-audit.kartu', compact(
            'assignment',
            'kartuData',
            'saldoBukuTotal',
            'nilaiFisikTotal',
            'selisihNilaiTotal',
            'exceptionAktifCount'
        ));
    }

    public function storeKartu(Request $request, $id)
    {
        $assignment = SurpriseAuditAssignment::where('visit_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();

        $items = $request->input('items', []);

        foreach ($items as $detailId => $data) {
            $harga = (float) ($data['harga_per_kartu'] ?? 0);
            $saldoBuku = (float) ($data['saldo_persediaan_buku'] ?? 0);
            $fisikKartu = (int) ($data['jumlah_fisik_kartu'] ?? 0);
            $pinMailer = (int) ($data['jumlah_pin_mailer'] ?? 0);

            $qtyBuku = $harga > 0 ? floor($saldoBuku / $harga) : 0;
            $nilaiFisik = $fisikKartu * $harga;
            $selisihQtyBukuFisik = $fisikKartu - $qtyBuku;
            $selisihPinFisik = $pinMailer - $fisikKartu;
            $selisihNilai = $nilaiFisik - $saldoBuku;

            $isSesuai = ($qtyBuku == $fisikKartu) && 
                       ($fisikKartu == $pinMailer) && 
                       ($nilaiFisik == $saldoBuku);

            $statusRekonsiliasi = $isSesuai ? 'SESUAI' : 'TIDAK SESUAI';
            $exceptionAktif = $isSesuai ? 'Tidak' : 'Ya';

            Sop05KkaKartuDetail::where('id', $detailId)
                ->where('surprise_audit_assignment_id', $assignment->id)
                ->update([
                    'lokasi_penyimpanan' => $data['lokasi_penyimpanan'] ?? 'Brankas Utama',
                    'saldo_persediaan_buku' => $saldoBuku,
                    'harga_per_kartu' => $harga,
                    'jumlah_fisik_kartu' => $fisikKartu,
                    'jumlah_pin_mailer' => $pinMailer,
                    'qty_menurut_buku' => $qtyBuku,
                    'nilai_fisik_kartu' => $nilaiFisik,
                    'selisih_qty_buku_fisik' => $selisihQtyBukuFisik,
                    'selisih_pin_fisik' => $selisihPinFisik,
                    'selisih_nilai' => $selisihNilai,
                    'status_rekonsiliasi' => $statusRekonsiliasi,
                    'sebab' => $data['sebab'] ?? null,
                    'dampak' => $data['dampak'] ?? null,
                    'referensi_bukti' => $data['referensi_bukti'] ?? null,
                    'validasi' => 'LENGKAP',
                    'exception_aktif' => $exceptionAktif,
                ]);
        }

        return redirect()->route('surprise_audit.kartu', $assignment->visit_id)
            ->with('success', 'Data KKA Rekonsiliasi Kartu ATM & PIN Mailer berhasil diperbarui.');
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
}