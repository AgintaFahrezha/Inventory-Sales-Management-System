@extends('layouts.app')

@section('title', 'Detail Produk - ' . $product->name)
@section('page_title', 'Detail & Riwayat Produk')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">&larr; Kembali ke Daftar Produk</a>
    @can('admin')
    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm">Edit Data Produk</a>
    @endcan
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Product Specs Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Produk</h3>
            @if($product->isLowStock())
                <span class="badge badge-danger">Stok Kritis</span>
            @else
                <span class="badge badge-success">Stok Aman</span>
            @endif
        </div>
        <div class="card-body">
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">SKU</div>
                <div style="font-family: monospace; font-size: 1.1rem; font-weight: 700; color: var(--primary);">{{ $product->sku }}</div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Nama Produk</div>
                <div style="font-size: 1.15rem; font-weight: 700;">{{ $product->name }}</div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Kategori</div>
                <div><span class="badge badge-neutral">{{ $product->category->name ?? '-' }}</span></div>
            </div>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 1rem 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Harga Beli Terakhir</div>
                    <div style="font-size: 1rem; font-weight: 600; color: var(--text-secondary);">
                        Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Harga Jual Satuan</div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary);">
                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            @php
                $margin = $product->selling_price - $product->purchase_price;
                $marginPercent = $product->purchase_price > 0 ? ($margin / $product->purchase_price) * 100 : 0;
            @endphp
            <div style="background: #f8fafc; padding: 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.85rem; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-secondary);">Estimasi Margin:</span>
                    <span style="font-weight: 700; color: var(--success);">Rp {{ number_format($margin, 0, ',', '.') }} ({{ number_format($marginPercent, 1) }}%)</span>
                </div>
            </div>

            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 1rem 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Stok Tersedia</div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: {{ $product->isLowStock() ? 'var(--danger)' : 'var(--text-primary)' }};">
                        {{ $product->stock }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); font-weight: 600;">Batas Minimum</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-secondary);">
                        {{ $product->minimum_stock }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Transaction Movement Logs -->
    <div>
        <!-- Sales Log -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Riwayat Penjualan Terakhir</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Faktur</th>
                            <th>Kuantitas</th>
                            <th>Harga Jual</th>
                            <th>Subtotal</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->saleItems as $sItem)
                        <tr>
                            <td>
                                @if($sItem->sale)
                                <a href="{{ route('sales.show', $sItem->sale) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                    {{ $sItem->sale->sale_number }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td style="font-weight: 700; color: var(--danger);">- {{ $sItem->quantity }} Unit</td>
                            <td>Rp {{ number_format($sItem->unit_price, 0, ',', '.') }}</td>
                            <td style="font-weight: 600;">Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                {{ $sItem->sale ? $sItem->sale->sold_at->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 1.5rem;">
                                Belum ada transaksi penjualan untuk produk ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Purchase Log -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pembelian (Stock-In) Terakhir</h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. PO</th>
                            <th>Kuantitas</th>
                            <th>Harga Beli</th>
                            <th>Subtotal</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->purchaseItems as $pItem)
                        <tr>
                            <td>
                                @if($pItem->purchase)
                                <a href="{{ route('purchases.show', $pItem->purchase) }}" style="font-weight: 700; color: var(--secondary); text-decoration: none;">
                                    {{ $pItem->purchase->purchase_number }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td style="font-weight: 700; color: var(--success);">+ {{ $pItem->quantity }} Unit</td>
                            <td>Rp {{ number_format($pItem->unit_cost, 0, ',', '.') }}</td>
                            <td style="font-weight: 600;">Rp {{ number_format($pItem->subtotal, 0, ',', '.') }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                {{ $pItem->purchase ? $pItem->purchase->purchased_at->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 1.5rem;">
                                Belum ada transaksi pembelian untuk produk ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
