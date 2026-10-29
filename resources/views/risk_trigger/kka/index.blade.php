@extends('layouts.app')
@section('title', 'KKA Risk Trigger (WP-RA-06-D) - Risk Trigger Visit (SOP 06)')

@section('content')
<!-- Header Harmonized SOP 06 -->
<div class="rtv-header">
    <div>
        <div class="rtv-breadcrumb">
            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a> &rsaquo;
            <span>Risk Trigger Visit (SOP 06)</span> &rsaquo;
            <span class="text-dark fw-semibold">KKA Risk Trigger</span>
        </div>
        <h4 class="rtv-title">
            <i class="bi bi-file-earmark-spreadsheet text-primary"></i> Kertas Kerja Audit (KKA) Risk Trigger
            <span class="badge-wp">WP-RA-06-D</span>
            @if($isRa)
                <span class="badge-role-scope"><i class="bi bi-geo-alt-fill text-success me-1"></i> Cabang: {{ $userCabangName }}</span>
            @else
                <span class="badge-role-scope"><i class="bi bi-shield-check text-primary me-1"></i> Mode Reviewer / Admin (Global)</span>
            @endif
        </h4>
        <p class="rtv-subtitle">
            KKA Induk (20 Kolom Excel A s.d. T) terintegrasi dengan agregasi Exception real-time dari 8 KKA Spesifik Area pengujian lapangan.
        </p>
    </div>
</div>

{{-- 5 Summary KPI Cards (Harmonized) --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md">
        <div class="rtv-kpi-card accent-primary">
            <div class="rtv-kpi-title">
                <span>Total KKA Induk</span>
                <i class="bi bi-file-earmark-text text-primary fs-6"></i>
            </div>
            <div class="rtv-kpi-val">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Otomatis dari Assignment</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="rtv-kpi-card accent-slate">
            <div class="rtv-kpi-title">
                <span>Draft / Proses</span>
                <i class="bi bi-pencil text-secondary fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #475569;">{{ number_format($stats['draft'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Dalam pengerjaan RA</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="rtv-kpi-card accent-warning">
            <div class="rtv-kpi-title">
                <span>Revisi</span>
                <i class="bi bi-arrow-repeat text-warning fs-6"></i>
            </div>
            <div class="rtv-kpi-val" style="color: #c2410c;">{{ number_format($stats['revisi'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Perlu perbaikan RA</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="rtv-kpi-card accent-success">
            <div class="rtv-kpi-title">
                <span>Final Disetujui</span>
                <i class="bi bi-check-circle text-success fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-success">{{ number_format($stats['final'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">Disetujui Reviewer</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="rtv-kpi-card accent-danger">
            <div class="rtv-kpi-title">
                <span>Exception Area</span>
                <i class="bi bi-exclamation-octagon text-danger fs-6"></i>
            </div>
            <div class="rtv-kpi-val text-danger">{{ number_format($stats['total_exception'] ?? 0) }}</div>
            <div class="rtv-kpi-sub">COUNTIFS 8 KKA Area</div>
        </div>
    </div>
</div>

{{-- Filter Card Harmonized --}}
<div class="rtv-filter-card">
    <form method="GET" action="{{ route('risk-trigger.kka.index') }}" class="row g-2 align-items-end">
        @if(!$isRa)
        <div class="col-12 col-md-3">
            <label class="form-label"><i class="bi bi-building me-1 text-primary"></i>Filter Unit / Cabang</label>
            <select name="kode_unit" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Cabang (Global) --</option>
                @foreach($units as $u)
                    <option value="{{ $u->unit_code }}" {{ $filterKodeUnit == $u->unit_code ? 'selected' : '' }}>
                        {{ $u->unit_code }} - {{ $u->unit_name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-6 col-md-2">
            <label class="form-label"><i class="bi bi-tag me-1 text-primary"></i>Area Risiko</label>
            <select name="area" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Area --</option>
                @foreach($standarAreas as $sa)
                    <option value="{{ $sa }}" {{ request('area') == $sa ? 'selected' : '' }}>{{ $sa }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label"><i class="bi bi-check2-circle me-1 text-primary"></i>Status Review</label>
            <select name="status_review" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="DRAFT / BELUM DIREVIEW" {{ request('status_review') == 'DRAFT / BELUM DIREVIEW' ? 'selected' : '' }}>DRAFT / BELUM DIREVIEW</option>
                <option value="REVISI" {{ request('status_review') == 'REVISI' ? 'selected' : '' }}>REVISI</option>
                <option value="FINAL" {{ request('status_review') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
            </select>
        </div>

        <div class="col-12 col-md">
            <label class="form-label"><i class="bi bi-search me-1 text-primary"></i>Cari KKA / Visit ID</label>
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Visit ID, Trigger ID, simpulan, dll..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                @if(request()->anyFilled(['kode_unit', 'area', 'status_review', 'search']))
                    <a href="{{ route('risk-trigger.kka.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Main Table Container: 20 Kolom Excel A s.d. T --}}
<div class="rtv-table-container">
    <div class="rtv-table-header">
        <h6 class="rtv-table-title">
            <i class="bi bi-file-earmark-spreadsheet-fill text-primary"></i>
            Daftar KKA Induk (KKA_Risk_Trigger - 20 Kolom Excel: Sel A s.d. T)
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-role-scope">Total: <strong>{{ number_format($kkas->total()) }}</strong> Visit</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table rtv-table mb-0" style="min-width: 2300px;">
            <thead>
                <tr>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                    <th style="min-width: 160px;">Visit ID (A)</th>
                    <th style="min-width: 150px;">Trigger ID (B)</th>
                    <th style="min-width: 160px;">Unit/Kantor (C)</th>
                    <th style="min-width: 130px;">Area (D)</th>
                    <th style="min-width: 120px; text-align: center;">Risk Level (E)</th>
                    <th style="min-width: 250px;">Pertanyaan Audit (F)</th>
                    <th style="min-width: 220px;">Kondisi Awal (G)</th>
                    <th style="min-width: 220px;">Kriteria (H)</th>
                    <th style="min-width: 220px;">Prosedur Utama (I)</th>
                    <th style="min-width: 200px;">Ringkasan Bukti (J)</th>
                    <th style="min-width: 200px;">Hasil Analisis (K)</th>
                    <th style="min-width: 120px; text-align: center;">Trigger Terbukti? (L)</th>
                    <th style="min-width: 120px; text-align: center;">Perlu Perluasan? (M)</th>
                    <th style="min-width: 180px;">Alasan Perluasan (N)</th>
                    <th style="min-width: 150px;">KKA Area (O)</th>
                    <th class="text-center" style="min-width: 130px;">Exception Area (P)</th>
                    <th style="min-width: 220px;">Simpulan RA (Q)</th>
                    <th style="min-width: 140px;">RA Pelaksana (R)</th>
                    <th style="min-width: 140px;">Reviewer (S)</th>
                    <th style="min-width: 150px; text-align: center;">Status Review (T)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kkas as $k)
                @php
                    $excCount = (int)($k->exception_area ?? 0);
                    $riskCls = match($k->risk_level) {
                        'KRITIS', 'HIGH', 'TINGGI' => 'badge-risk-tinggi',
                        'SEDANG', 'MEDIUM' => 'badge-risk-sedang',
                        default => 'badge-risk-rendah'
                    };
                    $reviewCls = match($k->status_review) {
                        'FINAL' => 'badge-status-success',
                        'REVISI' => 'badge-status-warning',
                        default => 'badge-status-neutral'
                    };
                @endphp
                <tr>
                    {{-- Aksi --}}
                    <td class="text-center">
                        <a href="{{ route('risk-trigger.kka.edit', ['id' => $k->id, 'tab' => $k->kka_area]) }}" 
                           class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 text-nowrap fw-semibold" 
                           title="Buka Lembar Kerja KKA Induk & 8 KKA Area">
                            <i class="bi bi-pencil-square me-1"></i> Buka KKA
                        </a>
                    </td>

                    {{-- Kolom A: Visit ID --}}
                    <td>
                        <span class="badge-code font-monospace fw-bold text-primary">{{ $k->visit_id }}</span>
                    </td>

                    {{-- Kolom B: Trigger ID --}}
                    <td>
                        <span class="font-monospace fw-semibold text-secondary small">{{ $k->trigger_id }}</span>
                    </td>

                    {{-- Kolom C: Unit/Kantor --}}
                    <td>
                        <div class="fw-semibold text-dark">{{ $k->unit_kantor }}</div>
                        <span class="badge-code">{{ $k->kode_unit }}</span>
                    </td>

                    {{-- Kolom D: Area --}}
                    <td>
                        <span class="fw-bold text-dark">{{ $k->area }}</span>
                    </td>

                    {{-- Kolom E: Risk Level (Harmonized) --}}
                    <td style="text-align: center;">
                        <span class="badge-risk {{ $riskCls }}">
                            {{ $k->risk_level }}
                        </span>
                    </td>

                    {{-- Kolom F: Pertanyaan Audit --}}
                    <td>
                        <div class="text-wrap small" style="max-width: 250px; line-height: 1.35; color: #1e293b;">
                            {{ $k->pertanyaan_audit }}
                        </div>
                    </td>

                    {{-- Kolom G: Kondisi Awal --}}
                    <td>
                        <div class="text-wrap text-muted small" style="max-width: 220px;">
                            {{ $k->kondisi_awal ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom H: Kriteria --}}
                    <td>
                        <div class="text-wrap text-muted small" style="max-width: 220px;">
                            {{ $k->kriteria ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom I: Prosedur Utama --}}
                    <td>
                        <div class="text-wrap text-muted small" style="max-width: 220px;">
                            {{ $k->prosedur_utama ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom J: Ringkasan Bukti --}}
                    <td>
                        @if(!empty($k->ringkasan_bukti))
                            <div class="text-wrap small" style="max-width: 200px;">
                                {{ Str::limit($k->ringkasan_bukti, 90) }}
                            </div>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>

                    {{-- Kolom K: Hasil Analisis --}}
                    <td>
                        @if(!empty($k->hasil_analisis))
                            <div class="text-wrap small" style="max-width: 200px;">
                                {{ Str::limit($k->hasil_analisis, 90) }}
                            </div>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>

                    {{-- Kolom L: Trigger Terbukti? --}}
                    <td style="text-align: center;">
                        @if($k->trigger_terbukti == 'Ya')
                            <span class="badge-status badge-status-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Ya</span>
                        @elseif($k->trigger_terbukti == 'Sebagian')
                            <span class="badge-status badge-status-warning"><i class="bi bi-dash-circle-fill me-1"></i> Sebagian</span>
                        @elseif($k->trigger_terbukti == 'Tidak')
                            <span class="badge-status badge-status-success"><i class="bi bi-check-circle-fill me-1"></i> Tidak</span>
                        @else
                            <span class="badge-status badge-status-neutral">-</span>
                        @endif
                    </td>

                    {{-- Kolom M: Perlu Perluasan? --}}
                    <td style="text-align: center;">
                        @if($k->perlu_perluasan == 'Ya')
                            <span class="badge-status badge-status-warning"><i class="bi bi-arrows-fullscreen me-1"></i> Ya</span>
                        @else
                            <span class="badge-status badge-status-neutral">Tidak</span>
                        @endif
                    </td>

                    {{-- Kolom N: Alasan Perluasan --}}
                    <td>
                        <div class="text-wrap small text-muted" style="max-width: 180px;">
                            {{ $k->alasan_perluasan ?: '-' }}
                        </div>
                    </td>

                    {{-- Kolom O: KKA Area --}}
                    <td>
                        <span class="badge-code font-monospace">
                            <i class="bi bi-file-earmark-code me-1"></i> {{ $k->kka_area ?: '-' }}
                        </span>
                    </td>

                    {{-- Kolom P: Exception Area --}}
                    <td class="text-center">
                        @if($excCount > 0)
                            <span class="badge-status badge-status-danger" title="COUNTIFS 8 KKA Area = {{ $excCount }}">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ $excCount }}
                            </span>
                        @else
                            <span class="badge-status badge-status-success">
                                0 Sesuai
                            </span>
                        @endif
                    </td>

                    {{-- Kolom Q: Simpulan RA --}}
                    <td>
                        @if(!empty($k->simpulan_ra))
                            <div class="text-wrap small" style="max-width: 220px;">
                                {{ Str::limit($k->simpulan_ra, 100) }}
                            </div>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>

                    {{-- Kolom R: RA Pelaksana --}}
                    <td>
                        <span class="text-dark fw-semibold small">{{ $k->ra_pelaksana ?: '-' }}</span>
                    </td>

                    {{-- Kolom S: Reviewer --}}
                    <td>
                        <span class="text-dark fw-semibold small">{{ $k->reviewer ?: '-' }}</span>
                    </td>

                    {{-- Kolom T: Status Review --}}
                    <td class="text-center">
                        <span class="badge-status {{ $reviewCls }}">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i> {{ $k->status_review }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="21" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <div class="fw-semibold text-dark">Belum Ada KKA Induk</div>
                        <small class="text-muted">KKA Induk otomatis terbuat setiap kali Assignment Visit diterbitkan.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kkas->hasPages())
    <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Menampilkan {{ $kkas->firstItem() }} s.d. {{ $kkas->lastItem() }} dari total {{ $kkas->total() }} KKA
        </div>
        <div>
            {{ $kkas->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>
@endsection
