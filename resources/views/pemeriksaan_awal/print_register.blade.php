<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Register Indikasi Fraud - {{ $kasus->indikasi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            font-size: 11pt;
            line-height: 1.4;
        }
        .header-logo {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .table-custom th {
            background-color: #f2f2f2 !important;
            font-weight: bold;
            border: 1px solid #000 !important;
            padding: 6px 10px;
        }
        .table-custom td {
            border: 1px solid #000 !important;
            padding: 6px 10px;
        }
        .badge-confidential {
            border: 2px solid #b02a37;
            color: #b02a37;
            font-weight: bold;
            padding: 4px 10px;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 1px;
            display: inline-block;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="p-4">
    <!-- Tombol Cetak (Hanya tampil di layar) -->
    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-printer me-2"></i> Pratinjau Lembar Register Indikasi Fraud</h6>
            <div class="small text-muted">Format standar SOP 09 Bank Sulteng (WP-RA-09-A)</div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">
                Tutup Jendela
            </button>
        </div>
    </div>

    <!-- Header Dokumen Bank Sulteng -->
    <div class="header-logo text-center">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
                <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h5>
                <div class="small text-muted">DIVISI SATUAN KERJA AUDIT INTERN (SKAI)</div>
                <div class="small" style="font-size: 8.5pt;">Gedung Kantor Pusat, Jl. Sultan Hasanuddin No. 20, Palu - Sulawesi Tengah</div>
            </div>
            <div class="text-end">
                <span class="badge-confidential">RAHASIA / SANGAT TERBATAS</span>
                <div class="small fw-bold mt-1 text-dark">FORM WP-RA-09-A</div>
            </div>
        </div>
    </div>

    <!-- Judul Formulir -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1" style="text-decoration: underline;">LEMBAR REGISTER & PEMERIKSAAN AWAL INDIKASI FRAUD</h5>
        <div class="fw-bold">NOMOR REGISTER: {{ $kasus->indikasi_id }}</div>
    </div>

    <!-- Tabel 1: Identitas Kasus & Unit Kerja -->
    <table class="table table-custom mb-3">
        <tr>
            <th style="width: 25%;">ID Indikasi Kasus</th>
            <td style="width: 25%;" class="fw-bold text-danger">{{ $kasus->indikasi_id }}</td>
            <th style="width: 25%;">Tanggal Diterima/Lapor</th>
            <td style="width: 25%;">{{ $kasus->tanggal_diterima ? date('d F Y', strtotime($kasus->tanggal_diterima)) : '-' }}</td>
        </tr>
        <tr>
            <th>Kantor Cabang / Unit</th>
            <td>{{ $kasus->nama_unit ?: $kasus->kode_unit }} [{{ $kasus->kode_unit }}]</td>
            <th>Resident Auditor (RA)</th>
            <td>{{ $kasus->nama_ra ?: 'Auditor SKAI' }}</td>
        </tr>
        <tr>
            <th>Tipe Kasus</th>
            <td class="fw-bold">{{ $kasus->tipe_kasus ?: 'INSIDENTIL' }}</td>
            <th>Pihak Terindikasi Terlibat</th>
            <td class="fw-bold">{{ $kasus->pihak_terlibat ?: 'INTERNAL' }}</td>
        </tr>
        <tr>
            <th>Area Kejadian</th>
            <td>{{ $kasus->area_kejadian ?: ($kasus->area ?: 'Operasional Kas & Teller') }}</td>
            <th>Sumber Informasi</th>
            <td>{{ $kasus->sumber_indikasi ?: 'Audit Rutin / Temuan RA' }}</td>
        </tr>
        <tr>
            <th>Estimasi Potensi Kerugian</th>
            <td colspan="3" class="fw-bold text-dark fs-6">
                Rp {{ number_format($kasus->nominal_awal, 0, ',', '.') }}
                <span class="text-muted fw-normal small fst-italic ms-2">({{ terbilang($kasus->nominal_awal) }} Rupiah)</span>
            </td>
        </tr>
    </table>

    <!-- Tabel 2: Dugaan Awal, Fakta & Kronologi (Poin 2 & 16) -->
    <table class="table table-custom mb-3">
        <tr>
            <th class="text-uppercase" style="background-color: #eaeaea !important;">1. DUGAAN AWAL (MODUS OPERANDI / INDIKASI AWAL)</th>
        </tr>
        <tr>
            <td style="white-space: pre-wrap; min-height: 80px;">{{ $kasus->dugaan_awal ?: ($kasus->dugaan_belum_terverifikasi ?: 'Belum ada uraian dugaan awal.') }}</td>
        </tr>
        <tr>
            <th class="text-uppercase" style="background-color: #eaeaea !important;">2. FAKTA TEMUAN LAPANGAN TERVERIFIKASI</th>
        </tr>
        <tr>
            <td style="white-space: pre-wrap; min-height: 80px;">{{ $kasus->fakta_temuan ?: ($kasus->fakta_awal_terverifikasi ?: 'Belum ada fakta terverifikasi.') }}</td>
        </tr>
        <tr>
            <th class="text-uppercase" style="background-color: #eaeaea !important;">3. KRONOLOGI AWAL KEJADIAN SECARA RINCI</th>
        </tr>
        <tr>
            <td style="white-space: pre-wrap; min-height: 120px;">{{ $kasus->kronologi_awal ?: 'Belum ada kronologi detail yang tercatat.' }}</td>
        </tr>
        <tr>
            <th class="text-uppercase" style="background-color: #eaeaea !important;">4. CATATAN AWAL RESIDENT AUDITOR (RA)</th>
        </tr>
        <tr>
            <td style="white-space: pre-wrap;">{{ $kasus->catatan_awal_ra ?: 'Tidak ada catatan tambahan dari auditor.' }}</td>
        </tr>
    </table>

    <!-- Tabel 3: Triase & Disposisi Awal (Poin 13 & 14) -->
    <table class="table table-custom mb-4">
        <tr>
            <th colspan="2" class="text-uppercase" style="background-color: #eaeaea !important;">5. PENETAPAN KEPUTUSAN TRIASE AWAL</th>
        </tr>
        <tr>
            <td style="width: 40%;"><strong>Total Skor Triase</strong></td>
            <td style="width: 60%;" class="fw-bold fs-6">{{ $kasus->skor_checklist ?: 0 }} Poin</td>
        </tr>
        <tr>
            <td><strong>Rekomendasi Triase Sistem</strong></td>
            <td class="fw-bold">{{ $kasus->saran_keputusan_triase ?: 'PEMBINAAN CABANG / MONITORING BERKALA' }}</td>
        </tr>
        <tr>
            <td><strong>Status Penanganan Saat Ini</strong></td>
            <td class="fw-bold text-danger">{{ $kasus->status_kasus ?: 'OPEN' }}</td>
        </tr>
        <tr>
            <td><strong>Dokumen / Foto Bukti Fisik</strong></td>
            <td>
                @if($kasus->file_bukti)
                    Ada (Nama Berkas: {{ $kasus->nama_bukti ?: basename($kasus->file_bukti) }} - {{ $kasus->tipe_bukti }})
                @else
                    Tidak ada berkas bukti fisik terlampir
                @endif
            </td>
        </tr>
    </table>

    <!-- Pengesahan / Tanda Tangan -->
    <div class="row mt-4 pt-3">
        <div class="col-6 text-center">
            <div class="small text-muted mb-1">Dibuat & Dilaporkan Oleh:</div>
            <div class="fw-bold">Resident Auditor (RA)</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-decoration-underline">{{ $kasus->nama_ra ?: 'Auditor SKAI' }}</div>
            <div class="small">Resident Auditor Cabang</div>
        </div>
        <div class="col-6 text-center">
            <div class="small text-muted mb-1">Mengetahui & Menyetujui:</div>
            <div class="fw-bold">Kepala Divisi SKAI</div>
            <div style="height: 70px;"></div>
            <div class="fw-bold text-decoration-underline">BUDI SETIAWAN</div>
            <div class="small">Kadiv Satuan Kerja Audit Intern</div>
        </div>
    </div>
</body>
</html>
@php
function terbilang($angka) {
    $angka = abs($angka);
    $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $terbilang = "";
    if ($angka < 12) {
        $terbilang = " " . $baca[$angka];
    } else if ($angka < 20) {
        $terbilang = terbilang($angka - 10) . " Belas";
    } else if ($angka < 100) {
        $terbilang = terbilang($angka / 10) . " Puluh" . terbilang($angka % 10);
    } else if ($angka < 200) {
        $terbilang = " Seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . " Ratus" . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $terbilang = " Seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . " Juta" . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        $terbilang = terbilang($angka / 1000000000) . " Miliar" . terbilang(fmod($angka, 1000000000));
    }
    return trim($terbilang);
}
@endphp

