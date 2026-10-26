@extends('layouts.app')

@section('title', 'Tambah Pemasok')
@section('page_title', 'Tambah Pemasok Baru')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulir Pemasok</h3>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">Nama Pemasok / Perusahaan <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        placeholder="contoh: PT Indofood Sukses Makmur"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Kontak (Opsional)</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        placeholder="sales@supplier.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                    <input 
                        type="text" 
                        name="phone" 
                        id="phone" 
                        class="form-control @error('phone') is-invalid @enderror" 
                        value="{{ old('phone') }}" 
                        placeholder="021-xxxxxxx / 0812xxxxxxx"
                    >
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Alamat Lengkap (Opsional)</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="3" 
                        class="form-control @error('address') is-invalid @enderror"
                        placeholder="Jl. Raya Industri No..."
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pemasok</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
