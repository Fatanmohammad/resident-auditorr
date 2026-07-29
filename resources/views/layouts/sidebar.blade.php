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

        {{-- Master Data: hanya Kabag & Kadiv --}}
        @if(in_array(auth()->user()->role, ['kadiv_skai', 'kabag_ra']))
        <div class="nav-group-title">Master Data</div>
        <a href="{{ route('cabang.index') }}" class="nav-item {{ request()->routeIs('cabang.*') ? 'active' : '' }}">
            <i class="bi bi-building nav-icon"></i> Master Cabang
        </a>
        @endif

        {{-- Input Parameter: semua kecuali PIMSIE & auditee --}}
        @if(!in_array(auth()->user()->role, ['pimsie', 'auditee']))
        <div class="nav-group-title">1. Input Parameter</div>
        <a href="{{ route('parameter.index') }}" class="nav-item {{ request()->routeIs('parameter.*') ? 'active' : '' }}">
            <i class="bi bi-sliders nav-icon"></i> Parameter RKAT
        </a>
        @endif

        {{-- Penjadwalan: semua bisa lihat, PIMSIE bisa buat --}}
        <div class="nav-group-title">2. Penjadwalan Audit</div>
        <a href="{{ route('audit-plan.index') }}" class="nav-item {{ request()->routeIs('audit-plan.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3 nav-icon"></i> Audit Plan
        </a>

        {{-- KKA: PIMSIE hanya lihat absensi RA, role lain sesuai --}}
        @if(auth()->user()->role !== 'auditee')
        <div class="nav-group-title">3. Pelaksanaan Audit</div>
        <a href="{{ route('kka.index') }}" class="nav-item {{ request()->routeIs('kka.*') ? 'active' : '' }}">
            <i class="bi bi-journal-check nav-icon"></i>
            {{ auth()->user()->role === 'pimsie' ? 'Absensi RA (KKA)' : 'Kartu Kerja Audit' }}
        </a>
        {{-- Temuan: PIMSIE bisa lihat temuan signifikan & berulang --}}
        <a href="{{ route('temuan.index') }}" class="nav-item {{ request()->routeIs('temuan.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle nav-icon"></i>
            {{ auth()->user()->role === 'pimsie' ? 'Temuan Signifikan' : 'Temuan Audit' }}
        </a>
        @endif

        {{-- Monitoring: bukan PIMSIE & auditee --}}
        @if(!in_array(auth()->user()->role, ['pimsie', 'auditee']))
        <div class="nav-group-title">4. Monitoring</div>
        <a href="{{ route('monitoring.index') }}" class="nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up nav-icon"></i> Monitoring Temuan
        </a>
        <a href="{{ route('tindak-lanjut.index') }}" class="nav-item {{ request()->routeIs('tindak-lanjut.*') ? 'active' : '' }}">
            <i class="bi bi-tools nav-icon"></i> Tindak Lanjut
        </a>
        @endif

        {{-- Scoring & Laporan: PIMSIE hanya lihat laporan --}}
        @if(auth()->user()->role !== 'auditee')
        <div class="nav-group-title">5. Scoring & Laporan</div>
        @if(auth()->user()->role !== 'pimsie')
        <a href="{{ route('scoring.index') }}" class="nav-item {{ request()->routeIs('scoring.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line nav-icon"></i> Scoring RA
        </a>
        @endif
        <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Laporan Audit
        </a>
        @endif

    </div>


</div>
