@php
    $evalData = $eval ?? (isset($evalCetakan) ? $evalCetakan : ($kka ? $kka->getEvaluationSummary('cetakan') : []));
@endphp

@if(!isset($isDossier) || !$isDossier)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KKA Rekonsiliasi Barang Cetakan Berharga — {{ $kka->kode_unit ?? '000' }}</title>
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

        .control-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 4px; }
        .control-table th, .control-table td { border: 1px solid #94a3b8; padding: 4px 6px; }
        .control-table th { background: #f8fafc; font-weight: bold; }

        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .signatures-table { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; font-size: 8pt; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 5px; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Kertas Kerja Audit (KKA) — Rekonsiliasi Persediaan Barang Cetakan Berharga</strong>
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
                <p class="bank-sub">KKA Persediaan Barang Cetakan Berharga &amp; Pengujian Kontrol Fisik</p>
            </td>
            <td style="width: 30%; text-align: right; font-size: 7.5pt;">
                <strong>NO. ARSIP:</strong><br>
                <span style="font-family: monospace; font-size: 8.5pt;">{{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</span><br>
                <span>Tanggal: <strong>{{ \Carbon\Carbon::parse($kka->tanggal_pemeriksaan)->format('d/m/Y') }}</strong></span>
            </td>
        </tr>
    </table>

    <div class="kka-title-box">
        <h2 class="kka-title">REKONSILIASI PERSEDIAAN BARANG CETAKAN BERHARGA &amp; PENGUJIAN KONTROL</h2>
        <div class="kka-no">Unit Cabang: <strong>{{ $kka->kode_unit }}</strong> | Resident Auditor: <strong>{{ $kka->user ? $kka->user->name : 'Resident Auditor' }}</strong></div>
    </div>

    <!-- TABEL REKONSILIASI BARANG CETAKAN -->
    <div class="section-box">
        <div class="section-header">
            <span>REKONSILIASI FISIK DENGAN SALDO BUKU / LAPORAN MUTASI</span>
            <span>Kolom Kondisi Telah Dilebur ke Kolom Kriteria (Sesuai Arahan Revisi)</span>
        </div>
        <div class="section-body">
            <table class="matriks">
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 25%;">Objek / Nama Barang</th>
                        <th style="width: 32%;">Kriteria Pengujian (Objek & Status Kondisi)</th>
                        <th style="width: 6%;">Satuan</th>
                        <th style="width: 9%;">Saldo Buku</th>
                        <th style="width: 9%;">Jumlah Fisik</th>
                        <th style="width: 9%;">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noBct = 1; @endphp
                    @foreach($bctItems as $item)
                        @php
                            $stok = (float)($item->stok_sistem ?? $item['stok_sistem'] ?? 0);
                            $fisik = (float)($item->hasil_fisik ?? $item['hasil_fisik'] ?? 0);
                            $selisih = $fisik - $stok;
                            $kriteria = $item->kriteria ?? $item['kriteria'] ?? '';
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $noBct++ }}</td>
                            <td><span style="font-weight: 500;">{{ $item->kategori ?? $item['kategori'] ?? '-' }}</span></td>
                            <td style="font-weight: bold;">{{ $item->nama_barang ?? $item['nama_barang'] ?? '-' }}</td>
                            <td>{{ $kriteria ?: ($item->nama_barang ?? $item['nama_barang'] ?? '-') }}</td>
                            <td style="text-align: center;">{{ $item->satuan ?? $item['satuan'] ?? 'Bh' }}</td>
                            <td style="text-align: right;">{{ number_format($stok, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: 500;">{{ number_format($fisik, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold; color: {{ abs($selisih) >= 0.01 ? '#dc2626' : '#16a34a' }};">
                                {{ abs($selisih) < 0.01 ? '0' : number_format($selisih, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- CHECKLIST KONTROL BARANG CETAKAN (YA / TIDAK) -->
    <div class="section-box">
        <div class="section-header">
            <span>PENGUJIAN PENGENDALIAN INTERNAL (KONTROL) BARANG CETAKAN</span>
            <span>Format Checklist Murni Ya / Tidak</span>
        </div>
        <div class="section-body">
            <table class="control-table">
                <thead>
                    <tr>
                        <th style="width: 4%; text-align: center;">No</th>
                        <th style="width: 60%;">Butir Uji Kontrol Fisik Barang Cetakan</th>
                        <th style="width: 14%; text-align: center;">Status (Ya / Tidak)</th>
                        <th style="width: 22%;">Catatan Observasi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $bctControlItems = [
                            ['no' => 1, 'butir' => 'Pencatatan register penerimaan dan pengeluaran barang cetakan tertib dan mutakhir.', 'key' => 'ctrl_1'],
                            ['no' => 2, 'butir' => 'Penyimpanan stok barang cetakan berada di lemari terkunci (dual control).', 'key' => 'ctrl_2'],
                            ['no' => 3, 'butir' => 'Berita acara pemusnahan warkat/barang cetakan rusak atau dibatalkan terdokumentasi.', 'key' => 'ctrl_3'],
                            ['no' => 4, 'butir' => 'Kesesuaian nomor seri warkat berurutan (tidak ada nomor seri hilang/melompat).', 'key' => 'ctrl_4'],
                        ];
                    @endphp
                    @foreach($bctControlItems as $c)
                        @php
                            $matchingCtrl = collect($bctControls ?? [])->firstWhere('butir', $c['butir']);
                            $jawaban = $matchingCtrl['jawaban'] ?? 'Ya';
                            $catatan = $matchingCtrl['catatan'] ?? 'Memenuhi ketentuan.';
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $c['no'] }}</td>
                            <td>{{ $c['butir'] }}</td>
                            <td style="text-align: center; font-weight: bold;">
                                <span class="badge-status {{ $jawaban === 'Tidak' ? 'badge-danger' : 'badge-success' }}">
                                    {{ $jawaban === 'Ya' ? '✓ YA' : '✗ TIDAK' }}
                                </span>
                            </td>
                            <td style="font-size: 7.5pt; color: #475569;">{{ $catatan }}</td>
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
            <span>Berlaku Menyeluruh untuk Halaman Rekonsiliasi Barang Cetakan Berharga</span>
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
                        {{ $evalData['penyebab_keterangan'] ?: 'Nihil / Seluruh fisik formulir, bilyet deposito, buku tabungan, dan warkat cetakan berharga sesuai sistem.' }}
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

    <!-- TANDA TANGAN KKA CETAKAN -->
    <table class="signatures-table">
        <tr>
            <td>
                <strong>Pelaksana Pemeriksaan / Resident Auditor (RA):</strong>
                <div class="sign-space"></div>
                <strong>{{ $kka->user ? $kka->user->name : 'Resident Auditor' }}</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Resident Auditor SKAI</span>
            </td>
            <td>
                <strong>Pengelola / Penanggung Jawab Barang Cetakan:</strong>
                <div class="sign-space"></div>
                <strong>Supervisor Operasional / Logistik Cabang</strong><br>
                <span style="font-size: 7.5pt; color: #64748b;">Custody Barang Cetakan Berharga</span>
            </td>
        </tr>
    </table>

@if(!isset($isDossier) || !$isDossier)
</body>
</html>
@endif
