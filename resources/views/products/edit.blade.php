@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page_title', 'Ubah Data Produk')

@section('content')
<div style="max-width: 750px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Produk: {{ $product->name }}</h3>
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- SKU -->
                    <div class="form-group">
                        <label for="sku" class="form-label">SKU Produk <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <input 
                            type="text" 
                            name="sku" 
                            id="sku" 
                            class="form-control @error('sku') is-invalid @enderror" 
                            value="{{ old('sku', $product->sku) }}" 
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
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                    <label for="name" class="form-label">Nama Produk <span style="color: var(--danger); font-weight: bold;">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name', $product->name) }}" 
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
                            value="{{ old('purchase_price', $product->purchase_price) }}" 
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
                            value="{{ old('selling_price', $product->selling_price) }}" 
                            required
                        >
                        @error('selling_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Current Stock (Display Only to maintain business logic rules) -->
                    <div class="form-group">
                        <label class="form-label">Stok Saat Ini (Hanya dari Transaksi)</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="{{ $product->stock }} Unit" 
                            disabled 
                            style="background-color: #f1f5f9; font-weight: 700;"
                        >
                        <small style="color: var(--text-secondary); font-size: 0.78rem;">
                            Stok hanya dapat diubah melalui transaksi Pembelian atau Penjualan untuk menjaga konsistensi audit.
                        </small>
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
                            value="{{ old('minimum_stock', $product->minimum_stock) }}" 
                            required
                        >
                        @error('minimum_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('products.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
