<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="header-title">@yield('title', 'Dashboard')</div>
    </div>

    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-logout" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>
</header>

@push('scripts')
<script>
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
