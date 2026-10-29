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
        padding: 0.45rem 0.95rem; 
        font-size: 0.86rem; 
        font-weight: 600; 
        color: #475569; 
        display: inline-flex; 
        align-items: center; 
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .rt-tab-item:hover, .kka-tab-item:hover { 
        background: #eff6ff; 
        border-color: #bfdbfe; 
        color: var(--bs-blue, #1e4b85); 
    }
    .rt-tab-item.active, .kka-tab-item.active { 
        background: var(--bs-blue, #1e4b85); 
        border-color: var(--bs-blue, #1e4b85); 
        color: #ffffff; 
        box-shadow: 0 2px 6px rgba(30, 75, 133, 0.25); 
    }
</style>

<div class="rt-tabs">
    <a href="{{ route('risk-trigger.control') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.control') ? 'active' : '' }}">
        Ringkasan Control
    </a>
    <a href="{{ route('risk-trigger.register') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.register') ? 'active' : '' }}">
        Register Trigger
    </a>
    <a href="{{ route('risk-trigger.assignment') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.assignment*') ? 'active' : '' }}">
        Assignment &amp; Scope
    </a>
    <a href="{{ route('risk-trigger.kka.index') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.kka*') ? 'active' : '' }}">
        KKA Pengujian Area
    </a>
    <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="rt-tab-item {{ request()->routeIs('risk-trigger.simpulan-tl*') ? 'active' : '' }}">
        Klarifikasi &amp; Simpulan TL
    </a>
</div>
