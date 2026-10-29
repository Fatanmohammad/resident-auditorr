@php
    $role = auth()->user()->role ?? '';
    $isAdmin = in_array(strtolower($role), ['admin', 'kabag_ra', 'kadiv_skai']);
@endphp

<style>
    .sop08-tabs { 
        display: flex; 
        align-items: center; 
        flex-wrap: wrap; 
        gap: 0.5rem; 
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
        padding: 0.5rem 1rem; 
        font-size: 0.86rem; 
        font-weight: 600; 
        color: #475569; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.5rem; 
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .sop08-tab-item i { font-size: 0.95rem; color: #64748b; transition: color 0.2s; }
    .sop08-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--bs-blue, #1e40af); 
    }
    .sop08-tab-item:hover i { color: var(--bs-blue, #1e40af); }
    .sop08-tab-item.active { 
        background: #1e3a8a; 
        border-color: #1e3a8a; 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25); 
    }
    .sop08-tab-item.active i { color: #ffffff !important; }
</style>

<div class="sop08-tabs">
    <a href="{{ route('pelaporan-bulanan.dashboard') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.dashboard*') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard SOP 08 (WP-RA-08-L)
    </a>
    <a href="{{ route('pelaporan-bulanan.kalender') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.kalender*') ? 'active' : '' }}">
        <i class="bi bi-calendar3-range"></i> Kalender Laporan (WP-RA-08-A)
    </a>
    <a href="{{ route('pelaporan-bulanan.checklist', request()->route('reportId')) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.checklist*') ? 'active' : '' }}">
        <i class="bi bi-ui-checks"></i> Checklist Laporan (WP-RA-08-B)
    </a>
    <a href="{{ route('pelaporan-bulanan.rekap-kegiatan', request()->route('reportId')) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.rekap-kegiatan*') ? 'active' : '' }}">
        <i class="bi bi-list-task"></i> Rekap Kegiatan (WP-RA-08-D)
    </a>
    <a href="{{ route('pelaporan-bulanan.rekap-isu-tl', request()->route('reportId')) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.rekap-isu-tl*') ? 'active' : '' }}">
        <i class="bi bi-exclamation-diamond"></i> Rekap Isu & TL (WP-RA-08-E)
    </a>
    <a href="{{ route('pelaporan-bulanan.review-laporan') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.review-laporan*') ? 'active' : '' }}">
        <i class="bi bi-clipboard-check"></i> Review Laporan (WP-RA-08-F)
    </a>
    <a href="{{ route('pelaporan-bulanan.koreksi-approval') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.koreksi-approval*') ? 'active' : '' }}">
        <i class="bi bi-check2-circle"></i> Koreksi & Approval (WP-RA-08-G)
    </a>
    <a href="{{ route('pelaporan-bulanan.isu-signifikan') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.isu-signifikan*') ? 'active' : '' }}">
        <i class="bi bi-shield-exclamation"></i> Isu Signifikan (WP-RA-08-H)
    </a>
    <a href="{{ route('pelaporan-bulanan.distribusi-quality') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.distribusi-quality*') ? 'active' : '' }}">
        <i class="bi bi-award"></i> Distribusi & Quality (WP-RA-08-I)
    </a>
    <a href="{{ route('pelaporan-bulanan.konsolidasi') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.konsolidasi*') ? 'active' : '' }}">
        <i class="bi bi-diagram-3"></i> Konsolidasi (WP-RA-08-J)
    </a>
    <a href="{{ route('pelaporan-bulanan.tren-isu') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.tren-isu*') ? 'active' : '' }}">
        <i class="bi bi-graph-up-arrow"></i> Tren Isu (WP-RA-08-K)
    </a>
    <a href="{{ route('pelaporan-bulanan.feedback') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.feedback*') ? 'active' : '' }}">
        <i class="bi bi-chat-square-quote"></i> Feedback (WP-RA-08-M)
    </a>
    <a href="{{ route('pelaporan-bulanan.master-ra') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-ra*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Roster RA (WP-RA-08-N)
    </a>
    <a href="{{ route('pelaporan-bulanan.log-harian') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.log-harian*') ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i> Log Harian (WP-RA-08-O)
    </a>
    <a href="{{ route('pelaporan-bulanan.kalender-kinerja') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.kalender-kinerja*') ? 'active' : '' }}">
        <i class="bi bi-calendar2-check"></i> Kinerja RA (WP-RA-08-P)
    </a>
    <a href="{{ route('pelaporan-bulanan.narasi', request()->route('reportId')) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.narasi*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i> Narasi Laporan
    </a>
    <a href="{{ route('pelaporan-bulanan.laporan-bulanan', request()->route('reportId')) }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.laporan-bulanan*') ? 'active' : '' }}">
        <i class="bi bi-printer"></i> Cetak Laporan (WP-RA-08-C)
    </a>
    @if($isAdmin)
    <a href="{{ route('pelaporan-bulanan.master-parameter') }}" class="sop08-tab-item {{ request()->routeIs('pelaporan-bulanan.master-parameter*') ? 'active' : '' }}">
        <i class="bi bi-sliders"></i> Master Parameter & Libur
    </a>
    @endif
</div>
