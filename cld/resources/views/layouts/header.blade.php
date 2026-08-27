<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="header-title">@yield('title', 'Dashboard')</div>
    </div>

    <div style="display:flex; align-items:center; gap:0.75rem;">
        {{-- Bell Notification --}}
        <div class="notif-wrapper" id="notifWrapper">
            <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
                <i class="bi bi-bell"></i>
                @if($headerNotifs->count() > 0)
                <span class="notif-badge">{{ $headerNotifs->count() }}</span>
                @endif
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">Notifikasi</div>
                @forelse($headerNotifs as $n)
                <a href="{{ $n['url'] }}" class="notif-item">
                    <div class="notif-icon"><i class="bi {{ $n['icon'] }}"></i></div>
                    <div class="notif-content">
                        <div class="notif-text">{{ $n['text'] }}</div>
                        <div class="notif-time">{{ $n['time'] }}</div>
                    </div>
                </a>
                @empty
                <div class="notif-empty"><i class="bi bi-bell-slash"></i><span>Tidak ada notifikasi</span></div>
                @endforelse
            </div>
        </div>

        {{-- User Profile Dropdown --}}
        <div class="notif-wrapper" id="profileWrapper">
            <button class="notif-btn" style="gap:0.6rem; padding:0.25rem 0.5rem;" onclick="toggleProfile()">
                <div class="avatar" style="width:34px;height:34px;font-size:0.75rem;background:var(--bs-blue);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div style="line-height:1.3; text-align:left;">
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-main);">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.68rem;color:var(--text-muted);">{{ strtoupper(str_replace('_',' ', auth()->user()->role)) }}</div>
                </div>
                <i class="bi bi-chevron-down" style="font-size:0.7rem;color:var(--text-muted);"></i>
            </button>
            <div class="notif-dropdown" id="profileDropdown" style="width:200px;">
                <div class="notif-header" style="display:flex;align-items:center;gap:0.5rem;">
                    <div class="avatar" style="width:30px;height:30px;font-size:0.7rem;background:var(--bs-blue);flex-shrink:0;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-size:0.8rem;font-weight:600;color:var(--text-main);">{{ auth()->user()->name }}</div>
                        <div style="font-size:0.68rem;color:var(--text-muted);">{{ strtoupper(str_replace('_',' ', auth()->user()->role)) }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" style="width:100%;display:flex;align-items:center;gap:0.6rem;padding:0.75rem 1rem;background:none;border:none;cursor:pointer;font-size:0.82rem;color:#dc2626;font-weight:500;transition:var(--transition);">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    function toggleNotif() {
        document.getElementById('notifDropdown').classList.toggle('open');
        document.getElementById('profileDropdown').classList.remove('open');
    }
    function toggleProfile() {
        document.getElementById('profileDropdown').classList.toggle('open');
        document.getElementById('notifDropdown').classList.remove('open');
    }
    document.addEventListener('click', function(e) {
        const notif = document.getElementById('notifWrapper');
        const profile = document.getElementById('profileWrapper');
        if (notif && !notif.contains(e.target)) document.getElementById('notifDropdown').classList.remove('open');
        if (profile && !profile.contains(e.target)) document.getElementById('profileDropdown').classList.remove('open');
    });

    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    function isMobile() {
        return window.innerWidth <= 768;
    }

    toggle?.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    });

    overlay?.addEventListener('click', function () {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });

    window.addEventListener('resize', function () {
        if (!isMobile()) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
        }
    });
</script>
@endpush
