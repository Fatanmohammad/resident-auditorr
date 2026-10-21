<ul class="nav nav-tabs mb-4 shadow-sm bg-white rounded p-1 border">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.index') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.index') }}">
            <i class="bi bi-speedometer2 me-1"></i> Ringkasan & Control
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.register_tl') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.register_tl') }}">
            <i class="bi bi-table me-1"></i> Register TL
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.rencana_reminder') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.rencana_reminder') }}">
            <i class="bi bi-alarm me-1"></i> Rencana & Reminder
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.log_bukti') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.log_bukti') }}">
            <i class="bi bi-journal-text me-1"></i> Log Bukti & Komunikasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.kka_verifikasi') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.kka_verifikasi') }}">
            <i class="bi bi-journal-check me-1"></i> KKA Verifikasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.closure_review') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.closure_review') }}">
            <i class="bi bi-check2-circle me-1"></i> Closure Review
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.eskalasi') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.eskalasi') }}">
            <i class="bi bi-arrow-up-right-circle me-1"></i> Perpanjangan & Eskalasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.status_aging') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.status_aging') }}">
            <i class="bi bi-clock-history me-1"></i> Status & Aging
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.kpi_ra') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.kpi_ra') }}">
            <i class="bi bi-graph-up me-1"></i> KPI RA
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('monitoring.ringkasan_bulanan') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('monitoring.ringkasan_bulanan') }}">
            <i class="bi bi-calendar-month me-1"></i> Ringkasan Bulanan
        </a>
    </li>
</ul>