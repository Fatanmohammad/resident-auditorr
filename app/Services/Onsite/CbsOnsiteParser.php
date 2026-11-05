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

        // Auto-detect delimiter secara akurat dari baris pertama (hitung frekuensi pemisah)
        $firstLine = fgets($file);
        rewind($file);

        $counts = [
            ','  => substr_count($firstLine, ','),
            ';'  => substr_count($firstLine, ';'),
            "\t" => substr_count($firstLine, "\t"),
            '|'  => substr_count($firstLine, '|'),
        ];
        arsort($counts);
        $delimiter = array_key_first($counts);
        if ($counts[$delimiter] === 0) {
            $delimiter = ',';
        }

        $header = fgetcsv($file, 0, $delimiter);
        if (!$header) {
            fclose($file);
            throw new \Exception("File CSV kosong atau tidak memiliki header.");
        }

        // Bangun mapping kolom dinamis (bersihkan dari spasi, karakter non-alphanumeric, & BOM)
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $cleanCol = strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', (string)$colName)));
            if ($cleanCol !== '') {
                $headerMap[$cleanCol] = $index;
            }
        }

        // 1. Cari kolom ID Transaksi / KD_TX
        $idxKdTx = null;
        foreach (['KD_TX', 'KDTX', 'ID_DATA_CBS', 'IDDATACBS', 'ID_TX', 'IDTX', 'KODE_TX', 'TX_ID', 'ID'] as $k) {
            if (isset($headerMap[$k])) { $idxKdTx = $headerMap[$k]; break; }
        }
        if ($idxKdTx === null) {
            $idxKdTx = 0; // Fallback kolom pertama
        }

        // 2. KD_CAB
        $idxKdCab = null;
        foreach (['KD_CAB', 'KDCAB', 'KODE_CAB', 'KODECAB', 'KODE_CABANG', 'CABANG'] as $k) {
            if (isset($headerMap[$k])) { $idxKdCab = $headerMap[$k]; break; }
        }

        // 3. NO_REK
        $idxNoRek = null;
        foreach (['NO_REK', 'NOREK', 'NO_REKENING', 'NOREKENING', 'REKENING'] as $k) {
            if (isset($headerMap[$k])) { $idxNoRek = $headerMap[$k]; break; }
        }

        // 4. NO_ARSIP
        $idxNoArsip = null;
        foreach (['NO_ARSIP', 'NOARSIP', 'NO_BUKTI', 'NOBUKTI', 'ARSIP', 'BUKTI', 'NO_SURAT'] as $k) {
            if (isset($headerMap[$k])) { $idxNoArsip = $headerMap[$k]; break; }
        }

        // 5. KET_TX
        $idxKetTx = null;
        foreach (['KET_TX', 'KETTX', 'KETERANGAN', 'KET', 'DESKRIPSI', 'URAIAN', 'RINCIAN', 'TRANSAKSI'] as $k) {
            if (isset($headerMap[$k])) { $idxKetTx = $headerMap[$k]; break; }
        }

        // 6. DB_KR
        $idxDbKr = null;
        foreach (['DB_KR', 'DBKR', 'D_K', 'DK', 'DEBET_KREDIT', 'DEBETKREDIT', 'TIPE'] as $k) {
            if (isset($headerMap[$k])) { $idxDbKr = $headerMap[$k]; break; }
        }

        // 7. TXTYPE
        $idxTxtype = null;
        foreach (['TXTYPE', 'TX_TYPE', 'JENIS_TX', 'JENISTX', 'TIPE_TX', 'TIPETX'] as $k) {
            if (isset($headerMap[$k])) { $idxTxtype = $headerMap[$k]; break; }
        }

        // 8. KD_USER
        $idxKdUser = null;
        foreach (['KD_USER', 'KDUSER', 'KODE_USER', 'KODEUSER', 'USER_ID', 'USERID', 'USER', 'PETUGAS', 'TELLER'] as $k) {
            if (isset($headerMap[$k])) { $idxKdUser = $headerMap[$k]; break; }
        }

        // 9. TGL_TX
        $idxTglTx = null;
        foreach (['TGL_TX', 'TGLTX', 'TGL_TRANSAKSI', 'TGLTRANSAKSI', 'TANGGAL_TX', 'TANGGALTX', 'TANGGAL_TRANSAKSI', 'TANGGALTRANSAKSI', 'TANGGAL', 'TGL', 'DATE', 'TRX_DATE'] as $k) {
            if (isset($headerMap[$k])) { $idxTglTx = $headerMap[$k]; break; }
        }

        // 10. TIME_STAMP
        $idxTimeStamp = null;
        foreach (['TIME_STAMP', 'TIMESTAMP', 'JAM', 'WAKTU', 'TIME'] as $k) {
            if (isset($headerMap[$k])) { $idxTimeStamp = $headerMap[$k]; break; }
        }

        // 11. JUMLAH_TX
        $idxJumlahTx = null;
        foreach (['JUMLAH_TX', 'JUMLAHTX', 'JUMLAH', 'NOMINAL', 'NILAI', 'SALDO_AKHIR', 'SALDO', 'DEBET', 'KREDIT', 'BAKI_DEBET'] as $k) {
            if (isset($headerMap[$k])) { $idxJumlahTx = $headerMap[$k]; break; }
        }

        // Positional fallback jika ada kolom yang belum terpetakan namanya
        $colCount = count($header);
        if ($idxKdCab === null)     $idxKdCab     = min($idxKdTx + 1, $colCount - 1);
        if ($idxNoRek === null)     $idxNoRek     = min($idxKdTx + 2, $colCount - 1);
        if ($idxNoArsip === null)   $idxNoArsip   = min($idxKdTx + 3, $colCount - 1);
        if ($idxKetTx === null)     $idxKetTx     = min($idxKdTx + 4, $colCount - 1);
        if ($idxDbKr === null)      $idxDbKr      = min($idxKdTx + 5, $colCount - 1);
        if ($idxTxtype === null)    $idxTxtype    = min($idxKdTx + 6, $colCount - 1);
        if ($idxKdUser === null)    $idxKdUser    = min($idxKdTx + 7, $colCount - 1);
        if ($idxTglTx === null)     $idxTglTx     = min($idxKdTx + 8, $colCount - 1);
        if ($idxTimeStamp === null) $idxTimeStamp = min($idxKdTx + 9, $colCount - 1);
        if ($idxJumlahTx === null)  $idxJumlahTx  = min($idxKdTx + 10, $colCount - 1);

        // Hapus data lama untuk kunjungan ini (delete-before-insert)
        OnsitePopulation::where('onsite_visit_id', $visit->id)->delete();
        OnsiteKkaFinding::where('onsite_visit_id', $visit->id)->delete();
        \App\Models\Onsite\OnsiteKkaTellerKas::where('onsite_visit_id', $visit->id)->delete();

        $rows = [];
        $rawRowsInspected = 0;
        $fallbackDate = $visit->tanggal_mulai ? date('Y-m-d', strtotime($visit->tanggal_mulai)) : now()->toDateString();

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if (empty(array_filter($row, fn($val) => $val !== null && trim((string)$val) !== ''))) {
                continue;
            }

            $rawRowsInspected++;

            // Cari tanggal transaksi
            $tglRaw = trim((string)($row[$idxTglTx] ?? ''));
            $tglTx  = $this->parseDate($tglRaw);

            // Jika tanggal di kolom tsb tidak valid, scan seluruh kolom pada baris ini
            if (!$tglTx) {
                foreach ($row as $val) {
                    $valStr = trim((string)$val);
                    if ($valStr !== '' && strlen($valStr) >= 8) {
                        $candidate = $this->parseDate($valStr);
                        if ($candidate) {
                            $tglTx = $candidate;
                            break;
                        }
                    }
                }
            }

            // Fallback ke tanggal kunjungan / hari ini jika baris tidak memuat format tanggal (JANGAN LEWATKAN BARIS!)
            if (!$tglTx) {
                $tglTx = $fallbackDate;
            }

            $dbKr = strtoupper(trim((string)($row[$idxDbKr] ?? '')));
            if ($dbKr === 'DEBET' || $dbKr === 'D') {
                $dbKr = 'D';
            } elseif ($dbKr === 'KREDIT' || $dbKr === 'K') {
                $dbKr = 'K';
            } else {
                $dbKr = 'D'; // Default fallback debit
            }

            $rawJumlah = trim((string)($row[$idxJumlahTx] ?? '0'));
            $cleanJumlah = preg_replace('/[,.]00$/', '', $rawJumlah);
            $cleanJumlah = str_replace(['Rp', ',', ' '], '', $cleanJumlah);
            $jumlah = (float) $cleanJumlah;

            // Jika nominal 0, cari kolom numerik lain pada baris ini
            if ($jumlah == 0) {
                foreach ($row as $val) {
                    $cleanVal = str_replace(['Rp', ',', '.', ' '], '', trim((string)$val));
                    if (is_numeric($cleanVal) && (float)$cleanVal >= 1000) {
                        $jumlah = (float)$cleanVal;
                        break;
                    }
                }
            }

            $noArsip = trim((string)($row[$idxNoArsip] ?? ''));
            if ($noArsip === '') {
                $noArsip = 'ARS-' . str_pad($rawRowsInspected, 4, '0', STR_PAD_LEFT);
            }

            $ketTx = trim((string)($row[$idxKetTx] ?? ''));
            if ($ketTx === '') {
                $ketTx = 'Transaksi CBS No ' . $rawRowsInspected;
            }

            $rows[] = [
                'kd_tx'     => trim((string)($row[$idxKdTx] ?? 'TX' . $rawRowsInspected)),
                'kd_cab'    => trim((string)($row[$idxKdCab] ?? $visit->kode_unit)),
                'no_rek'    => trim((string)($row[$idxNoRek] ?? '-')),
                'no_arsip'  => $noArsip,
                'ket_tx'    => $ketTx,
                'db_kr'     => $dbKr,
                'txtype'    => trim((string)($row[$idxTxtype] ?? 'TRX')),
                'kd_user'   => trim((string)($row[$idxKdUser] ?? 'SYSTEM')),
                'tgl_tx'    => $tglTx,
                'time_stamp'=> trim((string)($row[$idxTimeStamp] ?? '09:00:00')),
                'jumlah_tx' => $jumlah,
            ];
        }
        fclose($file);

        if (empty($rows)) {
            $headerStr = implode(', ', array_slice($header, 0, 10));
            throw new \Exception("File CBS tidak mengandung data transaksi (hanya terbaca header: [{$headerStr}], total baris terdeteksi: {$rawRowsInspected}). Pastikan file memuat baris data transaksi.");
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

        // Fallback jika tidak ada record dengan flag debit 'D', ambil seluruh populasi
        if ($populations->isEmpty()) {
            $populations = OnsitePopulation::where('onsite_visit_id', $visit->id)
                ->get()
                ->keyBy('populasi_key');
        }

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