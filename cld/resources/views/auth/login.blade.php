<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Resident Auditor Bank Sulteng</title>
    <link rel="stylesheet" href="{{ asset('css/custom-app.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng">
            </div>
            <div class="auth-title">
                <h1>Sistem Resident Auditor</h1>
                <p>PT Bank Sulteng — Masuk untuk melanjutkan</p>
            </div>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 1rem;">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}"
                        placeholder="email@banksulteng.co.id" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="remember" id="remember" style="width: auto;">
                    <label for="remember" style="font-size: 0.8rem; color: var(--text-muted); cursor: pointer;">Ingat saya</label>
                </div>
                <button type="submit" class="btn-login">Masuk</button>
            </form>

            <p style="text-align: center; font-size: 0.75rem; color: var(--text-muted); margin-top: 1.5rem;">
                Hubungi administrator jika lupa password
            </p>
        </div>
    </div>
</body>
</html>
