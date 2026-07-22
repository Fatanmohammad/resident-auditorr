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
                <!-- Gunakan gambar logo yang sesungguhnya -->
                <img src="{{ asset('img/logo.png') }}" alt="Logo Bank Sulteng" style="height: 70px; width: auto; object-fit: contain;">
            </div>
            
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">Username / NIP</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan Username Anda" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan Password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
