<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
        </a>
    </div>

    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
        </a>

        <a href="{{ route('audit-plan.index') }}" class="nav-item {{ request()->routeIs('audit-plan.*', 'units.*', 'raw-metrics.*', 'coverage.*', 'scheduling.*', 'final-audit-plan.*', 'critical-override.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard2-check nav-icon"></i> Audit Plan
        </a>
    </div>
</div>
