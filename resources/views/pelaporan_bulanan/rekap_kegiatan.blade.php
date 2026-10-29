@extends('layouts.app')
@section('title', 'Rekap Kegiatan Pengawasan')

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
                <i class="bi bi-list-task me-2"></i>Rekap Kegiatan Pengawasan
            </h4>
            <p class="text-muted small mb-0">
                Pencatatan Rencana, Realisasi, Populasi, Sampel, dan Pengujian Lintas Modul Pengawasan
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            @if($kalender)
                @php
                    $isLocked = ($kalender->status_final === 'APPROVED/FINAL') && !in_array(auth()->user()->role ?? '', ['admin', 'kabag_ra', 'kadiv_skai', 'reviewer']);
                @endphp
                @if(!$isLocked)
                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-kegiatan.sync', $kalender->report_id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm text-dark fw-bold" onclick="return confirm('Jalankan Auto-Sync lintas modul untuk {{ $kalender->report_id }}?')">
                            <i class="bi bi-arrow-repeat me-1"></i> Sinkronisasi Otomatis
                        </button>
                    </form>
                    <a href="{{ route('pelaporan-bulanan.rekap-kegiatan.create', $kalender->report_id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> + Tambah Baris Manual
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
        <form method="GET" action="{{ route('pelaporan-bulanan.rekap-kegiatan') }}" class="row g-2 align-items-center">
            <div class="col-md-7">
                <label class="form-label small fw-bold text-muted mb-1">Pilih Laporan Bulanan (Report ID)</label>
                <select name="reportId" class="form-select form-select-sm fw-bold" onchange="if(this.value) { window.location.href = '{{ url('/pelaporan-bulanan/rekap-kegiatan') }}/' + this.value; } else { this.form.submit(); }">
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
                    <span class="badge bg-light text-dark border px-2 py-1 me-1">Total: {{ $kegiatanList->count() }} Kegiatan</span>
                    <span class="badge bg-success px-2 py-1 me-1">Selesai: {{ $kegiatanList->where('status_kegiatan', 'SELESAI')->count() }}</span>
                    <span class="badge bg-danger px-2 py-1">High Risk: {{ $kegiatanList->sum('high_risk') }}</span>
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
                    <small>Dokumen laporan telah disetujui final oleh Pengawas/Reviewer SKAI. Penambahan dan pengeditan kegiatan telah dikunci.</small>
                </div>
            </div>
        @endif

        {{-- Tabel Rekap Kegiatan --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Source ID</th>
                            <th>Sumber</th>
                            <th>Jenis Kegiatan</th>
                            <th>Area Risiko</th>
                            <th class="text-center">Rencana</th>
                            <th class="text-center">Realisasi</th>
                            <th class="text-center">Populasi</th>
                            <th class="text-center">Sampel</th>
                            <th class="text-center">Diuji</th>
                            <th class="text-center text-warning">Exception</th>
                            <th class="text-center text-danger">Finding</th>
                            <th class="text-center text-danger">High Risk</th>
                            <th>Referensi WP/KKA</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatanList as $idx => $keg)
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $idx + 1 }}</td>
                            <td class="font-monospace small text-primary">{{ $keg->source_export_id }}</td>
                            <td><span class="badge bg-secondary">{{ $keg->sumber_sop }}</span></td>
                            <td class="fw-semibold text-dark">{{ $keg->jenis_kegiatan }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $keg->area }}</span></td>
                            <td class="text-center font-monospace">{{ $keg->rencana }}</td>
                            <td class="text-center font-monospace fw-bold text-success">{{ $keg->realisasi }}</td>
                            <td class="text-center font-monospace text-muted">{{ $keg->populasi }}</td>
                            <td class="text-center font-monospace">{{ $keg->sampel_terpilih }}</td>
                            <td class="text-center font-monospace fw-bold text-primary">{{ $keg->sudah_diuji }}</td>
                            <td class="text-center font-monospace fw-bold {{ $keg->exception > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ $keg->exception }}
                            </td>
                            <td class="text-center font-monospace fw-bold {{ $keg->finding > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $keg->finding }}
                            </td>
                            <td class="text-center font-monospace fw-bold {{ $keg->high_risk > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $keg->high_risk }}
                            </td>
                            <td class="small font-monospace">{{ $keg->referensi_wp_kka ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $keg->status_kegiatan === 'SELESAI' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $keg->status_kegiatan }}
                                </span>
                            </td>
                            <td class="small text-muted" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $keg->catatan }}">
                                {{ $keg->catatan ?: '-' }}
                            </td>
                            <td class="text-center text-nowrap">
                                @if(!$isLocked)
                                    <a href="{{ route('pelaporan-bulanan.rekap-kegiatan.edit', $keg->id) }}" class="btn btn-outline-primary btn-sm p-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pelaporan-bulanan.rekap-kegiatan.destroy', $keg->id) }}" class="d-inline" onsubmit="return confirm('Hapus baris kegiatan ini?')">
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
                                <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                Belum ada baris kegiatan pengawasan untuk <strong>{{ $kalender->report_id }}</strong>.
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
