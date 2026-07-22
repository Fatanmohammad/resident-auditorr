<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Resident Auditor') - Bank Sulteng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-app.css') }}">
</head>
<body>
    <div class="app-container">
        @include('layouts.sidebar')
        
        <div class="main-content">
            @include('layouts.header')
            
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
