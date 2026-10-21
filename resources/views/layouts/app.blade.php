<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Resident Auditor Bank Sulteng')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom-app.css') }}">
    
    <style>
        /* Perbaikan z-index untuk memastikan tombol header pojok kanan atas selalu bisa diklik */
        .action-header-tools {
            position: relative;
            z-index: 50 !important;
        }

        /* Memastikan container utama tidak melenceng atau menembus sidebar */
        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            flex: 1;
            min-width: 0; /* Mencegah flex child meluap keluar layar */
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .page-content {
            position: relative;
            z-index: 2;
        }

        /* Penyesuaian Sticky Header Tabel agar tidak menutupi tombol header */
        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
            position: relative;
            z-index: 1;
        }

        .table thead.sticky-top th {
            position: sticky;
            top: 0;
            z-index: 5 !important;
            background-color: #212529 !important;
            color: #ffffff !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="app-container">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        @include('layouts.sidebar')
        <div class="main-content">
            @include('layouts.header')
            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success auto-dismiss">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error auto-dismiss">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Notifikasi otomatis hilang setelah 10 detik
        setTimeout(function() {
            document.querySelectorAll('.auto-dismiss').forEach(function(el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            });
        }, 10000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>