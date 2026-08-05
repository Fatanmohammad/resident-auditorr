<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
        </a>
    </div>

    <div class="sidebar-nav">
        @php
            $role = auth()->user()->role;
// Role yang boleh mengelola master data (Admin/Operasional)
            $canManage = in_array($role, ['kabag_ra', 'kadiv_skai', 'admin']);
// Role yang bisa input raw metrics & override (admin = akses sama seperti kabag_ra)
            $canInput  = in_array($role, ['kabag_ra', 'ra', 'admin']);
// Audit Plan & submenunya (termasuk Data Unit) tampil untuk semua akun terkait termasuk RA
$canView   = in_array($role, ['ra', 'kabag_ra', 'kadiv_skai', 'pimsie', 'admin']);
            // RA hanya melihat submenu Data Unit di dalam Audit Plan (sesuai kebutuhan)
            $isRa = $role === 'ra';
        @endphp

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
        </a>

        {{-- ===================== AUDIT PLAN (parent) ===================== --}}
        @if($canView)
<div class="nav-group open {{ request()->routeIs('units.*', 'raw-metrics.*', 'risk-scoring.index', 'assignment-ra.*', 'coverage.*', 'scheduling.*', 'final-audit-plan.*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-clipboard2-pulse nav-icon"></i> Audit Plan
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">

{{-- Data Unit (menggantikan Input Data Mentah) --}}
                <a href="{{ route('units.index') }}" class="nav-item {{ (request()->routeIs('units.*', 'raw-metrics.*') && request('from') !== 'risk-scoring') ? 'active' : '' }}">
                    <i class="bi bi-building nav-icon"></i> Data Unit
                </a>

@if(!$isRa)
                {{-- Penilaian Risiko (sub-submenu) --}}
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
                @if($canManage)
                <a href="{{ route('coverage.index') }}" class="nav-item {{ request()->routeIs('coverage.*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap nav-icon"></i> Coverage Offsite
                </a>
                @endif

                {{-- Jadwal Onsite (sub-submenu) --}}
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
                @if($canManage)
                <a href="{{ route('final-audit-plan.change-log') }}" class="nav-item {{ request()->routeIs('final-audit-plan.change-log') ? 'active' : '' }}">
                    <i class="bi bi-clock-history nav-icon"></i> Change Log
                </a>
                @endif

            </div>
        </div>
        @endif

{{-- Menu Audit Plan legacy (approval workflow) — admin akses sama seperti kabag_ra --}}
        @if(in_array($role, ['pimsie', 'kadiv_skai', 'kabag_ra', 'admin']))
        <a href="{{ route('audit-plan.index') }}" class="nav-item {{ request()->routeIs('audit-plan.*') ? 'active' : '' }}">
            <i class="bi bi-kanban nav-icon"></i> Audit Plan (Workflow)
        </a>
        @endif

        {{-- Pengaturan Modul (Admin Only) --}}
        @if($role === 'admin')
        <a href="{{ route('master-setup.index') }}" class="nav-item {{ request()->routeIs('master-setup.*') ? 'active' : '' }}">
            <i class="bi bi-gear nav-icon"></i> Pengaturan Modul
        </a>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Toggle level-2 (nav-group)
    document.querySelectorAll('.nav-group-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            toggle.parentElement.classList.toggle('open');
        });
    });
    // Toggle level-3 (nav-subgroup)
    document.querySelectorAll('.nav-subgroup-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            toggle.parentElement.classList.toggle('open');
        });
    });
</script>
@endpush
