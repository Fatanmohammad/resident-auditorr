<header class="top-header">
    <div class="header-title">
        @yield('title', 'Dashboard')
    </div>
    
    <div class="user-profile">
        <div class="user-info">
            <div class="user-name">Resident Auditor</div>
            <div class="user-role">Administrator</div>
        </div>
        <div class="avatar">RA</div>
        <a href="{{ route('login') }}" style="margin-left: 1rem; color: var(--bs-blue); text-decoration: none; font-size: 0.875rem; font-weight: 500;">Logout</a>
    </div>
</header>
