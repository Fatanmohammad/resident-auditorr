@extends('layouts.app')
@section('title', 'Rekap Isu & Tindak Lanjut')

@section('content')
<style>
    .selector-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .badge-high { background-color: #ef4444; color: #fff; }
    .badge-medium { background-color: #f59e0b; color: #fff; }
    .badge-low { background-color: #10b981; color: #fff; }
    .badge-fraud { background-color: #7f1d1d; color: #fff; font-weight: bold; }
    .badge-signifikan-ya { background-color: #dc2626; color: #fff; font-weight: bold; }
    .badge-signifikan-tidak { background-color: #94a3b8; color: #fff; }

    .table thead th {
        background: #f8fafc;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
    }
    .table tbody td {
        font-size: 0.875rem;
        vertical-align: middle;
        padding: 0.75rem 0.85rem;
        border-color: #f1f5f9;
        color: #334155;
    }
</style>

<div class="container-fluid px-3 py-3">
    {{-- Header Modul --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e3a8a;">
                <i class="bi bi-exclamation-diamond me-2"></i>Rekap Isu &amp; Tindak Lanjut
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan Isu Exception, Finding, Indikasi Fraud, Penentuan Otomatis Flag Signifikan &amp; Jalur Tindak Lanjut
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($kalender)
                @php
                    $isLocked = ($kalender->status_final === 'APPROVED/FINAL') && !in_array(auth()->user()->role ?? '', ['admin', 'kabag_ra', 'kadiv_skai', 'reviewer']);
                @endphp
                @if(!$isLocked)
                    <a href="{{ route('pelaporan-bulanan.rekap-isu-tl.create', $kalender->report_id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> + Tambah Isu Manual
                    </a>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3 rounded-pill">
                        <i class="bi bi-lock-fill me-1"></i> Laporan Final (Terkunci)
                    </span>
                @endif
            @endif
        </div>
    </div>

    {{-- Tabs Navigasi Modul --}}
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

    {{-- Selector Report ID --}}
    <div class="selector-card">
        <form method="GET" action="{{ route('pelaporan-bulanan.rekap-isu-tl') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/rekap-isu-tl') }}/' + this.value; } else { this.form.submit(); }">
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
                    <span class="badge bg-light text-dark border px-2 py-1 me-1">Total: {{ $isuList->count() }} Isu</span>
                    <span class="badge bg-danger px-2 py-1 me-1">High Risk: {{ $isuList->where('risk_level', 'HIGH')->count() }}</span>
                    <span class="badge bg-warning text-dark px-2 py-1">Signifikan: {{ $isuList->where('flag_signifikan', 'YA')->count() }}</span>
                @endif
            </div>
        </form>
    </div>

    @if(!$kalender)
        <div class="alert alert-warning text-center py-4">
            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
            Silakan inisiasi kalender laporan terlebih dahulu pada menu <a href="{{ route('pelaporan-bulanan.kalender') }}" class="fw-bold">Kalender Laporan</a>.
        </div>
    @else
        @if($isLocked)
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 d-flex align-items-center">
                <i class="bi bi-lock-fill fs-4 me-3 text-success"></i>
                <div>
                    <h6 class="fw-bold mb-0">Laporan Telah Berstatus APPROVED/FINAL</h6>
                    <small>Dokumen laporan telah disetujui final oleh Pengawas/Reviewer SKAI. Penambahan dan pengeditan daftar isu telah dikunci.</small>
                </div>
            </div>
        @endif

        {{-- Tabel Rekap Isu & TL --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Issue ID</th>
                            <th>Sumber</th>
                            <th>Referensi</th>
                            <th>Area</th>
                            <th>Jenis Isu</th>
                            <th>Risk Level</th>
                            <th style="min-width: 220px;">Uraian Isu</th>
                            <th>PIC</th>
                            <th>Target TL</th>
                            <th>Status TL</th>
                            <th class="text-center">Aging</th>
                            <th class="text-center">Berulang</th>
                            <th class="text-end">Dampak</th>
                            <th class="text-center">Signifikan</th>
                            <th>Jalur TL</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($isuList as $idx => $isu)
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                            <td class="font-monospace small text-primary fw-bold">{{ $isu->issue_id }}</td>
                            <td><span class="badge bg-secondary">{{ $isu->sumber_sop }}</span></td>
                            <td class="small font-monospace">{{ $isu->referensi_sumber ?: '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $isu->area }}</span></td>
                            <td>
                                @if($isu->jenis_isu === 'INDIKASI FRAUD')
                                    <span class="badge badge-fraud"><i class="bi bi-shield-slash-fill me-1"></i>FRAUD</span>
                                @elseif($isu->jenis_isu === 'FINDING')
                                    <span class="badge bg-danger">FINDING</span>
                                @else
                                    <span class="badge bg-warning text-dark">EXCEPTION</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $isu->risk_level === 'HIGH' ? 'badge-high' : ($isu->risk_level === 'MEDIUM' ? 'badge-medium' : 'badge-low') }}">
                                    {{ $isu->risk_level }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ Str::limit($isu->uraian_isu, 70) }}</div>
                                @if($isu->rekomendasi)
                                    <small class="text-muted fst-italic d-block">Rek: {{ Str::limit($isu->rekomendasi, 50) }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $isu->pic ?: '-' }}</td>
                            <td class="font-monospace text-nowrap">
                                {{ $isu->target_tl ? \Carbon\Carbon::parse($isu->target_tl)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if($isu->status_tl === 'CLOSED')
                                    <span class="badge bg-success">CLOSED</span>
                                @elseif($isu->status_tl === 'OVERDUE')
                                    <span class="badge bg-danger">OVERDUE</span>
                                @elseif($isu->status_tl === 'IN PROGRESS')
                                    <span class="badge bg-info text-dark">IN PROGRESS</span>
                                @else
                                    <span class="badge bg-warning text-dark">OPEN</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace fw-bold {{ $isu->aging_overdue > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $isu->aging_overdue > 0 ? $isu->aging_overdue . ' hr' : '0' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $isu->berulang === 'YA' ? 'bg-danger' : 'bg-light text-muted border' }}">
                                    {{ $isu->berulang }}
                                </span>
                            </td>
                            <td class="text-end font-monospace">
                                {{ $isu->nilai_dampak > 0 ? 'Rp ' . number_format($isu->nilai_dampak, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $isu->flag_signifikan === 'YA' ? 'badge-signifikan-ya' : 'badge-signifikan-tidak' }} px-2 py-1">
                                    {{ $isu->flag_signifikan }}
                                </span>
                            </td>
                            <td class="font-monospace small fw-bold">
                                @if($isu->jalur_tindak_lanjut === 'SOP-RA-09/10')
                                    <span class="text-danger"><i class="bi bi-shield-exclamation me-1"></i>Investigasi Khusus</span>
                                @elseif($isu->jalur_tindak_lanjut === 'SOP-RA-10')
                                    <span class="text-warning"><i class="bi bi-arrow-up-circle-fill me-1"></i>Eskalasi Manajemen</span>
                                @elseif($isu->jalur_tindak_lanjut === 'SOP-RA-07')
                                    <span class="text-primary"><i class="bi bi-arrow-repeat me-1"></i>Monitoring TL</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-check2 me-1"></i>MONITOR</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @if(!$isLocked)
                                    <a href="{{ route('pelaporan-bulanan.rekap-isu-tl.edit', $isu->id) }}" class="btn btn-outline-primary btn-sm p-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-isu-tl.destroy', $isu->id) }}" class="d-inline" onsubmit="return confirm('Hapus baris isu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm p-1" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size:0.75rem;"><i class="bi bi-lock-fill me-1"></i>Final</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="17" class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check fs-2 d-block mb-2"></i>
                                Belum ada baris isu &amp; tindak lanjut untuk <strong>{{ $kalender->report_id }}</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection
