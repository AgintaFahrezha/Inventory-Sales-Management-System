@extends('layouts.app')

@section('title', 'Faktur Pesanan #' . $sale->sale_number)
@section('page_title', 'Rincian Faktur & Bukti Pesanan')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="no-print">
    <a href="{{ route('customer.orders') }}" class="btn btn-outline btn-sm">&larr; Kembali ke Riwayat Pesanan</a>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Faktur</span>
        </button>
        <a href="{{ route('customer.catalog') }}" class="btn btn-primary btn-sm">
            + Pesan Lagi
        </a>
    </div>
</div>

<div class="card" style="max-width: 860px; margin: 0 auto; box-shadow: var(--shadow-md);">
    <!-- Invoice Header -->
    <div class="card-header" style="background: #ffffff; padding: 1.75rem 2rem;">
        <div>
            <span class="badge {{ $sale->status === 'completed' ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                {{ $sale->status === 'completed' ? 'STATUS: SELESAI / LUNAS' : 'STATUS: DIBATALKAN' }}
            </span>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                {{ $sale->sale_number }}
            </h2>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Waktu Transaksi: {{ $sale->sold_at->format('d F Y, H:i') }} WIB
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">INVENTORY & SALES</div>
            <div style="font-size: 0.78rem; color: var(--text-secondary);">Sistem Pasokan Grosir</div>
        </div>
    </div>

    <div class="card-body" style="padding: 2rem;">
        <!-- Two Column Meta (Customer & Notes) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.35rem;">Informasi Pemesan / Toko:</div>
                <div style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">{{ $sale->customer_name }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    @if($sale->customer)
                        Telepon: {{ $sale->customer->phone ?: '-' }}<br>
                        Email: {{ $sale->customer->email ?: Auth::user()->email }}<br>
                        Alamat Pengiriman: {{ $sale->customer->address ?: '-' }}
                    @else
                        Akun: {{ Auth::user()->name }} ({{ Auth::user()->email }})
                    @endif
                </div>
            </div>

            <div>
                <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.35rem;">Catatan Pesanan:</div>
                <div style="font-size: 0.9rem; color: var(--text-primary); font-style: italic;">
                    "{{ $sale->notes ?: 'Tidak ada catatan khusus.' }}"
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="table-responsive" style="margin-bottom: 1.5rem;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th style="text-align: center;">Jumlah (Qty)</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                        <td style="font-weight: 600;">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ number_format($item->quantity) }} Unit</td>
                        <td style="text-align: right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700; padding-top: 1rem;">Total Kuantitas:</td>
                        <td style="text-align: center; font-weight: 800; padding-top: 1rem;">{{ number_format($sale->items->sum('quantity')) }} Unit</td>
                        <td style="padding-top: 1rem;"></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right; font-size: 1.15rem; font-weight: 800; color: var(--text-primary); border-top: 2px solid var(--border-color);">
                            Total Tagihan (Grand Total):
                        </td>
                        <td style="text-align: right; font-size: 1.3rem; font-weight: 800; color: var(--primary); border-top: 2px solid var(--border-color);">
                            Rp {{ number_format($sale->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($sale->status === 'cancelled')
        <div class="alert alert-error" style="margin-top: 1.5rem;">
            <strong>Status Pesanan:</strong> Pesanan ini telah dibatalkan oleh pihak pengelola.
        </div>
        @endif
    </div>
</div>
@endsection
