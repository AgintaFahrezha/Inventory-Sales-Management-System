@extends('layouts.app')

@section('title', 'Katalog Produk Belanja')
@section('page_title', 'Katalog Produk & Pasokan Barang')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Pilih produk yang Anda butuhkan untuk stok warung atau toko Anda, lalu klik tombol pesan.
        </p>
    </div>
    <a href="{{ route('customer.order') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span>Ke Form Pemesanan (Keranjang)</span>
    </a>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('customer.catalog') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap; align-items: center;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nama barang atau kode SKU..."
            >
        </div>

        <div style="min-width: 180px;">
            <select name="category_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline">Cari</button>
            @if($search || $categoryId)
                <a href="{{ route('customer.catalog') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Product Catalog Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    @forelse($products as $product)
    <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-md); overflow: hidden; transition: var(--transition);">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                <span class="badge badge-neutral">{{ $product->category->name ?? '-' }}</span>
                <span class="badge badge-success">Stok: {{ $product->stock }} Unit</span>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                {{ $product->name }}
            </h3>

            <div style="font-family: monospace; font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 1rem;">
                SKU: {{ $product->sku }}
            </div>

            <div style="border-top: 1px dashed var(--border-color); padding-top: 0.75rem; margin-top: 0.75rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">
                    Harga Satuan:
                </div>
                <div style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">
                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="card-footer" style="background: #ffffff; border-top: 1px solid var(--border-color); padding: 1rem 1.25rem;">
            <a href="{{ route('customer.order', ['product_id' => $product->id]) }}" class="btn btn-primary" style="width: 100%;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Pesan Produk Ini</span>
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; background: white; border: 1px solid var(--border-color); border-radius: var(--radius-md);">
        <h4 style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Tidak ada produk yang tersedia</h4>
        <p style="font-size: 0.85rem; color: var(--text-muted);">Coba ubah kata kunci pencarian atau kategori filter Anda.</p>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($products->hasPages())
<div class="card">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $products->firstItem() }} hingga {{ $products->lastItem() }} dari {{ $products->total() }} produk
            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endif
@endsection
