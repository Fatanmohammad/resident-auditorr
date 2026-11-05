@extends('layouts.app')
@section('title', 'Cetak Laporan Bulanan')

@section('content')
<style>
    /* Styling Eksekutif & Layout Formal */
    .sheet-container {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 2rem 2.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        max-width: 1100px;
        margin: 0 auto 2rem auto;
        color: #1e293b;
    }
    .sheet-header-title {
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .wp-code-badge {
        background: #1e3a8a;
        color: #fff;
        padding: 0.25rem 0.65rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .report-table th, .report-table td {
        border: 1px solid #cbd5e1;
        padding: 0.5rem 0.65rem;
        vertical-align: middle;
    }
    .report-table th {
        background-color: #f1f5f9;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        color: #334155;
    }
    .section-title {
        font-size: 0.88rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #1e3a8a;
        background: #f8fafc;
        border-left: 4px solid #1e3a8a;
        padding: 0.4rem 0.75rem;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .narasi-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        line-height: 1.6;
        min-height: 60px;
        color: #334155;
    }
    .selector-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* Print Mode Styling */
    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 11pt !important;
        }
        .sidebar, .navbar, .header, .sop08-tabs, .selector-bar, .no-print, .btn, .alert {
            display: none !important;
        }
        .main-content, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .sheet-container {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
        }
        .section-title {
            background: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .report-table th {
            background-color: #e2e8f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header & Tombol Aksi --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 no-print">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-printer me-2"></i>Laporan Bulanan Resident Auditor
            </h4>
            <p class="text-muted small mb-0">
                Lembar Eksekutif Laporan Bulanan Resmi Bank Sulteng (Tampilan Standar Sheet Laporan_Bulanan)
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($kalender)
                @if(!$isRa)
                    <a href="{{ route('pelaporan-bulanan.narasi', $kalender->report_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editor WYSIWYG
                    </a>
                @else
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 d-flex align-items-center">
                        <i class="bi bi-eye me-1"></i> Mode Preview (Read-Only)
                    </span>
                @endif
                <button onclick="window.print()" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen (Print / PDF)
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
    <div class="no-print">
        @include('pelaporan_bulanan.tabs')
    </div>

    @if($isRa)
        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center no-print">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
            <div>
                <h6 class="fw-bold mb-0">Mode Preview (Read-Only) Resident Auditor</h6>
                <small class="text-muted">
                    Sesuai SOP 08 revisi, Resident Auditor tidak lagi mengisi narasi kaku atau rekap manual. Seluruh lembar laporan ditarik 100% otomatis dari database pengawasan cabang Anda.
                </small>
            </div>
        </div>
    @endif

    {{-- Selector Report ID --}}
    <div class="selector-bar no-print shadow-sm">
        <form method="GET" action="{{ route('pelaporan-bulanan.laporan-bulanan') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/laporan-bulanan') }}/' + this.value; } else { this.form.submit(); }">
                    @forelse($availableReports as $rep)
                        <option value="{{ $rep->report_id }}" {{ ($reportId == $rep->report_id) ? 'selected' : '' }}>
                            {{ $rep->report_id }} - [{{ $rep->kode_unit }}] {{ $rep->nama_unit }} ({{ \Carbon\Carbon::parse($rep->periode)->translatedFormat('F Y') }})
                        </option>
                    @empty
                        <option value="">-- Belum ada laporan terinisiasi --</option>
                    @endforelse
                </select>
            </div>
            <div class="col-md-5 text-md-end mt-2 mt-md-0">
                @if($kalender)
                    <span class="badge {{ $kalender->status_final === 'APPROVED/FINAL' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                        Status Final: {{ $kalender->status_final }}
                    </span>
                @endif
            </div>
        </form>
    </div>

    @if(!$kalender)
        <div class="alert alert-warning text-center py-4 no-print">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            Silakan pilih atau inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan</a>.
        </div>
    @else
        {{-- LEMBAR FORMAL CETAK LAPORAN BULANAN --}}
        <div class="sheet-container">
            {{-- Header Perusahaan & Judul Dokumen --}}
            <div class="d-flex justify-content-between align-items-center sheet-header-title">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="Bank Sulteng" style="height: 48px; object-fit: contain;" onerror="this.style.display='none'">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">PT BANK PEMBANGUNAN DAERAH SULAWESI TENGAH</h5>
                        <div class="small fw-semibold text-muted">SATUAN KERJA AUDIT INTERN (SKAI) - BAGIAN RESIDENT AUDITOR</div>
                    </div>
                </div>
                <div class="text-end">
                    <span class="wp-code-badge">LAPORAN RESMI</span>
                    <div class="small fw-bold text-dark mt-1">LAPORAN BULANAN RESIDENT AUDITOR</div>
                </div>
            </div>

            {{-- 1. IDENTITAS LAPORAN & PANEL ATAS (A7:J13) --}}
            <div class="row g-3 mb-3">
                {{-- Kiri: Identitas Laporan (A7:B13) --}}
                <div class="col-md-4">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center bg-primary text-white">IDENTITAS LAPORAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted" style="width: 40%;">Report ID</td>
                                <td class="fw-bold font-monospace text-primary">{{ $kalender->report_id }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Periode</td>
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($kalender->periode)->translatedFormat('F Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Kode Unit</td>
                                <td class="font-monospace fw-bold">{{ $kalender->kode_unit }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Nama Unit</td>
                                <td class="fw-semibold">{{ $kalender->nama_unit }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Resident Auditor</td>
                                <td>{{ $kalender->resident_auditor }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Tanggal Submit</td>
                                <td class="font-monospace">
                                    {{ $kalender->tanggal_submit ? \Carbon\Carbon::parse($kalender->tanggal_submit)->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Status Final</td>
                                <td>
                                    <span class="badge {{ $kalender->status_final === 'APPROVED/FINAL' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $kalender->status_final }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Quality Score</td>
                                <td class="fw-bold font-monospace text-primary">{{ number_format($kalender->quality_score, 0) }} / 100</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Tengah: Capaian 6 Kegiatan (D7:F13 - SUMIFS dari Rekap Kegiatan) --}}
                <div class="col-md-4">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th colspan="3" class="text-center bg-primary text-white">CAPAIAN 6 KEGIATAN</th>
                            </tr>
                            <tr>
                                <th>Jenis Kegiatan</th>
                                <th class="text-center" style="width: 70px;">Realisasi</th>
                                <th class="text-center" style="width: 70px;">Diuji</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalRealisasi = 0;
                                $totalDiuji = 0;
                            @endphp
                            @foreach($capaianKegiatan as $jk => $metric)
                            @php
                                $totalRealisasi += $metric['realisasi'];
                                $totalDiuji += $metric['sudah_diuji'];
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $jk }}</td>
                                <td class="text-center font-monospace">{{ $metric['realisasi'] }}</td>
                                <td class="text-center font-monospace fw-bold text-primary">{{ $metric['sudah_diuji'] }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold bg-light">
                                <td>TOTAL AKTIVITAS</td>
                                <td class="text-center font-monospace">{{ $totalRealisasi }}</td>
                                <td class="text-center font-monospace text-primary">{{ $totalDiuji }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Kanan: Ringkasan Hasil & Risiko (H7:J12 - COUNTIFS dari Rekap Isu) --}}
                <div class="col-md-4">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th colspan="3" class="text-center bg-primary text-white">RINGKASAN HASIL & RISIKO</th>
                            </tr>
                            <tr>
                                <th>Indikator Isu</th>
                                <th class="text-center" style="width: 65px;">Jumlah</th>
                                <th class="text-center" style="width: 75px;">High Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalIsu = 0;
                                $totalHigh = 0;
                            @endphp
                            @foreach($ringkasanIsu as $label => $data)
                            @php
                                $totalIsu += $data['jumlah'];
                                $totalHigh += $data['high_risk'];
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $label }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $data['jumlah'] }}</td>
                                <td class="text-center font-monospace fw-bold {{ $data['high_risk'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $data['high_risk'] }}
                                </td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold bg-light">
                                <td>TOTAL ISU TERIDENTIFIKASI</td>
                                <td class="text-center font-monospace">{{ $totalIsu }}</td>
                                <td class="text-center font-monospace text-danger">{{ $totalHigh }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. CATATAN EKSEKUTIF, HASIL UTAMA, & RENCANA PENGAWASAN --}}
            <div class="mb-3">
                {{-- 1. Catatan Eksekutif Admin (WYSIWYG) --}}
                <div class="section-title"><i class="bi bi-file-earmark-richtext me-1"></i> 1. CATATAN &amp; ANALISIS EKSEKUTIF (ADMIN / SKAI)</div>
                <div class="narasi-box">
                    @if(!empty($narasi->catatan_eksekutif))
                        {!! $narasi->catatan_eksekutif !!}
                    @elseif(!empty($narasi->ringkasan_eksekutif))
                        {!! nl2br(e($narasi->ringkasan_eksekutif)) !!}
                    @else
                        <span class="text-muted fst-italic">Belum ada catatan eksekutif yang ditambahkan oleh Admin/SKAI.</span>
                    @endif
                </div>

                {{-- 2. Hasil Utama & Temuan Kunci (Tarik Otomatis Level Moderate to High & High) --}}
                <div class="section-title mt-3"><i class="bi bi-shield-exclamation me-1"></i> 2. HASIL UTAMA &amp; TEMUAN KUNCI (MODERATE TO HIGH &amp; HIGH)</div>
                <div class="table-responsive">
                    <table class="report-table mb-2">
                        <thead>
                            <tr>
                                <th style="width: 22%;">Area KKA &amp; Tingkat Risiko</th>
                                <th style="width: 45%;">Uraian Temuan &amp; Eksposur Nominal</th>
                                <th style="width: 33%;">Rekomendasi / Rencana Tindak Lanjut &amp; PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hasilUtamaRows as $row)
                            <tr>
                                <td class="align-top">
                                    <strong>{{ $row['area'] }}</strong>
                                    <div class="mt-1">
                                        <span class="badge {{ $row['risk_level'] === 'HIGH' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ $row['risk_level'] }}
                                        </span>
                                    </div>
                                    <div class="small text-muted mt-1" style="font-size: 0.72rem;">Sumber: {{ $row['source'] }}</div>
                                </td>
                                <td class="align-top">
                                    @if($row['is_temuan_berulang'])
                                        <div class="mb-1">
                                            <span class="badge bg-warning text-dark border border-warning fw-bold">
                                                <i class="bi bi-arrow-repeat me-1"></i> TEMUAN BERULANG
                                            </span>
                                        </div>
                                    @endif
                                    <div style="line-height: 1.5;">{!! nl2br(e($row['uraian'])) !!}</div>
                                    @if($row['nominal'] > 0)
                                        <div class="text-danger fw-bold mt-1 small">
                                            Nominal Eksposur: {{ $row['nominal_formatted'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-top">
                                    <div style="line-height: 1.5;">{!! nl2br(e($row['rekomendasi'] ?: '-')) !!}</div>
                                    <div class="small text-muted mt-2 pt-1 border-top">
                                        PIC: <strong>{{ $row['pic'] ?: '-' }}</strong> | Target: <strong>{{ $row['target_date'] ? \Carbon\Carbon::parse($row['target_date'])->format('d/m/Y') : '-' }}</strong>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-2 text-muted fst-italic">
                                    Tidak ada temuan signifikan (Moderate to High / High) pada periode pengawasan ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 3. Rencana Bulan Berikutnya (WYSIWYG Admin) --}}
                <div class="section-title mt-3"><i class="bi bi-calendar-check me-1"></i> 3. RENCANA KERJA PENGAWASAN BULAN BERIKUTNYA</div>
                <div class="narasi-box">
                    @if(!empty($narasi->rencana_bulan_depan))
                        {!! $narasi->rencana_bulan_depan !!}
                    @elseif(!empty($narasi->rencana_bulan_berikut))
                        {!! nl2br(e($narasi->rencana_bulan_berikut)) !!}
                    @else
                        <span class="text-muted fst-italic">Melanjutkan pengawasan rutin harian, offsite review, dan monitoring tindak lanjut SKAI.</span>
                    @endif
                </div>
            </div>

            {{-- 3. PANEL MONITORING PELAKSANAAN TUGAS RA (A44:K53 - Terhubung Otomatis ke Kalender Kinerja RA) --}}
            <div class="mt-4 pt-2 border-top">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <span>PANEL MONITORING PELAKSANAAN TUGAS RESIDENT AUDITOR (KINERJA BULANAN)</span>
                    <a href="{{ route('pelaporan-bulanan.kalender-kinerja', ['periode' => $kalender->periode]) }}" class="btn btn-xs btn-outline-primary d-print-none text-decoration-none py-0 px-2" style="font-size: 0.72rem;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Kalender Kinerja RA
                    </a>
                </div>

                @php
                    $perf = $raPerformance ?? [
                        'hari_kerja' => 21,
                        'cuti_sakit' => 0,
                        'hari_tugas_dipenuhi' => 0,
                        'tanpa_aktivitas' => 21,
                        'konsistensi' => 0.0,
                        'penyelesaian' => 0.0,
                        'kualitas' => null,
                        'skor' => null,
                        'status_bulanan' => 'BELUM ADA DATA',
                    ];
                    $stClass = match($perf['status_bulanan']) {
                        'BAIK DAN KONSISTEN' => 'bg-success text-white',
                        'MEMADAI' => 'bg-info text-dark',
                        'PERLU PENINGKATAN' => 'bg-warning text-dark',
                        'PERLU TINDAK LANJUT' => 'bg-danger text-white',
                        'BELUM CUKUP DATA REVIEW' => 'bg-secondary text-white',
                        default => 'bg-light text-muted border',
                    };
                @endphp

                <div class="table-responsive mt-2">
                    <table class="report-table text-center align-middle">
                        <thead>
                            <tr class="table-light">
                                <th style="width: 11%;">Hari Kerja</th>
                                <th style="width: 11%;">Cuti / Sakit</th>
                                <th style="width: 14%;">Tugas Dipenuhi</th>
                                <th style="width: 12%;">Tanpa Aktivitas</th>
                                <th style="width: 13%;">Konsistensi (40%)</th>
                                <th style="width: 13%;">Penyelesaian (30%)</th>
                                <th style="width: 13%;">Kualitas Review (30%)</th>
                                <th style="width: 13%;">Skor Kinerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-monospace fw-bold">{{ $perf['hari_kerja'] }} hr</td>
                                <td class="font-monospace {{ $perf['cuti_sakit'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">{{ $perf['cuti_sakit'] }} hr</td>
                                <td class="font-monospace fw-bold text-success">{{ $perf['hari_tugas_dipenuhi'] }} hr</td>
                                <td class="font-monospace {{ $perf['tanpa_aktivitas'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $perf['tanpa_aktivitas'] }} hr</td>
                                <td class="font-monospace fw-bold {{ $perf['konsistensi'] >= 0.8 ? 'text-success' : ($perf['konsistensi'] >= 0.6 ? 'text-warning' : 'text-danger') }}">
                                    {{ round($perf['konsistensi'] * 100, 1) }}%
                                </td>
                                <td class="font-monospace fw-bold {{ $perf['penyelesaian'] >= 0.8 ? 'text-success' : ($perf['penyelesaian'] >= 0.6 ? 'text-warning' : 'text-danger') }}">
                                    {{ round($perf['penyelesaian'] * 100, 1) }}%
                                </td>
                                <td class="font-monospace">
                                    @if($perf['kualitas'] !== null)
                                        <span class="fw-bold {{ $perf['kualitas'] >= 0.8 ? 'text-success' : 'text-warning' }}">{{ round($perf['kualitas'] * 100, 1) }}%</span>
                                    @else
                                        <span class="text-muted fst-italic small">Belum Direview</span>
                                    @endif
                                </td>
                                <td class="font-monospace fs-6 fw-bold {{ $perf['skor'] !== null && $perf['skor'] >= 80 ? 'text-success' : ($perf['skor'] !== null && $perf['skor'] >= 60 ? 'text-warning' : 'text-danger') }}">
                                    {{ $perf['skor'] !== null ? $perf['skor'] : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Status Bulanan Banner (Sel A51:K52) --}}
                <div class="d-flex align-items-center justify-content-between p-2 mt-2 rounded border bg-light">
                    <div class="small fw-semibold text-secondary">
                        <i class="bi bi-award me-1"></i> STATUS EVALUASI KINERJA BULANAN RESIDENT AUDITOR:
                    </div>
                    <div>
                        <span class="badge {{ $stClass }} px-3 py-2 fs-6 text-uppercase fw-bold letter-spacing-1">
                            {{ $perf['status_bulanan'] }}
                        </span>
                    </div>
                </div>

                <div class="small text-muted mt-1 fst-italic">
                    * Catatan: Metrik kinerja bulanan terhitung otomatis dari Roster RA, Log Aktivitas Harian, &amp; Kalender Kinerja RA.
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
