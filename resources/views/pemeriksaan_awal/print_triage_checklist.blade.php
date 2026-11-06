<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kertas Kerja Fraud Triage — {{ $checklist->nomor_triage ?? 'POJK-12' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; }
        .header-title { font-size: 13pt; font-weight: bold; text-align: center; text-transform: uppercase; }
        .header-sub { font-size: 10pt; text-align: center; }
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-custom th, .table-custom td { border: 1px solid #000; padding: 4px 6px; font-size: 9.5pt; vertical-align: top; }
        .table-custom th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .badge-triage { display: inline-block; padding: 2px 8px; border: 1px solid #000; font-weight: bold; font-size: 9pt; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            @page { size: portrait; margin: 1.2cm; }
        }
    </style>
</head>
<body class="p-4">

    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
            Cetak Dokumen / Simpan PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">
            Tutup
        </button>
    </div>

    <!-- Kop Header -->
    <div class="border-bottom pb-2 mb-3">
        <div class="header-title">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</div>
        <div class="header-title" style="font-size: 12pt;">SATUAN KERJA AUDIT INTERN (SKAI) &bull; RESIDENT AUDITOR</div>
        <div class="header-title" style="font-size: 12pt; margin-top: 4px; text-decoration: underline;">
            FRAUD TRIAGE CHECKLIST – RESIDENT AUDITOR
        </div>
        <div class="header-sub">
            Instrumen Pemeriksaan Awal Fraud &bull; Kantor Cabang Bank Umum &bull; Berdasarkan POJK No. 12 Tahun 2024
        </div>
    </div>

    <!-- Metadata Pemeriksaan -->
    <table style="width: 100%; margin-bottom: 12px; font-size: 10pt;">
        <tr>
            <td style="width: 20%; font-weight: bold;">Nomor Kertas Kerja</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;">{{ $checklist->nomor_triage ?? 'TRG-202611-0001' }}</td>
            <td style="width: 18%; font-weight: bold;">Tanggal Pemeriksaan</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ date('d F Y', strtotime($checklist->tanggal_pemeriksaan ?? now())) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Cabang Diperiksa</td>
            <td>:</td>
            <td>{{ $checklist->nama_cabang ?? '-' }}</td>
            <td style="font-weight: bold;">Nama Resident Auditor</td>
            <td>:</td>
            <td>{{ $checklist->nama_ra ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Hasil Keputusan Triage</td>
            <td>:</td>
            <td colspan="4">
                <span class="badge-triage">{{ $checklist->keputusan_triage ?? 'KLARIFIKASI TAMBAHAN' }}</span>
                &bull; Dijawab Ya: <strong>{{ $checklist->dijawab_ya ?? 0 }}</strong> &bull; Dijawab Tidak: <strong>{{ $checklist->dijawab_tidak ?? 0 }}</strong>
            </td>
        </tr>
    </table>

    <div style="font-size: 8.5pt; font-style: italic; border: 1px dashed #666; padding: 4px 8px; margin-bottom: 10px;">
        ⚠ PERHATIAN: Checklist ini adalah instrumen triage awal, bukan instrumen investigasi. Jawaban 'Ya' menunjukkan indikasi yang perlu ditelusuri, bukan kesimpulan telah terjadi fraud.
    </div>

    <!-- Tabel 28 Pertanyaan -->
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 48%;">Deskripsi Pertanyaan &amp; Indikator</th>
                <th style="width: 12%;">Jawaban</th>
                <th style="width: 20%;">Catatan / Observasi RA</th>
                <th style="width: 15%;">Dasar Regulasi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $savedMap = [];
                if (isset($checklist->checklist_answers) && is_array($checklist->checklist_answers)) {
                    foreach ($checklist->checklist_answers as $ans) {
                        $savedMap[$ans['no']] = $ans;
                    }
                }
                $currentSection = '';
            @endphp

            @foreach($masterQuestions as $q)
                @if($currentSection !== $q['section'])
                    @php $currentSection = $q['section']; @endphp
                    <tr style="background-color: #eee;">
                        <td colspan="5" style="font-weight: bold; font-size: 9pt;">
                            {{ $currentSection }}
                        </td>
                    </tr>
                @endif

                @php
                    $saved = $savedMap[$q['no']] ?? null;
                    $val = $saved['jawaban'] ?? 'Tidak';
                    $cat = $saved['catatan'] ?? '-';
                    $isCritical = !empty($q['is_critical']);
                @endphp

                <tr>
                    <td style="text-align: center;">{{ $q['no'] }}</td>
                    <td>
                        @if($isCritical) <strong>[⚡ KRITIS]</strong> @endif
                        {!! $q['question'] !!}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $val ?: '-' }}
                    </td>
                    <td>{{ $cat ?: '-' }}</td>
                    <td style="font-size: 8pt; color: #333;">{{ $q['regulasi'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Kesimpulan dan Rekomendasi RA -->
    <div style="margin-top: 15px; border: 1px solid #000; padding: 8px;">
        <div style="font-weight: bold; text-decoration: underline; margin-bottom: 4px;">
            CATATAN SIMPULAN &amp; REKOMENDASI TINDAK LANJUT RA:
        </div>
        <div style="font-size: 9.5pt; text-align: justify;">
            {{ $checklist->catatan_kesimpulan ?: 'Berdasarkan hasil pemeriksaan awal instrumen triage POJK 12/2024, seluruh indikator telah dievaluasi dan dicatatkan sesuai fakta lapangan.' }}
        </div>
    </div>

    <!-- Tanda Tangan -->
    <table style="width: 100%; margin-top: 30px; font-size: 10pt;">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                <strong>Kepala Bagian RA Korwas</strong><br><br><br><br>
                ( .................................................... )<br>
                NIP.
            </td>
            <td style="width: 50%; text-align: center;">
                Palu, {{ date('d F Y', strtotime($checklist->tanggal_pemeriksaan ?? now())) }}<br>
                <strong>Resident Auditor Pemeriksa</strong><br><br><br><br>
                ( <strong>{{ $checklist->nama_ra ?? 'Resident Auditor' }}</strong> )<br>
                NIP.
            </td>
        </tr>
    </table>

</body>
</html>
