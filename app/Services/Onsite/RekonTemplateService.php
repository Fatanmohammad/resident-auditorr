<?php

namespace App\Services\Onsite;

class RekonTemplateService
{
    // =========================================================
    // REKON BARANG CETAKAN — 27 item fixed
    // =========================================================
    public static function itemsBarangCetakan(): array
    {
        return [
            ['kategori' => 'Formulir',      'nama_barang' => 'Kwitansi pencairan kredit',          'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Slip setoran',                        'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Slip penarikan',                      'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Slip RTGS',                           'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simantap',                   'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpeda',                    'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan TabunganKU',                 'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan PNS',                        'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Tawa',                       'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Pensiunku',                  'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tabungan Simpel',                     'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'Tawa Plus',                           'satuan' => 'Bh'],
            ['kategori' => 'Buku Tabungan', 'nama_barang' => 'TabunganKU Plus',                     'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Pembukaan rekening perorangan',       'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Pembukaan rekening nonperorangan',    'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Pembukaan rekening Tawa',             'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Form keluhan nasabah',                'satuan' => 'Bh'],
            ['kategori' => 'Formulir',      'nama_barang' => 'Form KYC',                            'satuan' => 'Bh'],
            ['kategori' => 'Kertas',        'nama_barang' => 'Kertas kop bank',                     'satuan' => 'Rim'],
            ['kategori' => 'Amplop',        'nama_barang' => 'Amplop kecil berlogo',                'satuan' => 'Pack'],
            ['kategori' => 'Amplop',        'nama_barang' => 'Amplop panjang berlogo',              'satuan' => 'Pack'],
            ['kategori' => 'Amplop',        'nama_barang' => 'Amplop sedang berlogo',               'satuan' => 'Pack'],
            ['kategori' => 'Perlengkapan',  'nama_barang' => 'Signature Spectroline',               'satuan' => 'Dos'],
            ['kategori' => 'Perlengkapan',  'nama_barang' => 'Invisible Signature Spectroline',     'satuan' => 'Dos'],
            ['kategori' => 'Prioritas',     'nama_barang' => 'Amplop nasabah prioritas',            'satuan' => 'Bh'],
            ['kategori' => 'Prioritas',     'nama_barang' => 'Form pembukaan nasabah prioritas',    'satuan' => 'Bh'],
            ['kategori' => 'Perlengkapan',  'nama_barang' => 'Pita printer tally',                  'satuan' => 'Bh'],
        ];
    }

    public static function generateBarangCetakan(string $kodeUnit, string $tanggal, int $rekonId): array
    {
        $kode    = strtoupper(str_replace([' ', '-'], '', $kodeUnit));
        $tglFmt  = str_replace('-', '', $tanggal);
        $items   = [];

        foreach (self::itemsBarangCetakan() as $i => $item) {
            $no = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $items[] = array_merge($item, [
                'rekon_id'     => $rekonId,
                'procedure_id' => "FLD-ONS-{$kode}-{$tglFmt}-BCT-{$no}",
                'status_review'=> 'Belum Diuji',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        return $items;
    }

    // =========================================================
    // REKON CEK & BG — 10 item fixed
    // =========================================================
    public static function itemsCekBg(): array
    {
        return [
            ['sub_area' => 'Cek',       'objek' => 'Buku cek kosong/belum digunakan',                    'pecahan_seri' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Cek',       'objek' => 'Cek rusak atau dibatalkan',                           'pecahan_seri' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Cek',       'objek' => 'Cek tercetak/diserahkan tetapi belum diambil',        'pecahan_seri' => 'Seluruh fisik berada dalam custody unit'],
            ['sub_area' => 'Bilyet Giro','objek' => 'Bilyet giro kosong/belum digunakan',                 'pecahan_seri' => 'Seluruh buku dan nomor seri dalam listing'],
            ['sub_area' => 'Bilyet Giro','objek' => 'Bilyet giro rusak atau dibatalkan',                  'pecahan_seri' => 'Seluruh fisik dan nomor seri dalam register pembatalan'],
            ['sub_area' => 'Bilyet Giro','objek' => 'Bilyet giro tercetak/diserahkan tetapi belum diambil','pecahan_seri' => 'Seluruh fisik berada dalam custody unit'],
            ['sub_area' => 'Deposito',  'objek' => 'Bilyet deposito kosong',                              'pecahan_seri' => 'Seluruh fisik dan nomor seri dalam listing, bila berada dalam ruang lingkup'],
            ['sub_area' => 'Kontrol',   'objek' => 'Kartu kontrol harian',                                'pecahan_seri' => 'Dibuat, diperbarui, dan direkonsiliasi setiap hari'],
            ['sub_area' => 'Kontrol',   'objek' => 'Register penerimaan dan pengeluaran',                 'pecahan_seri' => 'Urutan seri, penerimaan, pengeluaran, dan serah terima lengkap'],
            ['sub_area' => 'Kontrol',   'objek' => 'Penyimpanan dokumen berharga',                        'pecahan_seri' => 'Terkunci, dual control, akses terbatas, dan penanggung jawab jelas'],
        ];
    }

    // =========================================================
    // REKON KARTU ATM — 6 kontrol fixed
    // =========================================================
    public static function kontrolKartuAtm(): array
    {
        return [
            ['keterangan' => 'Jumlah kartu dan PIN mailer',       'kriteria' => 'Jumlah kartu ATM sepadan dengan PIN mailer/komponen terkait atau perbedaannya dapat dijelaskan.'],
            ['keterangan' => 'Register penerimaan dan pengeluaran','kriteria' => 'Seluruh penerimaan, penyerahan, retur, dan pemusnahan tercatat lengkap dan mutakhir.'],
            ['keterangan' => 'Kontrol nomor seri/batch',           'kriteria' => 'Nomor seri atau batch dapat ditelusuri dari penerimaan sampai penyerahan kepada nasabah.'],
            ['keterangan' => 'Kartu rusak/retur',                  'kriteria' => 'Kartu rusak, gagal personalisasi, retur, dan kartu yang akan dimusnahkan dipisahkan serta dikendalikan.'],
            ['keterangan' => 'Penyimpanan dan akses',              'kriteria' => 'Persediaan disimpan aman; akses dibatasi dan menerapkan dual control sesuai ketentuan.'],
            ['keterangan' => 'Bukti serah terima',                 'kriteria' => 'Penyerahan kartu kepada nasabah atau unit lain didukung bukti yang sah dan sesuai tanggal nominatif.'],
        ];
    }

    public static function generateKontrolKartuAtm(string $kodeUnit, string $tanggal, int $rekonId): array
    {
        $kode   = strtoupper(str_replace([' ', '-'], '', $kodeUnit));
        $tglFmt = str_replace('-', '', $tanggal);
        $items  = [];
        $startNo = 31;

        foreach (self::kontrolKartuAtm() as $i => $item) {
            $no = str_pad($startNo + $i, 3, '0', STR_PAD_LEFT);
            $items[] = [
                'rekon_id'      => $rekonId,
                'procedure_id'  => "FLD-ONS-{$kode}-{$tglFmt}-KATREC-{$no}",
                'tipe'          => 'kontrol',
                'sub_area'      => 'Kontrol',
                'jenis_kartu'   => null,
                'keterangan'    => $item['keterangan'],
                'saldo_nominatif' => 'N/A',
                'fisik_kondisi' => null,
                'selisih'       => 'N/A',
                'kriteria'      => $item['kriteria'],
                'status_review' => 'Belum Diuji',
                'urutan'        => $startNo + $i,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        return $items;
    }

    public static function generateCekBg(string $kodeUnit, string $tanggal, int $rekonId): array
    {
        $kode   = strtoupper(str_replace([' ', '-'], '', $kodeUnit));
        $tglFmt = str_replace('-', '', $tanggal);
        $items  = [];

        foreach (self::itemsCekBg() as $i => $item) {
            $no = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            $items[] = array_merge($item, [
                'rekon_id'     => $rekonId,
                'procedure_id' => "FLD-ONS-{$kode}-{$tglFmt}-CBG-{$no}",
                'status_review'=> 'Belum Diuji',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        return $items;
    }
}
