<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
        </a>
    </div>

    <div class="sidebar-nav">
        @php
            $role = auth()->user()->role;
            $isRa = $role === 'ra';
            $isAdmin = $role === 'admin';
            
            // Hak Akses Audit Plan
            $canManageMaster = in_array($role, ['kabag_ra', 'kadiv_skai', 'admin']);
            $canViewAuditPlan = in_array($role, ['ra', 'kabag_ra', 'kadiv_skai', 'pimsie', 'admin']);
            $canAccessWorkflow = in_array($role, ['pimsie', 'kadiv_skai', 'kabag_ra', 'admin']);
        @endphp

        {{-- ===================== DASHBOARD ===================== --}}
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
        </a>

        {{-- ===================== AUDIT PLAN (SOP 01) ===================== --}}
        @if($canViewAuditPlan)
        <div class="nav-group {{ request()->routeIs('units.*', 'raw-metrics.*', 'risk-scoring.index', 'assignment-ra.*', 'coverage.*', 'scheduling.*', 'final-audit-plan.*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-clipboard2-pulse nav-icon"></i> Audit Plan
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">

                {{-- Data Unit --}}
                <a href="{{ route('units.index') }}" class="nav-item {{ (request()->routeIs('units.*', 'raw-metrics.*') && request('from') !== 'risk-scoring') ? 'active' : '' }}">
                    <i class="bi bi-building nav-icon"></i> Data Unit
                </a>

                @if(!$isRa)
                {{-- Penilaian Risiko --}}
                <div class="nav-subgroup {{ request()->routeIs('risk-scoring.index') || request('from') === 'risk-scoring' ? 'open' : '' }}">
                    <div class="nav-subgroup-toggle">
                        <i class="bi bi-shield-exclamation nav-icon"></i> Penilaian Risiko
                        <i class="bi bi-chevron-down nav-subgroup-arrow"></i>
                    </div>
                    <div class="nav-subgroup-children">
                        <a href="{{ route('risk-scoring.index') }}" class="nav-item {{ request()->routeIs('risk-scoring.index') || request('from') === 'risk-scoring' ? 'active' : '' }}">
                            <i class="bi bi-bar-chart nav-icon"></i> Hasil Skor & Kategori
                        </a>
                    </div>
                </div>

                {{-- Assignment RA --}}
                <a href="{{ route('assignment-ra.index') }}" class="nav-item {{ request()->routeIs('assignment-ra.*') ? 'active' : '' }}">
                    <i class="bi bi-person-check nav-icon"></i> Assignment RA
                </a>
                @endif

                {{-- Coverage Offsite --}}
                @if($canManageMaster)
                <a href="{{ route('coverage.index') }}" class="nav-item {{ request()->routeIs('coverage.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap nav-icon"></i> Coverage Offsite
                </a>
                @endif

                {{-- Jadwal Onsite --}}
                @if(!$isRa)
                <div class="nav-subgroup {{ request()->routeIs('scheduling.*') ? 'open' : '' }}">
                    <div class="nav-subgroup-toggle">
                        <i class="bi bi-calendar3 nav-icon"></i> Jadwal Onsite
                        <i class="bi bi-chevron-down nav-subgroup-arrow"></i>
                    </div>
                    <div class="nav-subgroup-children">
                        <a href="{{ route('scheduling.index') }}" class="nav-item {{ request()->routeIs('scheduling.index', 'scheduling.unit') ? 'active' : '' }}">
                            <i class="bi bi-calendar-week nav-icon"></i> Frekuensi & Kalender
                        </a>
                        <a href="{{ route('scheduling.capacity') }}" class="nav-item {{ request()->routeIs('scheduling.capacity') ? 'active' : '' }}">
                            <i class="bi bi-speedometer nav-icon"></i> Kapasitas RA
                        </a>
                    </div>
                </div>

                {{-- Final Audit Plan --}}
                <a href="{{ route('final-audit-plan.index') }}" class="nav-item {{ request()->routeIs('final-audit-plan.index', 'final-audit-plan.show') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-check nav-icon"></i> Final Audit Plan
                </a>
                @endif

                {{-- Change Log --}}
                @if($canManageMaster)
                <a href="{{ route('final-audit-plan.change-log') }}" class="nav-item {{ request()->routeIs('final-audit-plan.change-log') ? 'active' : '' }}">
                    <i class="bi bi-clock-history nav-icon"></i> Change Log
                </a>
                @endif

            </div>
        </div>
        @endif

        {{-- Workflow Audit Plan Legacy --}}
        @if($canAccessWorkflow)
        <a href="{{ route('audit-plan.index') }}" class="nav-item {{ request()->routeIs('audit-plan.*') ? 'active' : '' }}">
            <i class="bi bi-kanban nav-icon"></i> Audit Plan (Workflow)
        </a>
        @endif

        {{-- ===================== OFFSITE REVIEW (KHUSUS ROLE RA) ===================== --}}
        @if($isRa || auth()->user()->hasRole('kabag_ra') || isAdmin)
        <div class="nav-group {{ request()->routeIs('ra-offsite.*', 'offsite-review.*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-clipboard2-data nav-icon"></i> Offsite Review
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                
                {{-- Upload CSV --}}
                <a href="{{ route('ra-offsite.upload.index') }}" class="nav-item {{ request()->routeIs('ra-offsite.upload.*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-upload nav-icon"></i> Upload Data CSV
                </a>

                {{-- Register Harian & Review Staging --}}
                <a href="{{ route('ra-offsite.register.index') }}" class="nav-item {{ request()->routeIs('ra-offsite.register.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-check nav-icon"></i> Register Harian & Review
                </a>

                {{-- KKA / Dashboard Review --}}
                <a href="{{ route('offsite-review.index') }}" class="nav-item {{ request()->routeIs('offsite-review.*') && !request()->routeIs('ra-offsite.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text nav-icon"></i> Dashboard & KKA Review
                </a>

            </div>
        </div>
        @endif

        {{-- ===================== PENGATURAN & ADMIN ===================== --}}
        @if($isAdmin)
        <a href="{{ route('admin-offsite.index') }}" class="nav-item {{ request()->routeIs('admin-offsite.*') ? 'active' : '' }}">
            <i class="bi bi-house-check nav-icon"></i> Admin Offsite
        </a>

        <a href="{{ route('master-setup.index') }}" class="nav-item {{ request()->routeIs('master-setup.*') ? 'active' : '' }}">
            <i class="bi bi-gear nav-icon"></i> Pengaturan Modul
        </a>
        @endif

    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.nav-group-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            toggle.parentElement.classList.toggle('open');
        });
    });
    document.querySelectorAll('.nav-subgroup-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            toggle.parentElement.classList.toggle('open');
        });
    });
</script>
@endpush