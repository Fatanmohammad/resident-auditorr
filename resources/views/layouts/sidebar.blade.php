<div class="sidebar">
    <div class="sidebar-header" style="justify-content: center; height: 70px; padding: 0 1rem;">
        <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng" style="height: 38px; width: auto; object-fit: contain;">
    </div>
    
    <div class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i>
            Dashboard
        </a>
        
        <div class="nav-group-title">Master Data</div>
        <a href="{{ route('cabang.index') }}" class="nav-item {{ request()->routeIs('cabang.*') ? 'active' : '' }}">
            <i class="bi bi-building nav-icon"></i>
            Master Cabang
        </a>
        
        <div class="nav-group-title">Rencana Audit</div>
        <a href="{{ route('rencana.input') }}" class="nav-item {{ request()->routeIs('rencana.input') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-plus nav-icon"></i>
            Input Rencana Audit
        </a>
        <a href="{{ route('rencana.scoring') }}" class="nav-item {{ request()->routeIs('rencana.scoring') ? 'active' : '' }}">
            <i class="bi bi-star nav-icon"></i>
            Scoring Parameter
        </a>
        <a href="{{ route('rencana.approval') }}" class="nav-item {{ request()->routeIs('rencana.approval') ? 'active' : '' }}">
            <i class="bi bi-check2-circle nav-icon"></i>
            Approval Audit Plan
        </a>
        
        <div class="nav-group-title">Pelaksanaan Audit</div>
        <a href="{{ route('pelaksanaan.penugasan') }}" class="nav-item {{ request()->routeIs('pelaksanaan.penugasan') ? 'active' : '' }}">
            <i class="bi bi-card-checklist nav-icon"></i>
            Penugasan Audit
        </a>
        <a href="{{ route('pelaksanaan.audit') }}" class="nav-item {{ request()->routeIs('pelaksanaan.audit') ? 'active' : '' }}">
            <i class="bi bi-gear nav-icon"></i>
            Pelaksanaan Audit
        </a>
        
        <div class="nav-group-title">Tindak Lanjut</div>
        <a href="{{ route('tindaklanjut.monitoring') }}" class="nav-item {{ request()->routeIs('tindaklanjut.monitoring') ? 'active' : '' }}">
            <i class="bi bi-eye nav-icon"></i>
            Monitoring Temuan
        </a>
        <a href="{{ route('tindaklanjut.penyelesaian') }}" class="nav-item {{ request()->routeIs('tindaklanjut.penyelesaian') ? 'active' : '' }}">
            <i class="bi bi-tools nav-icon"></i>
            Penyelesaian
        </a>
        
        <div class="nav-group-title">Reporting</div>
        <a href="{{ route('reporting.sistem') }}" class="nav-item {{ request()->routeIs('reporting.sistem') ? 'active' : '' }}">
            <i class="bi bi-bar-chart nav-icon"></i>
            Sistem Skor
        </a>
        <a href="{{ route('reporting.laporan') }}" class="nav-item {{ request()->routeIs('reporting.laporan') ? 'active' : '' }}">
            <i class="bi bi-file-text nav-icon"></i>
            Laporan
        </a>
    </div>
</div>
