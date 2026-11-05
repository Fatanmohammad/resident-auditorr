<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Konsolidasi Bulanan SOP 08 - {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 20px;
        }
        .header-print {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .title-main {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 10pt;
            font-weight: 600;
            color: #475569;
        }
        .section-header {
            font-size: 10pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background: #f1f5f9;
            color: #0f172a;
            border-left: 4px solid #1e3a8a;
            padding: 5px 10px;
            margin-top: 18px;
            margin-bottom: 8px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        .table-data th, .table-data td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: middle;
        }
        .table-data th {
            background-color: #f8fafc;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f172a;
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .badge-hijau {
            color: #15803d;
            font-weight: 700;
        }
        .badge-merah {
            color: #b91c1c;
            font-weight: 700;
        }
        .badge-ulang {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 700;
            font-size: 7.5pt;
            padding: 1px 4px;
            border-radius: 3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .wysiwyg-content {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 9pt;
            line-height: 1.5;
            background: #ffffff;
            margin-bottom: 12px;
        }
        @media print {
            body {
                padding: 0;
                font-size: 9pt;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    {{-- Print Action Bar --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-4 p-2 bg-light border rounded">
        <div>
            <strong>Mode Cetak Dokumen / Export PDF</strong>
            <span class="text-muted ms-2">(Klik tombol cetak untuk menyimpan sebagai PDF atau kirim ke printer)</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('pelaporan-bulanan.konsolidasi', ['periode' => $periode]) }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Konsolidasi
            </a>
        </div>
    </div>

    {{-- Formal Header Bank Sulteng SKAI --}}
    <div class="header-print d-flex justify-content-between align-items-center">
        <div>
            <div class="title-main">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</div>
            <div class="subtitle">SATUAN KERJA AUDIT INTERN (SKAI) &bull; BAGIAN RESIDENT AUDITOR</div>
            <div class="small text-muted mt-1">Laporan Bulanan Konsolidasi Pengawasan Cabang (SOP 08)</div>
        </div>
        <div class="text-end">
            <div class="badge bg-dark text-white p-2 text-uppercase font-monospace" style="font-size: 8.5pt;">
                WP-RA-08-KONSOLIDASI
            </div>
            <div class="small fw-bold text-secondary mt-1">
                Periode: {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}
            </div>
        </div>
    </div>

    {{-- 1. Catatan & Analisis Eksekutif Admin --}}
    <div class="section-header">1. CATATAN &amp; ANALISIS EKSEKUTIF KONSOLIDASI</div>
    <div class="wysiwyg-content">
        @if($activeNarasi && !empty($activeNarasi->catatan_eksekutif))
            {!! $activeNarasi->catatan_eksekutif !!}
        @elseif($activeNarasi && !empty($activeNarasi->ringkasan_eksekutif))
            {!! nl2br(e($activeNarasi->ringkasan_eksekutif)) !!}
        @else
            <span class="text-muted fst-italic">Seluruh kegiatan pengawasan bulanan Resident Auditor cabang dilaksanakan sesuai SOP 08 dan program kerja audit tahunan. Rekapitulasi kuantitatif kejadian disajikan pada tabel matriks konsolidasi berikut.</span>
        @endif
    </div>

    {{-- 2. Tabel Matriks Konsolidasi Bulanan Seluruh Cabang --}}
    <div class="section-header">2. TABEL MATRIKS KONSOLIDASI BULANAN SELURUH CABANG</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25%;">Nama / Kode Cabang</th>
                <th style="width: 15%;">Status Kepatuhan KKA</th>
                <th style="width: 12%;">Jumlah Reversal</th>
                <th style="width: 12%;">Jumlah Selisih Kas</th>
                <th style="width: 12%;">Jumlah Pelampauan Limit</th>
                <th style="width: 12%;">Temuan Mod-to-High</th>
                <th style="width: 12%;">Status Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totRev = 0; $totSel = 0; $totLim = 0; $totHigh = 0; $totOd = 0;
            @endphp
            @forelse($matrixRows as $row)
            @php
                $rev = $row['reversal_count'] ?? 0;
                $sel = $row['selisih_kas_count'] ?? 0;
                $lim = $row['limit_biaya_count'] ?? 0;
                $high = $row['mod_to_high_count'] ?? $row['temuan_mod_high_count'] ?? 0;
                $od = $row['tl_overdue_count'] ?? 0;
                $act = $row['tl_active_count'] ?? $row['tl_aktif_count'] ?? 0;
                $kep = $row['kepatuhan_status'] ?? $row['status_kepatuhan'] ?? 'MERAH';

                $totRev += $rev;
                $totSel += $sel;
                $totLim += $lim;
                $totHigh += $high;
                $totOd += $od;
            @endphp
            <tr>
                <td><strong>[{{ $row['kode_unit'] ?? '' }}]</strong> {{ $row['nama_unit'] ?? '' }}</td>
                <td class="text-center">
                    @if($kep === 'HIJAU')
                        <span class="badge-hijau">&bull; LENGKAP / TUNTAS</span>
                    @else
                        <span class="badge-merah">&bull; BELUM SELESAI</span>
                    @endif
                </td>
                <td class="text-center font-monospace">{{ $rev }}</td>
                <td class="text-center font-monospace">{{ $sel }}</td>
                <td class="text-center font-monospace">{{ $lim }}</td>
                <td class="text-center font-monospace {{ $high > 0 ? 'fw-bold text-danger' : '' }}">{{ $high }}</td>
                <td class="text-center font-monospace">
                    @if($od > 0)
                        <span class="text-danger fw-bold">{{ $od }} Overdue</span>
                    @elseif($act > 0)
                        <span>{{ $act }} Aktif</span>
                    @else
                        <span class="text-muted">Nihil</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-2 text-muted">Tidak ada data matriks cabang.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($matrixRows) > 0)
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 700;">
                <td class="text-end">TOTAL KONSOLIDASI:</td>
                <td class="text-center">{{ collect($matrixRows)->where('kepatuhan_status', 'HIJAU')->count() }} Hijau / {{ collect($matrixRows)->where('kepatuhan_status', 'MERAH')->count() }} Merah</td>
                <td class="text-center font-monospace">{{ $totRev }}</td>
                <td class="text-center font-monospace">{{ $totSel }}</td>
                <td class="text-center font-monospace">{{ $totLim }}</td>
                <td class="text-center font-monospace text-danger">{{ $totHigh }}</td>
                <td class="text-center font-monospace text-danger">{{ $totOd }} Overdue</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- 3. Hasil Utama & Temuan Kunci --}}
    <div class="section-header">3. HASIL UTAMA &amp; TEMUAN KUNCI (MODERATE TO HIGH &amp; HIGH)</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 22%;">Cabang &amp; Area KKA</th>
                <th style="width: 45%;">Uraian Penjelasan Temuan &amp; Eksposur Nominal</th>
                <th style="width: 33%;">Rekomendasi / Rencana Tindak Lanjut &amp; PIC</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilUtamaRows as $t)
            <tr>
                <td class="align-top">
                    <strong>[{{ $t['kode_unit'] }}] {{ $t['nama_unit'] }}</strong><br>
                    <small class="text-muted">Area: {{ $t['area'] }}</small><br>
                    <span class="badge {{ $t['risk_level'] === 'HIGH' ? 'bg-danger text-white' : 'bg-warning text-dark' }}" style="font-size: 7.5pt;">
                        RISIKO: {{ $t['risk_level'] }}
                    </span>
                </td>
                <td class="align-top">
                    @if($t['is_temuan_berulang'])
                        <div class="mb-1">
                            <span class="badge-ulang">[TEMUAN BERULANG]</span>
                        </div>
                    @endif
                    <div>{!! nl2br(e($t['uraian'])) !!}</div>
                    @if($t['nominal'] > 0)
                        <div class="text-danger fw-bold mt-1" style="font-size: 8pt;">
                            Nominal Eksposur: {{ $t['nominal_formatted'] }}
                        </div>
                    @endif
                </td>
                <td class="align-top">
                    <div>{!! nl2br(e($t['rekomendasi'] ?: '-')) !!}</div>
                    <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 7.5pt;">
                        PIC: <strong>{{ $t['pic'] ?: '-' }}</strong> | Target: <strong>{{ $t['target_date'] ? \Carbon\Carbon::parse($t['target_date'])->format('d/m/Y') : '-' }}</strong>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center py-2 text-muted fst-italic">
                    Tidak ada temuan berskala Moderate to High atau High pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 4. Rekap Aktivitas & Log Harian Terintegrasi --}}
    <div class="section-header">4. REKAP MONITORING TINDAK LANJUT &amp; AKTIVITAS PENGAWASAN HARIAN TERINTEGRASI</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 20%;">Cabang</th>
                <th style="width: 25%;">Jenis Pengujian / Aktivitas</th>
                <th style="width: 33%;">Uraian Ringkas Pelaksanaan</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse(array_slice($unifiedActivities, 0, 50) as $act)
            <tr>
                <td class="text-center font-monospace">{{ $act['tanggal'] ?? (!empty($act['tanggal_raw']) ? \Carbon\Carbon::parse($act['tanggal_raw'])->format('d/m/Y') : '-') }}</td>
                <td>[{{ $act['kode_unit'] }}] {{ $act['nama_unit'] }}</td>
                <td class="fw-semibold">{{ $act['kegiatan'] ?? $act['jenis_aktivitas'] ?? '-' }}</td>
                <td class="text-muted">{{ $act['detail'] ?? $act['output_wp'] ?? $act['area'] ?? '-' }}</td>
                <td class="text-center"><span class="badge-hijau">{{ $act['status'] ?? $act['status_penyelesaian'] ?? 'COMPLETED' }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-2 text-muted">Belum ada aktivitas terekam untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 5. Rencana Bulan Berikutnya --}}
    <div class="section-header">5. RENCANA KERJA PENGAWASAN BULAN BERIKUTNYA</div>
    <div class="wysiwyg-content">
        @if($activeNarasi && !empty($activeNarasi->rencana_bulan_depan))
            {!! $activeNarasi->rencana_bulan_depan !!}
        @elseif($activeNarasi && !empty($activeNarasi->rencana_bulan_berikut))
            {!! nl2br(e($activeNarasi->rencana_bulan_berikut)) !!}
        @else
            <span class="text-muted fst-italic">Melanjutkan pengawasan rutin harian, monitoring tindak lanjut temuan berulang, rekonsiliasi kas dan ATM, serta pengawasan kepatuhan batas limit cabang.</span>
        @endif
    </div>

    {{-- Note: Signature block (TTD) intentionally removed per Requirement 5 --}}
</body>
</html>
