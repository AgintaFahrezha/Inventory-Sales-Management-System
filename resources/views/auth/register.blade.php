<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pembeli - {{ config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-wrapper" style="padding: 2rem 1rem;">
        <div class="auth-card" style="max-width: 520px;">
            <div class="auth-header">
                <div class="auth-logo">IS</div>
                <h2 style="font-weight: 800; font-size: 1.45rem; color: #1e293b; letter-spacing: -0.02em;">Daftar Akun Pembeli</h2>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">
                    Buka akun pelanggan untuk memesan stok barang dan melihat katalog pasokan.
                </p>
            </div>

            <!-- Flash Error / Success -->
            @if (session('error'))
                <div class="alert alert-error" style="font-size: 0.825rem; padding: 0.65rem 0.85rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap / Nama Toko Anda <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        placeholder="contoh: Toko Maju Jaya / Siti Rahma"
                        required 
                        autofocus
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Aktif <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email') }}" 
                            placeholder="nama@email.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Nomor WhatsApp / HP <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="text" 
                            name="phone" 
                            id="phone" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone') }}" 
                            placeholder="08123456789"
                            required
                        >
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Alamat Pengiriman / Alamat Toko <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="2" 
                        class="form-control @error('address') is-invalid @enderror" 
                        placeholder="Jalan, No. Rumah, RT/RW, Kota..."
                        required
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group">
                        <label for="password" class="form-label">Password <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Minimal 6 karakter"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Ulangi Password <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            class="form-control" 
                            placeholder="••••••••"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.95rem; margin-top: 0.5rem;">
                    Daftar Akun Pembeli Sekarang
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                        Masuk di sini &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
