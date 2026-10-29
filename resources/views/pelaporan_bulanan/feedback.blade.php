@extends('layouts.app')
@section('title', 'Register Feedback Konsolidasi & Perbaikan (WP-RA-08-M) - SOP 08')

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
    .table-responsive {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .table thead th {
        background: #f1f5f9;
        font-size: 0.73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #334155;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
    .table tbody td {
        font-size: 0.83rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-section-head { background: #f8fafc !important; }
    .table-section-ra { background: #f0fdf4 !important; }
    .table-section-verif { background: #eff6ff !important; }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pelaporan-bulanan.kalender') }}" class="text-decoration-none">SOP 08</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Feedback (WP-RA-08-M)</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-chat-square-quote text-primary"></i> Register Feedback Konsolidasi & Perbaikan
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 fw-semibold">WP-RA-08-M</span>
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Umpan balik kualitas pelaporan bulanan dari Manajemen/Reviewer kepada RA dan tindak lanjut perbaikan (14 Kolom A s.d. N).
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!$isRa)
            <a href="{{ route('pelaporan-bulanan.feedback.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> + Buat Feedback Konsolidasi
            </a>
            @endif
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
        <form method="GET" action="{{ route('pelaporan-bulanan.feedback') }}" class="row g-2 align-items-end">
            @if(!$isRa)
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Filter Unit Kerja / Cabang</label>
                <select name="kode_unit" class="form-select form-select-sm">
                    <option value="">-- Seluruh Cabang --</option>
                    @foreach($masterUnits as $u)
                        <option value="{{ $u->kode_unit }}" {{ request('kode_unit') == $u->kode_unit ? 'selected' : '' }}>
                            {{ $u->kode_unit }} - {{ $u->nama_unit }} ({{ $u->resident_auditor }})
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
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Jenis Feedback</label>
                <select name="jenis_feedback" class="form-select form-select-sm">
                    <option value="">-- Semua Jenis Feedback --</option>
                    @foreach($jenisList as $j)
                        <option value="{{ $j }}" {{ request('jenis_feedback') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Status Penyelesaian</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach($statusList as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ route('pelaporan-bulanan.feedback') }}" class="btn btn-outline-secondary btn-sm px-3" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- KPI Cards Summary --}}
    @php
        $totalFeedback = $feedbackList->count();
        $totalOpen = $feedbackList->where('status', 'OPEN')->count();
        $totalInProgress = $feedbackList->where('status', 'IN PROGRESS')->count();
        $totalMenungguVerif = $feedbackList->where('status', 'MENUNGGU VERIFIKASI')->count();
        $totalClosed = $feedbackList->where('status', 'CLOSED')->count();
        $totalPerluUlang = $feedbackList->where('verifikasi_reviewer', 'PERLU PERBAIKAN ULANG')->count();
    @endphp

    <div class="row g-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Total Feedback</div>
                <div class="kpi-value text-primary">{{ $totalFeedback }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Catatan Perbaikan</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Status Open</div>
                <div class="kpi-value text-danger">{{ $totalOpen }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Belum Ditindaklanjuti</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">In Progress</div>
                <div class="kpi-value text-warning">{{ $totalInProgress }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Sedang Dikerjakan RA</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Menunggu Verifikasi</div>
                <div class="kpi-value text-info">{{ $totalMenungguVerif }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">RA Selesai Menindak</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Closed / Memadai</div>
                <div class="kpi-value text-success">{{ $totalClosed }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Terverifikasi Reviewer</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card text-center">
                <div class="kpi-title">Perlu Perbaikan Ulang</div>
                <div class="kpi-value text-danger">{{ $totalPerluUlang }}</div>
                <div class="text-muted small" style="font-size: 0.72rem;">Tindak Lanjut Belum Cukup</div>
            </div>
        </div>
    </div>

    {{-- Main 14-Column Table --}}
    <div class="table-responsive shadow-sm">
        <table class="table table-hover table-bordered mb-0">
            <thead>
                {{-- Super Headers --}}
                <tr>
                    <th colspan="6" class="table-section-head text-primary">
                        <i class="bi bi-chat-left-dots me-1"></i> PEMBERIAN FEEDBACK OLEH REVIEWER (Kolom A s.d. F)
                    </th>
                    <th colspan="6" class="table-section-ra text-success">
                        <i class="bi bi-tools me-1"></i> TINDAK LANJUT & PENYELESAIAN OLEH RA (Kolom G s.d. L)
                    </th>
                    <th colspan="2" class="table-section-verif text-info">
                        <i class="bi bi-shield-check me-1"></i> VERIFIKASI AKHIR (Kolom M s.d. N)
                    </th>
                    <th rowspan="2" class="bg-light text-center" style="width: 100px;">Aksi</th>
                </tr>
                {{-- Column Headers A s.d. N --}}
                <tr>
                    <th title="Kolom A - Feedback ID">Feedback ID (A)</th>
                    <th title="Kolom B - Report ID">Report ID (B)</th>
                    <th title="Kolom C - Tanggal">Tanggal (C)</th>
                    <th title="Kolom D - Diberikan Kepada">Diberikan Kepada (D)</th>
                    <th title="Kolom E - Jenis Feedback">Jenis (E)</th>
                    <th title="Kolom F - Uraian Feedback" style="min-width: 200px;">Uraian Feedback (F)</th>

                    <th title="Kolom G - Tindakan Perbaikan" style="min-width: 200px;">Tindakan Perbaikan (G)</th>
                    <th title="Kolom H - PIC">PIC (H)</th>
                    <th title="Kolom I - Target">Target (I)</th>
                    <th title="Kolom J - Status">Status (J)</th>
                    <th title="Kolom K - Bukti">Bukti (K)</th>
                    <th title="Kolom L - Tanggal Selesai">Tgl Selesai (L)</th>

                    <th title="Kolom M - Verifikasi Reviewer">Verifikasi Reviewer (M)</th>
                    <th title="Kolom N - Catatan Reviewer">Catatan (N)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbackList as $fb)
                @php
                    $isClosed = $fb->status === 'CLOSED';
                    $isPerluUlang = $fb->verifikasi_reviewer === 'PERLU PERBAIKAN ULANG';
                @endphp
                <tr class="{{ $isPerluUlang ? 'table-danger' : ($isClosed ? 'table-success-subtle' : '') }}">
                    {{-- Kolom A: Feedback ID --}}
                    <td class="fw-bold text-primary font-monospace">{{ $fb->feedback_id }}</td>

                    {{-- Kolom B: Report ID --}}
                    <td>
                        <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $fb->report_id) }}" class="text-decoration-none" title="Lihat Laporan">
                            {{ $fb->report_id }}
                        </a>
                    </td>

                    {{-- Kolom C: Tanggal --}}
                    <td class="text-center">{{ \Carbon\Carbon::parse($fb->tanggal)->format('d/m/Y') }}</td>

                    {{-- Kolom D: Diberikan Kepada --}}
                    <td class="small">{{ $fb->diberikan_kepada }}</td>

                    {{-- Kolom E: Jenis Feedback --}}
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                            {{ $fb->jenis_feedback }}
                        </span>
                    </td>

                    {{-- Kolom F: Uraian Feedback --}}
                    <td style="max-width: 280px; white-space: normal;" class="small">
                        {{ $fb->uraian_feedback }}
                    </td>

                    {{-- Kolom G: Tindakan Perbaikan --}}
                    <td style="max-width: 280px; white-space: normal;" class="small">
                        @if($fb->tindakan_perbaikan)
                            <span class="text-dark">{{ $fb->tindakan_perbaikan }}</span>
                        @else
                            <span class="text-muted fst-italic">Belum diisi oleh RA</span>
                        @endif
                    </td>

                    {{-- Kolom H: PIC --}}
                    <td class="small">{{ $fb->pic ?: '-' }}</td>

                    {{-- Kolom I: Target --}}
                    <td class="text-center small">
                        {{ $fb->target ? \Carbon\Carbon::parse($fb->target)->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Kolom J: Status --}}
                    <td class="text-center">
                        @if($fb->status === 'CLOSED')
                            <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>CLOSED</span>
                        @elseif($fb->status === 'MENUNGGU VERIFIKASI')
                            <span class="badge bg-info text-dark">MENUNGGU VERIFIKASI</span>
                        @elseif($fb->status === 'IN PROGRESS')
                            <span class="badge bg-warning text-dark">IN PROGRESS</span>
                        @else
                            <span class="badge bg-danger">OPEN</span>
                        @endif
                    </td>

                    {{-- Kolom K: Bukti --}}
                    <td class="small">
                        @if($fb->bukti)
                            <span class="text-success"><i class="bi bi-file-earmark-check me-1"></i>{{ Str::limit($fb->bukti, 25) }}</span>
                        @else
                            <span class="text-muted fst-italic">-</span>
                        @endif
                    </td>

                    {{-- Kolom L: Tanggal Selesai --}}
                    <td class="text-center small">
                        {{ $fb->tanggal_selesai ? \Carbon\Carbon::parse($fb->tanggal_selesai)->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Kolom M: Verifikasi Reviewer --}}
                    <td class="text-center">
                        @if($fb->verifikasi_reviewer === 'MEMADAI / CLOSED')
                            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>MEMADAI / CLOSED</span>
                        @elseif($fb->verifikasi_reviewer === 'PERLU PERBAIKAN ULANG')
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>PERBAIKAN ULANG</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">BELUM DIVERIFIKASI</span>
                        @endif
                    </td>

                    {{-- Kolom N: Catatan --}}
                    <td style="max-width: 200px; white-space: normal;" class="small text-muted">
                        {{ $fb->catatan ?: '-' }}
                    </td>

                    {{-- Aksi --}}
                    <td class="text-center">
                        <a href="{{ route('pelaporan-bulanan.feedback.edit', $fb->id) }}" class="btn btn-outline-primary btn-sm px-2 py-1 shadow-sm" title="{{ $isRa ? 'Input Tindak Lanjut' : 'Edit / Verifikasi Feedback' }}">
                            <i class="bi {{ $isRa ? 'bi-pencil-square' : 'bi-shield-check' }} me-1"></i>
                            {{ $isRa ? 'Tindak Lanjut' : 'Review' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center py-4 text-muted">
                        <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-secondary"></i>
                        Belum ada data feedback konsolidasi yang terdaftar.
                        @if(!$isRa)
                            <div class="mt-2">
                                <a href="{{ route('pelaporan-bulanan.feedback.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Feedback Konsolidasi Pertama
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footnote RBAC Notice --}}
    <div class="card mt-3 bg-light border-0 shadow-sm">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 small text-muted">
                <i class="bi bi-shield-lock-fill text-primary fs-6"></i>
                <div>
                    <strong>Aturan Hak Akses & Pembagian Tanggung Jawab (WP-RA-08-M):</strong>
                    <strong>Admin / Reviewer:</strong> Berhak membuat feedback baru, menentukan arahan, melakukan Verifikasi Reviewer, dan mengunci status <code>CLOSED</code>.
                    <strong>Resident Auditor (RA):</strong> Hanya melihat feedback yang ditujukan ke cabangnya dan berhak menginput Tindakan Perbaikan (G), Bukti (K), Tanggal Selesai (L), serta mengubah status ke <code>IN PROGRESS / MENUNGGU VERIFIKASI</code>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
