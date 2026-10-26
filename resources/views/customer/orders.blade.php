@extends('layouts.app')

@section('title', 'Riwayat Pesanan Saya')
@section('page_title', 'Daftar Riwayat Pesanan & Belanja')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <p style="color: var(--text-secondary); font-size: 0.9rem;">
            Seluruh transaksi pemesanan barang yang pernah Anda lakukan melalui sistem.
        </p>
    </div>
    <a href="{{ route('customer.catalog') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Mulai Belanja Lagi</span>
    </a>
</div>

<!-- Search Bar -->
<div class="filter-bar">
    <form action="{{ route('customer.orders') }}" method="GET" style="display: flex; gap: 0.75rem; width: 100%; flex-wrap: wrap;">
        <div class="search-input-wrapper">
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}" 
                class="form-control" 
                placeholder="Cari nomor faktur (INV-...)..."
            >
        </div>
        <button type="submit" class="btn btn-outline">Cari</button>
        @if($search)
            <a href="{{ route('customer.orders') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>
</div>

<!-- Orders Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Jumlah Barang</th>
                    <th>Total Tagihan</th>
                    <th>Status Pesanan</th>
                    <th>Waktu Pemesanan</th>
                    <th style="text-align: right; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>
                        <a href="{{ route('customer.orders.show', $order) }}" style="font-family: monospace; font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $order->sale_number }}
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-neutral">
                            {{ $order->items->sum('quantity') }} Unit ({{ $order->items->count() }} Jenis)
                        </span>
                    </td>
                    <td style="font-weight: 800; color: var(--text-primary);">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($order->status === 'completed')
                            <span class="badge badge-success">Selesai / Lunas</span>
                        @else
                            <span class="badge badge-danger">Dibatalkan</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-secondary);">
                        {{ $order->sold_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline btn-sm">
                            Lihat Faktur &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 3rem;">
                        Belum ada riwayat transaksi pesanan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="card-footer">
        <div class="pagination-wrapper">
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Menampilkan {{ $orders->firstItem() }} hingga {{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
            </div>
            <div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
