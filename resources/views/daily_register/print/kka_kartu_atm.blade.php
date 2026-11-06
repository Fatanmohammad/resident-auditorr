@php
    $evalData = $eval ?? (isset($evalKartu) ? $evalKartu : ($kka ? $kka->getEvaluationSummary('kartu') : []));
@endphp
@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKA E.4.3 Rekonsiliasi Persediaan Kartu ATM — {{ $kka->kode_unit ?? '000' }}</title>
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
        table.matriks th.bg-group1 { background: #eff6ff; color: #1e40af; }
        table.matriks th.bg-group2 { background: #f0fdf4; color: #166534; }
        
        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .control-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 4px; }
        .control-table th, .control-table td { border: 1px solid #94a3b8; padding: 4px 6px; }
        .control-table th { background: #f8fafc; font-weight: bold; }
        
        .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; font-size: 8pt; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 5px; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Kertas Kerja Audit (KKA) E.4.3 — Rekonsiliasi Persediaan Kartu ATM &amp; PIN Mailer</strong>
            <div style="font-size: 7.5pt; color: #64748b;">No. Arsip: {{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</div>
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
                <p class="bank-sub">SATUAN KERJA AUDIT INTERN (SKAI) — KERTAS KERJA PEMERIKSAAN FISIK MINGGUAN</p>
                <p class="bank-sub">KKA Acuan E.4.3: Pengujian Fisik Persediaan Kartu ATM &amp; Kontrol Pengelolaan</p>
            </td>
            <td style="width: 30%; text-align: right; font-size: 7.5pt;">
                <strong>NO. ARSIP:</strong><br>
                <span style="font-family: monospace; font-size: 8.5pt;">{{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</span><br>
                <span>Posisi Tgl Rekon: <strong>{{ $kka->posisi_tanggal_rekon ? \Carbon\Carbon::parse($kka->posisi_tanggal_rekon)->format('d/m/Y') : date('d/m/Y') }}</strong></span>
            </td>
        </tr>
    </table>

    <div class="kka-title-box">
        <h2 class="kka-title">KERTAS KERJA REKONSILIASI PERSEDIAAN KARTU ATM (FORMAT RESMI E.4.3)</h2>
        <div class="kka-no">Unit Cabang: <strong>{{ $kka->kode_unit }}</strong> | Pemeriksa (RA): <strong>{{ $kka->user ? $kka->user->name : 'Resident Auditor' }}</strong></div>
    </div>

    <!-- SECTION A -->
    <div class="section-box">
        <div class="section-header">
            <span>SECTION A — REKONSILIASI STOK, SALDO BUKU (2 PEMBANDING), &amp; RINCIAN JENIS KARTU</span>
            <span>Posisi: {{ $kka->posisi_tanggal_rekon ? \Carbon\Carbon::parse($kka->posisi_tanggal_rekon)->translatedFormat('d F Y') : date('d F Y') }}</span>
        </div>
        <div class="section-body">
            <table class="matriks">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 3%;">No</th>
                        <th rowspan="2" style="width: 15%;">Jenis Kartu ATM</th>
                        <th colspan="3" style="background: #f1f5f9;">Rincian Fisik Aktual</th>
                        <th colspan="5" class="bg-group1">Pembanding 1: Saldo Buku Persediaan (Nilai Modal & Qty)</th>
                        <th colspan="3" class="bg-group2">Pembanding 2: Fisik Kartu vs PIN Mailer</th>
                    </tr>
                    <tr>
                        <th style="width: 6%;">Fisik Kosong</th>
                        <th style="width: 6%;">Fisik Rusak</th>
                        <th style="width: 6%;">Total Fisik</th>
                        
                        <th style="width: 8%;">Harga Modal (Rp)</th>
                        <th style="width: 10%;">Nominal Fisik (Rp)</th>
                        <th style="width: 6%;">Saldo Buku (Qty)</th>
                        <th style="width: 10%;">Saldo Buku (Rp)</th>
                        <th style="width: 8%;">Selisih Persediaan</th>

                        <th style="width: 6%;">Jml Fisik</th>
                        <th style="width: 6%;">PIN Mailer</th>
                        <th style="width: 8%;">Selisih PIN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totFisikKosong = 0; $totFisikRusak = 0; $totNominalFisik = 0;
                        $totBukuQty = 0; $totBukuNominal = 0; $totPinMailer = 0;
                        $hasSelisihSectionA = false;
                    @endphp
                    @forelse($stockRows as $idx => $row)
                        @php
                            $kosong = (int)($row->qty_fisik_kosong ?? $row->hasil_fisik ?? 0);
                            $rusak = (int)($row->qty_fisik_rusak ?? 0);
                            $totalFisik = $kosong + $rusak;
                            $hargaModal = (float)($row->harga_modal ?? ($row->jenis_kartu === 'Kartu Silver' ? 42180 : 68820));
                            $nomFisik = $hargaModal * $kosong;
                            $bukuQty = (int)($row->saldo_buku_qty ?? $row->saldo_nominatif ?? 0);
                            $bukuNom = (float)($row->saldo_buku_nominal ?? ($hargaModal * $bukuQty));
                            $selisihPersediaan = $nomFisik - $bukuNom;
                            $pinQty = (int)($row->pin_mailer_qty ?? $bukuQty);
                            $selisihPin = $kosong - $pinQty;

                            $totFisikKosong += $kosong;
                            $totFisikRusak += $rusak;
                            $totNominalFisik += $nomFisik;
                            $totBukuQty += $bukuQty;
                            $totBukuNominal += $bukuNom;
                            $totPinMailer += $pinQty;

                            if (abs($selisihPersediaan) >= 0.01 || $selisihPin !== 0) {
                                $hasSelisihSectionA = true;
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold;">{{ $row->jenis_kartu }}</td>
                            <td style="text-align: right;">{{ number_format($kosong, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($rusak, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($totalFisik, 0, ',', '.') }}</td>

                            <td style="text-align: right;">Rp {{ number_format($hargaModal, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold;">Rp {{ number_format($nomFisik, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($bukuQty, 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($bukuNom, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold; color: {{ abs($selisihPersediaan) >= 0.01 ? '#dc2626' : '#16a34a' }};">
                                {{ abs($selisihPersediaan) < 0.01 ? '0' : 'Rp ' . number_format($selisihPersediaan, 0, ',', '.') }}
                            </td>

                            <td style="text-align: right;">{{ number_format($kosong, 0, ',', '.') }}</td>
                            <td style="text-align: right;">{{ number_format($pinQty, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold; color: {{ $selisihPin !== 0 ? '#dc2626' : '#16a34a' }};">
                                {{ $selisihPin }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; color: #94a3b8; padding: 10px;">Belum ada data stok jenis kartu ATM.</td>
                        </tr>
                    @endforelse
                    <tr style="background: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: center;">TOTAL AKUMULASI:</td>
                        <td style="text-align: right;">{{ number_format($totFisikKosong, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($totFisikRusak, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($totFisikKosong + $totFisikRusak, 0, ',', '.') }}</td>
                        <td>-</td>
                        <td style="text-align: right;">Rp {{ number_format($totNominalFisik, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($totBukuQty, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($totBukuNominal, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: {{ abs($totNominalFisik - $totBukuNominal) >= 0.01 ? '#dc2626' : '#16a34a' }};">
                            Rp {{ number_format($totNominalFisik - $totBukuNominal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">{{ number_format($totFisikKosong, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ number_format($totPinMailer, 0, ',', '.') }}</td>
                        <td style="text-align: right; color: {{ ($totFisikKosong - $totPinMailer) !== 0 ? '#dc2626' : '#16a34a' }};">
                            {{ $totFisikKosong - $totPinMailer }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 6px 10px; background: #fafafa; border: 1px solid #e2e8f0; border-radius: 4px;">
                <div>
                    <strong>Status Selisih Section A:</strong>
                    @if(!$hasSelisihSectionA)
                        <span class="badge-status badge-success">TIDAK TERDAPAT SELISIH (HIJAU)</span>
                    @else
                        <span class="badge-status badge-danger">TERDAPAT SELISIH (MERAH)</span>
                    @endif
                </div>
                <div style="width: 65%; font-size: 7.5pt;">
                    <strong>Penyebab Selisih / Findings:</strong>
                    {{ $kka->rekon_kartu_penyebab_selisih ?: 'Tidak terdapat selisih antara fisik kartu ATM, saldo persediaan, dan PIN Mailer' }}
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION B -->
    <div class="section-box">
        <div class="section-header">
            <span>SECTION B — KONTROL PENGELOLAAN KARTU ATM (CHECKLIST MURNI YA / TIDAK)</span>
            <span>Standar Kontrol Operasional E.4.3</span>
        </div>
        <div class="section-body">
            <table class="control-table">
                <thead>
                    <tr>
                        <th style="width: 4%; text-align: center;">No</th>
                        <th style="width: 60%;">Butir Uji Pengendalian Internal (Kontrol)</th>
                        <th style="width: 14%; text-align: center;">Hasil Uji (Ya / Tidak)</th>
                        <th style="width: 22%;">Catatan / Keterangan Observasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($controlRows as $cIdx => $c)
                        @php
                            $jawaban = $c->jawaban_kontrol ?? ($c->kondisi === 'Sesuai' ? 'Ya' : 'Tidak');
                            $isTidak = ($jawaban === 'Tidak');
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $cIdx + 1 }}</td>
                            <td>{{ $c->jenis_kartu }}</td>
                            <td style="text-align: center; font-weight: bold;">
                                <span class="badge-status {{ $isTidak ? 'badge-danger' : 'badge-success' }}">
                                    {{ $jawaban === 'Ya' ? '✓ YA' : '✗ TIDAK' }}
                                </span>
                            </td>
                            <td style="font-size: 7.5pt; color: #475569;">
                                {{ $c->catatan_kontrol ?: ($c->simpulan_ra ?: '-') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- BLOK EVALUASI & PENILAIAN RISIKO TUNGGAL -->
    <div class="section-box" style="border: 1px solid #2563eb; margin-top: 15px; margin-bottom: 15px; border-radius: 4px; overflow: hidden; page-break-inside: avoid;">
        <div style="background: #1e40af; color: #fff; padding: 6px 10px; font-weight: bold; font-size: 8.5pt; display: flex; justify-content: space-between;">
            <span>RINGKASAN EVALUASI &amp; PENILAIAN RISIKO KKA KARTU ATM</span>
            <span>Berlaku Menyeluruh untuk Halaman Rekonsiliasi Kartu ATM</span>
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
                        {{ $evalData['penyebab_keterangan'] ?: 'Nihil / Seluruh fisik persediaan kartu ATM sesuai catatan dan kontrol terpenuhi.' }}
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

    <!-- TANDA TANGAN KKA KARTU ATM -->
    <table class="signatures-table">
        <tr>
            <td>
                <strong>Pelaksana Pemeriksaan / Resident Auditor (RA):</strong>
                <div class="sign-space"></div>
                <strong>{{ $kka->user ? $kka->user->name : 'Resident Auditor' }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Resident Auditor SKAI</span>
            </td>
            <td>
                <strong>Pemegang Kunci Lemari / Supervisor CS & Kartu:</strong>
                <div class="sign-space"></div>
                <strong>Supervisor Pelayanan Nasabah & Kartu</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Pengelola Dual Control Persediaan Kartu</span>
            </td>
        </tr>
    </table>

@if(!isset($isDossier) || !$isDossier)
</body>
</html>
@endif
