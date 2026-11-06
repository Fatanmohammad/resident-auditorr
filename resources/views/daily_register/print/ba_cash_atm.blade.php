@php
    $evalData = $eval ?? (isset($evalAtm) ? $evalAtm : ($kka ? $kka->getEvaluationSummary('atm') : []));
@endphp
@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Cash Opname ATM — {{ $kka->kode_unit ?? '000' }}</title>
    <style>
        @page { size: landscape; margin: 10mm 12mm 12mm 12mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 12px;
        }
        .no-print-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
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
            font-size: 9pt;
            cursor: pointer;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 6px 14px;
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
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .bank-title { font-size: 13pt; font-weight: bold; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .bank-sub { font-size: 7.5pt; color: #64748b; margin: 2px 0 0 0; }
        .ba-title-box { text-align: center; margin: 10px 0; }
        .ba-title { font-size: 11pt; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .ba-no { font-size: 8pt; margin-top: 3px; color: #374151; }
        
        .atm-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 16px;
            page-break-inside: avoid;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .atm-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .atm-identity-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            padding: 8px 12px;
            background: #f1f5f9;
            font-size: 8pt;
            border-bottom: 1px solid #e2e8f0;
        }
        .atm-card-body { padding: 10px 12px; }
        .table-cassette { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8pt; }
        .table-cassette th, .table-cassette td { border: 1px solid #cbd5e1; padding: 4px 6px; }
        .table-cassette th { background: #e2e8f0; font-weight: bold; text-align: center; }
        .atm-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 8px;
            padding: 8px;
            background: #fafafa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 8.5pt;
        }
        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .signatures-table { width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 6px; }
        .sign-space { height: 50px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Berita Acara Cash Opname Mesin ATM &amp; CRM</strong>
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
                <p class="bank-sub">Kertas Kerja Audit (KKA) Fisik — Berita Acara Cash Opname Kas ATM &amp; CRM</p>
            </td>
            <td style="width: 30%; text-align: right; font-size: 8pt;">
                <strong>NO. ARSIP:</strong><br>
                <span style="font-family: monospace; font-size: 9pt;">{{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</span><br>
                <span>Status: <strong>{{ strtoupper($kka->status_final ?? 'FINAL') }}</strong></span>
            </td>
        </tr>
    </table>

    <div class="ba-title-box">
        <h2 class="ba-title">BERITA ACARA CASH OPNAME KAS MESIN ATM &amp; KASSETTE</h2>
        <div class="ba-no">Unit Cabang: <strong>{{ $kka->kode_unit }}</strong> | Tanggal: <strong>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->translatedFormat('d F Y') }}</strong></div>
    </div>

    @forelse($atmMachines as $idx => $atm)
        @php
            $saldoCbs = (float)($atm->saldo_cbs ?? 0);
            $totalFisik = (float)($atm->total_fisik ?? 0);
            $selisih = $saldoCbs - $totalFisik;
        @endphp
        <div class="atm-card">
            <div class="atm-card-header">
                <div style="font-size: 9.5pt; font-weight: bold; color: #1e3a8a;">
                    🏧 ATM CARD #{{ $idx + 1 }} — TERMINAL ID: <span style="font-family: monospace; font-size: 11pt; color: #0f172a;">{{ $atm->terminal_id }}</span>
                </div>
                <div>
                    <span class="badge-status {{ $atm->status_uji === 'Selesai Diuji' ? 'badge-success' : 'badge-warning' }}">
                        {{ strtoupper($atm->status_uji ?? 'BELUM DIUJI') }}
                    </span>
                </div>
            </div>

            <div class="atm-identity-grid">
                <div><strong>Lokasi Mesin:</strong><br>{{ $atm->lokasi ?: '-' }}</div>
                <div><strong>Merk / Tipe ATM:</strong><br>{{ $atm->merk ?: '-' }}</div>
                <div><strong>Denominasi Mesin:</strong><br>Rp {{ number_format($atm->denominasi ?? 100000, 0, ',', '.') }}</div>
                <div><strong>Pengisian Terakhir:</strong><br>{{ $atm->tanggal_pengisian_terakhir ? \Carbon\Carbon::parse($atm->tanggal_pengisian_terakhir)->format('d/m/Y') : '-' }}</div>
            </div>

            <div class="atm-card-body">
                <strong>Rincian Fisik Uang Kas Per Kaset:</strong>
                <table class="table-cassette">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 35%;">Nama / Jenis Kaset</th>
                            <th style="width: 20%;">Denominasi (Rp)</th>
                            <th style="width: 15%;">Jumlah Lembar</th>
                            <th style="width: 25%;">Total Nominal Fisik (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($atm->cassettes as $cIdx => $cas)
                            <tr>
                                <td style="text-align: center;">{{ $cIdx + 1 }}</td>
                                <td style="font-weight: 500;">{{ $cas->nama_kaset }}</td>
                                <td style="text-align: right;">Rp {{ number_format($cas->denominasi ?? 0, 0, ',', '.') }}</td>
                                <td style="text-align: right;">{{ number_format($cas->jumlah_lembar ?? 0, 0, ',', '.') }} Lbr</td>
                                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($cas->total_nominal ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 10px;">Belum ada rincian kaset yang diinput.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="atm-summary-grid">
                    <div>
                        <span style="color: #64748b;">Saldo Sistem (CBS):</span><br>
                        <strong style="font-size: 10pt;">Rp {{ number_format($saldoCbs, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b;">Total Fisik Seluruh Kaset:</span><br>
                        <strong style="font-size: 10pt; color: #1e3a8a;">Rp {{ number_format($totalFisik, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b;">Selisih Kas ATM:</span><br>
                        @if(abs($selisih) < 0.01)
                            <span class="badge-status badge-success">NIHIL / SESUAI (Rp 0)</span>
                        @elseif($totalFisik > $saldoCbs)
                            <span class="badge-status badge-danger">ADA SELISIH: LEBIH (+Rp {{ number_format(abs($totalFisik - $saldoCbs), 0, ',', '.') }})</span>
                        @else
                            <span class="badge-status badge-danger">ADA SELISIH: KURANG (-Rp {{ number_format(abs($saldoCbs - $totalFisik), 0, ',', '.') }})</span>
                        @endif
                    </div>
                </div>

                @if(abs($selisih) >= 0.01 || !empty($atm->keterangan_selisih))
                    <div style="margin-top: 8px; font-size: 8pt; background: #fef2f2; border: 1px solid #fecaca; padding: 6px 10px; border-radius: 4px; color: #991b1b;">
                        <strong>Uraian Penyebab Selisih Kas ATM:</strong> {{ $atm->keterangan_selisih ?: 'Wajib diisi keterangan selisih.' }}
                    </div>
                @endif

                <table class="signatures-table">
                    <tr>
                        <td>
                            <strong>Pihak Pelaksana Cash Opname:</strong>
                            <div class="sign-space"></div>
                            <strong>{{ $atm->pelaksana_nama ?: ($kka->user ? $kka->user->name : 'Resident Auditor') }}</strong><br>
                            <span style="font-size: 7.5pt; color: #64748b;">{{ $atm->pelaksana_jabatan ?: 'RA Pelaksana SKAI' }}</span>
                        </td>
                        <td>
                            <strong>Pihak Saksi / Petugas Kaset ATM:</strong>
                            <div class="sign-space"></div>
                            <strong>{{ $atm->saksi_nama ?: 'Supervisor / Pengelola ATM' }}</strong><br>
                            <span style="font-size: 7.5pt; color: #64748b;">{{ $atm->saksi_jabatan ?: 'Pemegang Kunci ATM Dual Control' }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 30px; border: 1px dashed #cbd5e1; border-radius: 8px; color: #64748b;">
            Belum ada mesin ATM yang ditambahkan pada KKA Fisik ini.
        </div>
    @endforelse

    <!-- BLOK EVALUASI & PENILAIAN RISIKO TUNGGAL MESIN ATM -->
    <div class="section-box" style="border: 1px solid #2563eb; margin-top: 15px; margin-bottom: 15px; border-radius: 4px; overflow: hidden; page-break-inside: avoid;">
        <div style="background: #1e40af; color: #fff; padding: 6px 10px; font-weight: bold; font-size: 8.5pt; display: flex; justify-content: space-between;">
            <span>RINGKASAN EVALUASI &amp; PENILAIAN RISIKO CASH OPNAME ATM</span>
            <span>Berlaku Menyeluruh untuk Halaman Cash Opname Mesin ATM</span>
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
                        {{ $evalData['penyebab_keterangan'] ?: 'Nihil / Seluruh fisik kaset mesin ATM sesuai dengan catatan saldo sistem CBS.' }}
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

@if(!isset($isDossier) || !$isDossier)
</body>
</html>
@endif
