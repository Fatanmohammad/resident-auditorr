<ul class="nav nav-tabs mb-4 shadow-sm bg-white rounded p-1 border">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.index') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.index') }}">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.register') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.register') }}">
            <i class="bi bi-journal-plus me-1"></i> Register Indikasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.triase') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.triase') }}">
            <i class="bi bi-diagram-3 me-1"></i> Triase Awal
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.bukti_custody') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.bukti_custody') }}">
            <i class="bi bi-shield-check me-1"></i> Chain of Custody
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.kka') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.kka') }}">
            <i class="bi bi-journal-check me-1"></i> KKA Pemeriksaan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('pemeriksaan_awal.eskalasi') ? 'active fw-bold text-primary border-primary border-bottom-0' : 'text-secondary' }}" href="{{ route('pemeriksaan_awal.eskalasi') }}">
            <i class="bi bi-arrow-up-right-circle me-1"></i> Keputusan & Eskalasi
        </a>
    </li>
</ul>