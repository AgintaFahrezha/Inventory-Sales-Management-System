@extends('layouts.app')

@section('title', 'Tambah Pelanggan / Pembeli')
@section('page_title', 'Tambah Pelanggan / Pembeli Baru')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulir Data Pembeli / Pelanggan</h3>
            <a href="{{ route('customers.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Pelanggan / Toko Pembeli <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        placeholder="contoh: Toko Kelontong Berkah / Budi Santoso"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor WhatsApp / Telepon (Opsional)</label>
                    <input 
                        type="text" 
                        name="phone" 
                        id="phone" 
                        class="form-control @error('phone') is-invalid @enderror" 
                        value="{{ old('phone') }}" 
                        placeholder="0812xxxxxxxx"
                    >
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email (Opsional)</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        placeholder="pelanggan@example.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Alamat Lengkap / Lokasi Toko (Opsional)</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="3" 
                        class="form-control @error('address') is-invalid @enderror"
                        placeholder="Jl. Mawar No..."
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
