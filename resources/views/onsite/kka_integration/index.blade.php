@extends('layouts.app')

@section('content')

<style>
    :root { --primary: #1e3a8a; --border: #e2e8f0; --muted: #64748b; --dark: #1e293b; }
    .stat-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.25rem; display:flex; align-items:flex-start; gap:1rem; }
    .stat-icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
    .data-table { width:100%; border-collapse:collapse; min-width:900px; }
    .data-table th { padding:0.875rem 1rem; background:#f8fafc; font-size:0.75rem; color:var(--muted); border-bottom:1px solid var(--border); text-transform:uppercase; font-weight:600; }
    .data-table td { padding:0.875rem 1rem; vertical-align:middle; border-bottom:1px solid var(--border); font-size:0.84rem; }
    .data-table tbody tr:hover { background:#f8fafc; }
    .btn-back { background:#fff; border:1px solid var(--border); padding:0.4rem 1rem; border-radius:6px; font-weight:600; font-size:0.85rem; color:var(--dark); cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; }
    .btn-back:hover { background:#f8fafc; color:var(--primary); }
    .btn-action { background:var(--primary); color:#fff; font-size:0.8rem; font-weight:600; padding:0.35rem 0.9rem; border-radius:6px; border:none; text-decoration:none; display:inline-block; }
    .btn-action:hover { background:#1e40af; color:#fff; }
</style>

<div style="padding: 1.5rem;">

    {{-- Header Page & Tombol Kembali --}}
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; flex-wrap:wrap; gap:0.75rem;">
        <div>
            <a href="{{ isset($visit) ? route('onsite.kka', $visit->id) : '#' }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Ringkasan KKA
            </a>
            <h4 style="font-weight:700; color:var(--dark); margin:0.4rem 0 0.2rem;">KKA Control &amp; Integrasi Onsite — {{ $visit->kode_unit ?? '-' }}</h4>
            <p style="color:var(--muted); font-size:0.88rem; margin:0;">
                {{ $visit->nama_unit ?? '-' }} &nbsp;|&nbsp; Periode: {{ $visit->periode ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Summary Cards (Berjajar 4 kolom dengan Grid) --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9; color:#475569;"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Sampel Eligible</div>
                <div style="font-size:1.4rem; font-weight:700; color:var(--dark);">{{ $totalEligibleAll ?? 0 }}</div>
                <div style="font-size:0.75rem; color:var(--muted);">Transaksi CBS</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Prosedur Telah Diuji</div>
                <div style="font-size:1.4rem; font-weight:700; color:#16a34a;">{{ $totalDiujiAll ?? 0 }} / {{ $totalEligibleAll ?? 0 }}</div>
                <div style="font-size:0.75rem; color:var(--muted);">Area Pengujian</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed; color:#ea580c;"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Total Exception</div>
                <div style="font-size:1.4rem; font-weight:700; color:#ea580c;">{{ $totalExceptionAll ?? 0 }}</div>
                <div style="font-size:0.75rem; color:var(--muted);">Temuan Minor</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef2f2; color:#dc2626;"><i class="bi bi-shield-exclamation"></i></div>
            <div>
                <div style="font-size:0.72rem; font-weight:600; color:var(--muted); text-transform:uppercase;">Risiko Kritis / High</div>
                <div style="font-size:1.4rem; font-weight:700; color:#dc2626;">{{ $totalHighRiskAll ?? 0 }}</div>
                <div style="font-size:0.75rem; color:var(--muted);">Perlu Atensi</div>
            </div>
        </div>
    </div>

    {{-- Matriks Tabel 11 Area KKA --}}
    <div style="background:#fff; border:1px solid var(--border); border-radius:10px; overflow:hidden;">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; background:#fff;">
            <div>
                <div style="font-weight:700; color:var(--primary); font-size:0.95rem;">Matriks Integrasi 11 Area KKA (SOP 04.1)</div>
                <div style="font-size:0.78rem; color:var(--muted);">Status integrasi dan pemantauan master sheet excel area audit onsite</div>
            </div>
            <div>
                <span style="padding:0.3rem 0.8rem; border-radius:20px; font-size:0.78rem; font-weight:600; background: {{ ($totalExceptionAll ?? 0) > 0 ? '#fef2f2; color:#dc2626;' : '#f0fdf4; color:#16a34a;' }}">
                    Status Integrasi: {{ $statusIntegrasi ?? 'CLEAN' }}
                </span>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width:5%;">No</th>
                        <th>Area Review KKA</th>
                        <th>Sheet Master Excel</th>
                        <th style="text-align:center;">Eligible</th>
                        <th style="text-align:center;">Diuji</th>
                        <th style="text-align:center;">Exception</th>
                        <th style="text-align:center;">High / Kritis</th>
                        <th style="text-align:center;">Status KKA</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $listRows = is_iterable($summary ?? null) ? $summary : [];
                    @endphp

                    @forelse($listRows as $row)
                    <tr>
                        <td style="text-align:center; color:var(--muted); font-weight:600;">{{ $row['no'] ?? '-' }}</td>
                        <td style="font-weight:600; color:var(--dark);">{{ $row['area'] ?? '-' }}</td>
                        <td>
                            <code style="background:#f1f5f9; padding:0.15rem 0.4rem; border-radius:4px; font-size:0.78rem; color:#475569;">{{ $row['sheet'] ?? '-' }}</code>
                        </td>
                        <td style="text-align:center; font-weight:600;">{{ $row['eligible'] ?? 0 }}</td>
                        <td style="text-align:center; font-weight:600; color:var(--primary);">{{ $row['diuji'] ?? 0 }}</td>
                        <td style="text-align:center; font-weight:600; color: {{ ($row['exception'] ?? 0) > 0 ? '#dc2626' : 'var(--muted)' }};">
                            {{ $row['exception'] ?? 0 }}
                        </td>
                        <td style="text-align:center; font-weight:600; color: {{ ($row['high_risk'] ?? 0) > 0 ? '#dc2626' : 'var(--muted)' }};">
                            {{ $row['high_risk'] ?? 0 }}
                        </td>
                        <td style="text-align:center;">
                            @php
                                $status = strtoupper($row['status_kka'] ?? 'BELUM');
                                $badgeBg = '#f1f5f9';
                                $badgeColor = '#475569';
                                if(in_array($status, ['SELESAI', 'APPROVED'])) {
                                    $badgeBg = '#f0fdf4'; $badgeColor = '#16a34a';
                                } elseif(in_array($status, ['IN PROGRESS', 'DIUJI'])) {
                                    $badgeBg = '#fffbeb'; $badgeColor = '#ca8a04';
                                }
                            @endphp
                            <span style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.72rem; font-weight:600;">
                                {{ $row['status_kka'] ?? 'Belum' }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if(isset($row['route']) && \Illuminate\Support\Facades\Route::has($row['route']))
                                <a href="{{ route($row['route'], $visit->id) }}" class="btn-action">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Buka Area
                                </a>
                            @else
                                <span style="color:var(--muted); font-size:0.78rem; font-style:italic;">Belum Ada Rute</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:3rem; color:var(--muted);">
                            <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>
                            Data matriks area belum tersedia.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection