@extends('layouts.app')
@section('title', 'Kalender Pelaksanaan Tugas & Kinerja Bulanan RA')

@section('content')
<style>
    .kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
    }
    .kpi-title {
        font-size: 0.75rem;
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
    .table-responsive {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .table-matrix thead th {
        background: #f8fafc;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #334155;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        padding: 0.6rem 0.5rem;
    }
    .table-matrix tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.55rem 0.5rem;
    }
    .day-cell {
        width: 26px;
        min-width: 26px;
        max-width: 26px;
        height: 26px;
        text-align: center;
        font-weight: 700;
        font-size: 0.72rem;
        line-height: 26px;
        padding: 0 !important;
    }
    /* Status Badge Colors */
    .st-libur { background-color: #f1f5f9; color: #94a3b8; }
    .st-cuti { background-color: #ede9fe; color: #6d28d9; }
    .st-sakit { background-color: #fef3c7; color: #b45309; }
    .st-dinas { background-color: #ccfbf1; color: #0f766e; }
    .st-pelatihan { background-color: #e0f2fe; color: #0369a1; }
    .st-kosong { background-color: #fee2e2; color: #dc2626; font-weight: 800; }
    .st-belum-tuntas { background-color: #ffedd5; color: #c2410c; }
    .st-perbaikan { background-color: #fef08a; color: #854d0e; }
    .st-selesai-blm-review { background-color: #dbeafe; color: #1d4ed8; }
    .st-baik { background-color: #dcfce7; color: #15803d; font-weight: 800; }

    .table-section-id { background: #f8fafc !important; }
    .table-section-kpi { background: #eff6ff !important; }
    .table-section-cal { background: #fafaf9 !important; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Kinerja RA</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-check text-primary"></i> Kalender Pelaksanaan Tugas & Kinerja Bulanan RA
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Matriks pemantauan kehadiran harian, 3 pilar evaluasi kinerja (Konsistensi 40%, Penyelesaian 30%, Kualitas 30%), skor kumulatif, serta status pencapaian bulanan.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Export
            </button>
            <a href="{{ route('pelaporan-bulanan.log-harian', ['periode' => $periode]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-journal-check me-1"></i> Buka Log Harian
            </a>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    @php
        $raRows = $kinerjaData['data'];
        $daysInMonth = $kinerjaData['days_in_month'];
        $hariKerjaBulan = $kinerjaData['hari_kerja_bulan'];

        $totalRa = count($raRows);
        $avgKonsistensi = $totalRa > 0 ? round(collect($raRows)->avg('konsistensi') * 100, 1) : 0;
        $avgPenyelesaian = $totalRa > 0 ? round(collect($raRows)->avg('penyelesaian') * 100, 1) : 0;
        $ratedRows = collect($raRows)->whereNotNull('kualitas');
        $avgKualitas = $ratedRows->isNotEmpty() ? round($ratedRows->avg('kualitas') * 100, 1) : null;
        $scoredRows = collect($raRows)->whereNotNull('skor');
        $avgSkor = $scoredRows->isNotEmpty() ? round($scoredRows->avg('skor'), 1) : null;
        $baikKonsistenCount = collect($raRows)->where('status_bulanan', 'BAIK DAN KONSISTEN')->count();
        $perluTlCount = collect($raRows)->where('status_bulanan', 'PERLU TINDAK LANJUT')->count();
    @endphp

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.kalender-kinerja') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Periode Kinerja (Bulan)</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}">
            </div>
            @if(!$isRa)
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Filter Kantor Cabang</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Kantor Cabang --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_unit') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Filter Resident Auditor</label>
                <select name="ra_id" class="form-select form-select-sm">
                    <option value="">-- Seluruh Resident Auditor --</option>
                    @foreach($masterRas as $mra)
                        <option value="{{ $mra->ra_id }}" {{ request('ra_id') == $mra->ra_id ? 'selected' : '' }}>
                            {{ $mra->ra_id }} - {{ $mra->nama_ra }} ({{ $mra->nama_kc }})
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Akun Resident Auditor Anda (Terisolasi)</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $myRaId ? ($myRaId . ' - ' . auth()->user()->name) : 'Terisolasi Cabang Anda' }}" readonly>
            </div>
            @endif
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ route('pelaporan-bulanan.kalender-kinerja') }}" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Hari Kerja Efektif</div>
                <div class="kpi-value text-primary">{{ $hariKerjaBulan }} <span class="fs-6 text-muted">hari</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Formula NETWORKDAYS</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Rata2 Konsistensi</div>
                <div class="kpi-value text-success">{{ $avgKonsistensi }}%</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Bobot: 40%</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Rata2 Penyelesaian</div>
                <div class="kpi-value text-info">{{ $avgPenyelesaian }}%</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Bobot: 30%</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Rata2 Kualitas</div>
                <div class="kpi-value {{ $avgKualitas !== null ? 'text-primary' : 'text-muted' }}">
                    {{ $avgKualitas !== null ? $avgKualitas . '%' : '-' }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">Bobot: 30%</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Rata2 Skor Kinerja</div>
                <div class="kpi-value {{ $avgSkor !== null ? ($avgSkor >= 80 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                    {{ $avgSkor !== null ? $avgSkor : '-' }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">Skala 0–100</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Status Kinerja</div>
                <div class="kpi-value text-success">{{ $baikKonsistenCount }} <span class="fs-6 text-muted">/ {{ $totalRa }}</span></div>
                <div class="text-muted small" style="font-size: 0.72rem;">Baik &amp; Konsisten (&ge;90)</div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered table-matrix mb-0">
            <thead>
                {{-- Super Headers --}}
                <tr>
                    <th colspan="7" class="table-section-id text-primary">
                        <i class="bi bi-person-bounding-box me-1"></i> IDENTITAS & HARI KERJA
                    </th>
                    <th colspan="9" class="table-section-kpi text-success">
                        <i class="bi bi-award me-1"></i> METRIK KINERJA & SKOR
                    </th>
                    <th colspan="31" class="table-section-cal text-dark">
                        <i class="bi bi-calendar3 me-1"></i> MATRIKS KEHADIRAN & TUGAS HARIAN (TANGGAL 1 - 31)
                    </th>
                </tr>
                {{-- Column Headers --}}
                <tr>
                    <th>RA ID</th>
                    <th>Nama RA</th>
                    <th>KC</th>
                    <th>Kerja</th>
                    <th>Cuti/Skt</th>
                    <th>Dipenuhi</th>
                    <th>Kosong</th>

                    <th>Konsistensi</th>
                    <th>Aktivitas</th>
                    <th>Selesai</th>
                    <th>Penyelesaian</th>
                    <th>Direview</th>
                    <th>Memadai</th>
                    <th>Kualitas</th>
                    <th>Skor</th>
                    <th>Status Bulanan</th>

                    {{-- 31 Kolom Tanggal --}}
                    @for($t = 1; $t <= 31; $t++)
                        <th class="day-cell {{ $t > $daysInMonth ? 'bg-secondary-subtle' : '' }}" title="Tanggal {{ $t }}">
                            {{ $t }}
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($raRows as $row)
                <tr>
                    {{-- Identitas & Hari Kerja (A s.d. G) --}}
                    <td class="text-center fw-bold font-monospace text-primary">{{ $row['ra_id'] }}</td>
                    <td class="fw-semibold">{{ $row['nama_ra'] }}</td>
                    <td class="text-center font-monospace" title="{{ $row['nama_kc'] }}">{{ $row['kode_kc'] }}</td>
                    <td class="text-center fw-bold">{{ $row['hari_kerja'] }}</td>
                    <td class="text-center">{{ $row['cuti_sakit'] }}</td>
                    <td class="text-center fw-bold text-success">{{ $row['hari_tugas_dipenuhi'] }}</td>
                    <td class="text-center {{ $row['tanpa_aktivitas'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                        {{ $row['tanpa_aktivitas'] }}
                    </td>

                    {{-- Metrik Kinerja & Skor (H s.d. P) --}}
                    <td class="text-center fw-bold text-success">{{ number_format($row['konsistensi'] * 100, 0) }}%</td>
                    <td class="text-center">{{ $row['aktivitas'] }}</td>
                    <td class="text-center">{{ $row['selesai'] }}</td>
                    <td class="text-center fw-bold text-info">{{ number_format($row['penyelesaian'] * 100, 0) }}%</td>
                    <td class="text-center text-muted">{{ $row['direview'] }}</td>
                    <td class="text-center text-muted">{{ $row['memadai'] }}</td>
                    <td class="text-center fw-bold {{ $row['kualitas'] !== null ? ($row['kualitas'] >= 0.8 ? 'text-success' : 'text-warning') : 'text-muted' }}">
                        {{ $row['kualitas'] !== null ? number_format($row['kualitas'] * 100, 0) . '%' : '-' }}
                    </td>
                    <td class="text-center fw-bold fs-6 {{ $row['skor'] !== null ? ($row['skor'] >= 80 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                        {{ $row['skor'] !== null ? $row['skor'] : '-' }}
                    </td>
                    <td class="text-center">
                        @if($row['status_bulanan'] === 'BAIK DAN KONSISTEN')
                            <span class="badge bg-success"><i class="bi bi-star-fill me-1"></i>BAIK &amp; KONSISTEN</span>
                        @elseif($row['status_bulanan'] === 'MEMADAI')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">MEMADAI</span>
                        @elseif($row['status_bulanan'] === 'PERLU PENINGKATAN')
                            <span class="badge bg-warning text-dark">PERLU PENINGKATAN</span>
                        @elseif($row['status_bulanan'] === 'PERLU TINDAK LANJUT')
                            <span class="badge bg-danger">PERLU TINDAK LANJUT</span>
                        @elseif($row['status_bulanan'] === 'BELUM CUKUP DATA REVIEW')
                            <span class="badge bg-info-subtle text-info border">BELUM CUKUP REVIEW</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">BELUM ADA DATA</span>
                        @endif
                    </td>

                    {{-- Matriks Hari 1 s.d. 31 (Q s.d. AU) --}}
                    @for($t = 1; $t <= 31; $t++)
                        @php
                            $m = $row['matrix_hari'][$t] ?? ['status' => ''];
                            $st = $m['status'];
                            $class = 'st-libur';
                            $label = '';

                            if ($st === 'LIBUR') {
                                $class = 'st-libur';
                                $label = 'L';
                            } elseif ($st === 'CUTI') {
                                $class = 'st-cuti';
                                $label = 'C';
                            } elseif ($st === 'SAKIT') {
                                $class = 'st-sakit';
                                $label = 'S';
                            } elseif ($st === 'DINAS LUAR') {
                                $class = 'st-dinas';
                                $label = 'D';
                            } elseif ($st === 'PELATIHAN') {
                                $class = 'st-pelatihan';
                                $label = 'P';
                            } elseif ($st === 'TIDAK ADA AKTIVITAS') {
                                $class = 'st-kosong';
                                $label = 'X';
                            } elseif ($st === 'BELUM TUNTAS') {
                                $class = 'st-belum-tuntas';
                                $label = 'BT';
                            } elseif ($st === 'PERLU PERBAIKAN') {
                                $class = 'st-perbaikan';
                                $label = 'PP';
                            } elseif ($st === 'SELESAI - BELUM REVIEW') {
                                $class = 'st-selesai-blm-review';
                                $label = 'SR';
                            } elseif ($st === 'BAIK') {
                                $class = 'st-baik';
                                $label = 'OK';
                            }
                        @endphp
                        <td class="day-cell {{ $class }}" title="Tgl {{ $t }}: {{ $st ?: '-' }}">
                            {{ $label }}
                        </td>
                    @endfor
                </tr>
                @empty
                <tr>
                    <td colspan="47" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                        Tidak ada data kinerja Resident Auditor pada periode <strong>{{ $periode }}</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legend & Formula Notes --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <h6 class="fw-bold small text-dark mb-2">
                <i class="bi bi-info-circle text-primary me-1"></i> Panduan Singkatan &amp; Hierarki Status Harian:
            </h6>
            <div class="d-flex flex-wrap gap-2 small">
                <span class="badge st-baik border px-2 py-1">OK: BAIK (Completed & Memadai)</span>
                <span class="badge st-selesai-blm-review border px-2 py-1">SR: SELESAI (Belum Review)</span>
                <span class="badge st-perbaikan border px-2 py-1">PP: PERLU PERBAIKAN</span>
                <span class="badge st-belum-tuntas border px-2 py-1">BT: BELUM TUNTAS</span>
                <span class="badge st-dinas border px-2 py-1">D: DINAS LUAR</span>
                <span class="badge st-pelatihan border px-2 py-1">P: PELATIHAN</span>
                <span class="badge st-cuti border px-2 py-1">C: CUTI</span>
                <span class="badge st-sakit border px-2 py-1">S: SAKIT</span>
                <span class="badge st-kosong border px-2 py-1">X: TIDAK ADA AKTIVITAS</span>
                <span class="badge st-libur border px-2 py-1">L: LIBUR (Akhir Pekan / Libur Nasional)</span>
            </div>
            <div class="small text-muted mt-2 border-top pt-2">
                <strong>Formula Skor Kinerja Bulanan:</strong>
                <code>=ROUND((Konsistensi &times; 40) + (Penyelesaian &times; 30) + (Kualitas Review &times; 30), 0)</code>
                &nbsp;|&nbsp; Ambang Status: &ge;90 (Baik &amp; Konsisten), &ge;80 (Memadai), &ge;60 (Perlu Peningkatan), &lt;60 (Perlu Tindak Lanjut).
            </div>
        </div>
    </div>
</div>
@endsection
