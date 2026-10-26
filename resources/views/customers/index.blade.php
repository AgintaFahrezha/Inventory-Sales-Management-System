@extends('layouts.app')

@section('title', 'Pelanggan / Pembeli')
@section('page_title', 'Master Data Pelanggan / Pembeli (Customers)')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Daftar pelanggan, toko langganan, dan pembeli untuk pencatatan transaksi penjualan kasir (stock-out).
        </p>
    </div>
    @can('admin')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pembeli / Pelanggan</span>
    </a>
    @endcan
</div>

<!-- Search Bar -->
<div class="filter-bar">
    <form action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nama pembeli, nomor telepon, email, atau alamat..."
            >
        </div>
        <button type="submit" class="btn btn-outline">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Cari</span>
        </button>
        @if($search)
            <a href="{{ route('customers.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>
</div>

<!-- Customers Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Pembeli / Pelanggan</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Riwayat Belanja</th>
                    @can('admin')
                    <th style="text-align: right; width: 160px;">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $index => $customer)
                <tr>
                    <td>{{ $customers->firstItem() + $index }}</td>
                    <td style="font-weight: 700; color: var(--text-primary);">
                        {{ $customer->name }}
                    </td>
                    <td style="font-size: 0.85rem;">
                        {{ $customer->phone ?: '-' }}
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem;">
                        {{ $customer->email ?: '-' }}
                    </td>
                    <td style="color: var(--text-secondary); font-size: 0.85rem; max-width: 250px;">
                        {{ $customer->address ?: '-' }}
                    </td>
                    <td>
                        <span class="badge badge-indigo">
                            {{ $customer->sales_count }} Transaksi
                        </span>
                    </td>
                    @can('admin')
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline btn-sm" title="Edit">
                                Edit
                            </a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?');" style="margin: 0;">
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
                        Tidak ada data pembeli/pelanggan yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $customers->firstItem() }} hingga {{ $customers->lastItem() }} dari {{ $customers->total() }} pelanggan
            </div>
            <div>
                {{ $customers->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
