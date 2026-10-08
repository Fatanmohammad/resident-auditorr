<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permintaan Data — {{ $visit->kode_unit }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000; background: #fff; }

        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm 25mm 20mm 30mm; }

        /* KOP SURAT */
        .kop { display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop img { width: 70px; height: 70px; object-fit: contain; margin-right: 15px; }
        .kop-text { flex: 1; text-align: center; }
        .kop-text .nama-bank { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-text .alamat { font-size: 9pt; color: #333; margin-top: 3px; }

        /* NOMOR SURAT */
        .nomor-surat { margin-bottom: 20px; font-size: 11pt; }
        .nomor-surat table td { padding: 2px 0; vertical-align: top; }
        .nomor-surat table td:first-child { width: 130px; }
        .nomor-surat table td:nth-child(2) { width: 10px; text-align: center; }

        /* JUDUL */
        .judul { text-align: center; margin: 20px 0; }
        .judul h2 { font-size: 13pt; text-transform: uppercase; text-decoration: underline; letter-spacing: 1px; }

        /* PEMBUKA */
        .pembuka { margin-bottom: 15px; line-height: 1.8; }
        .pembuka .kepada { margin: 10px 0 5px 0; }
        .pembuka .kepada table td { padding: 1px 0; vertical-align: top; }
        .pembuka .kepada table td:first-child { width: 130px; }
        .pembuka .kepada table td:nth-child(2) { width: 10px; text-align: center; }

        /* TABEL DOKUMEN */
        .tabel-dokumen { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11pt; }
        .tabel-dokumen th { background: #f0f0f0; border: 1px solid #000; padding: 6px 8px; text-align: center; font-weight: bold; }
        .tabel-dokumen td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        .tabel-dokumen td:first-child { text-align: center; width: 35px; }
        .tabel-dokumen td:nth-child(3) { width: 120px; }

        /* PENUTUP */
        .penutup { margin-top: 15px; line-height: 1.8; }
        .catatan { margin: 10px 0; font-style: italic; font-size: 10.5pt; }

        /* TTD */
        .ttd { margin-top: 30px; display: flex; justify-content: space-between; }
        .ttd-box { text-align: center; width: 200px; }
        .ttd-box .ttd-label { font-size: 11pt; }
        .ttd-box .ttd-space { height: 70px; }
        .ttd-box .ttd-nama { font-weight: bold; text-decoration: underline; font-size: 11pt; }
        .ttd-box .ttd-jabatan { font-size: 10pt; }

        /* PRINT */
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .page { padding: 15mm 20mm 15mm 25mm; }
        }

        /* TOMBOL */
        .btn-print-bar { position: fixed; top: 20px; right: 20px; display: flex; gap: 10px; z-index: 999; }
        .btn-print { background: #1e3a8a; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-back { background: #64748b; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>

{{-- Tombol aksi (tidak ikut print) --}}
<div class="btn-print-bar no-print">
    <a href="{{ route('onsite.permintaan.create', $visit->id) }}" class="btn-back">
        <i>&#8592;</i> Edit
    </a>
    <button class="btn-print" onclick="window.print()">
        &#128438; Cetak Surat
    </button>
</div>

<div class="page">

    {{-- KOP SURAT --}}
    <div class="kop">
        <img src="{{ asset('img/logo.png') }}" alt="Logo">
        <div class="kop-text">
            <div class="nama-bank">PT. Bank Sulteng</div>
            <div class="alamat">Jl. Sam Ratulangi No. 12, Palu, Sulawesi Tengah &nbsp;|&nbsp; Telp. (0451) 421234</div>
            <div class="alamat">Satuan Kerja Audit Intern (SKAI)</div>
        </div>
    </div>

    {{-- NOMOR SURAT --}}
    <div class="nomor-surat">
        <table>
            <tr>
                <td>Nomor</td>
                <td>:</td>
                <td><strong>{{ $permintaan->nomor_surat }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($permintaan->tanggal_surat)->isoFormat('D MMMM YYYY') }}</td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>:</td>
                <td>Segera</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>:</td>
                <td>—</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td><strong>Permintaan Data dalam rangka Audit Onsite</strong></td>
            </tr>
        </table>
    </div>

    {{-- KEPADA --}}
    <div class="pembuka">
        <p>Kepada Yth.</p>
        <div class="kepada">
            <table>
                <tr>
                    <td>{{ $permintaan->kepada }}</td>
                </tr>
                @if($permintaan->jabatan_kepada)
                <tr>
                    <td>{{ $permintaan->jabatan_kepada }}</td>
                </tr>
                @endif
                <tr>
                    <td><strong>{{ $visit->nama_unit }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- JUDUL --}}
    <div class="judul">
        <h2>Surat Permintaan Data</h2>
    </div>

    {{-- ISI SURAT --}}
    <div class="pembuka">
        <p>Dengan hormat,</p>
        <br>
        <p>
            Dalam rangka pelaksanaan <strong>Audit Onsite</strong> pada
            <strong>{{ $visit->nama_unit }} (Kode Unit: {{ $visit->kode_unit }})</strong>
            yang dijadwalkan pada tanggal
            <strong>{{ \Carbon\Carbon::parse($visit->tanggal_mulai)->isoFormat('D MMMM YYYY') }}</strong>
            s/d
            <strong>{{ \Carbon\Carbon::parse($visit->tanggal_selesai)->isoFormat('D MMMM YYYY') }}</strong>,
            kami mohon kesediaan Saudara untuk menyiapkan dan menyerahkan data/dokumen berikut:
        </p>
    </div>

    {{-- TABEL DOKUMEN --}}
    <table class="tabel-dokumen">
        <thead>
            <tr>
                <th>No.</th>
                <th>Jenis Dokumen / Data yang Diminta</th>
                <th>Periode Data</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permintaan->items as $item)
            <tr>
                <td>{{ $item->urut }}</td>
                <td>{{ $item->jenis_dokumen }}</td>
                <td>{{ $item->periode_data ?? '—' }}</td>
                <td>{{ $item->keterangan ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- PENUTUP --}}
    <div class="penutup">
        <p>
            Kami mohon data/dokumen tersebut dapat disiapkan sebelum atau pada saat tim audit tiba.
            Atas perhatian dan kerja sama Saudara, kami ucapkan terima kasih.
        </p>
        @if($permintaan->catatan)
        <p class="catatan">Catatan: {{ $permintaan->catatan }}</p>
        @endif
    </div>

    {{-- TANDA TANGAN --}}
    <div class="ttd">
        <div class="ttd-box">
            <div class="ttd-label">Mengetahui,</div>
            <div class="ttd-label">Kepala SKAI</div>
            <div class="ttd-space"></div>
            <div class="ttd-nama">( _________________________ )</div>
            <div class="ttd-jabatan">Kepala Satuan Kerja Audit Intern</div>
        </div>
        <div class="ttd-box">
            <div class="ttd-label">Palu, {{ \Carbon\Carbon::parse($permintaan->tanggal_surat)->isoFormat('D MMMM YYYY') }}</div>
            <div class="ttd-label">Resident Auditor,</div>
            <div class="ttd-space"></div>
            <div class="ttd-nama">( _________________________ )</div>
            <div class="ttd-jabatan">Resident Auditor</div>
        </div>
    </div>

</div>

</body>
</html>
