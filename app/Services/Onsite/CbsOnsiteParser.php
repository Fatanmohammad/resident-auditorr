<?php

namespace App\Services\Onsite;

use App\Models\Onsite\OnsiteVisit;
use App\Models\Onsite\OnsitePopulation;
use App\Models\Onsite\OnsiteKkaFinding;
use App\Models\Offsite\MasterParameter;
use Carbon\Carbon;

class CbsOnsiteParser
{
    // Prioritas sampling
    const PRIO_MANDATORY  = 1;
    const PRIO_CERTAINTY  = 2;
    const PRIO_TARGETED   = 3;
    const PRIO_INITIAL    = 5;

    // Jam kerja normal teller (06:00 - 17:00)
    const JAM_MULAI_NORMAL = '06:00:00';
    const JAM_SELESAI_NORMAL = '17:00:00';

    public function parse(string $filePath, OnsiteVisit $visit): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File CBS tidak ditemukan di folder staging.');
        }

        // Ambil threshold Certainty dari master_parameters (default 100 juta)
        $thresholdCertainty = (float) (MasterParameter::where('kategori', 'ONSITE_CERTAINTY_THRESHOLD')->value('nilai_batas') ?? 100000000);

        $file = fopen($filePath, 'r');

        // Auto-detect delimiter dari baris pertama
        $firstLine = fgets($file);
        $delimiter = str_contains($firstLine, "\t") ? "\t" : ',';
        rewind($file);

        $header = fgetcsv($file, 0, $delimiter);

        // Strip BOM UTF-8 dan whitespace dari kolom pertama header
        $firstCol = strtoupper(trim(str_replace("\xEF\xBB\xBF", '', $header[0] ?? '')));
        if (!$header || $firstCol !== 'KD_TX') {
            fclose($file);
            throw new \Exception("File bukan format CBS yang valid. Header pertama harus KD_TX, ditemukan: '{$firstCol}'");
        }

        // Hapus data lama untuk kunjungan ini (delete-before-insert)
        OnsitePopulation::where('onsite_visit_id', $visit->id)->delete();
        OnsiteKkaFinding::where('onsite_visit_id', $visit->id)->delete();

        $rows = [];
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if (count($row) < 11) continue;

            $tglRaw = trim($row[8] ?? '');
            $tglTx  = $this->parseDate($tglRaw);
            if (!$tglTx) continue;

            $rows[] = [
                'kd_tx'     => trim($row[0] ?? ''),
                'kd_cab'    => trim($row[1] ?? ''),
                'no_rek'    => trim($row[2] ?? ''),
                'no_arsip'  => trim($row[3] ?? ''),
                'ket_tx'    => trim($row[4] ?? ''),
                'db_kr'     => strtoupper(trim($row[5] ?? '')),
                'txtype'    => trim($row[6] ?? ''),
                'kd_user'   => trim($row[7] ?? ''),
                'tgl_tx'    => $tglTx,
                'time_stamp'=> trim($row[9] ?? ''),
                'jumlah_tx' => (float) str_replace(',', '', trim($row[10] ?? '0')),
            ];
        }
        fclose($file);

        if (empty($rows)) {
            throw new \Exception('File CBS tidak mengandung data transaksi yang valid.');
        }

        // Hitung Stable Score per kd_user per tgl_tx
        $stableScores = $this->hitungStableScore($rows);

        // Bangun populasi_key dan simpan ke DB
        $populasiInsert = [];
        foreach ($rows as $r) {
            $populasiInsert[] = [
                'onsite_visit_id' => $visit->id,
                'kode_unit'       => $visit->kode_unit,
                'kd_tx'           => $r['kd_tx'],
                'kd_cab'          => $r['kd_cab'],
                'no_rek'          => $r['no_rek'],
                'no_arsip'        => $r['no_arsip'],
                'ket_tx'          => $r['ket_tx'],
                'db_kr'           => $r['db_kr'],
                'txtype'          => $r['txtype'],
                'kd_user'         => $r['kd_user'],
                'tgl_tx'          => $r['tgl_tx'],
                'time_stamp'      => $r['time_stamp'],
                'jumlah_tx'       => $r['jumlah_tx'],
                'populasi_key'    => $r['tgl_tx'] . '|' . $r['no_arsip'],
                'stable_score'    => $stableScores[$r['kd_user']][$r['tgl_tx']] ?? 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        foreach (array_chunk($populasiInsert, 1000) as $chunk) {
            OnsitePopulation::insert($chunk);
        }

        $totalPopulasi = count($populasiInsert);

        // Update total_populasi di visit
        $visit->update(['total_populasi' => $totalPopulasi]);

        // Jalankan sampling
        $totalSampel = $this->runSampling($visit, $thresholdCertainty);

        return [
            'total_populasi' => $totalPopulasi,
            'total_sampel'   => $totalSampel,
        ];
    }

    /**
     * Hitung Stable Score = jumlah transaksi unik (per kd_user per tgl_tx)
     * Stable Score = count distinct no_arsip per user per hari
     */
    private function hitungStableScore(array $rows): array
    {
        $scores = [];
        foreach ($rows as $r) {
            $user = $r['kd_user'];
            $tgl  = $r['tgl_tx'];
            if (!isset($scores[$user][$tgl])) {
                $scores[$user][$tgl] = [];
            }
            $scores[$user][$tgl][$r['no_arsip']] = true;
        }

        $result = [];
        foreach ($scores as $user => $tglMap) {
            foreach ($tglMap as $tgl => $arsipMap) {
                $result[$user][$tgl] = count($arsipMap);
            }
        }
        return $result;
    }

    /**
     * Jalankan 4 jenis sampling, ambil baris D (debit) saja sebagai representasi kasus
     */
    private function runSampling(OnsiteVisit $visit, float $threshold): int
    {
        // Ambil semua populasi untuk kunjungan ini, hanya baris D
        $populations = OnsitePopulation::where('onsite_visit_id', $visit->id)
            ->where('db_kr', 'D')
            ->get()
            ->keyBy('populasi_key');

        // Kumpulkan semua populasi_key yang sudah dipilih (hindari duplikat)
        $dipilih = [];
        $kkaInsert = [];

        // =============================================
        // 1. MANDATORY — aturan kritis wajib dipilih
        // =============================================
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            $alasan = $this->cekMandatory($pop);
            if ($alasan) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Mandatory', self::PRIO_MANDATORY, $alasan);
            }
        }

        // =============================================
        // 2. CERTAINTY — nominal >= threshold
        // =============================================
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            if ($pop->jumlah_tx >= $threshold) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Certainty', self::PRIO_CERTAINTY,
                    'Nominal >= Rp ' . number_format($threshold, 0, ',', '.'));
            }
        }

        // =============================================
        // 3. TARGETED — anomali (di luar jam, pegawai terkait)
        // =============================================
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            $alasan = $this->cekTargeted($pop);
            if ($alasan) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Targeted', self::PRIO_TARGETED, $alasan);
            }
        }

        // =============================================
        // 4. INITIAL — sisa kuota minimum SOP 02
        // =============================================
        $kuotaInitial = $this->hitungKuotaInitial($visit);
        $sisaKuota    = max(0, $kuotaInitial - count($dipilih));

        if ($sisaKuota > 0) {
            // Pilih secara acak dari yang belum dipilih
            $belumDipilih = $populations->filter(fn($p) => !isset($dipilih[$p->populasi_key]));
            $sample = $belumDipilih->random(min($sisaKuota, $belumDipilih->count()));

            foreach ($sample as $pop) {
                $key = $pop->populasi_key;
                if (isset($dipilih[$key])) continue;
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Initial', self::PRIO_INITIAL, 'Kuota Minimum SOP 02');
            }
        }

        // Insert semua KKA sekaligus
        foreach (array_chunk($kkaInsert, 500) as $chunk) {
            OnsiteKkaFinding::insert($chunk);
        }

        $totalSampel = count($kkaInsert);
        $visit->update(['total_sampel' => $totalSampel]);

        return $totalSampel;
    }

    private function cekMandatory(OnsitePopulation $pop): ?string
    {
        $ket = strtoupper($pop->ket_tx ?? '');

        if (preg_match('/(REV-|REVERSAL|PEMBATALAN)/i', $ket))
            return 'Reversal / Pembatalan';

        if (preg_match('/(SELISIH KAS|KEKURANGAN KAS|KELEBIHAN KAS)/i', $ket))
            return 'Selisih Kas';

        if (preg_match('/(INTERN|AKUN INTERN|PENAMPUNG|SUSPENSE)/i', $ket))
            return 'Internal Account';

        if (preg_match('/(TUNAI|CASH|SETOR TUNAI|TARIK TUNAI)/i', $ket) && $pop->jumlah_tx >= 50000000)
            return 'Tunai Besar';

        if (preg_match('/(BELUM TERPETAKAN|UNKNOWN|LAIN-LAIN)/i', $ket))
            return 'Kode Belum Terpetakan';

        return null;
    }

    private function cekTargeted(OnsitePopulation $pop): ?string
    {
        $jam = $pop->time_stamp ?? '';

        // Transaksi di luar jam kerja normal
        if ($jam && ($jam < self::JAM_MULAI_NORMAL || $jam > self::JAM_SELESAI_NORMAL)) {
            return 'Transaksi Di Luar Jam Kerja (' . $jam . ')';
        }

        return null;
    }

    /**
     * Kuota Initial = jumlah HARI KERJA kunjungan × 5 (default minimum per hari)
     * Mengabaikan hari Sabtu dan Minggu (weekend)
     */
    private function hitungKuotaInitial(OnsiteVisit $visit): int
    {
        $perHari = (int) (MasterParameter::where('kategori', 'ONSITE_INITIAL_PER_HARI')->value('nilai_batas') ?? 5);

        $start = Carbon::parse($visit->tanggal_mulai)->startOfDay();
        $end   = Carbon::parse($visit->tanggal_selesai)->startOfDay();

        // Hitung hanya hari kerja (Senin - Jumat) di rentang tanggal kunjungan
        $jumlahHariKerja = $start->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end) + 1;

        return $jumlahHariKerja * $perHari;
    }

    private function buildKka(OnsiteVisit $visit, OnsitePopulation $pop, string $jenis, int $prioritas, string $alasan): array
    {
        $tgl = str_replace('-', '', $pop->tgl_tx);
        return [
            'onsite_visit_id'       => $visit->id,
            'onsite_population_id'  => $pop->id,
            'kode_unit'             => $visit->kode_unit,
            'sample_id'             => "SMP-ONS-TLR-{$tgl}-{$pop->populasi_key}",
            'tgl_tx'                => $pop->tgl_tx,
            'no_arsip'              => $pop->no_arsip,
            'ket_tx'                => $pop->ket_tx,
            'jumlah_tx'             => $pop->jumlah_tx,
            'kd_user'               => $pop->kd_user,
            'stable_score'          => $pop->stable_score,
            'jenis_sampling'        => $jenis,
            'prioritas'             => $prioritas,
            'alasan_sampling'       => $alasan,
            'status_review'         => 'Belum Direview',
            'created_at'            => now(),
            'updated_at'            => now(),
        ];
    }

    private function parseDate(string $raw): ?string
    {
        if (empty($raw)) return null;
        $ts = strtotime($raw);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}