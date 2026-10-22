<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
        </a>
    </div>

    <div class="sidebar-nav">
        @php
            $role = auth()->user()->role ?? '';
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

        {{-- ===================== MODUL ONSITE (SOP 04) ===================== --}}
        <div class="nav-group {{ request()->is('onsite*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-building-check nav-icon"></i> Modul Onsite
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="{{ route('onsite.index') }}" class="nav-item {{ request()->is('onsite') || request()->is('onsite/create') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check nav-icon"></i> Daftar Kunjungan
                </a>
                <a href="{{ route('onsite.create') }}" class="nav-item {{ request()->is('onsite/create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle nav-icon"></i> Buat Kunjungan
                </a>
            </div>
        </div>

        {{-- ===================== MODUL OFFSITE (SOP 02) ===================== --}}
        <div class="nav-group {{ request()->is('offsite*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-laptop nav-icon"></i> Modul Offsite
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                
                {{-- MENU KHUSUS ADMIN / PIMPINAN: Rekapitulasi Per Cabang --}}
                @if(in_array($role, ['admin', 'kabag_ra', 'kadiv_skai']))
                <a href="{{ url('/offsite') }}" class="nav-item {{ request()->is('offsite') ? 'active' : '' }}">
                    <i class="bi bi-building-check nav-icon"></i> Rekapitulasi Cabang
                </a>
                @endif

                {{-- MENU KHUSUS RA: Upload Data CSV --}}
                @if($role === 'ra')
                <a href="{{ url('/offsite/upload') }}" class="nav-item {{ request()->is('offsite/upload*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-upload nav-icon"></i> Upload Data (Dump)
                </a>
                @endif

                {{-- MENU BERSAMA (Register & KKA) --}}
                <a href="{{ url('/offsite/register') }}" class="nav-item {{ request()->is('offsite/register*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text nav-icon"></i> Register Harian
                </a>
                
                <a href="{{ url('/offsite/kka') }}" class="nav-item {{ request()->is('offsite/kka*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check nav-icon"></i> KKA Offsite
                </a>
            </div>
        </div>

        {{-- ===================== MODUL DAILY REGISTER ===================== --}}
        @php
            $canAccessDailyRegister = in_array($role, ['admin', 'kabag_ra', 'kadiv_skai', 'ra']);
        @endphp
        
        @if($canAccessDailyRegister)
        <div class="nav-group {{ request()->is('daily-register*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-calendar2-day nav-icon"></i> Modul Daily Register
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                
                @if(in_array($role, ['admin', 'kabag_ra', 'kadiv_skai']))
                <a href="{{ url('/daily-register/rekapitulasi-cabang') }}" class="nav-item {{ request()->is('daily-register/rekapitulasi-cabang') ? 'active' : '' }}">
                    <i class="bi bi-building-check nav-icon"></i> Rekapitulasi Cabang
                </a>
                @endif

                @if($role === 'ra')
                <a href="{{ url('/daily-register/upload') }}" class="nav-item {{ request()->is('daily-register/upload*') ? 'active' : '' }}">
                    <i class="bi bi-cloud-upload nav-icon"></i> Upload Data
                </a>
                @endif

                <a href="{{ url('/daily-register/register') }}" class="nav-item {{ request()->is('daily-register/register') ? 'active' : '' }}">
                    <i class="bi bi-journal-text nav-icon"></i> Register Harian
                </a>
                
                <a href="{{ url('/daily-register/kka') }}" class="nav-item {{ request()->is('daily-register/kka') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check nav-icon"></i> KKA Daily Register
                </a>

                <a href="{{ url('/daily-register/physical-kka') }}" class="nav-item {{ request()->is('daily-register/physical-kka*') ? 'active' : '' }}">
                    <i class="bi bi-file-spreadsheet nav-icon"></i> KKA Fisik (5 Tab Pemeriksaan)
                </a>

                <a href="{{ url('/daily-register/klarifikasi') }}" class="nav-item {{ request()->is('daily-register/klarifikasi') ? 'active' : '' }}">
                    <i class="bi bi-chat-left-text nav-icon"></i> Register Klarifikasi
                </a>
            </div>
        </div>
        @endif
        {{-- ================================================================== --}}

        {{-- ===================== MODUL SURPRISE AUDIT (SOP 05) ===================== --}}
        <div class="nav-group {{ request()->is('surprise-audit*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-shield-lock nav-icon"></i> Modul Surprise Audit
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="{{ route('surprise_audit.index') }}" class="nav-item {{ request()->routeIs('surprise_audit.index') ? 'active' : '' }}">
                    <i class="bi bi-list-check nav-icon"></i> Daftar Surprise Audit
                </a>
                <a href="{{ route('surprise_audit.create') }}" class="nav-item {{ request()->routeIs('surprise_audit.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle nav-icon"></i> Buat Penugasan Baru
                </a>
            </div>
        </div>

        {{-- ===================== MODUL MONITORING TINDAK LANJUT (SOP 07) ===================== --}}
        <div class="nav-group {{ request()->is('monitoring*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-shield-check nav-icon"></i> Monitoring Tindak Lanjut
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="{{ route('monitoring.index') }}" class="nav-item {{ request()->routeIs('monitoring.index') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 nav-icon"></i> Ringkasan & Control
                </a>
                <a href="{{ route('monitoring.register_tl') }}" class="nav-item {{ request()->routeIs('monitoring.register_tl') ? 'active' : '' }}">
                    <i class="bi bi-table nav-icon"></i> Register TL
                </a>
                <a href="{{ route('monitoring.rencana_reminder') }}" class="nav-item {{ request()->routeIs('monitoring.rencana_reminder') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event nav-icon"></i> Rencana & Reminder
                </a>
                <a href="{{ route('monitoring.log_bukti') }}" class="nav-item {{ request()->routeIs('monitoring.log_bukti') ? 'active' : '' }}">
                    <i class="bi bi-journal-check nav-icon"></i> Log Bukti & Komunikasi
                </a>
                <a href="{{ route('monitoring.kka_verifikasi') }}" class="nav-item {{ request()->routeIs('monitoring.kka_verifikasi') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text nav-icon"></i> KKA Verifikasi
                </a>
                <a href="{{ route('monitoring.closure_review') }}" class="nav-item {{ request()->routeIs('monitoring.closure_review') ? 'active' : '' }}">
                    <i class="bi bi-check2-all nav-icon"></i> Closure Review
                </a>
                <a href="{{ route('monitoring.eskalasi') }}" class="nav-item {{ request()->routeIs('monitoring.eskalasi') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle nav-icon"></i> Perpanjangan & Eskalasi
                </a>
                <a href="{{ route('monitoring.status_aging') }}" class="nav-item {{ request()->routeIs('monitoring.status_aging') ? 'active' : '' }}">
                    <i class="bi bi-clock-history nav-icon"></i> Status & Aging
                </a>
            </div>
        </div>

        {{-- ===================== MODUL PEMERIKSAAN AWAL FRAUD (SOP 09) ===================== --}}
        <div class="nav-group {{ request()->is('pemeriksaan-awal*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-shield-exclamation nav-icon"></i> Pemeriksaan Awal Fraud
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="{{ route('pemeriksaan_awal.index') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.index') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 nav-icon"></i> Dashboard Indikasi
                </a>
                <a href="{{ route('pemeriksaan_awal.register') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.register') ? 'active' : '' }}">
                    <i class="bi bi-journal-plus nav-icon"></i> Register Indikasi
                </a>
                <a href="{{ route('pemeriksaan_awal.triase') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.triase') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3 nav-icon"></i> Triase Awal
                </a>
                <a href="{{ route('pemeriksaan_awal.bukti_custody') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.bukti_custody') ? 'active' : '' }}">
                    <i class="bi bi-shield-check nav-icon"></i> Chain of Custody
                </a>
                <a href="{{ route('pemeriksaan_awal.kka') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.kka') ? 'active' : '' }}">
                    <i class="bi bi-journal-check nav-icon"></i> KKA Pemeriksaan
                </a>
                <a href="{{ route('pemeriksaan_awal.eskalasi') }}" class="nav-item {{ request()->routeIs('pemeriksaan_awal.eskalasi') ? 'active' : '' }}">
                    <i class="bi bi-arrow-up-right-circle nav-icon"></i> Keputusan & Eskalasi
                </a>
            </div>
        </div>

        {{-- ===================== RISK TRIGGER VISIT (SOP 06) ===================== --}}
        @php
            $canAccessRiskTrigger = in_array($role, ['admin', 'kabag_ra', 'kadiv_skai', 'ra', 'reviewer', 'pimsie']);
        @endphp
        @if($canAccessRiskTrigger)
        <div class="nav-group {{ request()->is('risk-trigger*') ? 'open' : '' }}">
            <div class="nav-group-toggle">
                <i class="bi bi-lightning-charge-fill nav-icon text-warning"></i> Risk Trigger Visit (SOP 06)
                <i class="bi bi-chevron-down nav-arrow"></i>
            </div>
            <div class="nav-group-children">
                <a href="{{ route('risk-trigger.control') }}" class="nav-item {{ request()->routeIs('risk-trigger.control') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 nav-icon"></i> Ringkasan Control
                </a>
                <a href="{{ route('risk-trigger.register') }}" class="nav-item {{ request()->routeIs('risk-trigger.register') ? 'active' : '' }}">
                    <i class="bi bi-table nav-icon"></i> Register Trigger
                </a>
                <a href="{{ route('risk-trigger.assignment') }}" class="nav-item {{ request()->routeIs('risk-trigger.assignment*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check nav-icon"></i> Assignment & Scope
                </a>
                <a href="{{ route('risk-trigger.program-kerja') }}" class="nav-item {{ request()->routeIs('risk-trigger.program-kerja*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text nav-icon"></i> Program Kerja & Data
                </a>
                <a href="{{ route('risk-trigger.kka.index') }}" class="nav-item {{ request()->routeIs('risk-trigger.kka*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet nav-icon"></i> KKA Risk Trigger
                </a>
                <a href="{{ route('risk-trigger.simpulan-tl.index') }}" class="nav-item {{ request()->routeIs('risk-trigger.simpulan-tl*') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle nav-icon"></i> Klarifikasi & Simpulan TL
                </a>
            </div>
        </div>
        @endif
        {{-- ======================================================================= --}}

        {{-- ===================== PENGATURAN & ADMIN ===================== --}}
        @if($isAdmin)
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