<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
    </div>

    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
        </a>

        @if(in_array(auth()->user()->role, ['kadiv_skai', 'kabag_ra']))
        <div class="nav-group-title">Master Data</div>
        <a href="{{ route('cabang.index') }}" class="nav-item {{ request()->routeIs('cabang.*') ? 'active' : '' }}">
            <i class="bi bi-building nav-icon"></i> Master Cabang
        </a>
        @endif

        <div class="nav-group-title">1. Input Parameter</div>
        <a href="{{ route('parameter.index') }}" class="nav-item {{ request()->routeIs('parameter.*') ? 'active' : '' }}">
            <i class="bi bi-sliders nav-icon"></i> Parameter RKAT
        </a>

        <div class="nav-group-title">2. Penjadwalan Audit</div>
        <a href="{{ route('audit-plan.index') }}" class="nav-item {{ request()->routeIs('audit-plan.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3 nav-icon"></i> Audit Plan
        </a>

        <div class="nav-group-title">3. Pelaksanaan Audit</div>
        <a href="{{ route('kka.index') }}" class="nav-item {{ request()->routeIs('kka.*') ? 'active' : '' }}">
            <i class="bi bi-journal-check nav-icon"></i> Kartu Kerja Audit
        </a>
        <a href="{{ route('temuan.index') }}" class="nav-item {{ request()->routeIs('temuan.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle nav-icon"></i> Temuan Audit
        </a>

        <div class="nav-group-title">4. Monitoring</div>
        <a href="{{ route('monitoring.index') }}" class="nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up nav-icon"></i> Monitoring Temuan
        </a>
        <a href="{{ route('tindak-lanjut.index') }}" class="nav-item {{ request()->routeIs('tindak-lanjut.*') ? 'active' : '' }}">
            <i class="bi bi-tools nav-icon"></i> Tindak Lanjut
        </a>

        <div class="nav-group-title">5. Scoring & Laporan</div>
        <a href="{{ route('scoring.index') }}" class="nav-item {{ request()->routeIs('scoring.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line nav-icon"></i> Scoring RA
        </a>
        <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Laporan Audit
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div>
                <div class="user-badge-name">{{ auth()->user()->name }}</div>
                <div class="user-badge-role">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</div>
            </div>
        </div>
    </div>
</div>
