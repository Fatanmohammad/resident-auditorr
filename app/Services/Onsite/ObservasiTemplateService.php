<?php

namespace App\Services\Onsite;

class ObservasiTemplateService
{
    // 20 item tetap gedung & lingkungan
    public static function itemsGedung(): array
    {
        return [
            ['area' => 'Keamanan',          'objek' => 'Petugas keamanan dan buku tamu',                        'kriteria' => 'Petugas tersedia; identitas, tujuan, dan waktu kunjungan dicatat serta diawasi'],
            ['area' => 'Keamanan',          'objek' => 'Akses masuk gedung dan area terbatas',                  'kriteria' => 'Akses dibatasi sesuai kewenangan dan tidak dapat dimasuki pihak yang tidak berkepentingan'],
            ['area' => 'CCTV',              'objek' => 'Cakupan kamera pada area kritis',                       'kriteria' => 'Banking hall, teller, customer service, khasanah, akses masuk, dan area penyimpanan yang relevan terpantau'],
            ['area' => 'CCTV',              'objek' => 'Fungsi kamera, waktu, dan mutu rekaman',                'kriteria' => 'Kamera berfungsi, waktu rekaman benar, gambar dapat dikenali, dan rekaman dapat ditelusuri'],
            ['area' => 'CCTV',              'objek' => 'Backup dan masa simpan rekaman',                        'kriteria' => 'Rekaman tersimpan sesuai ketentuan dan dapat diputar kembali untuk periode yang diwajibkan'],
            ['area' => 'Layanan Nasabah',   'objek' => 'Mesin/sistem antrean',                                  'kriteria' => 'Tersedia bila diwajibkan, berfungsi, dan mengatur antrean layanan dengan baik'],
            ['area' => 'Gedung',            'objek' => 'Kondisi bangunan dan kebersihan',                       'kriteria' => 'Atap, dinding, plafon, lantai, pintu, dan area kerja bersih serta tidak rusak atau membahayakan'],
            ['area' => 'Gedung',            'objek' => 'Suhu ruangan, pencahayaan, dan ventilasi',              'kriteria' => 'Suhu, penerangan, dan sirkulasi udara memadai untuk layanan dan kegiatan kerja'],
            ['area' => 'Kelistrikan',       'objek' => 'Panel, stopkontak, kabel, stabilizer, dan UPS',         'kriteria' => 'Instalasi tertutup dan tertata; perangkat pendukung tersedia serta berfungsi'],
            ['area' => 'Keselamatan',       'objek' => 'APAR',                                                  'kriteria' => 'Tersedia, mudah dijangkau, tersegel, dan belum melewati masa berlaku'],
            ['area' => 'Keselamatan',       'objek' => 'Jalur evakuasi, pintu darurat, dan titik kumpul',       'kriteria' => 'Tanda terlihat, jalur tidak terhalang, dan pintu dapat digunakan'],
            ['area' => 'Keselamatan',       'objek' => 'Kotak P3K dan komunikasi darurat',                      'kriteria' => 'Tersedia, mudah diakses, lengkap, dan dapat digunakan'],
            ['area' => 'Layanan Nasabah',   'objek' => 'Privasi nasabah pada area layanan',                     'kriteria' => 'Percakapan, dokumen, PIN, dan tampilan layar tidak mudah diketahui pihak lain'],
            ['area' => 'Layanan Nasabah',   'objek' => 'Area teller dan customer service',                      'kriteria' => 'Pembatas, meja layanan, tata letak, dan peralatan masih baik serta aman'],
            ['area' => 'Lingkungan Kerja',  'objek' => 'Penataan meja, kabel, dokumen, dan jalur berjalan',     'kriteria' => 'Tertib, aman, tidak menghambat pergerakan, dan tidak membuka akses ke dokumen rahasia'],
            ['area' => 'Arsip',             'objek' => 'Ruang/lemari penyimpanan arsip',                        'kriteria' => 'Dokumen terlindungi, tersusun, akses terbatas, dan mudah ditelusuri'],
            ['area' => 'Fasilitas',         'objek' => 'Toilet, pantry, air bersih, dan sanitasi',              'kriteria' => 'Bersih, berfungsi, tidak bocor/tersumbat, dan tidak menimbulkan risiko kesehatan'],
            ['area' => 'Fasilitas',         'objek' => 'Genset dan fasilitas listrik cadangan',                 'kriteria' => 'Tersedia sesuai kebutuhan, terpelihara, dan dapat digunakan saat diperlukan'],
            ['area' => 'Identitas Gedung',  'objek' => 'Papan nama, informasi layanan, dan branding',           'kriteria' => 'Terpasang, terbaca, akurat, dan dalam kondisi baik'],
            ['area' => 'Lingkungan Sekitar','objek' => 'Risiko banjir, kebakaran, longsor, dan gangguan sekitar','kriteria' => 'Risiko dikenali dan tindakan mitigasi tersedia serta memadai'],
        ];
    }

    // 3 item per ATM (dinamis)
    public static function itemsAtm(int $nomorAtm): array
    {
        $slot = 'ATM ' . str_pad($nomorAtm, 2, '0', STR_PAD_LEFT);
        return [
            ['area' => 'ATM', 'objek' => "{$slot} | Bangunan/Ruang ATM",          'kriteria' => 'Ruang bersih, suhu dan pencahayaan memadai, pintu/booth/branding baik, aman, dan tidak membahayakan pengguna.'],
            ['area' => 'ATM', 'objek' => "{$slot} | Mesin dan Kaset ATM",          'kriteria' => 'Mesin, layar, keypad, card reader, printer, anti-skimming, panel, dan maksimal empat kaset dalam kondisi baik serta berfungsi.'],
            ['area' => 'ATM', 'objek' => "{$slot} | Infrastruktur Pendukung ATM",  'kriteria' => 'Listrik, stabilizer, UPS, jaringan, CCTV, logbook, dan fasilitas pendukung tersedia, berfungsi, serta dapat ditelusuri.'],
        ];
    }

    // Generate semua items untuk observasi baru
    public static function generate(int $jumlahAtm, string $kodeUnit, string $tanggal, int $observasiId): array
    {
        $items = [];
        $urut  = 1;

        foreach (self::itemsGedung() as $i => $item) {
            $no  = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $tgl = str_replace('-', '', $tanggal);
            $items[] = [
                'onsite_observasi_id' => $observasiId,
                'procedure_id'        => "FLD-ONS-{$kodeUnit}-{$tgl}-OBS-{$no}",
                'area'                => $item['area'],
                'objek'               => $item['objek'],
                'kriteria'            => $item['kriteria'],
                'tipe'                => 'gedung',
                'atm_slot'            => null,
                'urut'                => $urut++,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        for ($a = 1; $a <= $jumlahAtm; $a++) {
            foreach (self::itemsAtm($a) as $j => $item) {
                $slot = 'ATM ' . str_pad($a, 2, '0', STR_PAD_LEFT);
                $no   = str_pad(20 + (($a - 1) * 3) + $j + 1, 3, '0', STR_PAD_LEFT);
                $tgl  = str_replace('-', '', $tanggal);
                $items[] = [
                    'onsite_observasi_id' => $observasiId,
                    'procedure_id'        => "FLD-ONS-{$kodeUnit}-{$tgl}-OBS-{$no}",
                    'area'                => $item['area'],
                    'objek'               => $item['objek'],
                    'kriteria'            => $item['kriteria'],
                    'tipe'                => 'atm',
                    'atm_slot'            => $slot,
                    'urut'                => $urut++,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }
        }

        return $items;
    }
}
