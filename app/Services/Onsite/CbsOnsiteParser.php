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

        // Auto-detect delimiter dari baris pertama (koma, titik koma, atau tab)
        $firstLine = fgets($file);
        $delimiter = ',';
        if (str_contains($firstLine, "\t")) {
            $delimiter = "\t";
        } elseif (str_contains($firstLine, ';')) {
            $delimiter = ';';
        }
        rewind($file);

        $header = fgetcsv($file, 0, $delimiter);
        if (!$header) {
            fclose($file);
            throw new \Exception("File CSV kosong atau tidak memiliki header.");
        }

        // Bangun mapping kolom dinamis
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleanCol = strtoupper(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$colName)));
            $headerMap[$cleanCol] = $index;
        }

        // Cari letak kolom KD_TX (atau variasi ID_DATA_CBS, KDTX, ID_TX, dsb)
        $idxKdTx = $headerMap['KD_TX'] 
            ?? ($headerMap['KDTX'] 
            ?? ($headerMap['ID_DATA_CBS'] 
            ?? ($headerMap['ID_TX'] 
            ?? ($headerMap['KODE_TX'] 
            ?? ($headerMap['TX_ID'] 
            ?? ($headerMap['ID'] ?? null))))));

        if ($idxKdTx === null) {
            // Jika ID_DATA_CBS ada di kolom pertama dan KD_TX ada di sebelahnya
            if (isset($header[1]) && strtoupper(trim($header[1])) === 'KD_TX') {
                $idxKdTx = 1;
            } else {
                $firstCol = strtoupper(trim(str_replace("\xEF\xBB\xBF", '', $header[0] ?? '')));
                fclose($file);
                throw new \Exception("File bukan format CBS yang valid. Kolom 'KD_TX' atau 'ID_DATA_CBS' tidak ditemukan dalam header (header pertama: '{$firstCol}').");
            }
        }

        // Pemetaan kolom fleksibel berdasarkan nama header atau urutan relatif
        $idxKdCab = $headerMap['KD_CAB'] 
            ?? ($headerMap['KDCAB'] 
            ?? ($headerMap['KODE_CAB'] 
            ?? ($headerMap['KODE_CABANG'] 
            ?? ($headerMap['CABANG'] ?? ($idxKdTx + 1)))));

        $idxNoRek = $headerMap['NO_REK'] 
            ?? ($headerMap['NOREK'] 
            ?? ($headerMap['NO_REKENING'] 
            ?? ($headerMap['REKENING'] ?? ($idxKdTx + 2))));

        $idxNoArsip = $headerMap['NO_ARSIP'] 
            ?? ($headerMap['NOARSIP'] 
            ?? ($headerMap['NO_BUKTI'] 
            ?? ($headerMap['ARSIP'] ?? ($idxKdTx + 3))));

        $idxKetTx = $headerMap['KET_TX'] 
            ?? ($headerMap['KET'] 
            ?? ($headerMap['KETERANGAN'] 
            ?? ($headerMap['DESKRIPSI'] 
            ?? ($headerMap['URAIAN'] ?? ($idxKdTx + 4)))));

        $idxDbKr = $headerMap['DB_KR'] 
            ?? ($headerMap['DBKR'] 
            ?? ($headerMap['D_K'] 
            ?? ($headerMap['DK'] 
            ?? ($headerMap['DEBET_KREDIT'] ?? ($idxKdTx + 5)))));

        $idxTxtype = $headerMap['TXTYPE'] 
            ?? ($headerMap['TX_TYPE'] 
            ?? ($headerMap['JENIS_TX'] 
            ?? ($headerMap['TIPE_TX'] ?? ($idxKdTx + 6))));

        $idxKdUser = $headerMap['KD_USER'] 
            ?? ($headerMap['KDUSER'] 
            ?? ($headerMap['KODE_USER'] 
            ?? ($headerMap['USER_ID'] 
            ?? ($headerMap['USERID'] 
            ?? ($headerMap['USER'] ?? ($idxKdTx + 7))))));

        $idxTglTx = $headerMap['TGL_TX'] 
            ?? ($headerMap['TGLTX'] 
            ?? ($headerMap['TGL'] 
            ?? ($headerMap['TANGGAL'] 
            ?? ($headerMap['TANGGAL_TX'] ?? ($idxKdTx + 8)))));

        $idxTimeStamp = $headerMap['TIME_STAMP'] 
            ?? ($headerMap['TIMESTAMP'] 
            ?? ($headerMap['JAM'] 
            ?? ($headerMap['WAKTU'] 
            ?? ($headerMap['TIME'] ?? ($idxKdTx + 9)))));

        $idxJumlahTx = $headerMap['JUMLAH_TX'] 
            ?? ($headerMap['JUMLAHTX'] 
            ?? ($headerMap['NOMINAL'] 
            ?? ($headerMap['JUMLAH'] 
            ?? ($headerMap['NILAI'] ?? ($idxKdTx + 10)))));

        // Hapus data lama untuk kunjungan ini (delete-before-insert)
        OnsitePopulation::where('onsite_visit_id', $visit->id)->delete();
        OnsiteKkaFinding::where('onsite_visit_id', $visit->id)->delete();
        \App\Models\Onsite\OnsiteKkaTellerKas::where('onsite_visit_id', $visit->id)->delete();

        $rows = [];
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if (empty(array_filter($row, fn($val) => $val !== null && trim((string)$val) !== ''))) continue;

            $tglRaw = trim($row[$idxTglTx] ?? '');
            $tglTx  = $this->parseDate($tglRaw);
            if (!$tglTx) continue;

            $dbKr = strtoupper(trim($row[$idxDbKr] ?? ''));
            if ($dbKr === 'DEBET' || $dbKr === 'D') {
                $dbKr = 'D';
            } elseif ($dbKr === 'KREDIT' || $dbKr === 'K') {
                $dbKr = 'K';
            } else {
                $dbKr = 'D'; // Default fallback debit
            }

            $rawJumlah = trim($row[$idxJumlahTx] ?? '0');
            $cleanJumlah = preg_replace('/[,.]00$/', '', $rawJumlah);
            $cleanJumlah = str_replace(['Rp', ',', ' '], '', $cleanJumlah);
            $jumlah = (float) $cleanJumlah;

            $noArsip = trim($row[$idxNoArsip] ?? '');
            if ($noArsip === '') {
                $noArsip = 'ARS-' . uniqid();
            }

            $rows[] = [
                'kd_tx'     => trim($row[$idxKdTx] ?? ''),
                'kd_cab'    => trim($row[$idxKdCab] ?? ''),
                'no_rek'    => trim($row[$idxNoRek] ?? ''),
                'no_arsip'  => $noArsip,
                'ket_tx'    => trim($row[$idxKetTx] ?? ''),
                'db_kr'     => $dbKr,
                'txtype'    => trim($row[$idxTxtype] ?? ''),
                'kd_user'   => trim($row[$idxKdUser] ?? ''),
                'tgl_tx'    => $tglTx,
                'time_stamp'=> trim($row[$idxTimeStamp] ?? ''),
                'jumlah_tx' => $jumlah,
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
     * Jalankan jenis sampling, ambil baris D (debit) saja sebagai representasi kasus
     */
    private function runSampling(OnsiteVisit $visit, float $threshold): int
    {
        $populations = OnsitePopulation::where('onsite_visit_id', $visit->id)
            ->where('db_kr', 'D')
            ->get()
            ->keyBy('populasi_key');

        $dipilih = [];
        $kkaInsert = [];

        // 1. MANDATORY
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            $alasan = $this->cekMandatory($pop);
            if ($alasan) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Mandatory', self::PRIO_MANDATORY, $alasan);
            }
        }

        // 2. CERTAINTY
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            if ($pop->jumlah_tx >= $threshold) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Certainty', self::PRIO_CERTAINTY,
                    'Nominal >= Rp ' . number_format($threshold, 0, ',', '.'));
            }
        }

        // 3. TARGETED
        foreach ($populations as $key => $pop) {
            if (isset($dipilih[$key])) continue;

            $alasan = $this->cekTargeted($pop);
            if ($alasan) {
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Targeted', self::PRIO_TARGETED, $alasan);
            }
        }

        // 4. INITIAL
        $kuotaInitial = $this->hitungKuotaInitial($visit);
        $sisaKuota    = max(0, $kuotaInitial - count($dipilih));

        if ($sisaKuota > 0) {
            $belumDipilih = $populations->filter(fn($p) => !isset($dipilih[$p->populasi_key]));
            $sample = $belumDipilih->random(min($sisaKuota, $belumDipilih->count()));

            foreach ($sample as $pop) {
                $key = $pop->populasi_key;
                if (isset($dipilih[$key])) continue;
                $dipilih[$key] = true;
                $kkaInsert[] = $this->buildKka($visit, $pop, 'Initial', self::PRIO_INITIAL, 'Kuota Minimum SOP 02');
            }
        }

        foreach (array_chunk($kkaInsert, 500) as $chunk) {
            OnsiteKkaFinding::insert($chunk);
        }

        // Sinkronisasi otomatis ke KKA Teller & Kas
        $tellerInsert = [];
        foreach ($kkaInsert as $item) {
            $tellerInsert[] = [
                'onsite_visit_id'       => $item['onsite_visit_id'],
                'onsite_population_id'  => $item['onsite_population_id'],
                'sample_id'             => $item['sample_id'],
                'tgl_tx'                => $item['tgl_tx'],
                'no_arsip'              => $item['no_arsip'],
                'ket_tx'                => $item['ket_tx'],
                'jumlah_tx'             => $item['jumlah_tx'],
                'kd_user'               => $item['kd_user'],
                'jenis_sampling'        => $item['jenis_sampling'],
                'prioritas'             => $item['prioritas'],
                'alasan_sampling'       => $item['alasan_sampling'],
                'status_uji'            => 'Belum Diuji',
                'risk_level'            => 'Low',
                'status_review'         => 'Belum Direview',
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }
        foreach (array_chunk($tellerInsert, 500) as $chunk) {
            \App\Models\Onsite\OnsiteKkaTellerKas::insert($chunk);
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

        if ($jam && ($jam < self::JAM_MULAI_NORMAL || $jam > self::JAM_SELESAI_NORMAL)) {
            return 'Transaksi Di Luar Jam Kerja (' . $jam . ')';
        }

        return null;
    }

    private function hitungKuotaInitial(OnsiteVisit $visit): int
    {
        $perHari = (int) (MasterParameter::where('kategori', 'ONSITE_INITIAL_PER_HARI')->value('nilai_batas') ?? 5);

        $start = Carbon::parse($visit->tanggal_mulai)->startOfDay();
        $end   = Carbon::parse($visit->tanggal_selesai)->startOfDay();

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
        $trimmed = trim($raw);
        if ($trimmed === '') return null;

        // Jika serial number Excel (contoh: 45230)
        if (is_numeric($trimmed) && (int)$trimmed > 30000 && (int)$trimmed < 60000) {
            return date('Y-m-d', ((int)$trimmed - 25569) * 86400);
        }

        // Format YYYYMMDD (contoh: 20261102)
        if (is_numeric($trimmed) && strlen($trimmed) === 8) {
            return substr($trimmed, 0, 4) . '-' . substr($trimmed, 4, 2) . '-' . substr($trimmed, 6, 2);
        }

        $clean = str_replace('/', '-', $trimmed);
        $ts = strtotime($clean);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}