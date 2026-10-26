@extends('layouts.app')

@section('title', 'Transaksi Penjualan (Stock-Out)')
@section('page_title', 'Daftar Transaksi Penjualan')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Pencatatan kasir dan transaksi keluar kepada pelanggan. Stok produk berkurang secara otomatis ketika transaksi sukses.
        </p>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Penjualan Baru</span>
    </a>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form action="{{ route('sales.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap; align-items: center;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari No. Faktur atau nama pelanggan..."
            >
        </div>

        <div>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 0.4rem;">
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-control" title="Tanggal Mulai">
            <span style="color: var(--text-secondary);">-</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-control" title="Tanggal Akhir">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-outline">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Filter</span>
            </button>
            @if($search || $status || $startDate || $endDate)
                <a href="{{ route('sales.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Sales Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Nama Pelanggan</th>
                    <th>Kasir / Petugas</th>
                    <th>Jumlah Item</th>
                    <th>Total Penjualan</th>
                    <th>Status</th>
                    <th>Waktu Transaksi</th>
                    <th style="text-align: right; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" style="font-family: monospace; font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $sale->sale_number }}
                        </a>
                    </td>
                    <td style="font-weight: 600;">
                        {{ $sale->customer_name }}
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $sale->user->name ?? 'Kasir' }}
                    </td>
                    <td>
                        <span class="badge badge-neutral">
                            {{ $sale->items->sum('quantity') }} Unit ({{ $sale->items->count() }} Jenis)
                        </span>
                    </td>
                    <td style="font-weight: 800; color: var(--text-primary);">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($sale->status === 'completed')
                            <span class="badge badge-success">Selesai</span>
                        @else
                            <span class="badge badge-danger">Dibatalkan</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $sale->sold_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline btn-sm">
                            Faktur &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada transaksi penjualan yang sesuai kriteria pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sales->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $sales->firstItem() }} hingga {{ $sales->lastItem() }} dari {{ $sales->total() }} transaksi
            </div>
            <div>
                {{ $sales->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
