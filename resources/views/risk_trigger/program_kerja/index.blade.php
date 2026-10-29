@extends('layouts.app')
@section('title', 'Program Kerja Terarah & Daftar Data (WP-RA-06-D) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">Program Kerja &amp; Data</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-journal-text text-primary"></i> Program Kerja Terarah &amp; Daftar Data
            <span class="badge-wp">WP-RA-06-D</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            Program kerja audit spesifik per area risiko (Master Parameter), formula ukuran sampel pemicu &amp; pembanding, serta pelacakan kesiapan dokumen pengujian mengacu pada sheet <em>Program_Kerja_Data</em>.
        </p>
    </div>
</div>

{{-- 4 Summary KPI Cards (Harmonized) --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Total Program Kerja</span>
                <i class="bi bi-journal-bookmark text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Terhubung ke Visit ID aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>Siap Uji</span>
                <i class="bi bi-check-circle text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($stats['siap_uji'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Dokumen lengkap / objek fisik</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>Menunggu Data</span>
                <i class="bi bi-hourglass-split text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #c2410c;">{{ number_format($stats['menunggu_data'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Sudah diminta, menunggu serah terima</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rtv-kpi-card accent-slate">
            <div class="rtv-kpi-title">
                <span>Belum Diminta</span>
                <i class="bi bi-clock text-secondary fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #475569;">{{ number_format($stats['belum_diminta'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Belum mengajukan permintaan data</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="rtv-filter-card">
    <form method="GET" action="{{ route('risk-trigger.program-kerja') }}" class="row g-2 align-items-end">
        @if(!$isRa)
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-buildings me-1 text-primary"></i> Kantor Cabang / Unit
            </label>
            <select name="kode_unit" class="form-select">
                <option value="">-- Semua Unit Cabang --</option>
                @foreach($units as $u)
                    <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                        {{ $u->unit_code }} - {{ $u->unit_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @else
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Cabang Tugas (Isolasi RA)
            </label>
            <input type="text" class="form-control bg-light" value="{{ $userCabangName }}" readonly title="Data otomatis dibatasi untuk cabang penugasan Anda">
        </div>
        @endif

        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-grid me-1 text-primary"></i> Area Risiko
            </label>
            <select name="area" class="form-select">
                <option value="">-- Semua Area --</option>
                @foreach($standarAreas as $ar)
                    <option value="{{ $ar }}" {{ request('area') == $ar ? 'selected' : '' }}>{{ $ar }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-check2-circle me-1 text-primary"></i> Status Kesiapan
            </label>
            <select name="status_kesiapan" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="SIAP UJI" {{ request('status_kesiapan') == 'SIAP UJI' ? 'selected' : '' }}>SIAP UJI</option>
                <option value="MENUNGGU DATA" {{ request('status_kesiapan') == 'MENUNGGU DATA' ? 'selected' : '' }}>MENUNGGU DATA</option>
                <option value="BELUM DIMINTA" {{ request('status_kesiapan') == 'BELUM DIMINTA' ? 'selected' : '' }}>BELUM DIMINTA</option>
                <option value="BELUM SIAP" {{ request('status_kesiapan') == 'BELUM SIAP' ? 'selected' : '' }}>BELUM SIAP</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-search me-1 text-primary"></i> Cari Data
            </label>
            <input type="text" name="search" class="form-control" placeholder="Visit ID / Trigger / Area / Tujuan..." value="{{ request('search') }}">
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-fill" title="Terapkan Filter">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            <a href="{{ route('risk-trigger.program-kerja') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill text-center" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel 18 Kolom Excel Program_Kerja_Data --}}
<div class="rtv-table-container">
    <div class="rtv-table-header">
        <h6 class="rtv-table-title">
            <i class="bi bi-journal-bookmark-fill text-primary"></i> Daftar Program Kerja Terarah &amp; Status Kesiapan Data (18 Kolom SOP 06)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-role-scope">
                Total: <strong>{{ number_format($programKerja->total()) }}</strong> Data
            </span>
            <span class="text-muted small">
                (Menampilkan {{ $programKerja->firstItem() ?? 0 }} - {{ $programKerja->lastItem() ?? 0 }})
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table rtv-table mb-0" style="min-width: 2400px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="min-width: 170px;">Visit ID (A)</th>
                    <th style="min-width: 160px;">Trigger ID (B)</th>
                    <th style="min-width: 140px;">Area (C)</th>
                    <th style="min-width: 130px;">KKA Tujuan (D)</th>
                    <th style="min-width: 260px;">Tujuan Uji (E)</th>
                    <th style="min-width: 260px;">Kriteria Utama (F)</th>
                    <th style="min-width: 280px;">Data / Bukti Minimum (G)</th>
                    <th style="min-width: 240px;">Metode Uji (H)</th>
                    <th style="min-width: 170px;">Metode Sampling (I)</th>
                    <th class="text-center" style="min-width: 120px;">Sampel Pemicu (J)</th>
                    <th class="text-center" style="min-width: 130px;">Sampel Pembanding (K)</th>
                    <th style="min-width: 220px;">Langkah Tambahan RA (L)</th>
                    <th style="min-width: 120px;">Cutoff Data (M)</th>
                    <th style="min-width: 120px;">Tgl Minta (N)</th>
                    <th style="min-width: 120px;">Tgl Terima (O)</th>
                    <th style="min-width: 240px;">Bukti Awal (P)</th>
                    <th class="text-center" style="min-width: 160px;">Status Kesiapan (Q)</th>
                    <th style="min-width: 220px;">Catatan Akses/Data (R)</th>
                    <th class="text-center" style="min-width: 140px; position: sticky; right: 0; background: #f8fafc; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">{{ $isRa ? 'Aksi RA' : 'Aksi' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programKerja as $idx => $pk)
                @php
                    $statusKesiapanCls = match($pk->status_kesiapan) {
                        'SIAP UJI' => 'badge-status-success',
                        'MENUNGGU DATA' => 'badge-status-warning',
                        'BELUM DIMINTA' => 'badge-status-neutral',
                        default => 'badge-status-danger'
                    };
                @endphp
                <tr>
                    <td class="text-center text-muted fw-bold">{{ $programKerja->firstItem() + $idx }}</td>

                    {{-- Kolom A: Visit ID --}}
                    <td>
                        <span class="badge-code font-monospace fw-bold text-primary">
                            <i class="bi bi-tag-fill me-1"></i>{{ $pk->visit_id }}
                        </span>
                    </td>

                    {{-- Kolom B: Trigger ID --}}
                    <td>
                        <a href="{{ route('risk-trigger.show', $pk->register_trigger_id) }}" class="fw-bold text-decoration-none text-primary" title="Lihat Master Trigger">
                            {{ $pk->trigger_id }}
                        </a>
                    </td>

                    {{-- Kolom C: Area --}}
                    <td>
                        <span class="fw-bold text-dark">{{ $pk->area }}</span>
                    </td>

                    {{-- Kolom D: KKA Tujuan --}}
                    <td>
                        <span class="badge-code">{{ $pk->kka_tujuan }}</span>
                    </td>

                    {{-- Kolom E: Tujuan Uji --}}
                    <td>
                        <div style="max-width: 250px; white-space: normal; line-height: 1.35; color: #1e293b;" class="small">
                            {{ $pk->tujuan_uji }}
                        </div>
                    </td>

                    {{-- Kolom F: Kriteria Utama --}}
                    <td>
                        <div style="max-width: 250px; white-space: normal; line-height: 1.35; color: #475569;" class="small">
                            {{ $pk->kriteria_utama }}
                        </div>
                    </td>

                    {{-- Kolom G: Data / Bukti Minimum --}}
                    <td>
                        <div style="max-width: 270px; white-space: normal; line-height: 1.35; color: #334155;" class="small">
                            {{ $pk->data_bukti_minimum }}
                        </div>
                    </td>

                    {{-- Kolom H: Metode Uji --}}
                    <td>
                        <div style="max-width: 230px; white-space: normal; color: #475569;" class="small">
                            {{ $pk->metode_uji }}
                        </div>
                    </td>

                    {{-- Kolom I: Metode Sampling --}}
                    <td>
                        <span class="badge-status badge-status-neutral">{{ $pk->metode_sampling }}</span>
                    </td>

                    {{-- Kolom J: Sampel Pemicu --}}
                    <td class="text-center">
                        <span class="badge-code fw-bold">
                            {{ $pk->sampel_pemicu }}
                        </span>
                    </td>

                    {{-- Kolom K: Sampel Pembanding --}}
                    <td class="text-center">
                        <span class="badge-code fw-bold text-primary">
                            {{ $pk->sampel_pembanding }}
                        </span>
                        <div style="font-size: 0.68rem; color: #64748b; margin-top: 2px;">CEIL(SQRT)</div>
                    </td>

                    {{-- Kolom L: Langkah Tambahan RA --}}
                    <td>
                        <div style="max-width: 220px; white-space: normal; line-height: 1.35; font-size: 0.8rem;" class="{{ $pk->langkah_tambahan_ra ? 'text-dark' : 'text-muted' }}">
                            {{ $pk->langkah_tambahan_ra ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom M: Cutoff Data --}}
                    <td>
                        <span class="font-monospace text-dark small">
                            {{ $pk->cutoff_data ? $pk->cutoff_data->format('d/m/Y') : '-' }}
                        </span>
                    </td>

                    {{-- Kolom N: Tanggal Permintaan --}}
                    <td>
                        <span class="{{ $pk->tanggal_permintaan ? 'text-dark fw-semibold small' : 'text-muted small' }}">
                            {{ $pk->tanggal_permintaan ? $pk->tanggal_permintaan->format('d/m/Y') : 'Belum diminta' }}
                        </span>
                    </td>

                    {{-- Kolom O: Tanggal Diterima --}}
                    <td>
                        <span class="{{ $pk->tanggal_diterima ? 'text-dark fw-semibold small' : 'text-muted small' }}">
                            {{ $pk->tanggal_diterima ? $pk->tanggal_diterima->format('d/m/Y') : 'Belum diterima' }}
                        </span>
                    </td>

                    {{-- Kolom P: Bukti Awal --}}
                    <td>
                        <div style="max-width: 230px; white-space: normal; line-height: 1.35; font-size: 0.8rem; color: #475569;">
                            {{ Str::limit($pk->referensi_bukti_awal, 95) }}
                        </div>
                    </td>

                    {{-- Kolom Q: Status Kesiapan --}}
                    <td class="text-center">
                        <span class="badge-status {{ $statusKesiapanCls }}">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>{{ $pk->status_kesiapan }}
                        </span>
                    </td>

                    {{-- Kolom R: Catatan Akses/Data --}}
                    <td>
                        <div style="max-width: 220px; white-space: normal; line-height: 1.35; font-size: 0.8rem;" class="{{ $pk->catatan_akses_data ? 'text-danger' : 'text-muted' }}">
                            {{ $pk->catatan_akses_data ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom Aksi Sticky --}}
                    <td class="text-center px-2 py-2" style="position: sticky; right: 0; background: #ffffff; z-index: 2; box-shadow: -4px 0 6px -2px rgba(0,0,0,0.05);">
                        <a href="{{ route('risk-trigger.program-kerja.edit', $pk->id) }}" 
                           class="btn {{ $isRa ? 'btn-outline-primary' : 'btn-outline-secondary' }} btn-sm rounded-pill px-2.5 py-1 fw-semibold" 
                           style="font-size: 0.76rem; white-space: nowrap;" 
                           title="{{ $isRa ? 'Buka Halaman Input Pelaksanaan Kerja & Kesiapan Data RA' : 'Tinjau Pelaksanaan Kerja & Kesiapan Data' }}">
                            @if($isRa)
                                <i class="bi bi-pencil-square me-1"></i> Input Data
                            @else
                                <i class="bi bi-eye me-1"></i> Tinjau PKA
                            @endif
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center py-5 text-muted">
                        <div class="py-4">
                            <i class="bi bi-journal-x text-muted fs-1 mb-2 d-block"></i>
                            <h6 class="mt-3 fw-bold text-dark">Belum Ada Program Kerja Terarah</h6>
                            <p class="text-muted mb-3 small" style="max-width: 500px; margin: 0 auto;">
                                Program Kerja Terarah akan terbit secara otomatis setiap kali Reviewer menerbitkan <strong>Assignment &amp; Scope</strong> untuk trigger pemicu visit.
                            </p>
                            @if(!$isRa)
                            <a href="{{ route('risk-trigger.assignment.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i> Buat Penugasan Visit Sekarang
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($programKerja->hasPages())
    <div class="card-footer bg-white py-3 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Menampilkan {{ $programKerja->firstItem() }} s.d. {{ $programKerja->lastItem() }} dari total {{ $programKerja->total() }} program kerja
        </div>
        <div>
            {{ $programKerja->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
