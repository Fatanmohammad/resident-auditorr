<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Lengkap Tahap 2 KKA Fisik & Rekonsiliasi — {{ $kka->kode_unit ?? '000' }}</title>
    <style>
        @page { size: landscape; margin: 8mm 10mm 10mm 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .page-break { page-break-after: always; }
        .no-print-bar {
            background: #1e3a8a;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #22c55e;
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 9pt;
            cursor: pointer;
        }
        .btn-close {
            background: #334155;
            color: #fff;
            border: none;
            padding: 7px 14px;
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

        /* Common Styles for all Sub-Sections */
        .header-table { width: 100%; border-bottom: 2px solid #1e3a8a; padding-bottom: 6px; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .bank-title { font-size: 13pt; font-weight: bold; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .bank-sub { font-size: 7.5pt; color: #64748b; margin: 2px 0 0 0; }
        
        .ba-title-box, .kka-title-box { text-align: center; margin: 10px 0; }
        .ba-title, .kka-title { font-size: 11pt; font-weight: bold; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .ba-no, .kka-no { font-size: 8pt; margin-top: 3px; color: #374151; }
        
        .info-table { width: 100%; margin-bottom: 12px; font-size: 8.5pt; border-collapse: collapse; }
        .info-table td { padding: 3px 6px; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; }
        .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 4px 6px; }
        .table-data th { background: #f8fafc; font-weight: bold; text-align: center; }
        .group-header { background: #e2e8f0; font-weight: bold; padding: 4px 8px; }
        .subtotal-row { background: #f1f5f9; font-weight: bold; }
        .total-summary-box { border: 1.5px solid #1e3a8a; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px; }

        .atm-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 14px;
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
            font-size: 8pt;
        }

        .section-box {
            border: 1.5px solid #1e3a8a;
            border-radius: 6px;
            margin-bottom: 10px;
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

        .control-table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 4px; }
        .control-table th, .control-table td { border: 1px solid #94a3b8; padding: 4px 6px; }
        .control-table th { background: #f8fafc; font-weight: bold; }

        .badge-status { display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 7.5pt; }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

        .signatures-table { width: 100%; margin-top: 15px; border-collapse: collapse; page-break-inside: avoid; font-size: 8pt; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 6px; }
        .sign-space { height: 45px; }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong style="font-size: 10pt;">Dossier Berkas Lengkap Tahap 2: KKA Fisik &amp; Rekonsiliasi Bank Sulteng</strong>
            <div style="font-size: 8pt; color: #cbd5e1;">Unit: {{ $kka->kode_unit }} | No. Arsip: {{ $kka->no_arsip ?? 'ARSIP-KKA-' . ($kka->kode_unit ?? '000') . '-' . date('Ymd') . '-001' }}</div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak Seluruh Berkas (Print / PDF)</button>
            <a href="{{ route('daily-register.physical-kka.index', $kka->id) }}" class="btn-close">← Kembali ke Form</a>
        </div>
    </div>

    <!-- 1. BA KAS KHASANAH -->
    @include('daily_register.print.ba_cash_khasanah', ['isDossier' => true, 'eval' => $evalKas])

    <div class="page-break"></div>

    <!-- 2. BA CASH OPNAME ATM -->
    @include('daily_register.print.ba_cash_atm', ['isDossier' => true, 'eval' => $evalAtm])

    <div class="page-break"></div>

    <!-- 3. KKA KARTU ATM E.4.3 -->
    @include('daily_register.print.kka_kartu_atm', ['isDossier' => true, 'eval' => $evalKartu])

    <div class="page-break"></div>

    <!-- 4. KKA REKONSILIASI STOK CEK & BILYET GIRO -->
    @include('daily_register.print.kka_stok_cek_bg', ['isDossier' => true, 'eval' => $evalStok])

    <div class="page-break"></div>

    <!-- 5. KKA BARANG CETAKAN -->
    @include('daily_register.print.kka_barang_cetakan', ['isDossier' => true, 'eval' => $evalCetakan])

    <div class="page-break"></div>

    <!-- 6. KKA OBSERVASI LINGKUNGAN & MATRIKS CCTV (E.1.5 & E.4.4) -->
    @include('daily_register.print.kka_observasi', ['isDossier' => true, 'eval' => $evalObs])

</body>
</html>
