<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">IS</div>
                <h2 style="font-weight: 800; font-size: 1.45rem; color: #1e293b; letter-spacing: -0.02em;">Masuk ke Sistem</h2>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">Inventory & Sales Management System</p>
            </div>

            <!-- Flash Error / Success -->
            @if (session('success'))
                <div class="alert alert-success" style="font-size: 0.825rem; padding: 0.65rem 0.85rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error" style="font-size: 0.825rem; padding: 0.65rem 0.85rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Pengguna</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        placeholder="contoh: admin@example.com"
                        required 
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="••••••••"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #64748b; cursor: pointer;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem;">
                    Masuk ke Dashboard
                </button>

                <div style="margin-top: 1.15rem; text-align: center; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <span style="font-size: 0.85rem; color: #64748b;">Belum punya akun?</span>
                    <a href="{{ route('register') }}" style="color: var(--primary); font-size: 0.85rem; font-weight: 700; text-decoration: none; margin-left: 0.25rem;">
                        Daftar Akun Pembeli Baru &rarr;
                    </a>
                </div>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="demo-account-box">
                <div class="demo-account-title">Akun Demo Pengujian (UTS)</div>
                <div class="demo-btn-group" style="grid-template-columns: 1fr 1fr 1fr; gap: 0.4rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('admin@example.com', 'password')">
                        👑 Admin
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('staff@example.com', 'password')">
                        👤 Staff
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="fillCredentials('pembeli@example.com', 'password')" style="border-color: #818cf8; color: var(--primary); font-weight: 700; background: #eef2ff;">
                        🛒 Pembeli
                    </button>
                </div>
                <div style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 0.5rem;">
                    Tip: Klik tombol <strong>🛒 Pembeli</strong> di atas lalu tekan "Masuk ke Dashboard" untuk langsung membuka Halaman Portal Pembeli.
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
