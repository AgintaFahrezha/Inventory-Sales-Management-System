@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page_title', 'Ubah Data Pengguna')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Akun: {{ $user->name }}</h3>
            <a href="{{ route('users.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $user->name) }}" 
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email', $user->email) }}" 
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Peran / Hak Akses (Role) <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin / Owner</option>
                        <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Pembeli / Customer</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Minimal 6 karakter jika diubah"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
