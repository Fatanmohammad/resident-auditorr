@php
    $evalData = $eval ?? (isset($evalStok) ? $evalStok : ($kka ? $kka->getEvaluationSummary('stok') : []));
@endphp

@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKA Rekonsiliasi Stok Cek / Bilyet Giro — {{ $kka->kode_unit ?? '000' }}</title>
    <style>
        @page { size: landscape; margin: 8mm 10mm 10mm 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 8px;
        }
        .no-print-bar {
            background: #1e3a8a;
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #22c55e;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            font-weight: 700;
            font-size: 8.5pt;
            cursor: pointer;
        }
        .btn-close {
            background: #334155;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 8pt;
            cursor: pointer;
            text-decoration: none;
        }
        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 5px; margin-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .bank-title { font-size: 12pt; font-weight: bold; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .bank-sub { font-size: 7.5pt; color: #64748b; margin: 2px 0 0 0; }
        .kka-title-box { text-align: center; margin: 8px 0; }
        .kka-title { font-size: 11pt; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .kka-no { font-size: 8pt; margin-top: 3px; color: #374151; }

        .section-box {
            border: 1.5px solid #1e3a8a;
            border-radius: 6px;
            margin-bottom: 12px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .section-header {
            background: #1e3a8a;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 8.5pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-body { padding: 8px; }

        table.matriks { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-bottom: 6px; }
        table.matriks th, table.matriks td { border: 1px solid #94a3b8; padding: 4px 6px; vertical-align: middle; }
        table.matriks th { background: #f8fafc; font-weight: bold; text-align: center; }

        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

        .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; font-size: 8pt; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 5px; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong style="color: #fff; font-size: 9.5pt;">KKA Rekonsiliasi Stok Cek / Bilyet Giro (KKA_Rekon_Stok_Cek_BG)</strong>
            <span style="margin-left: 10px; font-size: 8pt; color: #cbd5e1;">No. Arsip: {{ $kka->no_arsip ?? 'ARSIP-STOK-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') }}</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Formulir (Print / PDF)</button>
            <a href="{{ route('daily-register.physical-kka.index', $kka->id) }}" class="btn-close">← Kembali ke Form</a>
        </div>
    </div>
@endif

    <!-- SECTION CONTENT -->
    <table class="header-table">
        <tr>
            <td style="width: 65%;">
                <h1 class="bank-title">PT. BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h1>
                <p class="bank-sub">SATUAN KERJA AUDIT INTERN (SKAI) — RESIDENT AUDITOR</p>
                <p class="bank-sub">Kantor Cabang: <strong>{{ $namaCabang ?? ('Cabang ' . ($kka->kode_unit ?? '000')) }}</strong> (Kode: {{ $kka->kode_unit ?? '000' }})</p>
            </td>
            <td style="width: 35%; text-align: right; font-size: 7.5pt; color: #475569;">
                <div><strong>KKA_Rekon_Stok_Cek_BG</strong></div>
                <div>Tanggal Uji: <strong>{{ \Carbon\Carbon::parse($evalData['tgl_uji'] ?? ($kka->posisi_tanggal_rekon ?? now()))->translatedFormat('d F Y') }}</strong></div>
                <div>Status: <span class="badge-status {{ ($kka->status_final ?? '') === 'Final' ? 'badge-success' : 'badge-warning' }}">{{ $kka->status_final ?? 'Draft' }}</span></div>
            </td>
        </tr>
    </table>

    <div class="kka-title-box">
        <h2 class="kka-title">KERTAS KERJA REKONSILIASI STOK CEK &amp; BILYET GIRO</h2>
        <div class="kka-no">No. Berkas: {{ $kka->no_arsip ?? ('ARSIP-STOK-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd')) }} | Posisi Tanggal Rekon: {{ $kka->posisi_tanggal_rekon ?? date('Y-m-d') }}</div>
    </div>

    <div class="section-box">
        <div class="section-header">
            <span>HASIL REKONSILIASI FISIK STOK DOKUMEN &amp; WARKAT BERHARGA</span>
            <span style="font-size: 7.5pt; font-weight: normal;">Diperiksa oleh: {{ $kka->user->name ?? 'Resident Auditor' }}</span>
        </div>
        <div class="section-body">
            <table class="matriks">
                <thead>
                    <tr>
                        <th style="width: 4%;">NO</th>
                        <th style="width: 15%;">SUB AREA / KATEGORI</th>
                        <th style="width: 45%;">KRITERIA / JENIS WARKAT</th>
                        <th style="width: 12%;">SALDO BUKU</th>
                        <th style="width: 12%;">JUMLAH FISIK</th>
                        <th style="width: 12%;">SELISIH</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $stkList = isset($stokItems) ? $stokItems : collect();
                        $counter = 1;
                    @endphp
                    @forelse($stkList as $idx => $row)
                        @php
                            $subArea = is_array($row) ? ($row['sub_area'] ?? '') : ($row->sub_area ?? '');
                            $objek = is_array($row) ? (($row['objek'] ?? '') . (isset($row['pecahan']) ? ' | ' . $row['pecahan'] : '')) : ($row->kondisi_objek ?? '');
                            $isCtrl = ($subArea === 'Kontrol');
                            $saldoSistem = is_array($row) ? (float)($row['saldo_sistem'] ?? 0) : (float)($row->saldo_sistem ?? 0);
                            $hasilFisik = is_array($row) ? (float)($row['hasil_fisik'] ?? 0) : (float)($row->hasil_fisik ?? 0);
                            $selisih = $isCtrl ? 0 : ($saldoSistem - $hasilFisik);
                            $statusUji = is_array($row) ? ($row['status_uji'] ?? ($isCtrl ? 'Ya' : 'Sesuai')) : ($row->status_uji ?: ($isCtrl ? 'Ya' : 'Sesuai'));
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $counter++ }}</td>
                            <td><span style="font-weight: 600;">{{ $subArea }}</span></td>
                            <td>{{ $objek }}</td>
                            <td style="text-align: right;">
                                {{ $isCtrl ? '-' : number_format($saldoSistem, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 500;">
                                @if($isCtrl)
                                    <span class="badge-status {{ $statusUji === 'Tidak' ? 'badge-danger' : 'badge-success' }}">
                                        {{ $statusUji === 'Tidak' ? '✗ Tidak' : '✓ Ya' }}
                                    </span>
                                @else
                                    {{ number_format($hasilFisik, 0, ',', '.') }}
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: bold; color: {{ abs($selisih) >= 0.01 ? '#dc2626' : '#16a34a' }};">
                                {{ $isCtrl ? '-' : (abs($selisih) < 0.01 ? '0' : number_format($selisih, 0, ',', '.')) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 10px;">Tidak ada rincian stok tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- BLOK EVALUASI & PENILAIAN RISIKO TUNGGAL -->
    <div class="section-box" style="border-color: #2563eb;">
        <div class="section-header" style="background: #1e40af;">
            <span>RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA</span>
            <span>Berlaku Menyeluruh untuk Halaman KKA Rekonsiliasi Stok Cek / BG</span>
        </div>
        <div class="section-body" style="background: #f8fafc;">
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
                        {{ $evalData['penyebab_keterangan'] ?: 'Nihil / Seluruh fisik warkat cek, bilyet giro, dan buku kontrol sesuai dan tertib.' }}
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

    <!-- TANDA TANGAN DUA PIHAK -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>Diperiksa Oleh:</div>
                <div style="font-weight: bold; margin-top: 2px;">Resident Auditor (RA)</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $kka->user->name ?? 'Resident Auditor' }}</div>
                <div style="font-size: 7.5pt; color: #475569;">NIP: {{ $kka->user->nip ?? '........................' }}</div>
            </td>
            <td>
                <div>Mengetahui / Penanggung Jawab Warkat:</div>
                <div style="font-weight: bold; margin-top: 2px;">Supervisor Operasional / Layanan Cabang</div>
                <div class="sign-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">( .................................................... )</div>
                <div style="font-size: 7.5pt; color: #475569;">NIP: ....................................................</div>
            </td>
        </tr>
    </table>

@if(!isset($isDossier) || !$isDossier)
</body>
</html>
@endif
