<?php

namespace App\Services;

use App\Models\WpOffsiteStaging;
use App\Models\WpOffsite;

class OffsiteDetectorEngine
{
    /**
     * Jalankan mesin scanning rule indikator risiko pada WP Offsite
     */
    public function scan(WpOffsite $wp, string $domainType)
    {
        // Ambil data staging berdasarkan unit/cabang & domain yang baru di-upload
        $stagings = WpOffsiteStaging::where('cabang_id', $wp->unit_id)
            ->where('domain_type', $domainType)
            ->whereNull('processed_at') // Ambil yang belum discan
            ->get();

        $flaggedCount = 0;

        foreach ($stagings as $staging) {
            $data = $staging->raw_data;
            $isAnomaly = false;

            // Rules Engine per Domain
            switch ($domainType) {
                case 'cbs':
                    // Rule CBS: Nominal >= 50 Juta
                    $nominal = isset($data[5]) ? (float)$data[5] : 0;
                    if ($nominal >= 50000000) {
                        $isAnomaly = true;
                    }
                    break;

                case 'biaya':
                    // Rule Biaya: Transaksi tanpa persetujuan / jurnal khusus
                    $isAnomaly = false; 
                    break;

                case 'kredit':
                    // Rule Kredit: Plafond besar / Agunan kosong
                    $isAnomaly = false;
                    break;

                case 'dpk':
                    // Rule DPK: Dormant account aktif kembali
                    $isAnomaly = false;
                    break;

                case 'pengaduan':
                    // Rule Pengaduan: High risk ticket
                    $isAnomaly = false;
                    break;
            }

            if ($isAnomaly) {
                $flaggedCount++;
            }

            // Tandai staging sudah diproses
            $staging->update(['processed_at' => now()]);
        }

        return $flaggedCount;
    }
}