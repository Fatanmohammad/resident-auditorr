@php
    $role = auth()->user()->role ?? '';
    $isRa = strtolower($role) === 'ra';
@endphp

<style>
    .rt-tabs, .kka-tabs { 
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
    .rt-tab-item, .kka-tab-item {
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
    .rt-tab-item i, .kka-tab-item i { font-size: 0.95rem; color: #64748b; transition: color 0.2s; }
    .rt-tab-item:hover, .kka-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--bs-blue, #1e4b85); 
    }
    .rt-tab-item:hover i, .kka-tab-item:hover i { color: var(--bs-blue, #1e4b85); }
    .rt-tab-item.active, .kka-tab-item.active { 
        background: var(--bs-blue, #1e4b85); 
        border-color: var(--bs-blue, #1e4b85); 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 75, 133, 0.25); 
    }
    .rt-tab-item.active i, .kka-tab-item.active i { color: #ffffff !important; }
</style>

<div class="rt-tabs">
    <a href="{{ route('risk-trigger.control') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.control') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Ringkasan Control (WP-RA-06-A)
    </a>
    <a href="{{ route('risk-trigger.register') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.register') ? 'active' : '' }}">
        <i class="bi bi-table"></i> Register Trigger (WP-RA-06-B)
    </a>
    <a href="{{ route('risk-trigger.assignment') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.assignment*') ? 'active' : '' }}">
        <i class="bi bi-clipboard-check"></i> Assignment &amp; Scope (WP-RA-06-C)
    </a>
    <a href="{{ route('risk-trigger.kka.index') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.kka*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-spreadsheet"></i> KKA Pengujian 8 Area (WP-RA-06-D)
    </a>
    <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.simpulan-tl*') ? 'active' : '' }}">
        <i class="bi bi-check2-circle"></i> Klarifikasi &amp; Simpulan TL (WP-RA-06-E)
    </a>
</div>
