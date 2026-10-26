@extends('layouts.app')

@section('title', 'Detail Pembelian #' . $purchase->purchase_number)
@section('page_title', 'Purchase Order (PO)')

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;" class="no-print">
    <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm">&larr; Kembali ke Daftar Pembelian</a>
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak / Cetak PDF</span>
        </button>

        @if($purchase->status === 'completed')
            @can('admin')
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('PERHATIAN: Membatalkan pembelian akan mengurangi stok produk kembali. Lanjutkan pembatalan?');" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-danger-outline btn-sm">
                    Batalkan Transaksi Pembelian
                </button>
            </form>
            @endcan
        @endif
    </div>
</div>

<div class="card" style="max-width: 860px; margin: 0 auto; box-shadow: var(--shadow-md);">
    <!-- Invoice/PO Banner -->
    <div class="card-header" style="background: #ffffff; padding: 1.75rem 2rem;">
        <div>
            <span class="badge {{ $purchase->status === 'completed' ? 'badge-success' : 'badge-danger' }}" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                {{ $purchase->status === 'completed' ? 'STATUS: SELESAI / STOK MASUK' : 'STATUS: DIBATALKAN' }}
            </span>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                {{ $purchase->purchase_number }}
            </h2>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                Waktu Transaksi: {{ $purchase->purchased_at->format('d F Y, H:i') }} WIB
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 1.15rem; font-weight: 800; color: var(--secondary);">INVENTORY & SALES</div>
            <div style="font-size: 0.78rem; color: var(--text-secondary);">Sistem Distribusi Terpadu</div>
        </div>
    </div>

    <div class="card-body" style="padding: 2rem;">
        <!-- Two Column Meta (Supplier & Staff) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.35rem;">Pemasok / Supplier:</div>
                <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">{{ $purchase->supplier->name ?? '-' }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    Telepon: {{ $purchase->supplier->phone ?: '-' }}<br>
                    Email: {{ $purchase->supplier->email ?: '-' }}<br>
                    Alamat: {{ $purchase->supplier->address ?: '-' }}
                </div>
            </div>

            <div>
                <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.35rem;">Informasi Admin / Petugas:</div>
                <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);">Dicatat oleh: {{ $purchase->user->name ?? 'Sistem' }}</div>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    Role: {{ strtoupper($purchase->user->role ?? '-') }}<br>
                    Catatan: {{ $purchase->notes ?: 'Tidak ada catatan tambahan.' }}
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
                        <th style="text-align: right;">Harga Beli Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                        <td style="font-weight: 600;">{{ $item->product->name ?? 'Produk Dihapus' }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ number_format($item->quantity) }} Unit</td>
                        <td style="text-align: right;">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 700; padding-top: 1rem;">Total Kuantitas:</td>
                        <td style="text-align: center; font-weight: 800; padding-top: 1rem;">{{ number_format($purchase->items->sum('quantity')) }} Unit</td>
                        <td style="padding-top: 1rem;"></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align: right; font-size: 1.15rem; font-weight: 800; color: var(--text-primary); border-top: 2px solid var(--border-color);">
                            Total Pembelian (Grand Total):
                        </td>
                        <td style="text-align: right; font-size: 1.3rem; font-weight: 800; color: var(--primary); border-top: 2px solid var(--border-color);">
                            Rp {{ number_format($purchase->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($purchase->status === 'cancelled')
        <div class="alert alert-error" style="margin-top: 1.5rem;">
            <strong>Catatan Pembatalan:</strong> Transaksi pembelian ini telah dibatalkan oleh Admin. Seluruh stok masuk dari transaksi ini telah dikembalikan ke database.
        </div>
        @endif
    </div>
</div>
@endsection
