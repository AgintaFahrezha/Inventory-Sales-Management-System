@extends('layouts.app')

@section('title', 'Transaksi Pembelian (Stock-In)')
@section('page_title', 'Daftar Pembelian Stok (Purchase Order)')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Pencatatan barang masuk dari pemasok. Stok produk bertambah secara otomatis saat transaksi disimpan.
        </p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Pembelian Baru</span>
    </a>
</div>

<!-- Filter & Search Bar -->
<div class="filter-bar">
    <form action="{{ route('purchases.index') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap; align-items: center;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari No. PO atau nama pemasok..."
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
                <a href="{{ route('purchases.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Purchases Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. PO</th>
                    <th>Pemasok</th>
                    <th>Dicatat Oleh</th>
                    <th>Jumlah Item</th>
                    <th>Total Pembelian</th>
                    <th>Status</th>
                    <th>Tanggal Transaksi</th>
                    <th style="text-align: right; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>
                        <a href="{{ route('purchases.show', $purchase) }}" style="font-family: monospace; font-weight: 700; color: var(--secondary); text-decoration: none;">
                            {{ $purchase->purchase_number }}
                        </a>
                    </td>
                    <td style="font-weight: 600;">
                        {{ $purchase->supplier->name ?? '-' }}
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $purchase->user->name ?? 'Sistem' }}
                    </td>
                    <td>
                        <span class="badge badge-neutral">
                            {{ $purchase->items->sum('quantity') }} Unit ({{ $purchase->items->count() }} Jenis)
                        </span>
                    </td>
                    <td style="font-weight: 800; color: var(--text-primary);">
                        Rp {{ number_format($purchase->total, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($purchase->status === 'completed')
                            <span class="badge badge-success">Selesai</span>
                        @else
                            <span class="badge badge-danger">Dibatalkan</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $purchase->purchased_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline btn-sm">
                            Detail &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 2.5rem;">
                        Tidak ada transaksi pembelian yang sesuai kriteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($purchases->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $purchases->firstItem() }} hingga {{ $purchases->lastItem() }} dari {{ $purchases->total() }} pembelian
            </div>
            <div>
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
