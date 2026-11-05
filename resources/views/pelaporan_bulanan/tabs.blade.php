@php
    $role = auth()->user()->role ?? '';
    $isAdmin = in_array(strtolower($role), ['admin', 'kabag_ra', 'kadiv_skai']);
    $isRa = strtolower($role) === 'ra';
    $activeReportId = $reportId ?? request()->route('reportId') ?? request()->query('reportId') ?? request()->query('report_id');
@endphp

<style>
    .sop08-tabs { 
        display: flex; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 0.45rem; 
        padding: 0.65rem 0.85rem; 
        margin-bottom: 1.25rem; 
        background: #ffffff; 
        border: 1px solid var(--border-color, #e2e8f0); 
        border-radius: 12px; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .sop08-tab-item {
        background: #f8fafc; 
        border: 1px solid #e2e8f0; 
        border-radius: 8px; 
        padding: 0.45rem 0.85rem; 
        font-size: 0.84rem; 
        font-weight: 600; 
        color: #475569; 
        display: inline-flex; 
        align-items: center; 
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .sop08-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--bs-blue, #1e40af); 
    }
    .sop08-tab-item.active { 
        background: #1e3a8a; 
        border-color: #1e3a8a; 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25); 
    }
</style>

<div class="sop08-tabs">
    @if($isRa)
        <a href="{{ route('pelaporan-bulanan.dashboard') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.dashboard*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.laporan-bulanan*') ? 'active' : '' }}">
            <i class="bi bi-eye me-1"></i> Mode Preview Laporan
        </a>
        <a href="{{ route('pelaporan-bulanan.checklist', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.checklist*') ? 'active' : '' }}">
            <i class="bi bi-ui-checks me-1"></i> Status Kepatuhan KKA
        </a>
        <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.log-harian*') || request()->routeIs('pelaporan-bulanan.rekap-kegiatan*') ? 'active' : '' }}">
            <i class="bi bi-journal-check me-1"></i> Rekap Aktivitas Harian
        </a>
        <a href="{{ route('monitoring.index') }}" class="sop08-tab-item {{ request()->routeIs('monitoring*') ? 'active' : '' }}">
            <i class="bi bi-shield-check me-1"></i> Monitoring Tindak Lanjut
        </a>
    @else
        <a href="{{ route('pelaporan-bulanan.konsolidasi') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.konsolidasi*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3 me-1"></i> Matriks Konsolidasi Cabang
        </a>
        <a href="{{ route('pelaporan-bulanan.dashboard') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.dashboard*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
        <a href="{{ route('pelaporan-bulanan.narasi', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.narasi*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square me-1"></i> Editor WYSIWYG
        </a>
        <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.log-harian*') || request()->routeIs('pelaporan-bulanan.rekap-kegiatan*') ? 'active' : '' }}">
            <i class="bi bi-journal-check me-1"></i> Rekap Aktivitas Harian
        </a>
        <a href="{{ route('monitoring.index') }}" class="sop08-tab-item {{ request()->routeIs('monitoring*') ? 'active' : '' }}">
            <i class="bi bi-shield-check me-1"></i> Monitoring Tindak Lanjut
        </a>
        <a href="{{ route('pelaporan-bulanan.kalender') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.kalender*') ? 'active' : '' }}">
            <i class="bi bi-calendar3-range me-1"></i> Kalender Laporan
        </a>
        <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-ra*') || request()->routeIs('pelaporan-bulanan.kalender-kinerja*') ? 'active' : '' }}">
            <i class="bi bi-people me-1"></i> Roster &amp; Kinerja
        </a>
        <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.laporan-bulanan*') ? 'active' : '' }}">
            <i class="bi bi-printer me-1"></i> Preview &amp; Cetak
        </a>
        @if($isAdmin)
        <a href="{{ route('pelaporan-bulanan.master-parameter') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-parameter*') ? 'active' : '' }}">
            <i class="bi bi-sliders me-1"></i> Master &amp; Libur
        </a>
        @endif
    @endif
</div>
