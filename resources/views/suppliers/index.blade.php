@extends('layouts.app')

@section('title', 'Pemasok (Suppliers)')
@section('page_title', 'Master Data Pemasok')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Daftar vendor dan pemasok barang dagangan untuk transaksi pengadaan / stock-in.
        </p>
    </div>
    @can('admin')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pemasok</span>
    </a>
    @endcan
</div>

<!-- Search Bar -->
<div class="filter-bar">
    <form action="{{ route('suppliers.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nama pemasok, kontak, email, atau alamat..."
            >
        </div>
        <button type="submit" class="btn btn-outline">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Cari</span>
        </button>
        @if($search)
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>
</div>

<!-- Suppliers Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Pemasok</th>
                    <th>Email</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Transaksi PO</th>
                    @can('admin')
                    <th style="text-align: right; width: 160px;">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $suppliers->firstItem() + $index }}</td>
                    <td style="font-weight: 700; color: var(--text-primary);">
                        {{ $supplier->name }}
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $supplier->email ?: '-' }}
                    </td>
                    <td style="font-size: 0.85rem;">
                        {{ $supplier->phone ?: '-' }}
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem; max-width: 250px;">
                        {{ $supplier->address ?: '-' }}
                    </td>
                    <td>
                        <span class="badge badge-indigo">
                            {{ $supplier->purchases_count }} Transaksi
                        </span>
                    </td>
                    @can('admin')
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline btn-sm" title="Edit">
                                Edit
                            </a>
                            <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemasok ini?');" style="margin: 0;">
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
                    <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada data pemasok yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($suppliers->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $suppliers->firstItem() }} hingga {{ $suppliers->lastItem() }} dari {{ $suppliers->total() }} pemasok
            </div>
            <div>
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
