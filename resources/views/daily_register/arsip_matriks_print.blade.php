<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Cetak Arsip Matriks KKA Harian — {{ $namaSheet }}</title>
    <style>
        @page {
            size: landscape;
            margin: 8mm 8mm 10mm 8mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 12px;
            font-size: 8.5pt;
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
            color: #ffffff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 9pt;
            cursor: pointer;
        }
        .btn-print:hover { background: #1e40af; }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 9pt;
            cursor: pointer;
        }
        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
        .report-header {
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .report-header h2 {
            margin: 0 0 4px 0;
            font-size: 13pt;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-header p {
            margin: 0;
            font-size: 8pt;
            color: #64748b;
        }
        .meta-table {
            margin-bottom: 10px;
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2px 6px;
        }
        .meta-table td strong {
            color: #0f172a;
        }
        table.matriks-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        table.matriks-table th, table.matriks-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: middle;
        }
        table.matriks-table th {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
        }
        table.matriks-table tr:nth-child(even) {
            background: #fdfdfe;
        }
        .chk-cell {
            text-align: center;
            font-weight: bold;
            font-size: 8.5pt;
            width: 26px;
            min-width: 26px;
        }
        .chk-ya {
            color: #15803d;
        }
        .chk-tidak {
            color: #b91c1c;
            background: #fee2e2;
        }
        .chk-empty {
            color: #94a3b8;
        }
        .badge-risk {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
        }
        .risk-low { background: #dcfce7; color: #166534; }
        .risk-moderate { background: #fef9c3; color: #854d0e; }
        .risk-high { background: #fee2e2; color: #991b1b; }
        .risk-critical { background: #450a0a; color: #ffffff; }
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            font-size: 8pt;
        }
        .sig-box {
            text-align: center;
            width: 220px;
        }
        .sig-space {
            height: 50px;
        }
    </style>
</head>
<body>

    @if(empty($isExcel))
    <div class="no-print-bar">
        <div>
            <strong>Report Arsip Matriks KKA Harian (Landscape)</strong> — Siap Cetak / Simpan sebagai PDF
        </div>
        <div style="display:flex; gap:8px;">
            <button class="btn-print" onclick="window.print()">
                Cetak / Simpan PDF
            </button>
            <button class="btn-close" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>
    @endif

    <div class="report-header">
        <div>
            <h2>PT BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h2>
            <p><strong>DIVISI SATUAN KERJA AUDIT INTERN (SKAI)</strong> — KERTAS KERJA AUDIT (KKA) DAILY REGISTER</p>
            <p style="margin-top: 2px;">ARSIP MATRIKS REGISTER HARIAN &amp; CHECKLIST PENGUJIAN</p>
        </div>
        <div style="text-align: right; font-size: 8pt;">
            <div><strong>Area / Sheet:</strong> {{ $namaSheet }}</div>
            <div><strong>Kode Unit KC:</strong> {{ $kodeUnit ?: 'Semua KC' }}</div>
            <div><strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d F Y H:i') }} WITA</div>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Area Pemeriksaan:</strong></td>
            <td style="width: 35%;">{{ $namaSheet }}</td>
            <td style="width: 15%;"><strong>Status Arsip:</strong></td>
            <td style="width: 35%;"><span style="color:#15803d; font-weight:bold;">FINAL / SELESAI (TERARSIP)</span></td>
        </tr>
        <tr>
            <td><strong>Total Transaksi:</strong></td>
            <td>{{ count($findings) }} Data Pemeriksaan</td>
            <td><strong>Total Nominal:</strong></td>
            <td><strong style="color:#b91c1c;">Rp {{ number_format($findings->sum('nominal_terkait'), 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    @php
        $subAreaGroups = [];
        foreach ($checklists as $chk) {
            $code = $chk->sub_area_code;
            if (!isset($subAreaGroups[$code])) {
                $subAreaGroups[$code] = [
                    'code'  => $code,
                    'name'  => $chk->sub_area_name,
                    'items' => collect(),
                ];
            }
            $subAreaGroups[$code]['items']->push($chk);
        }
    @endphp

    @if(empty($subAreaGroups))
    <div style="text-align:center; padding: 20px; color:#64748b; border:1px solid #cbd5e1; border-radius:8px;">
        Tidak ada checklist untuk area ini.
    </div>
    @endif

    @foreach($subAreaGroups as $code => $grp)
    @php
        $groupChecklists = $grp['items'];
        $subChkIds = $groupChecklists->pluck('id')->toArray();
        $groupFindings = $findings->filter(function($f) use ($code, $subChkIds, $subAreaGroups) {
            $selected = $f->selected_sub_areas;
            if (is_string($selected)) {
                $selected = json_decode($selected, true);
            }
            if (!empty($selected) && is_array($selected)) {
                return in_array($code, $selected);
            }
            if ($f->checklistAnswers && $f->checklistAnswers->whereIn('checklist_id', $subChkIds)->isNotEmpty()) {
                return true;
            }
            $desc = strtolower(($f->kategori ?? '') . ' ' . ($f->rincian ?? '') . ' ' . ($f->deskripsi ?? '') . ' ' . ($f->jenis_exception_awal ?? ''));
            if ($code === 'A2') return str_contains($desc, 'kas kecil');
            if ($code === 'A3') return str_contains($desc, 'khasanah') || str_contains($desc, 'brankas');
            if ($code === 'A1') return !str_contains($desc, 'kas kecil') && !str_contains($desc, 'khasanah') && !str_contains($desc, 'brankas');

            $firstCode = array_key_first($subAreaGroups);
            return $code === $firstCode;
        });
    @endphp

    <div style="margin-top: 15px; margin-bottom: 6px; padding: 6px 12px; background: #f1f5f9; border-left: 4px solid #1e3a8a; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="color: #1e3a8a; font-size: 9pt;"><i class="bi bi-folder2-open me-1"></i> Sub-Area: {{ $grp['name'] }}</strong>
            <span style="color: #475569; font-size: 8pt; margin-left: 8px;">({{ $groupChecklists->count() }} Item Checklist)</span>
        </div>
        <div style="font-size: 8pt; font-weight: 600; color: #1e3a8a;">
            {{ $groupFindings->count() }} Transaksi Terkait
        </div>
    </div>

    <table class="matriks-table" style="margin-bottom: 8px;">
        <thead>
            <tr>
                <th rowspan="2" style="width: 24px;">NO</th>
                <th rowspan="2" style="width: 75px;">NO. ARSIP</th>
                <th rowspan="2" style="width: 60px;">TANGGAL</th>
                <th rowspan="2" style="width: 40px;">UNIT</th>
                <th rowspan="2" style="width: 80px;">NO REFERENSI</th>
                <th rowspan="2" style="width: 70px;">USER/MAKER</th>
                <th rowspan="2" style="width: 80px;">NOMINAL (RP)</th>
                <th rowspan="2" style="min-width: 130px;">DESKRIPSI / NARASI</th>
                @if(count($groupChecklists) > 0)
                    <th colspan="{{ count($groupChecklists) }}">MATRIKS CHECKLIST PENGUJIAN ([✓] Sesuai / [✗] Temuan)</th>
                @endif
                <th rowspan="2" style="width: 30px;">IMP</th>
                <th rowspan="2" style="width: 30px;">LIK</th>
                <th rowspan="2" style="width: 55px;">RISK</th>
                <th rowspan="2" style="width: 60px;">STATUS</th>
            </tr>
            @if(count($groupChecklists) > 0)
            <tr>
                @foreach($groupChecklists as $idx => $chk)
                    <th class="chk-cell" title="{{ $chk->item_checklist }} (No. {{ $chk->item_no }})">
                        #{{ $chk->item_no }}
                    </th>
                @endforeach
            </tr>
            @endif
        </thead>
        <tbody>
            @forelse($groupFindings as $index => $item)
            @php
                $ansMap = $item->checklistAnswers->keyBy('checklist_id');
                $riskCls = 'risk-low';
                if ($item->risk_final === 'Moderate') $riskCls = 'risk-moderate';
                elseif ($item->risk_final === 'High') $riskCls = 'risk-high';
                elseif ($item->risk_final === 'Critical') $riskCls = 'risk-critical';
            @endphp
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td style="font-weight: 600; font-family: monospace;">{{ $item->no_arsip ?: '-' }}</td>
                <td style="text-align: center;">{{ $item->tanggal_data ? date('d/m/Y', strtotime($item->tanggal_data)) : '-' }}</td>
                <td style="text-align: center; font-weight: 600;">{{ $item->kode_unit }}</td>
                <td style="font-family: monospace;">{{ $item->staging_id ?: ($item->rincian ? \Illuminate\Support\Str::limit($item->rincian, 15) : '-') }}</td>
                <td>{{ $item->kategori ?: '-' }}</td>
                <td style="text-align: right; font-weight: 600; color: {{ $item->nominal_terkait >= 50000000 ? '#b91c1c' : '#0f172a' }};">
                    {{ number_format((float)$item->nominal_terkait, 0, ',', '.') }}
                </td>
                <td>
                    {{ $item->deskripsi ?: ($item->rincian ?: '-') }}
                    @if($item->jenis_exception_ra)
                        <div style="font-size:7pt; color:#b91c1c; margin-top:2px;">
                            <strong>Exception:</strong> {{ $item->jenis_exception_ra }}
                        </div>
                    @endif
                </td>

                @foreach($groupChecklists as $chk)
                    @php
                        $answer = $ansMap->get($chk->id);
                        $val = $answer ? $answer->jawaban : null;
                    @endphp
                    <td class="chk-cell {{ $val === 'Ya' ? 'chk-ya' : ($val === 'Tidak' ? 'chk-tidak' : 'chk-empty') }}">
                        @if($val === 'Ya')
                            [✓]
                        @elseif($val === 'Tidak')
                            [✗]
                        @else
                            -
                        @endif
                    </td>
                @endforeach

                <td style="text-align: center; font-weight: bold;">{{ $item->skor_dampak ?: 1 }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $item->skor_kemungkinan ?: 1 }}</td>
                <td style="text-align: center;">
                    <span class="badge-risk {{ $riskCls }}">{{ strtoupper($item->risk_final ?: 'LOW') }}</span>
                </td>
                <td style="text-align: center; font-weight: bold; color: #15803d;">
                    SELESAI
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ 12 + count($groupChecklists) }}" style="text-align: center; padding: 14px; color: #64748b;">
                    Tidak ada data arsip riwayat selesai untuk grup sub-area ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($groupChecklists) > 0)
    <div style="margin-top: 2px; margin-bottom: 12px; font-size: 7pt; color: #475569; background: #f8fafc; padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 4px;">
        <strong>Legenda Item Checklist ({{ $grp['name'] }}):</strong>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-top: 4px;">
            @foreach($groupChecklists as $chk)
                <div><strong>#{{ $chk->item_no }}:</strong> {{ $chk->item_checklist }}</div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    <div class="signature-section">
        <div class="sig-box">
            <div>Dibuat &amp; Diuji Oleh,</div>
            <div style="font-weight: 600;">Resident Auditor (RA)</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Resident Auditor' }}</div>
            <div style="color: #64748b;">NIP: {{ auth()->user()->nip ?? '-' }}</div>
        </div>
        <div class="sig-box">
            <div>Mengetahui / Mengonfirmasi,</div>
            <div style="font-weight: 600;">Pemimpin Seksi / Reviewer SKAI</div>
            <div class="sig-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">( .................................................... )</div>
            <div style="color: #64748b;">Reviewer Pengawas</div>
        </div>
    </div>

</body>
</html>
