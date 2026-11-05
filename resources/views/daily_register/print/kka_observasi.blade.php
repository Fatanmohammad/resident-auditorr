@php
    $evalData = $eval ?? (isset($evalObs) ? $evalObs : ($kka ? $kka->getEvaluationSummary('observasi') : []));
@endphp

@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKA Observasi Lingkungan & Matriks CCTV — {{ $kka->kode_unit ?? '000' }}</title>
    <style>
        @page { size: landscape; margin: 10mm 10mm 12mm 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .no-print-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #1e3a8a;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 8.5pt;
            cursor: pointer;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 8.5pt;
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
        table.matriks th, table.matriks td { border: 1px solid #94a3b8; padding: 3px 5px; vertical-align: middle; }
        table.matriks th { background: #f8fafc; font-weight: bold; text-align: center; }

        .group-row { background: #e2e8f0; font-weight: bold; font-size: 8pt; color: #1e3a8a; }

        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-cctv { background: #eff6ff; color: #1e40af; border: 1px solid #93c5fd; font-weight: 700; }

        .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; font-size: 8pt; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 5px; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong style="color: #1e3a8a; font-size: 9.5pt;">KKA Observasi Lingkungan &amp; Matriks CCTV (Acuan Standar Audit E.1.5 &amp; E.4.4)</strong>
            <span style="margin-left: 10px; font-size: 8pt; color: #64748b;">No. Arsip: {{ $kka->no_arsip ?? 'ARSIP-OBS-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') }}</span>
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
                <div><strong>Lampiran SOP 04.2 / E.1.5 / E.4.4</strong></div>
                <div>Tanggal Uji: <strong>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan ?? now())->translatedFormat('d F Y') }}</strong></div>
                <div>Status: <span class="badge-status {{ ($kka->status_final ?? '') === 'Final' ? 'badge-success' : 'badge-warning' }}">{{ $kka->status_final ?? 'Draft' }}</span></div>
            </td>
        </tr>
    </table>

    <div class="kka-title-box">
        <h2 class="kka-title">KERTAS KERJA OBSERVASI LINGKUNGAN, PERIMETER KEAMANAN &amp; FASILITAS FISIK</h2>
        <div class="kka-no">No. Berkas: {{ $kka->no_arsip ?? ('ARSIP-OBS-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd')) }} | Acuan Standar Audit Bank Sulteng (E.1.5 Matriks CCTV 9 Titik &amp; E.4.4 Fasilitas ATM)</div>
    </div>

    <div class="section-box">
        <div class="section-header">
            <span>HASIL PEMERIKSAAN FISIK LINGKUNGAN &amp; MATRIKS PENGAMANAN</span>
            <span style="font-size: 7.5pt; font-weight: normal;">Diperiksa oleh: {{ $kka->user->name ?? 'Resident Auditor' }}</span>
        </div>
        <div class="section-body">
            <table class="matriks">
                <thead>
                    <tr>
                        <th style="width: 3%;">NO</th>
                        <th style="width: 22%;">AREA OBSERVASI</th>
                        <th style="width: 30%;">KRITERIA YANG DIHARAPKAN</th>
                        <th style="width: 14%;">PENEGASAN BACKUP 90 HARI (E.1.5)</th>
                        <th style="width: 9%;">HASIL UJI</th>
                        <th style="width: 22%;">KONDISI AKTUAL / KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $obsList = isset($obsDetails) && count($obsDetails) > 0 ? $obsDetails : (isset($obsItems) ? $obsItems : []);
                        $lastCategory = null;
                        $counter = 1;
                    @endphp
                    @foreach($obsList as $idx => $row)
                        @php
                            $area = is_array($row) ? ($row['area'] ?? '') : ($row->area_observasi ?? '');
                            $kriteria = is_array($row) ? ($row['kriteria'] ?? '') : ($row->kriteria_diharapkan ?? '');
                            $hasil = is_array($row) ? ($row['hasil_observasi'] ?? 'Sesuai') : ($row->hasil_observasi ?? 'Sesuai');
                            $kondisi = is_array($row) ? ($row['kondisi_aktual'] ?? '') : ($row->kondisi_aktual ?? '');
                            $penyebab = is_array($row) ? ($row['penyebab'] ?? '') : ($row->penyebab ?? '');
                            
                            $isCctv = str_contains($area, 'CCTV') || str_contains($area, 'E.1.5');

                            // Detect Category Group for Visual Sub-headings
                            $category = 'Observasi Lingkungan';
                            if (str_contains($area, 'Titik Alarm')) {
                                $category = '1. SPESIFIK 4 TITIK ALARM (PANIC BUTTON)';
                            } elseif (str_contains($area, 'Area Terbatas')) {
                                $category = '2. SPESIFIK 5 AREA TERBATAS (RESTRICTED ACCESS)';
                            } elseif ($isCctv) {
                                $category = '3. MATRIKS 9 TITIK CCTV (ACUAN E.1.5 — WAJIB BACKUP MINIMAL 90 HARI)';
                            } elseif (str_contains($area, 'Security') || str_contains($area, 'Kepegawaian')) {
                                $category = '4. MUTASI & BUKU MUTASI SECURITY SERTA ABSENSI KARYAWAN';
                            } elseif (str_contains($area, 'Branding Interior')) {
                                $category = '5. BRANDING INTERIOR KANTOR (STANDAR CORPORATE BANK SULTENG)';
                            } elseif (str_contains($area, 'Fasilitas ATM')) {
                                $category = '6. SECTION KHUSUS ATM (ACUAN STANDAR AUDIT E.4.4)';
                            } elseif (str_contains($area, 'Keselamatan') || str_contains($area, 'Kelistrikan')) {
                                $category = '7. KESELAMATAN KERJA & FASILITAS LISTRIK CADANGAN';
                            }
                        @endphp

                        @if($category !== $lastCategory)
                            <tr class="group-row">
                                <td colspan="6" style="padding: 4px 8px; text-transform: uppercase;">
                                    <strong>{{ $category }}</strong>
                                </td>
                            </tr>
                            @php $lastCategory = $category; @endphp
                        @endif

                        <tr>
                            <td style="text-align: center;">{{ $counter++ }}</td>
                            <td><strong>{{ $area }}</strong></td>
                            <td>{{ $kriteria }}</td>
                            <td style="text-align: center;">
                                @if($isCctv)
                                    <span class="badge-status badge-cctv">✓ Wajib Min. 90 Hari</span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($hasil === 'Sesuai')
                                    <span class="badge-status badge-success">Sesuai</span>
                                @elseif($hasil === 'Tidak Sesuai')
                                    <span class="badge-status badge-danger">Tidak Sesuai</span>
                                @else
                                    <span class="badge-status badge-warning">{{ $hasil ?: 'Sesuai' }}</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $kondisi ?: 'Sesuai standar operasional.' }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- BLOK EVALUASI & PENILAIAN RISIKO TUNGGAL -->
    <div class="section-box" style="border-color: #2563eb;">
        <div class="section-header" style="background: #1e40af;">
            <span>RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA</span>
            <span>Berlaku Menyeluruh untuk Halaman Observasi Lingkungan &amp; Fisik</span>
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
                        {{ $evalData['penyebab_keterangan'] ?: 'Nihil / Seluruh pengujian fisik lingkungan, matriks CCTV, dan perimeter pengamanan kantor memenuhi ketentuan.' }}
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
                <div>Mengetahui / Mengkonfirmasi:</div>
                <div style="font-weight: bold; margin-top: 2px;">Pemimpin Cabang / Reviewer SKAI</div>
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
