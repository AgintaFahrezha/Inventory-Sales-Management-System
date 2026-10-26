@extends('layouts.app')

@section('title', 'Dashboard Pembeli')
@section('page_title', 'Selamat Datang, ' . Auth::user()->name)

@section('content')
<!-- Welcome Banner -->
<div class="card" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; padding: 1.75rem 2rem; border-radius: var(--radius-lg); margin-bottom: 1.5rem; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: white; border: none; margin-bottom: 0.5rem;">
                🛒 Akun Pembeli Resmi
            </span>
            <h2 style="font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em;">
                Pusat Belanja & Pemesanan Barang
            </h2>
            <p style="opacity: 0.9; font-size: 0.9rem; margin-top: 0.25rem;">
                Dapatkan pasokan sembako, minuman, dan perlengkapan usaha Anda dengan harga grosir dan stok terupdate.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('customer.catalog') }}" class="btn" style="background: white; color: var(--primary); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                Lihat Katalog Produk &rarr;
            </a>
            <a href="{{ route('customer.order') }}" class="btn btn-outline" style="background: rgba(255,255,255,0.15); color: white; border-color: rgba(255,255,255,0.3);">
                + Buat Pesanan Baru
            </a>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Belanja Saya</h3>
            <div class="metric-value">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
            <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.35rem;">
                Total belanjaan selesai
            </div>
        </div>
        <div class="metric-icon-box emerald">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-info">
            <h3>Jumlah Pesanan</h3>
            <div class="metric-value">{{ number_format($totalOrders) }} Transaksi</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.35rem;">
                Riwayat transaksi Anda
            </div>
        </div>
        <div class="metric-icon-box indigo">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-info">
            <h3>Status Pelanggan</h3>
            <div class="metric-value" style="font-size: 1.25rem;">Terverifikasi</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.35rem;">
                {{ $customer->name }}
            </div>
        </div>
        <div class="metric-icon-box sky">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
    </div>
</div>

<!-- Featured Ready Stock Products -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Produk Siap Kirim (Stok Tersedia)</h3>
        <a href="{{ route('customer.catalog') }}" class="btn btn-outline btn-sm">Lihat Semua Produk &rarr;</a>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            @foreach($featuredProducts as $item)
            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; transition: var(--transition);">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <span class="badge badge-neutral">{{ $item->category->name ?? 'Umum' }}</span>
                        <span style="font-size: 0.75rem; color: var(--success); font-weight: 700;">Stok: {{ $item->stock }} Unit</span>
                    </div>
                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-primary);">
                        {{ $item->name }}
                    </h4>
                    <div style="font-family: monospace; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                        SKU: {{ $item->sku }}
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem; margin-top: 0.5rem;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase;">Harga Satuan</div>
                        <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                            Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                        </div>
                    </div>
                    <a href="{{ route('customer.order', ['product_id' => $item->id]) }}" class="btn btn-primary btn-sm">
                        + Pesan
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Riwayat Pesanan Terakhir Saya</h3>
        <a href="{{ route('customer.orders') }}" class="btn btn-outline btn-sm">Semua Riwayat &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Faktur</th>
                    <th>Jumlah Barang</th>
                    <th>Total Pembayaran</th>
                    <th>Status</th>
                    <th>Tanggal Pemesanan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    <td>
                        <a href="{{ route('customer.orders.show', $order) }}" style="font-family: monospace; font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $order->sale_number }}
                        </a>
                    </td>
                    <td>{{ $order->items->sum('quantity') }} Unit ({{ $order->items->count() }} Macam)</td>
                    <td style="font-weight: 800; color: var(--text-primary);">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </td>
                    <td>
                        <span class="badge {{ $order->status === 'completed' ? 'badge-success' : 'badge-danger' }}">
                            {{ $order->status === 'completed' ? 'Selesai / Lunas' : 'Dibatalkan' }}
                        </span>
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
                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                        Anda belum memiliki riwayat pesanan. Mulai belanja dengan menekan tombol <strong>+ Buat Pesanan Baru</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
