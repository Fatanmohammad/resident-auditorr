@php
    $role = auth()->user()->role ?? '';
    $isAdmin = in_array(strtolower($role), ['admin', 'kabag_ra', 'kadiv_skai']);
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
    <a href="{{ route('pelaporan-bulanan.dashboard') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.dashboard*') ? 'active' : '' }}">
        Dashboard
    </a>
    <a href="{{ route('pelaporan-bulanan.kalender') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.kalender*') ? 'active' : '' }}">
        Kalender Laporan
    </a>
    <a href="{{ route('pelaporan-bulanan.checklist', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.checklist*') ? 'active' : '' }}">
        Checklist Laporan
    </a>
    <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.rekap-kegiatan*') ? 'active' : '' }}">
        Rekap Kegiatan
    </a>
    <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.rekap-isu-tl*') ? 'active' : '' }}">
        Rekap Isu &amp; TL
    </a>
    <a href="{{ route('pelaporan-bulanan.review-laporan') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.review-laporan*') ? 'active' : '' }}">
        Review Laporan
    </a>
    <a href="{{ route('pelaporan-bulanan.koreksi-approval') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.koreksi-approval*') ? 'active' : '' }}">
        Koreksi &amp; Approval
    </a>
    <a href="{{ route('pelaporan-bulanan.isu-signifikan') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.isu-signifikan*') ? 'active' : '' }}">
        Isu Signifikan
    </a>
    <a href="{{ route('pelaporan-bulanan.distribusi-quality') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.distribusi-quality*') ? 'active' : '' }}">
        Distribusi &amp; Quality
    </a>
    <a href="{{ route('pelaporan-bulanan.konsolidasi') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.konsolidasi*') ? 'active' : '' }}">
        Konsolidasi
    </a>
    <a href="{{ route('pelaporan-bulanan.tren-isu') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.tren-isu*') ? 'active' : '' }}">
        Tren Isu
    </a>
    <a href="{{ route('pelaporan-bulanan.feedback') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.feedback*') ? 'active' : '' }}">
        Feedback
    </a>
    <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-ra*') ? 'active' : '' }}">
        Roster RA
    </a>
    <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.log-harian*') ? 'active' : '' }}">
        Log Harian
    </a>
    <a href="{{ route('pelaporan-bulanan.kalender-kinerja') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.kalender-kinerja*') ? 'active' : '' }}">
        Kinerja RA
    </a>
    <a href="{{ route('pelaporan-bulanan.narasi', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.narasi*') ? 'active' : '' }}">
        Narasi Laporan
    </a>
    <a href="{{ route('pelaporan-bulanan.laporan-bulanan', $activeReportId) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.laporan-bulanan*') ? 'active' : '' }}">
        Cetak Laporan
    </a>
    @if($isAdmin)
    <a href="{{ route('pelaporan-bulanan.master-parameter') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-parameter*') ? 'active' : '' }}">
        Master &amp; Libur
    </a>
    @endif
</div>
