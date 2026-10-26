@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Ringkasan Bisnis')

@section('content')
<!-- Quick Action Buttons -->
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Transaksi Penjualan</span>
    </a>
    <a href="{{ route('purchases.create') }}" class="btn btn-outline">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Input Pembelian Stok (PO)</span>
    </a>
    @can('admin')
    <a href="{{ route('products.create') }}" class="btn btn-outline">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <span>Tambah Produk Baru</span>
    </a>
    @endcan
</div>

<!-- Metrics Overview Cards -->
<div class="metrics-grid">
    <!-- Total Produk -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Produk Aktif</h3>
            <div class="metric-value">{{ number_format($totalProducts) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.35rem;">
                {{ $totalCategories }} Kategori terdaftar
            </div>
        </div>
        <div class="metric-icon-box indigo">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
    </div>

    <!-- Penjualan Hari Ini -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Penjualan Hari Ini</h3>
            <div class="metric-value">Rp {{ number_format($salesTodayRevenue, 0, ',', '.') }}</div>
            <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.35rem;">
                Pendapatan transaksi hari ini
            </div>
        </div>
        <div class="metric-icon-box emerald">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <!-- Penjualan Bulan Ini -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Omzet Bulan Ini</h3>
            <div class="metric-value">Rp {{ number_format($salesMonthRevenue, 0, ',', '.') }}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.35rem;">
                Akumulasi penjualan sukses
            </div>
        </div>
        <div class="metric-icon-box sky">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
    </div>

    <!-- Total Transaksi Selesai -->
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Transaksi</h3>
            <div class="metric-value">{{ number_format($totalTransactionsCount) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.35rem;">
                Penjualan & Pembelian selesai
            </div>
        </div>
        <div class="metric-icon-box amber">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
    </div>

    <!-- Peringatan Stok Rendah -->
    <div class="metric-card" style="{{ $lowStockCount > 0 ? 'border-color: #fca5a5; background-color: #fffafa;' : '' }}">
        <div class="metric-info">
            <h3>Peringatan Stok Rendah</h3>
            <div class="metric-value" style="{{ $lowStockCount > 0 ? 'color: var(--danger);' : '' }}">
                {{ number_format($lowStockCount) }}
            </div>
            <div style="font-size: 0.75rem; color: {{ $lowStockCount > 0 ? 'var(--danger-text)' : 'var(--text-secondary)' }}; margin-top: 0.35rem;">
                {{ $lowStockCount > 0 ? 'Segera lakukan restock barang!' : 'Semua stok dalam batas aman' }}
            </div>
        </div>
        <div class="metric-icon-box rose">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
    </div>
</div>

<!-- Low Stock Alert List (If any) -->
@if($lowStockProducts->count() > 0)
<div class="card" style="border-left: 4px solid var(--danger);">
    <div class="card-header" style="background-color: #fef2f2;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" color="var(--danger)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="card-title" style="color: var(--danger-text);">Perhatian: Daftar Produk Mencapai Batas Minimum Stok</h3>
        </div>
        <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="btn btn-danger-outline btn-sm">
            Lihat Semua ({{ $lowStockCount }})
        </a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Stok Saat Ini</th>
                    <th>Batas Minimum</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $p)
                <tr>
                    <td><code>{{ $p->sku }}</code></td>
                    <td style="font-weight: 600;">{{ $p->name }}</td>
                    <td><span class="badge badge-neutral">{{ $p->category->name ?? '-' }}</span></td>
                    <td>
                        <span style="font-size: 1.05rem; font-weight: 800; color: var(--danger);">
                            {{ $p->stock }}
                        </span>
                    </td>
                    <td>{{ $p->minimum_stock }}</td>
                    <td>
                        <span class="badge badge-danger">Kritis / Rendah</span>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ route('purchases.create') }}" class="btn btn-outline btn-sm" title="Restock via Pembelian">
                            + Restock PO
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Two Column Layout: Recent Sales & Recent Purchases -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.5rem;">
    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Penjualan Terakhir</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm">Semua Penjualan &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                {{ $sale->sale_number }}
                            </a>
                        </td>
                        <td>{{ $sale->customer_name }}</td>
                        <td style="font-weight: 700;">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                                {{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                            </span>
                        </td>
                        <td style="font-size: 0.78rem; color: var(--text-secondary);">
                            {{ $sale->sold_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 1.5rem;">
                            Belum ada riwayat penjualan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pembelian Stok Terakhir</h3>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm">Semua Pembelian &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Pemasok</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPurchases as $purchase)
                    <tr>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" style="font-weight: 700; color: var(--secondary); text-decoration: none;">
                                {{ $purchase->purchase_number }}
                            </a>
                        </td>
                        <td>{{ $purchase->supplier->name ?? '-' }}</td>
                        <td style="font-weight: 700;">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $purchase->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                                {{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                            </span>
                        </td>
                        <td style="font-size: 0.78rem; color: var(--text-secondary);">
                            {{ $purchase->purchased_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 1.5rem;">
                            Belum ada riwayat pembelian stok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
