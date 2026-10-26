@extends('layouts.app')

@section('title', 'Katalog Produk & Stok')
@section('page_title', 'Master Data Produk & Stok')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Daftar seluruh inventaris barang dagangan, SKU unik, harga pokok, harga jual, dan status stok.
        </p>
    </div>
    @can('admin')
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Produk Baru</span>
    </a>
    @endcan
</div>

<!-- Filter & Search Bar -->
<div class="filter-bar">
    <form action="{{ route('products.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap; align-items: center;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari SKU atau nama produk..."
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
            <button type="submit" class="btn btn-outline">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Filter</span>
            </button>

            @if($filter === 'low_stock')
                <a href="{{ route('products.index', array_merge(request()->except('filter', 'page'))) }}" class="btn btn-danger btn-sm">
                    Stok Rendah Aktif ✕
                </a>
            @else
                <a href="{{ route('products.index', array_merge(request()->all(), ['filter' => 'low_stock', 'page' => 1])) }}" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: #fca5a5;">
                    ⚠️ Hanya Stok Rendah ({{ $lowStockTotal }})
                </a>
            @endif

            @if($search || $categoryId || $filter)
                <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Products Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok Tersedia</th>
                    <th>Min. Stok</th>
                    <th>Status Stok</th>
                    <th style="text-align: right; width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr style="{{ $product->isLowStock() ? 'background-color: #fffafa;' : '' }}">
                    <td>
                        <a href="{{ route('products.show', $product) }}" style="font-family: monospace; font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $product->sku }}
                        </a>
                    </td>
                    <td style="font-weight: 600;">
                        <a href="{{ route('products.show', $product) }}" style="color: var(--text-primary); text-decoration: none;">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-neutral">
                            {{ $product->category->name ?? '-' }}
                        </span>
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                    </td>
                    <td style="font-weight: 700; color: var(--text-primary);">
                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                    </td>
                    <td>
                        <span style="font-size: 1.05rem; font-weight: 800; color: {{ $product->isLowStock() ? 'var(--danger)' : 'var(--text-primary)' }};">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $product->minimum_stock }}
                    </td>
                    <td>
                        @if($product->isLowStock())
                            <span class="badge badge-danger">⚠️ Stok Rendah</span>
                        @else
                            <span class="badge badge-success">✓ Aman</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-outline btn-sm" title="Lihat Detail">
                                Detail
                            </a>
                            @can('admin')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline btn-sm" title="Ubah Data">
                                Edit
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan produk ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-outline btn-sm" title="Hapus / Soft Delete">
                                    Hapus
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada data produk yang memenuhi kriteria pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $products->firstItem() }} hingga {{ $products->lastItem() }} dari {{ $products->total() }} produk
            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
