@php
    $evalData = $eval ?? (isset($evalKas) ? $evalKas : ($kka ? $kka->getEvaluationSummary('kas') : []));
@endphp
@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Pemeriksaan Kas Khasanah — {{ $kka->kode_unit ?? '000' }}</title>
    <style>
        @page { size: portrait; margin: 12mm 15mm 15mm 15mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 15px;
        }
        .no-print-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #1e3a8a;
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 9pt;
            cursor: pointer;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 9pt;
            cursor: pointer;
            text-decoration: none;
        }
        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .header-table td { vertical-align: middle; }
        .bank-title { font-size: 14pt; font-weight: bold; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .bank-sub { font-size: 8pt; color: #64748b; margin: 2px 0 0 0; }
        .ba-title-box { text-align: center; margin: 15px 0; }
        .ba-title { font-size: 12pt; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .ba-no { font-size: 8.5pt; margin-top: 4px; color: #374151; }
        .info-table { width: 100%; margin-bottom: 12px; font-size: 8.5pt; border-collapse: collapse; }
        .info-table td { padding: 3px 6px; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; }
        .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 4px 8px; }
        .table-data th { background: #f8fafc; font-weight: bold; text-align: center; }
        .group-header { background: #e2e8f0; font-weight: bold; padding: 4px 8px; }
        .subtotal-row { background: #f1f5f9; font-weight: bold; }
        .total-summary-box { border: 1.5px solid #1e3a8a; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px; }
        .badge-status { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 8pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .signatures-table { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 10px; }
        .sign-space { height: 60px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Berita Acara Cash Opname Khasanah & Kas Besar (Sistem Olibs)</strong>
            <div style="font-size: 8pt; color: #64748b;">No. Arsip: {{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
            <a href="{{ route('daily-register.physical-kka.index', $kka->id) }}" class="btn-close">← Kembali ke Form</a>
        </div>
    </div>
@endif

    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <h1 class="bank-title">PT. BANK PEMBANGUNAN DAERAH SULTENG</h1>
                <p class="bank-sub">SATUAN KERJA AUDIT INTERN (SKAI) — RESIDENT AUDITOR REGIONAL</p>
                <p class="bank-sub">Kertas Kerja Audit (KKA) Fisik & Berita Acara Resmi</p>
            </td>
            <td style="width: 30%; text-align: right; font-size: 8pt;">
                <strong>NO. ARSIP:</strong><br>
                <span style="font-family: monospace; font-size: 9pt;">{{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</span><br>
                <span>Status: <strong>{{ strtoupper($kka->status_final ?? 'FINAL') }}</strong></span>
            </td>
        </tr>
    </table>

    <div class="ba-title-box">
        <h2 class="ba-title">BERITA ACARA PEMERIKSAAN KAS KHASANAH (OLIBS)</h2>
        <div class="ba-no">Kode Unit: <strong>{{ $kka->kode_unit }}</strong> | Tanggal: <strong>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</strong></div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 20%;"><strong>Unit / Cabang</strong></td>
            <td style="width: 30%;">: {{ $kka->kode_unit }} - {{ $namaCabang ?? 'Kantor Cabang Bank Sulteng' }}</td>
            <td style="width: 20%;"><strong>Tanggal Pemeriksaan</strong></td>
            <td style="width: 30%;">: {{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Pemeriksa (RA)</strong></td>
            <td>: {{ $kka->user ? $kka->user->name : 'Resident Auditor' }}</td>
            <td><strong>Waktu Pelaksanaan</strong></td>
            <td>: {{ date('H:i') }} WITA (Khasanah Ditutup)</td>
        </tr>
    </table>

    <!-- TABEL RINCIAN FISIK 3 KELOMPOK -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Kelompok & Pecahan Uang</th>
                <th style="width: 25%;">Jumlah (Lembar / Keping)</th>
                <th style="width: 35%;">Total Nominal Fisik (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- KELOMPOK 1: ULE -->
            <tr class="group-header">
                <td colspan="4">KELOMPOK 1: UANG KERTAS (ULE / LAYAK EDAR)</td>
            </tr>
            @php $subtotalUle = 0; $noUle = 1; @endphp
            @foreach($uleDetails as $d)
                @php $nom = (float)($d['nominal'] ?? ($d['hasil_fisik'] ?? 0)); $subtotalUle += $nom; @endphp
                <tr>
                    <td style="text-align: center;">{{ $noUle++ }}</td>
                    <td>{{ $d['pecahan'] }}</td>
                    <td style="text-align: right;">{{ number_format($d['jumlah_lembar'] ?? 0, 0, ',', '.') }} Lembar</td>
                    <td style="text-align: right; font-weight: 500;">Rp {{ number_format($nom, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="3" style="text-align: right;">Subtotal Uang Kertas Layak Edar (ULE):</td>
                <td style="text-align: right;">Rp {{ number_format($subtotalUle, 0, ',', '.') }}</td>
            </tr>

            <!-- KELOMPOK 2: UANG LOGAM -->
            <tr class="group-header">
                <td colspan="4">KELOMPOK 2: UANG LOGAM</td>
            </tr>
            @php $subtotalLogam = 0; $noLogam = 1; @endphp
            @foreach($logamDetails as $d)
                @php $nom = (float)($d['nominal'] ?? ($d['hasil_fisik'] ?? 0)); $subtotalLogam += $nom; @endphp
                <tr>
                    <td style="text-align: center;">{{ $noLogam++ }}</td>
                    <td>{{ $d['pecahan'] }}</td>
                    <td style="text-align: right;">{{ number_format($d['jumlah_lembar'] ?? 0, 0, ',', '.') }} Kpg</td>
                    <td style="text-align: right; font-weight: 500;">Rp {{ number_format($nom, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="3" style="text-align: right;">Subtotal Uang Logam:</td>
                <td style="text-align: right;">Rp {{ number_format($subtotalLogam, 0, ',', '.') }}</td>
            </tr>

            <!-- KELOMPOK 3: UTLE -->
            <tr class="group-header">
                <td colspan="4">KELOMPOK 3: UANG LUSUH (UTLE / TIDAK LAYAK EDAR)</td>
            </tr>
            @php $subtotalUtle = 0; $noUtle = 1; @endphp
            @foreach($utleDetails as $d)
                @php $nom = (float)($d['nominal'] ?? ($d['hasil_fisik'] ?? 0)); $subtotalUtle += $nom; @endphp
                <tr>
                    <td style="text-align: center;">{{ $noUtle++ }}</td>
                    <td>{{ $d['pecahan'] }}</td>
                    <td style="text-align: right;">{{ number_format($d['jumlah_lembar'] ?? 0, 0, ',', '.') }} Lembar</td>
                    <td style="text-align: right; font-weight: 500;">Rp {{ number_format($nom, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="subtotal-row">
                <td colspan="3" style="text-align: right;">Subtotal Uang Lusuh (UTLE):</td>
                <td style="text-align: right;">Rp {{ number_format($subtotalUtle, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- RINGKASAN & REKONSILIASI KAS OLIBS -->
    @php
        $totalFisikKas = $subtotalUle + $subtotalLogam + $subtotalUtle;
        $saldoOlibs = (float)($kka->saldo_kas_olibs ?? 0);
        $selisihKas = $totalFisikKas - $saldoOlibs;
    @endphp
    <div class="total-summary-box">
        <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; padding: 3px 0;"><strong>A. Total Saldo Fisik Kas Khasanah (1 + 2 + 3):</strong></td>
                <td style="width: 40%; text-align: right; font-size: 10pt; font-weight: bold; color: #1e3a8a;">Rp {{ number_format($totalFisikKas, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;"><strong>B. Saldo Menurut Kas Besar Sistem Olibs:</strong></td>
                <td style="text-align: right; font-size: 10pt; font-weight: bold;">Rp {{ number_format($saldoOlibs, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-top: 1px dashed #cbd5e1;">
                <td style="padding: 5px 0;"><strong>C. Selisih Kas (Fisik - Sistem Olibs):</strong></td>
                <td style="text-align: right; font-size: 10.5pt; font-weight: bold;">
                    @if(abs($selisihKas) < 0.01)
                        <span class="badge-status badge-success">NIHIL / SESUAI (Rp 0)</span>
                    @elseif($selisihKas > 0)
                        <span class="badge-status badge-danger">ADA SELISIH LEBIH (+Rp {{ number_format(abs($selisihKas), 0, ',', '.') }})</span>
                    @else
                        <span class="badge-status badge-danger">ADA SELISIH KURANG (-Rp {{ number_format(abs($selisihKas), 0, ',', '.') }})</span>
                    @endif
                </td>
            </tr>
            @if(abs($selisihKas) >= 0.01 || !empty($kka->penjelasan_selisih_kas))
            <tr>
                <td colspan="2" style="padding-top: 8px; font-size: 8.5pt;">
                    <strong>Penjelasan Penyebab Selisih:</strong><br>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 6px 10px; border-radius: 4px; margin-top: 4px; color: #991b1b;">
                        {{ $kka->penjelasan_selisih_kas ?? 'Belum ada uraian penjelasan selisih.' }}
                    </div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- BLOK EVALUASI & PENILAIAN RISIKO TUNGGAL -->
    <div class="section-box" style="border: 1px solid #2563eb; margin-top: 15px; margin-bottom: 15px; border-radius: 4px; overflow: hidden; page-break-inside: avoid;">
        <div style="background: #1e40af; color: #fff; padding: 6px 10px; font-weight: bold; font-size: 8.5pt; display: flex; justify-content: space-between;">
            <span>RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KAS KHASANAH</span>
            <span>Berlaku Menyeluruh untuk Halaman Kas Khasanah</span>
        </div>
        <div style="background: #f8fafc; padding: 8px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 8px;">
                <tr>
                    <td style="width: 22%; font-weight: bold; padding: 4px; border-bottom: 1px solid #e2e8f0;">Tanggal Uji:</td>
                    <td style="width: 78%; padding: 4px; border-bottom: 1px solid #e2e8f0;">
                        {{ \Carbon\Carbon::parse($evalData['tgl_uji'] ?? now())->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 4px; vertical-align: top; border-bottom: 1px solid #e2e8f0;">Penyebab &amp; Keterangan:</td>
                    <td style="padding: 4px; border-bottom: 1px solid #e2e8f0; white-space: pre-line;">
                        {{ $evalData['penyebab_keterangan'] ?: ($kka->penjelasan_selisih_kas ?: 'Nihil / Seluruh saldo fisik kas khasanah sesuai dengan catatan sistem.') }}
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; font-size: 8pt; text-align: center; border: 1px solid #cbd5e1; background: #fff;">
                <thead>
                    <tr style="background: #e2e8f0; font-weight: bold;">
                        <th style="padding: 5px; border: 1px solid #cbd5e1; width: 20%;">Impact (1-5)</th>
                        <th style="padding: 5px; border: 1px solid #cbd5e1; width: 20%;">Likelihood (1-5)</th>
                        <th style="padding: 5px; border: 1px solid #cbd5e1; width: 20%;">Critical</th>
                        <th style="padding: 5px; border: 1px solid #cbd5e1; width: 20%;">Risk Score</th>
                        <th style="padding: 5px; border: 1px solid #cbd5e1; width: 20%;">Risk Level</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 6px; font-weight: bold; border: 1px solid #cbd5e1;">{{ $evalData['impact'] ?? 1 }}</td>
                        <td style="padding: 6px; font-weight: bold; border: 1px solid #cbd5e1;">{{ $evalData['likelihood'] ?? 1 }}</td>
                        <td style="padding: 6px; font-weight: bold; border: 1px solid #cbd5e1;">
                            <span class="badge-status {{ ($evalData['is_critical'] ?? false) ? 'badge-danger' : 'badge-success' }}">
                                {{ ($evalData['is_critical'] ?? false) ? 'Ya' : 'Tidak' }}
                            </span>
                        </td>
                        <td style="padding: 6px; font-weight: bold; border: 1px solid #cbd5e1;">{{ $evalData['risk_score'] ?? 1 }}</td>
                        <td style="padding: 6px; font-weight: bold; border: 1px solid #cbd5e1;">
                            @php
                                $lvl = $evalData['risk_level'] ?? 'Low';
                                $lvlClass = match($lvl) {
                                    'Low' => 'badge-success',
                                    'Low to Moderate' => 'badge-info',
                                    'Moderate' => 'badge-warning',
                                    'Moderate to High' => 'badge-danger',
                                    'High' => 'badge-danger',
                                    default => 'badge-success'
                                };
                            @endphp
                            <span class="badge-status {{ $lvlClass }}">{{ $lvl }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TANDA TANGAN BERITA ACARA -->
    <table class="signatures-table">
        <tr>
            <td>
                <strong>Pihak Pelaksana Pemeriksaan (RA):</strong>
                <div class="sign-space"></div>
                <strong>{{ $kka->kas_pelaksana_nama ?: ($kka->user ? $kka->user->name : 'Resident Auditor') }}</strong><br>
                <span style="font-size: 8pt; color: #64748b;">{{ $kka->kas_pelaksana_jabatan ?: 'Resident Auditor SKAI' }}</span>
            </td>
            <td>
                <strong>Pihak Saksi / Petugas Khasanah:</strong>
                <div class="sign-space"></div>
                <strong>{{ $kka->kas_saksi_nama ?: 'Supervisor Operasional / Pemegang Khasanah' }}</strong><br>
                <span style="font-size: 8pt; color: #64748b;">{{ $kka->kas_saksi_jabatan ?: 'Pemegang Kunci Khasanah' }}</span>
            </td>
        </tr>
    </table>

@if(!isset($isDossier) || !$isDossier)
</body>
</html>
@endif
