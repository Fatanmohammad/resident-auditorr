@extends('layouts.app')
@section('title', 'Log Aktivitas Harian RA')

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
        font-size: 0.73rem;
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
    .table thead th {
        background: #f8fafc;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
    }
    .table tbody td {
        font-size: 0.875rem;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.75rem 0.85rem;
        color: #334155;
    }
    .table-section-ra { background: #f8fafc !important; }
    .table-section-activity { background: #f0fdf4 !important; }
    .table-section-review { background: #eff6ff !important; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">Pelaporan Bulanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log Harian</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-journal-check text-success"></i> Log Aktivitas Harian Resident Auditor
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Pencatatan aktivitas pengujian harian, kehadiran (Kerja/Cuti/Sakit/Dinas), dan hasil review mutu oleh Reviewer/Bagian RA.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Tombol Auto Pull dari Modul Kerja --}}
            <form method="POST" action="{{ route('pelaporan-bulanan.log-harian.pull') }}" class="d-inline">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                @if(!$isRa)
                    <input type="hidden" name="ra_id" value="{{ request('ra_id') ?: ($masterRas->first()?->ra_id) }}">
                @endif
                <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" onclick="return confirm('Tarik otomatis aktivitas audit yang telah dikerjakan di modul SOP 03, SOP 04, SOP 05, SOP 06 pada periode {{ $periode }}?')">
                    <i class="bi bi-cloud-arrow-down me-1"></i> Tarik Log dari Modul Kerja
                </button>
            </form>

            <a href="{{ route('pelaporan-bulanan.log-harian.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> + Input Log Harian / Status Hari
            </a>

            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Export
            </button>
        </div>
    </div>

    {{-- Sub-Menu Navigation Tabs --}}
    @include('pelaporan_bulanan.tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.log-harian') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Periode Bulan</label>
                <input type="month" name="periode" class="form-control form-control-sm" value="{{ $periode }}">
            </div>
            @if(!$isRa)
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Resident Auditor</label>
                <select name="ra_id" class="form-select form-select-sm">
                    <option value="">-- Seluruh Resident Auditor --</option>
                    @foreach($masterRas as $mra)
                        <option value="{{ $mra->ra_id }}" {{ request('ra_id') == $mra->ra_id ? 'selected' : '' }}>
                            {{ $mra->ra_id }} - {{ $mra->nama_ra }} ({{ $mra->nama_kc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Kantor Cabang</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Cabang --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_unit') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">Akun Resident Auditor Anda</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $myMasterRa ? ($myMasterRa->ra_id . ' - ' . $myMasterRa->nama_ra . ' [' . $myMasterRa->nama_kc . ']') : 'Terisolasi Cabang Anda' }}" readonly>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Hari</label>
                <select name="status_hari" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statusHariList as $sh)
                        <option value="{{ $sh }}" {{ request('status_hari') == $sh ? 'selected' : '' }}>{{ $sh }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Hasil Review</label>
                <select name="hasil_review" class="form-select form-select-sm">
                    <option value="">-- Semua Hasil Review --</option>
                    @foreach($hasilReviewList as $hr)
                        <option value="{{ $hr }}" {{ request('hasil_review') == $hr ? 'selected' : '' }}>{{ $hr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm px-2 w-100 shadow-sm" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    @php
        $totalLogs = $logList->count();
        $kerjaCount = $logList->where('status_hari', 'KERJA')->count();
        $completedKerja = $logList->where('status_hari', 'KERJA')->where('status_penyelesaian', 'COMPLETED')->count();
        $cutiSakitDinas = $logList->whereIn('status_hari', ['CUTI', 'SAKIT', 'DINAS LUAR', 'PELATIHAN'])->count();
        $belumReview = $logList->where('status_hari', 'KERJA')->where('hasil_review', 'BELUM DIREVIEW')->count();
        $memadaiCount = $logList->where('status_hari', 'KERJA')->where('hasil_review', 'MEMADAI')->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Log Terdaftar</div>
                <div class="kpi-value text-primary">{{ $totalLogs }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Periode {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Aktivitas Kerja</div>
                <div class="kpi-value text-success">{{ $kerjaCount }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">{{ $completedKerja }} Selesai (Completed)</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Cuti / Sakit / Dinas</div>
                <div class="kpi-value text-info">{{ $cutiSakitDinas }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Status Hari Izin Resmi</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Belum Direview</div>
                <div class="kpi-value text-warning">{{ $belumReview }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Menunggu Reviewer</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Review Memadai</div>
                <div class="kpi-value text-success">{{ $memadaiCount }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Kualitas Kerja Baik</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Kualitas Review</div>
                @php
                    $reviewedCount = $logList->where('status_hari', 'KERJA')->whereIn('hasil_review', ['MEMADAI', 'PERLU PERBAIKAN', 'TIDAK MEMADAI'])->count();
                    $kualitasPct = $reviewedCount > 0 ? round(($memadaiCount / $reviewedCount) * 100, 0) : null;
                @endphp
                <div class="kpi-value {{ $kualitasPct !== null ? ($kualitasPct >= 80 ? 'text-success' : 'text-danger') : 'text-muted' }}">
                    {{ $kualitasPct !== null ? $kualitasPct . '%' : '-' }}
                </div>
                <div class="text-muted small" style="font-size: 0.72rem;">{{ $reviewedCount }} Log Dinilai</div>
            </div>
        </div>
    </div>

    {{-- Form Batch Review (Admin / Reviewer Only) --}}
    @if(!$isRa)
    <form method="POST" action="{{ route('pelaporan-bulanan.log-harian.batch-review') }}" id="batchReviewForm">
        @csrf
        <div class="card mb-3 border-primary-subtle bg-light shadow-sm">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-2 py-1"><i class="bi bi-check2-square me-1"></i> Review Massal</span>
                    <span class="small text-muted">Centang log pada tabel di bawah lalu tentukan hasil evaluasi:</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select name="hasil_review" class="form-select form-select-sm" required style="width: 220px;">
                        <option value="MEMADAI">Nilai: MEMADAI</option>
                        <option value="PERLU PERBAIKAN">Nilai: PERLU PERBAIKAN</option>
                        <option value="TIDAK MEMADAI">Nilai: TIDAK MEMADAI</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold" onclick="return confirm('Terapkan penilaian hasil review untuk seluruh log terpilih?')">
                        <i class="bi bi-save me-1"></i> Terapkan Review
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Main 17-Column Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                {{-- Super Headers --}}
                <tr>
                    @if(!$isRa)
                    <th rowspan="2" style="width: 35px;" class="text-center align-middle bg-light">
                        <input type="checkbox" id="checkAll" class="form-check-input" onclick="toggleCheckboxes(this)">
                    </th>
                    @endif
                    <th colspan="6" class="table-section-ra text-primary">
                        <i class="bi bi-person-lines-fill me-1"></i> IDENTITAS LOG &amp; RA
                    </th>
                    <th colspan="6" class="table-section-activity text-success">
                        <i class="bi bi-clipboard-pulse me-1"></i> AKTIVITAS KERJA / STATUS KEHADIRAN
                    </th>
                    <th colspan="3" class="table-section-review text-info">
                        <i class="bi bi-shield-check me-1"></i> REVIEW MUTU REVIEWER
                    </th>
                    <th rowspan="2" class="bg-light text-center" style="width: 70px;">Aksi</th>
                </tr>
                {{-- Column Headers --}}
                <tr>
                    <th>Log ID</th>
                    <th>Tanggal</th>
                    <th>RA ID</th>
                    <th>Nama RA</th>
                    <th>Kode</th>
                    <th>Nama Unit</th>

                    <th>Status Hari</th>
                    <th>Sumber Modul</th>
                    <th style="min-width: 180px;">Jenis Aktivitas</th>
                    <th>Area</th>
                    <th>Referensi WP</th>
                    <th>Status</th>

                    <th>Hasil Review</th>
                    <th>Reviewer</th>
                    <th>Tgl Review</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logList as $log)
                @php
                    $isKerja = $log->status_hari === 'KERJA';
                @endphp
                <tr>
                    @if(!$isRa)
                    <td class="text-center">
                        <input type="checkbox" name="log_ids[]" value="{{ $log->id }}" class="form-check-input row-checkbox">
                    </td>
                    @endif

                    {{-- Kolom A: Log ID --}}
                    <td class="fw-bold text-primary font-monospace">{{ $log->log_id }}</td>

                    {{-- Kolom B: Tanggal --}}
                    <td class="text-center">{{ \Carbon\Carbon::parse($log->tanggal)->format('d/m/Y') }}</td>

                    {{-- Kolom C: RA ID --}}
                    <td class="text-center font-monospace">{{ $log->ra_id }}</td>

                    {{-- Kolom D: Nama RA --}}
                    <td class="fw-semibold">{{ $log->nama_ra }}</td>

                    {{-- Kolom E: Kode Unit --}}
                    <td class="text-center font-monospace">{{ $log->kode_unit }}</td>

                    {{-- Kolom F: Nama Unit --}}
                    <td>{{ $log->nama_unit }}</td>

                    {{-- Kolom G: Status Hari --}}
                    <td class="text-center">
                        @if($log->status_hari === 'KERJA')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2">KERJA</span>
                        @elseif($log->status_hari === 'CUTI')
                            <span class="badge bg-purple-subtle text-purple border px-2" style="background:#ede9fe; color:#6d28d9;">CUTI</span>
                        @elseif($log->status_hari === 'SAKIT')
                            <span class="badge bg-warning-subtle text-warning border px-2" style="background:#fef3c7; color:#b45309;">SAKIT</span>
                        @elseif($log->status_hari === 'DINAS LUAR')
                            <span class="badge bg-info-subtle text-info border px-2">DINAS LUAR</span>
                        @elseif($log->status_hari === 'PELATIHAN')
                            <span class="badge bg-primary-subtle text-primary border px-2">PELATIHAN</span>
                        @else
                            <span class="badge bg-secondary">{{ $log->status_hari }}</span>
                        @endif
                    </td>

                    {{-- Kolom H: Sumber SOP --}}
                    <td class="small">{{ $log->sumber_sop ?: '-' }}</td>

                    {{-- Kolom I: Jenis Aktivitas --}}
                    <td style="max-width: 260px; white-space: normal;" class="small">
                        {{ $log->jenis_aktivitas }}
                        @if($log->jumlah_item > 0)
                            <span class="badge bg-light text-muted border ms-1">{{ $log->jumlah_item }} item</span>
                        @endif
                    </td>

                    {{-- Kolom J: Area --}}
                    <td class="small">{{ $log->area ?: '-' }}</td>

                    {{-- Kolom K: Output / Referensi WP --}}
                    <td class="small font-monospace">{{ $log->output_referensi_wp ?: '-' }}</td>

                    {{-- Kolom L: Status Penyelesaian --}}
                    <td class="text-center">
                        @if($log->status_penyelesaian === 'COMPLETED')
                            <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>COMPLETED</span>
                        @elseif($log->status_penyelesaian === 'IN PROGRESS')
                            <span class="badge bg-warning text-dark">IN PROGRESS</span>
                        @elseif($log->status_penyelesaian === 'PENDING')
                            <span class="badge bg-danger">PENDING</span>
                        @else
                            <span class="text-muted fst-italic">-</span>
                        @endif
                    </td>

                    {{-- Kolom M: Hasil Review --}}
                    <td class="text-center">
                        @if($log->hasil_review === 'MEMADAI')
                            <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>MEMADAI</span>
                        @elseif($log->hasil_review === 'PERLU PERBAIKAN')
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-exclamation-triangle-fill me-1"></i>PERLU PERBAIKAN</span>
                        @elseif($log->hasil_review === 'TIDAK MEMADAI')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>TIDAK MEMADAI</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">BELUM DIREVIEW</span>
                        @endif
                    </td>

                    {{-- Kolom N: Reviewer --}}
                    <td class="small text-muted">{{ $log->reviewer ?: '-' }}</td>

                    {{-- Kolom O: Tanggal Review --}}
                    <td class="text-center small text-muted">
                        {{ $log->tanggal_review ? \Carbon\Carbon::parse($log->tanggal_review)->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Aksi --}}
                    <td class="text-center">
                        <a href="{{ route('pelaporan-bulanan.log-harian.edit', $log->id) }}" class="btn btn-outline-dark btn-sm px-2 py-1" title="Edit / Review Log">
                            <i class="bi {{ $isRa ? 'bi-pencil' : 'bi-shield-check' }}"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isRa ? 16 : 17 }}" class="text-center py-4 text-muted">
                        <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                        Belum ada catatan log aktivitas harian pada periode <strong>{{ $periode }}</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!$isRa)
    </form>
    @endif

    {{-- Footnote RBAC Notice --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 small text-muted">
                <i class="bi bi-shield-lock-fill text-primary fs-6"></i>
                <div>
                    <strong>Aturan Hak Akses Log Harian RA:</strong>
                    <strong>Resident Auditor (RA):</strong> Berhak menginput aktivitas harian atau izin (Cuti/Sakit/Dinas/Pelatihan) untuk cabangnya, tetapi dilarang mengisi atau mengubah kolom Hasil Review.
                    <strong>Reviewer / Bagian RA:</strong> Berhak menilai Hasil Review secara individual maupun menggunakan fitur <em>Review Massal</em> untuk mendukung formula Kualitas dan Skor Kinerja RA.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCheckboxes(master) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}
</script>
@endsection
