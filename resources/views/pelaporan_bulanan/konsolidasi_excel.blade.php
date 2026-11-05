<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if gte mso 9]>
    @verbatim
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Konsolidasi_SOP08</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    @endverbatim
    <![endif]-->
    <style>
        .title { font-size: 14pt; font-weight: bold; color: #1e3a8a; }
        .subtitle { font-size: 10pt; font-weight: bold; color: #475569; }
        .section { font-size: 11pt; font-weight: bold; background-color: #e2e8f0; border: 1px solid #94a3b8; }
        .th-main { background-color: #0f172a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000; }
        .td-data { border: 1px solid #cbd5e1; vertical-align: middle; }
        .td-center { border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; }
        .td-num { border: 1px solid #cbd5e1; text-align: right; mso-number-format: "\#\,\#\#0"; }
        .td-total { background-color: #f1f5f9; font-weight: bold; border: 1px solid #000000; }
        .hijau { color: #15803d; font-weight: bold; }
        .merah { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" class="title">PT. BANK SULTENG - SATUAN KERJA AUDIT INTERN (SKAI)</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">LAPORAN BULANAN KONSOLIDASI RESIDENT AUDITOR (SOP 08)</td>
        </tr>
        <tr>
            <td colspan="7">Periode: {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</td>
        </tr>
        <tr><td></td></tr>

        {{-- 1. Catatan Eksekutif Admin --}}
        <tr>
            <td colspan="7" class="section">1. CATATAN &amp; ANALISIS EKSEKUTIF KONSOLIDASI</td>
        </tr>
        <tr>
            <td colspan="7" style="border: 1px solid #cbd5e1; padding: 8px;">
                @if($activeNarasi && !empty($activeNarasi->catatan_eksekutif))
                    {!! strip_tags($activeNarasi->catatan_eksekutif, '<p><br><b><strong><i><u><ul><ol><li>') !!}
                @elseif($activeNarasi && !empty($activeNarasi->ringkasan_eksekutif))
                    {!! nl2br(e($activeNarasi->ringkasan_eksekutif)) !!}
                @else
                    Seluruh kegiatan pengawasan bulanan Resident Auditor cabang dilaksanakan sesuai SOP 08 dan program kerja audit tahunan.
                @endif
            </td>
        </tr>
        <tr><td></td></tr>

        {{-- 2. Tabel Matriks Konsolidasi Bulanan Seluruh Cabang --}}
        <tr>
            <td colspan="7" class="section">2. TABEL MATRIKS KONSOLIDASI BULANAN SELURUH CABANG</td>
        </tr>
        <tr>
            <th class="th-main">Nama / Kode Cabang</th>
            <th class="th-main">Status Kepatuhan KKA</th>
            <th class="th-main">Jumlah Reversal</th>
            <th class="th-main">Jumlah Selisih Kas</th>
            <th class="th-main">Jumlah Pelampauan Limit</th>
            <th class="th-main">Temuan Mod-to-High</th>
            <th class="th-main">Status Tindak Lanjut</th>
        </tr>
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
            <td class="td-data">[{{ $row['kode_unit'] ?? '' }}] {{ $row['nama_unit'] ?? '' }}</td>
            <td class="td-center {{ $kep === 'HIJAU' ? 'hijau' : 'merah' }}">
                {{ $kep === 'HIJAU' ? 'LENGKAP / TUNTAS' : 'BELUM SELESAI' }}
            </td>
            <td class="td-num">{{ $rev }}</td>
            <td class="td-num">{{ $sel }}</td>
            <td class="td-num">{{ $lim }}</td>
            <td class="td-num {{ $high > 0 ? 'merah' : '' }}">{{ $high }}</td>
            <td class="td-center">
                @if($od > 0)
                    {{ $od }} Overdue
                @elseif($act > 0)
                    {{ $act }} Aktif
                @else
                    Nihil
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="td-center">Tidak ada data matriks cabang.</td>
        </tr>
        @endforelse
        <tr>
            <td class="td-total" style="text-align: right;">TOTAL KONSOLIDASI:</td>
            <td class="td-total" style="text-align: center;">{{ collect($matrixRows)->where('kepatuhan_status', 'HIJAU')->count() }} Hijau / {{ collect($matrixRows)->where('kepatuhan_status', 'MERAH')->count() }} Merah</td>
            <td class="td-total td-num">{{ $totRev }}</td>
            <td class="td-total td-num">{{ $totSel }}</td>
            <td class="td-total td-num">{{ $totLim }}</td>
            <td class="td-total td-num merah">{{ $totHigh }}</td>
            <td class="td-total td-num merah">{{ $totOd }} OD</td>
        </tr>
        <tr><td></td></tr>

        {{-- 3. Hasil Utama & Temuan Kunci --}}
        <tr>
            <td colspan="7" class="section">3. HASIL UTAMA &amp; TEMUAN KUNCI (MODERATE TO HIGH &amp; HIGH)</td>
        </tr>
        <tr>
            <th class="th-main" colspan="2">Cabang &amp; Area KKA</th>
            <th class="th-main" colspan="3">Uraian Penjelasan Temuan &amp; Eksposur Nominal</th>
            <th class="th-main" colspan="2">Rekomendasi / Tindak Lanjut &amp; PIC</th>
        </tr>
        @forelse($hasilUtamaRows as $t)
        <tr>
            <td class="td-data" colspan="2">
                <b>[{{ $t['kode_unit'] }}] {{ $t['nama_unit'] }}</b><br>
                Area: {{ $t['area'] }} (Risk: {{ $t['risk_level'] }})
            </td>
            <td class="td-data" colspan="3">
                @if($t['is_temuan_berulang'])
                    <b>[TEMUAN BERULANG]</b><br>
                @endif
                {{ $t['uraian'] }}
                @if($t['nominal'] > 0)
                    <br><b>Nominal: {{ $t['nominal_formatted'] }}</b>
                @endif
            </td>
            <td class="td-data" colspan="2">
                {{ $t['rekomendasi'] ?: '-' }}<br>
                <small>PIC: {{ $t['pic'] ?: '-' }} | Target: {{ $t['target_date'] ? \Carbon\Carbon::parse($t['target_date'])->format('d/m/Y') : '-' }}</small>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="td-center">Tidak ada temuan Moderate to High atau High pada periode ini.</td>
        </tr>
        @endforelse
        <tr><td></td></tr>

        {{-- 4. Rekap Aktivitas & Log Harian Terintegrasi --}}
        <tr>
            <td colspan="7" class="section">4. REKAP MONITORING TINDAK LANJUT &amp; LOG KEGIATAN TERINTEGRASI</td>
        </tr>
        <tr>
            <th class="th-main">Tanggal</th>
            <th class="th-main" colspan="2">Cabang</th>
            <th class="th-main" colspan="2">Aktivitas / Pengujian</th>
            <th class="th-main">Detail</th>
            <th class="th-main">Status</th>
        </tr>
        @forelse(array_slice($unifiedActivities, 0, 100) as $act)
        <tr>
            <td class="td-center">{{ $act['tanggal'] ?? (!empty($act['tanggal_raw']) ? \Carbon\Carbon::parse($act['tanggal_raw'])->format('d/m/Y') : '-') }}</td>
            <td class="td-data" colspan="2">[{{ $act['kode_unit'] }}] {{ $act['nama_unit'] }}</td>
            <td class="td-data" colspan="2">{{ $act['kegiatan'] ?? $act['jenis_aktivitas'] ?? '-' }}</td>
            <td class="td-data">{{ $act['detail'] ?? $act['output_wp'] ?? $act['area'] ?? '-' }}</td>
            <td class="td-center hijau">{{ $act['status'] ?? $act['status_penyelesaian'] ?? 'COMPLETED' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="td-center">Belum ada aktivitas terekam.</td>
        </tr>
        @endforelse
        <tr><td></td></tr>

        {{-- 5. Rencana Bulan Berikutnya --}}
        <tr>
            <td colspan="7" class="section">5. RENCANA KERJA PENGAWASAN BULAN BERIKUTNYA</td>
        </tr>
        <tr>
            <td colspan="7" style="border: 1px solid #cbd5e1; padding: 8px;">
                @if($activeNarasi && !empty($activeNarasi->rencana_bulan_depan))
                    {!! strip_tags($activeNarasi->rencana_bulan_depan, '<p><br><b><strong><i><u><ul><ol><li>') !!}
                @elseif($activeNarasi && !empty($activeNarasi->rencana_bulan_berikut))
                    {!! nl2br(e($activeNarasi->rencana_bulan_berikut)) !!}
                @else
                    Melanjutkan pengawasan rutin harian, monitoring tindak lanjut temuan berulang, dan mitigasi risiko operasional.
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
