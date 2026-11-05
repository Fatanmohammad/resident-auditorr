<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo Eskalasi SKAI - {{ $kasus->nomor_memo_eskalasi ?: $kasus->indikasi_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            font-size: 11pt;
            line-height: 1.5;
        }
        .header-logo {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .badge-confidential {
            border: 2px solid #b02a37;
            color: #b02a37;
            font-weight: bold;
            padding: 3px 8px;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 1px;
            display: inline-block;
        }
        .memo-meta td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .memo-box {
            border: 1px solid #000;
            padding: 10px 15px;
            margin-bottom: 15px;
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
                margin: 2cm 1.5cm 1.5cm 1.5cm;
            }
        }
    </style>
</head>
<body class="p-4">
    <!-- Tombol Cetak (Hanya layar) -->
    <div class="no-print mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bi bi-printer me-2"></i> Pratinjau Memo Resmi Disposisi Eskalasi SKAI</h6>
            <div class="small text-muted">Format resmi Memo Internal Bank Sulteng untuk pelaporan indikasi fraud kepada Direksi</div>
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

    <!-- Kop Surat Resmi PT Bank Sulteng -->
    <div class="header-logo text-center">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
                <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h5>
                <div class="small text-muted fw-bold">DIVISI SATUAN KERJA AUDIT INTERN (SKAI)</div>
                <div class="small" style="font-size: 8.5pt;">Gedung Kantor Pusat, Jl. Sultan Hasanuddin No. 20, Palu - Sulawesi Tengah</div>
            </div>
            <div class="text-end">
                <span class="badge-confidential">SANGAT RAHASIA</span>
                <div class="small fw-bold mt-1 text-dark">SOP-SKAI-09</div>
            </div>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="text-center mb-3">
        <h5 class="fw-bold text-uppercase mb-0" style="text-decoration: underline; letter-spacing: 1px;">MEMORANDUM INTERNAL</h5>
        <div class="fw-bold small mt-1">NOMOR: {{ $kasus->nomor_memo_eskalasi ?: ('MEMO/SKAI-FRD/' . date('Y') . '/' . date('m') . '/' . str_pad($kasus->id, 3, '0', STR_PAD_LEFT)) }}</div>
    </div>

    <!-- Tabel Meta Memo -->
    <table class="table table-borderless memo-meta mb-3" style="width: 100%;">
        <tr>
            <td style="width: 15%; font-weight: bold;">KEPADA</td>
            <td style="width: 2%;">:</td>
            <td style="width: 83%; font-weight: bold;">Yth. Direktur Utama PT. Bank Sulteng<br>Cq. Direktur Kepatuhan & Manajemen Risiko</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">DARI</td>
            <td>:</td>
            <td style="font-weight: bold;">Kepala Divisi Satuan Kerja Audit Intern (SKAI)</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">TANGGAL</td>
            <td>:</td>
            <td>{{ $kasus->tanggal_memo ? date('d F Y', strtotime($kasus->tanggal_memo)) : date('d F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">PERIHAL</td>
            <td>:</td>
            <td style="font-weight: bold; text-decoration: underline;">
                {{ $kasus->perihal_memo ?: ('Laporan Hasil Pemeriksaan Awal & Rekomendasi Eskalasi Indikasi Fraud Kasus [' . $kasus->indikasi_id . ']') }}
            </td>
        </tr>
    </table>

    <hr style="border-top: 1px solid #000; margin-top: 5px; margin-bottom: 15px;">

    <!-- Isi Memorandum -->
    <div class="content-body" style="text-align: justify;">
        <p>Dengan hormat,</p>
        <p>
            Sehubungan dengan pelaksanaan fungsi pengawasan internal dan deteksi dini penyimpangan operasional di lingkungan PT. Bank Sulteng sesuai ketentuan <strong>SOP 09 (Pemeriksaan Awal Indikasi Fraud)</strong>, bersama ini kami laporkan hasil pemeriksaan awal yang dilakukan oleh Resident Auditor (RA) atas temuan indikasi kecurangan di <strong>{{ $kasus->nama_unit ?: $kasus->kode_unit }}</strong> dengan ringkasan sebagai berikut:
        </p>

        <!-- Ringkasan Kasus Box -->
        <div class="memo-box bg-light">
            <table class="table table-borderless table-sm mb-0" style="font-size: 10.5pt;">
                <tr>
                    <td style="width: 30%; font-weight: bold;">1. ID Kasus Indikasi</td>
                    <td style="width: 2%;">:</td>
                    <td style="width: 68%;" class="fw-bold text-danger">{{ $kasus->indikasi_id }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">2. Kantor Cabang / Unit</td>
                    <td>:</td>
                    <td>{{ $kasus->nama_unit ?: $kasus->kode_unit }} (Kode: {{ $kasus->kode_unit }})</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">3. Area Kejadian / Objek</td>
                    <td>:</td>
                    <td>{{ $kasus->area_kejadian ?: ($kasus->area ?: 'Operasional Kas & Teller') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">4. Klasifikasi / Tipe Kasus</td>
                    <td>:</td>
                    <td><strong>{{ $kasus->tipe_kasus ?: 'INSIDENTIL' }}</strong> (Pihak Terlibat: <strong>{{ $kasus->pihak_terlibat ?: 'INTERNAL' }}</strong>)</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">5. Estimasi Potensi Kerugian</td>
                    <td>:</td>
                    <td class="fw-bold text-dark">Rp {{ number_format($kasus->nominal_awal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">6. Total Skor Triase Awal</td>
                    <td>:</td>
                    <td><strong>{{ $kasus->skor_checklist ?: 0 }} Poin</strong> - Rekomendasi: <strong>{{ $kasus->saran_keputusan_triase ?: 'INVESTIGASI SEGERA' }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Uraian Kasus -->
        <div class="mb-3">
            <div class="fw-bold text-decoration-underline mb-1">A. DUGAAN AWAL & MODUS OPERANDI</div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $kasus->dugaan_awal ?: ($kasus->dugaan_belum_terverifikasi ?: '-') }}</p>
        </div>

        <div class="mb-3">
            <div class="fw-bold text-decoration-underline mb-1">B. FAKTA TEMUAN LAPANGAN TERVERIFIKASI</div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $kasus->fakta_temuan ?: ($kasus->fakta_awal_terverifikasi ?: '-') }}</p>
        </div>

        <div class="mb-3">
            <div class="fw-bold text-decoration-underline mb-1">C. KRONOLOGI AWAL KEJADIAN</div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $kasus->kronologi_awal ?: '-' }}</p>
        </div>

        @if($kasus->catatan_awal_ra)
        <div class="mb-3">
            <div class="fw-bold text-decoration-underline mb-1">D. CATATAN AWAL RESIDENT AUDITOR</div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $kasus->catatan_awal_ra }}</p>
        </div>
        @endif

        <!-- Uraian Memo / Rekomendasi Kadiv SKAI -->
        <div class="mb-3">
            <div class="fw-bold text-decoration-underline mb-1">E. ANALISIS & REKOMENDASI KADIV SKAI</div>
            <p class="mb-2" style="white-space: pre-wrap;">{{ $kasus->arahan_kadiv ?: ($kasus->isi_memo ?: 'Berdasarkan fakta dan skor triase di atas, Divisi SKAI merekomendasikan penerbitan Surat Penugasan Investigasi Khusus untuk mengamankan bukti fisik, audit forensik IT, dan klarifikasi menyeluruh kepada pihak-pihak terkait.') }}</p>
        </div>

        <p class="mt-3">
            Demikian memorandum ini kami sampaikan untuk menjadi periksa dan mohon arahan serta petunjuk Direksi lebih lanjut. Atas perhatian dan perkenan Bapak Direksi, kami haturkan terima kasih.
        </p>
    </div>

    <!-- Tanda Tangan & Kolom Disposisi Direksi -->
    <div class="row mt-4 pt-2">
        <div class="col-6">
            <div class="p-2 border rounded" style="min-height: 140px; font-size: 9.5pt;">
                <div class="fw-bold text-decoration-underline mb-1">DISPOSISI / ARAHAN DIREKSI:</div>
                <div class="text-muted small fst-italic">[ &nbsp; ] Setuju Rekomendasi Investigasi Khusus</div>
                <div class="text-muted small fst-italic">[ &nbsp; ] Pembinaan Cabang / Tindakan Perbaikan</div>
                <div class="text-muted small fst-italic mt-2">Catatan: ..............................................................</div>
                <div class="mt-4 pt-3 text-end fw-bold">Tanda Tangan Direksi: ....................</div>
            </div>
        </div>
        <div class="col-6 text-center">
            <div class="small text-muted mb-1">Palu, {{ $kasus->tanggal_memo ? date('d F Y', strtotime($kasus->tanggal_memo)) : date('d F Y') }}</div>
            <div class="fw-bold">DIVISI SATUAN KERJA AUDIT INTERN</div>
            <div class="small fw-semibold text-muted">Kepala Divisi,</div>
            <div style="height: 65px;"></div>
            <div class="fw-bold text-decoration-underline">BUDI SETIAWAN, SE., Ak., CA</div>
            <div class="small">NIP. 19780512 200312 1 002</div>
        </div>
    </div>

    <!-- Tembusan -->
    <div class="mt-3 pt-2 small text-muted border-top">
        <strong>Tembusan Yth:</strong>
        <ol class="mb-0 ps-3">
            <li>Dewan Komisaris PT. Bank Sulteng (sebagai laporan)</li>
            <li>Divisi Kepatuhan & Manajemen Risiko</li>
            <li>Arsip SKAI (Klasifikasi: Rahasia)</li>
        </ol>
    </div>
</body>
</html>

