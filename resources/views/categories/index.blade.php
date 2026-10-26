@extends('layouts.app')

@section('title', 'Kategori Produk')
@section('page_title', 'Master Data Kategori')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Kelola pengelompokan produk untuk memudahkan katalog dan pelaporan inventaris.
        </p>
    </div>
    @can('admin')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Kategori</span>
    </a>
    @endcan
</div>

<!-- Search Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('categories.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nama kategori atau deskripsi..."
            >
        </div>
        <button type="submit" class="btn btn-outline">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Cari</span>
        </button>
        @if($search)
            <a href="{{ route('categories.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>
</div>

<!-- Categories Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
                    <th>Dibuat Pada</th>
                    @can('admin')
                    <th style="text-align: right; width: 160px;">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                <tr>
                    <td>{{ $categories->firstItem() + $index }}</td>
                    <td style="font-weight: 700; color: var(--text-primary);">
                        {{ $category->name }}
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $category->description ?: '-' }}
                    </td>
                    <td>
                        <span class="badge badge-indigo">
                            {{ $category->products_count }} Produk
                        </span>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-secondary);">
                        {{ $category->created_at->format('d/m/Y') }}
                    </td>
                    @can('admin')
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline btn-sm" title="Edit">
                                Edit
                            </a>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-outline btn-sm" title="Hapus">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada data kategori yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $categories->firstItem() }} hingga {{ $categories->lastItem() }} dari {{ $categories->total() }} kategori
            </div>
            <div>
                {{ $categories->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
