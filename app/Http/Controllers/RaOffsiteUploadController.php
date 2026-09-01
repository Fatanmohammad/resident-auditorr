<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WpOffsite;
use App\Models\DumpTransaksiCbs;
use App\Models\DumpDpkApuppt;
use App\Models\DumpKredit;
use App\Models\DumpBiayaBeban;
use App\Models\DumpPengaduan;
use App\Services\OffsiteGenerationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RaOffsiteUploadController extends Controller
{
    public function __construct(private OffsiteGenerationService $generation) {}

    public function index()
    {
        $user = Auth::user();
        $accessibleIds = $user->cabangIdYangDapatDiakses();

        // Dropdown pakai units (bukan cabangs) karena WpOffsite pakai unit_id
        $units = $accessibleIds === null
            ? \App\Models\Unit::where('is_active', true)->orderBy('unit_name')->get()
            : \App\Models\Unit::where('is_active', true)
                ->whereIn('cabang_id', $accessibleIds)
                ->orderBy('unit_name')
                ->get();

        return view('ra-offsite.upload', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'             => 'required|exists:units,id',
            'domain_type'         => 'required|in:cbs,dpk,kredit,biaya,pengaduan',
            'file_csv'            => 'required|file|mimes:csv,txt|max:20480',
            'tanggal_data_manual' => 'nullable|required_if:domain_type,kredit,dpk|date',
        ]);

        $file         = $request->file('file_csv');
        $domainType   = strtoupper($request->domain_type);
        $cabangId     = $request->unit_id;
        $tanggalManual = $request->tanggal_data_manual;
        $user         = Auth::user();

        // Baca CSV sekali — array_combine header+row supaya asosiatif
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 4096, ',');
        if (!$header) {
            return back()->withErrors(['file_csv' => 'File CSV kosong atau header tidak terbaca.']);
        }
        // Bersihkan BOM UTF-8 jika ada
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");

        $rows = [];
        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            if (empty(array_filter($row))) continue;
            $rowPadded = array_pad(array_slice($row, 0, count($header)), count($header), null);
            $rows[] = array_combine($header, $rowPadded);
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->withErrors(['file_csv' => 'File CSV tidak memiliki baris data.']);
        }

        DB::beginTransaction();
        try {
            // Buat atau ambil WP untuk unit & periode ini
            $sampleDate = $tanggalManual ?? now()->format('Y-m-d');
            $dt         = Carbon::parse($sampleDate);
            $unit       = \App\Models\Unit::find($cabangId);
            $kodeUnit   = $unit->unit_code ?? str_pad($cabangId, 3, '0', STR_PAD_LEFT);
            $kodeWp     = 'WP-OFF-' . $kodeUnit . '-' . $dt->format('Ym');

            $wp = WpOffsite::firstOrCreate(
                [
                    'unit_id'      => $cabangId,
                    'periode_mulai' => $dt->copy()->firstOfMonth()->format('Y-m-d'),
                ],
                [
                    'kode_wp'               => $kodeWp,
                    'kode_unit'             => $kodeUnit,
                    'nama_unit'             => $unit->unit_name ?? 'Unit Kerja',
                    'jenis_unit'            => $unit->unit_type ?? null,
                    'periode_selesai'       => $dt->copy()->endOfMonth()->format('Y-m-d'),
                    'ra_pelaksana_id'       => $user->id,
                    'reviewer_bagian_ra_id' => null,
                    'status_wp'             => 'Draft',
                ]
            );

            // Hapus data dump lama domain ini untuk WP yang sama (supaya tidak duplikat)
            $this->truncateDump($domainType, $wp->id);

            // Insert ke tabel dump_* yang sesuai
            $insertedCount = 0;
            foreach ($rows as $rowAssoc) {
                $this->insertDump($domainType, $rowAssoc, $wp, $kodeUnit, $tanggalManual);
                $insertedCount++;
            }

            // Catat aktivitas upload
            DB::table('kka_activity_logs')->insert([
                'user_id'             => $user->id,
                'user_name'           => $user->name,
                'kode_unit'           => $kodeUnit,
                'case_id'             => (string) $wp->id,
                'sheet_name'          => $domainType,
                'action'              => 'UPLOAD',
                'deskripsi_perubahan' => "Upload {$insertedCount} baris domain {$domainType} dari file {$file->getClientOriginalName()} untuk unit {$kodeUnit}.",
                'status_review'       => 'Belum',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::commit();

            // Jalankan pipeline deteksi via OffsiteGenerationService (satu-satunya engine)
            $hasil = $this->generation->generate($wp);

            return redirect()->back()->with(
                'success',
                "Berhasil! {$insertedCount} baris domain {$domainType} diunggah. " .
                "{$hasil['total_diproses']} baris diproses, {$hasil['total_masuk_kka']} masuk KKA."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file_csv' => 'Gagal memproses file: ' . $e->getMessage()]);
        }
    }

    private function truncateDump(string $domainType, int $wpId): void
    {
        $map = [
            'CBS'       => 'dump_transaksi_cbs',
            'DPK'       => 'dump_dpk_apuppt',
            'KREDIT'    => 'dump_kredit',
            'BIAYA'     => 'dump_biaya_beban',
            'PENGADUAN' => 'dump_pengaduan',
        ];

        if (isset($map[$domainType])) {
            DB::table($map[$domainType])->where('wp_offsite_id', $wpId)->delete();
        }
    }

    private function insertDump(string $domainType, array $d, WpOffsite $wp, string $kodeUnit, ?string $tanggalManual): void
    {
        $tglManual = $tanggalManual ?? now()->format('Y-m-d');

        match ($domainType) {
            'CBS' => DumpTransaksiCbs::create([
                'wp_offsite_id'    => $wp->id,
                'kode_unit'        => $d['KD_CAB'] ?? $kodeUnit,
                'tanggal_data'     => $this->parseDate($d['TGL_TX'] ?? null) ?? $tglManual,
                'no_referensi'     => $d['NO_ARSIP'] ?? null,
                'kode_transaksi'   => $d['KD_TX'] ?? null,
                'nama_transaksi'   => $d['KET_TX'] ?? null,
                'user_maker'       => $d['KD_USER'] ?? $d['USER_MAKER'] ?? null,
                'nama_user'        => $d['NAMA_USER'] ?? null,
                'nominal'          => $this->parseNominal($d['JUMLAH_TX'] ?? null),
                'd_k'              => $d['DB_KR'] ?? $d['D_K'] ?? $d['DK'] ?? null,
                'deskripsi_narasi' => $d['KET_TX'] ?? null,
            ]),

            'DPK' => DumpDpkApuppt::create([
                'wp_offsite_id'   => $wp->id,
                'kode_unit'       => $d['KD_CAB'] ?? $kodeUnit,
                'tanggal_data'    => $tglManual,
                'produk'          => $d['PRODNM'] ?? $d['KD_PRODUK'] ?? null,
                'cif_nasabah'     => $d['NO_NSB'] ?? $d['NO_NASABAH'] ?? null,
                'no_rekening'     => $d['NO_REK'] ?? null,
                'nama_nasabah'    => $d['NAMA_SINGKAT'] ?? null,
                'tanggal_buka'    => $this->parseDate($d['TGL_BUKA_REK'] ?? null),
                'jatuh_tempo'     => $this->parseDate($d['TGL_JT'] ?? null),
                'saldo_akhir'     => $this->parseNominal($d['SALDO_AKHIR'] ?? null),
                'status_rekening' => $d['KD_STATUS'] ?? $d['ACCSTS'] ?? null,
            ]),

            'KREDIT' => DumpKredit::create([
                'wp_offsite_id'       => $wp->id,
                'kode_unit'           => $d['KD_CAB'] ?? $kodeUnit,
                'tanggal_data'        => $tglManual,
                'cif_nasabah'         => $d['NO_NSB'] ?? null,
                'no_rekening_kredit'  => $d['NO_REK'] ?? null,
                'no_akad'             => $d['NO_AKD'] ?? null,
                'nama_debitur'        => $d['NAMA_SINGKAT'] ?? null,
                'produk_kredit'       => $d['PRD_NAME'] ?? $d['GL_PRD_NAME'] ?? null,
                'jenis_kredit'        => $d['JENIS_KREDIT'] ?? null,
                'tanggal_realisasi'   => $this->parseDate($d['TGLMULAI'] ?? $d['TGL_MULAI'] ?? null),
                'plafon'              => $this->parseNominal($d['PLAFOND'] ?? null),
                'baki_debet'          => $this->parseNominal($d['SALDO_AKHIR'] ?? null),
                'kolektibilitas'      => $d['KOLEKTIBILITY'] ?? null,
                'tunggakan_pokok'     => $this->parseNominal($d['TUNGG_POKOK'] ?? null),
                'tunggakan_bunga'     => $this->parseNominal($d['TUNGG_BUNGA'] ?? null),
                'total_agunan'        => $this->parseNominal($d['TOTAGUNAN'] ?? null),
            ]),

            'BIAYA' => DumpBiayaBeban::create([
                'wp_offsite_id'        => $wp->id,
                'kode_unit'            => $d['KD_CAB'] ?? $kodeUnit,
                'tanggal_data'         => $this->parseDate($d['TGL_TX'] ?? null) ?? $tglManual,
                'no_rekening'          => $d['NO_REK'] ?? null,
                'no_arsip'             => $d['NO_ARSIP'] ?? null,
                'kode_transaksi'       => $d['KD_TX'] ?? null,
                'keterangan_transaksi' => $d['KET_TX'] ?? $d['URAIAN'] ?? null,
                'd_k'                  => $d['D_K'] ?? $d['DK'] ?? null,
                'nominal'              => $this->parseNominal($d['JUMLAH_TX'] ?? null),
                'user_input'           => $d['KD_USER'] ?? $d['USER_MAKER'] ?? null,
                'auto_system_flag'     => ($d['ISAUTOTX'] ?? '0') == '1',
            ]),

            'PENGADUAN' => DumpPengaduan::create([
                'wp_offsite_id'      => $wp->id,
                'kode_unit'          => $d['KD_CAB'] ?? $kodeUnit,
                'tanggal_data'       => $tglManual,
                'no_tiket'           => $d['NO_TIKET'] ?? $d['no_tiket'] ?? null,
                'no_nasabah'         => $d['NO_NSB'] ?? $d['NO_CIF'] ?? $d['CIF'] ?? null,
                'no_rekening_nasabah'=> $d['NO_REK'] ?? $d['NO_REKENING'] ?? null,
                'nama_nasabah'       => $d['NAMA_NSB'] ?? $d['NAMA_NASABAH'] ?? null,
                'tanggal_pengaduan'  => $this->parseDate($d['TGL_TERIMA'] ?? $d['TGL_PENGADUAN'] ?? null),
                'jenis_pengaduan'    => $d['JENIS_PENGADUAN'] ?? $d['jenis_pengaduan'] ?? null,
                'isi_pengaduan'      => $d['ISI_PENGADUAN'] ?? $d['isi_pengaduan'] ?? null,
                'status_pengaduan'   => $d['STATUS'] ?? $d['STATUS_PENGADUAN'] ?? null,
                'tanggal_selesai'    => $this->parseDate($d['TGL_SELESAI'] ?? null),
                'nominal_kerugian'   => $this->parseNominal($d['NOMINAL_KERUGIAN'] ?? null),
                'bukti_penyelesaian' => $d['BUKTI_PENYELESAIAN'] ?? null,
                'catatan_tl_cabang'  => $d['CATATAN_TL_CABANG'] ?? $d['CATATAN_TL'] ?? null,
            ]),

            default => null,
        };
    }

    private function parseDate(?string $val): ?string
    {
        if (empty($val)) return null;
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function parseNominal(?string $val): float
    {
        if (empty($val)) return 0;
        // Hapus titik ribuan dan ganti koma desimal
        return (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^\d,.]/', '', $val));
    }
}
