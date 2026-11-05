@extends('layouts.app')
@section('title', 'Matriks Konsolidasi Bulanan Seluruh Cabang (SOP 08)')

@section('content')
<style>
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
    }
    .kpi-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .kpi-value {
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .table-matrix thead th {
        background: #0f172a;
        color: #f8fafc;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        padding: 0.75rem 0.65rem;
    }
    .table-matrix tbody td {
        font-size: 0.84rem;
        vertical-align: middle;
        padding: 0.7rem 0.65rem;
        color: #1e293b;
    }
    .table-matrix tfoot th {
        background: #e2e8f0;
        font-size: 0.82rem;
        font-weight: 800;
        color: #0f172a;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.75rem 0.65rem;
    }
    .badge-hijau {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
    .badge-merah {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }
    .badge-ulang {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 0.2rem 0.45rem;
        border-radius: 4px;
    }
    .section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
    }
    .section-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.85rem 1.25rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.dashboard') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Konsolidasi Bulanan</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill text-primary"></i> Konsolidasi Bulanan &amp; Matriks Capaian Seluruh Cabang
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Akumulasi kejadian operasional, indikator kepatuhan KKA otomatis, dan temuan kunci seluruh unit kerja PT. Bank Sulteng.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pelaporan-bulanan.konsolidasi.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Print PDF
            </a>
            <a href="{{ route('pelaporan-bulanan.konsolidasi.export-excel', request()->query()) }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm text-white">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (.xls)
            </a>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Mode Preview Notice for RA --}}
    @if($isRa)
        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center">
            <i class="bi bi-eye-fill fs-4 me-3 text-primary"></i>
            <div>
                <h6 class="fw-bold mb-0">Mode Preview (Read-Only) Resident Auditor</h6>
                <small class="text-muted">
                    Sesuai SOP 08 revisi, seluruh data ditarik 100% otomatis dari database tanpa pengisian form manual. Halaman ini berfungsi sebagai rekapitulasi capaian dan temuan cabang Anda.
                </small>
            </div>
        </div>
    @endif

    {{-- SMART FILTERS CARD FOR ADMIN --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.konsolidasi') }}" class="row g-2 align-items-end">
            {{-- 1. Periode Bulan-Tahun --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar3 me-1"></i> Periode (Bulan)</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}">
            </div>

            {{-- 2. Filter Cabang --}}
            @if(!$isRa)
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-building me-1"></i> Cabang / Unit Kerja</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Cabang (Konsolidasi) --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ ($filters['kode_unit'] ?? '') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Unit Kerja Anda</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->unit_kerja ?? 'Cabang Terisolasi' }}" readonly>
            </div>
            @endif

            {{-- 3. Filter Status Kepatuhan KKA (Semua | Merah / Belum Selesai | Hijau / Lengkap) --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-shield-check me-1"></i> Kepatuhan KKA</label>
                <select name="kepatuhan" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="MERAH" {{ ($filters['kepatuhan'] ?? '') === 'MERAH' ? 'selected' : '' }}>🔴 Merah / Belum Selesai</option>
                    <option value="HIJAU" {{ ($filters['kepatuhan'] ?? '') === 'HIJAU' ? 'selected' : '' }}>🟢 Hijau / Lengkap</option>
                </select>
            </div>

            {{-- 4. Filter Status Koreksi / Approval --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-check2-circle me-1"></i> Status Approval</label>
                <select name="status_approval" class="form-select form-select-sm">
                    <option value="">-- Semua Approval --</option>
                    <option value="MENUNGGU_APPROVAL" {{ ($filters['status_approval'] ?? '') === 'MENUNGGU_APPROVAL' ? 'selected' : '' }}>Menunggu Approval / Koreksi</option>
                    <option value="APPROVED" {{ ($filters['status_approval'] ?? '') === 'APPROVED' ? 'selected' : '' }}>Approved / Final</option>
                </select>
            </div>

            {{-- 5. Filter Isu Signifikan --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Isu Signifikan</label>
                <select name="isu_signifikan" class="form-select form-select-sm">
                    <option value="">-- Semua Isu --</option>
                    <option value="YA" {{ ($filters['isu_signifikan'] ?? '') === 'YA' ? 'selected' : '' }}>Ada Isu Signifikan / High</option>
                    <option value="TIDAK" {{ ($filters['isu_signifikan'] ?? '') === 'TIDAK' ? 'selected' : '' }}>Tanpa Isu Signifikan</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="{{ route('pelaporan-bulanan.konsolidasi') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    @php
        $totalCabang = count($matrixRows);
        $totalHijau = collect($matrixRows)->where('kepatuhan_status', 'HIJAU')->count();
        $totalMerah = collect($matrixRows)->where('kepatuhan_status', 'MERAH')->count();
        $totalReversal = collect($matrixRows)->sum('reversal_count');
        $totalSelisihKas = collect($matrixRows)->sum('selisih_kas_count');
        $totalLimitBiaya = collect($matrixRows)->sum('limit_biaya_count');
        $totalTemuanHigh = collect($matrixRows)->sum('mod_to_high_count');
        $totalOverdue = collect($matrixRows)->sum('tl_overdue_count');
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Kepatuhan KKA Lengkap</div>
                <div class="kpi-value text-success">{{ $totalHijau }} <span class="fs-6 text-muted">/ {{ $totalCabang }}</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">🟢 Patuh / Nihil Tuntas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Kepatuhan Belum Selesai</div>
                <div class="kpi-value text-danger">{{ $totalMerah }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">🔴 Tertunggak / Belum Lengkap</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Akumulasi Kejadian Harian</div>
                <div class="kpi-value text-primary">{{ $totalReversal + $totalSelisihKas + $totalLimitBiaya }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Reversal ({{ $totalReversal }}) | Selisih ({{ $totalSelisihKas }}) | Limit ({{ $totalLimitBiaya }})</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="kpi-card text-center">
                <div class="kpi-title">Temuan Mod-to-High &amp; Overdue</div>
                <div class="kpi-value text-danger">{{ $totalTemuanHigh }} <span class="fs-6 text-muted">| {{ $totalOverdue }} OD</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Prioritas Pengawasan SKAI</div>
            </div>
        </div>
    </div>

    {{-- 3. TABEL MATRIKS KONSOLIDASI BULANAN SELURUH CABANG (7 KOLOM UTAMA) --}}
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-table me-2 text-primary"></i> Tabel Matriks Konsolidasi Bulanan Seluruh Cabang
                </h6>
                <small class="text-muted">Perbandingan real-time frekuensi kejadian harian 1 bulan penuh (seluruh cabang PT. Bank Sulteng ditampilkan)</small>
            </div>
            <span class="badge bg-primary px-3 py-1">Periode {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-matrix mb-0">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama / Kode Cabang</th>
                        <th style="width: 14%;">Status Kepatuhan KKA</th>
                        <th style="width: 12%;">Jumlah Transaksi Reversal</th>
                        <th style="width: 12%;">Jumlah Selisih Kas</th>
                        <th style="width: 13%;">Jumlah Pelampauan Limit / Biaya</th>
                        <th style="width: 12%;">Jumlah Temuan Mod-to-High</th>
                        <th style="width: 12%;">Status Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matrixRows as $row)
                    <tr>
                        {{-- 1. Nama / Kode Cabang --}}
                        <td>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-light text-dark border me-1 font-monospace">{{ $row['kode_unit'] }}</span>
                                    <strong class="text-dark">{{ $row['nama_unit'] }}</strong>
                                    <div class="small text-muted" style="font-size: 0.72rem;">RA: {{ $row['resident_auditor'] ?: '-' }}</div>
                                </div>
                                @if($row['report_id'])
                                    <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $row['report_id']) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.72rem;" title="Lihat Preview Laporan">
                                        <i class="bi bi-eye"></i> Preview
                                    </a>
                                @endif
                            </div>
                        </td>

                        {{-- 2. Status Kepatuhan KKA (Badge Hijau / Merah) --}}
                        <td class="text-center">
                            @if($row['kepatuhan_status'] === 'HIJAU')
                                <span class="badge-hijau d-inline-block">
                                    <i class="bi bi-check-circle-fill me-1"></i> {{ $row['kepatuhan_badge'] }}
                                </span>
                            @else
                                <span class="badge-merah d-inline-block">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $row['kepatuhan_badge'] }}
                                </span>
                            @endif
                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">{{ $row['kepatuhan_detail'] }}</div>
                        </td>

                        {{-- 3. Jumlah Transaksi Reversal --}}
                        <td class="text-center font-monospace fw-bold {{ $row['reversal_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ number_format($row['reversal_count']) }}
                        </td>

                        {{-- 4. Jumlah Selisih Kas --}}
                        <td class="text-center font-monospace fw-bold {{ $row['selisih_kas_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ number_format($row['selisih_kas_count']) }}
                        </td>

                        {{-- 5. Jumlah Pelampauan Limit / Biaya Besar --}}
                        <td class="text-center font-monospace fw-bold {{ $row['limit_biaya_count'] > 0 ? 'text-warning' : 'text-muted' }}">
                            {{ number_format($row['limit_biaya_count']) }}
                        </td>

                        {{-- 6. Jumlah Temuan Moderate-to-High & High --}}
                        <td class="text-center">
                            @if($row['mod_to_high_count'] > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-1 font-monospace fs-6">
                                    {{ $row['mod_to_high_count'] }}
                                </span>
                            @else
                                <span class="text-muted font-monospace">0</span>
                            @endif
                        </td>

                        {{-- 7. Status Tindak Lanjut Aktif / Overdue --}}
                        <td class="text-center">
                            @if($row['tl_overdue_count'] > 0)
                                <span class="badge bg-danger rounded-pill px-2 py-1 me-1" title="Temuan Overdue">
                                    {{ $row['tl_overdue_count'] }} Overdue
                                </span>
                            @endif
                            @if($row['tl_active_count'] > 0)
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1" title="Temuan Aktif / In Progress">
                                    {{ $row['tl_active_count'] }} Aktif
                                </span>
                            @elseif($row['tl_overdue_count'] == 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                    Nihil / Tuntas
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Tidak ada data matriks cabang untuk filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($matrixRows) > 0)
                <tfoot>
                    <tr>
                        <th class="text-end">TOTAL KONSOLIDASI SELURUH CABANG:</th>
                        <th class="text-center">{{ $totalHijau }} Hijau / {{ $totalMerah }} Merah</th>
                        <th class="text-center font-monospace">{{ number_format($totalReversal) }}</th>
                        <th class="text-center font-monospace">{{ number_format($totalSelisihKas) }}</th>
                        <th class="text-center font-monospace">{{ number_format($totalLimitBiaya) }}</th>
                        <th class="text-center font-monospace text-danger">{{ number_format($totalTemuanHigh) }}</th>
                        <th class="text-center font-monospace text-danger">{{ number_format($totalOverdue) }} Overdue</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- 4. BAGIAN HASIL UTAMA & TEMUAN KUNCI (LEVEL MODERATE TO HIGH & HIGH) --}}
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-exclamation me-2 text-danger"></i> Hasil Utama &amp; Temuan Kunci (Level Moderate to High &amp; High)
                </h6>
                <small class="text-muted">Ditarik 100% otomatis dari seluruh modul pengawasan dengan penanda otomatis "Temuan Berulang"</small>
            </div>
            <span class="badge bg-danger px-3 py-1">{{ count($hasilUtamaRows) }} Temuan Signifikan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 22%;">Cabang &amp; Area KKA</th>
                        <th style="width: 45%;">Uraian Penjelasan Temuan &amp; Eksposur Nominal</th>
                        <th style="width: 33%;">Rekomendasi / Rencana Tindak Lanjut &amp; PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hasilUtamaRows as $t)
                    <tr>
                        {{-- Cabang & Area --}}
                        <td class="align-top">
                            <span class="badge bg-light text-dark border font-monospace me-1">{{ $t['kode_unit'] }}</span>
                            <strong>{{ $t['nama_unit'] }}</strong>
                            <div class="mt-1">
                                <span class="badge bg-secondary-subtle text-secondary border small">{{ $t['area'] }}</span>
                                <span class="badge {{ $t['risk_level'] === 'HIGH' ? 'bg-danger' : 'bg-warning text-dark' }} ms-1">
                                    {{ $t['risk_level'] }}
                                </span>
                            </div>
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Sumber: {{ $t['source'] }}</div>
                        </td>

                        {{-- Uraian Temuan + Badge Berulang + Nominal --}}
                        <td class="align-top">
                            @if($t['is_temuan_berulang'])
                                <div class="mb-1">
                                    <span class="badge-ulang">
                                        <i class="bi bi-arrow-repeat me-1"></i> TEMUAN BERULANG
                                    </span>
                                </div>
                            @endif
                            <div class="text-dark small" style="line-height: 1.5;">
                                {!! nl2br(e($t['uraian'])) !!}
                            </div>
                            @if($t['nominal'] > 0)
                                <div class="mt-2 text-danger fw-bold small">
                                    <i class="bi bi-cash-stack me-1"></i> Nominal Eksposur: {{ $t['nominal_formatted'] }}
                                </div>
                            @endif
                        </td>

                        {{-- Rekomendasi & PIC --}}
                        <td class="align-top">
                            <div class="small text-dark" style="line-height: 1.5;">
                                <strong>Rekomendasi:</strong><br>
                                {!! nl2br(e($t['rekomendasi'] ?: '-')) !!}
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2 pt-1 border-top small text-muted" style="font-size: 0.75rem;">
                                <span><i class="bi bi-person-badge me-1"></i> PIC: <strong>{{ $t['pic'] ?: '-' }}</strong></span>
                                <span><i class="bi bi-clock me-1"></i> Target: <strong>{{ $t['target_date'] ? \Carbon\Carbon::parse($t['target_date'])->format('d/m/Y') : '-' }}</strong></span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                            Tidak ada temuan berskala Moderate to High atau High pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- REKAP AKTIVITAS HARIAN TERINTEGRASI (PENGGABUNGAN LOG HARIAN & KKA FISIK) --}}
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-journal-check me-2 text-success"></i> Rekap Aktivitas Harian Terintegrasi (100% Tarikan DB)
                </h6>
                <small class="text-muted">Data otomatis pengawasan harian tanpa entri duplikat manual</small>
            </div>
            <span class="badge bg-success px-3 py-1">{{ count($unifiedActivities) }} Aktivitas Terekam</span>
        </div>
        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
            <table class="table table-sm table-hover table-bordered mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 20%;">Cabang</th>
                        <th style="width: 25%;">Jenis Kegiatan / Pengujian</th>
                        <th style="width: 33%;">Uraian Detail Aktivitas</th>
                        <th style="width: 10%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($unifiedActivities, 0, 100) as $act)
                    <tr>
                        <td class="font-monospace small text-center">{{ $act['tanggal'] ?? (!empty($act['tanggal_raw']) ? \Carbon\Carbon::parse($act['tanggal_raw'])->format('d/m/Y') : '-') }}</td>
                        <td class="small"><span class="badge bg-light text-dark border me-1">{{ $act['kode_unit'] }}</span> {{ $act['nama_unit'] }}</td>
                        <td class="small fw-semibold">{{ $act['kegiatan'] ?? $act['jenis_aktivitas'] ?? '-' }}</td>
                        <td class="small text-muted">{{ $act['detail'] ?? $act['output_wp'] ?? $act['area'] ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $act['status'] ?? $act['status_penyelesaian'] ?? 'COMPLETED' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 text-muted">Belum ada aktivitas terekam untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
