@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page_title', 'Tambah Produk Baru')

@section('content')
<div style="max-width: 750px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Produk & Stok Awal</h3>
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- SKU -->
                    <div class="form-group">
                        <label for="sku" class="form-label">SKU Produk (Kode Unik) <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="text" 
                            name="sku" 
                            id="sku" 
                            class="form-control @error('sku') is-invalid @enderror" 
                            value="{{ old('sku') }}" 
                            placeholder="contoh: BR-RAMOS-05"
                            required
                        >
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category_id" class="form-label">Kategori <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Product Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Nama Produk Lengkap <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        placeholder="contoh: Beras Premium Ramos 5kg"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Purchase Price -->
                    <div class="form-group">
                        <label for="purchase_price" class="form-label">Harga Beli / Pokok (Rp) <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="number" 
                            name="purchase_price" 
                            id="purchase_price" 
                            step="0.01" 
                            min="0"
                            class="form-control @error('purchase_price') is-invalid @enderror" 
                            value="{{ old('purchase_price', 0) }}" 
                            required
                        >
                        @error('purchase_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Selling Price -->
                    <div class="form-group">
                        <label for="selling_price" class="form-label">Harga Jual Satuan (Rp) <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="number" 
                            name="selling_price" 
                            id="selling_price" 
                            step="0.01" 
                            min="0"
                            class="form-control @error('selling_price') is-invalid @enderror" 
                            value="{{ old('selling_price', 0) }}" 
                            required
                        >
                        @error('selling_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Initial Stock -->
                    <div class="form-group">
                        <label for="stock" class="form-label">Stok Awal (Unit)</label>
                        <input 
                            type="number" 
                            name="stock" 
                            id="stock" 
                            min="0"
                            class="form-control @error('stock') is-invalid @enderror" 
                            value="{{ old('stock', 0) }}"
                        >
                        <small style="color: var(--text-secondary); font-size: 0.78rem;">
                            Stok bertambah otomatis saat transaksi pembelian berhasil dicatat.
                        </small>
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Minimum Stock -->
                    <div class="form-group">
                        <label for="minimum_stock" class="form-label">Batas Minimum Stok (Peringatan) <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="number" 
                            name="minimum_stock" 
                            id="minimum_stock" 
                            min="0"
                            class="form-control @error('minimum_stock') is-invalid @enderror" 
                            value="{{ old('minimum_stock', 5) }}" 
                            required
                        >
                        <small style="color: var(--text-secondary); font-size: 0.78rem;">
                            Sistem akan menandai produk sebagai "Stok Rendah" jika stok &le; nilai ini.
                        </small>
                        @error('minimum_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
