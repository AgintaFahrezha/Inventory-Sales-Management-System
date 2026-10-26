@extends('layouts.app')

@section('title', 'Buat Transaksi Penjualan')
@section('page_title', 'Kasir / Transaksi Penjualan (Stock-Out)')

@section('content')
<div style="max-width: 960px;">
    <form action="{{ route('sales.store') }}" method="POST" id="salesForm">
        @csrf

        <!-- Customer & Meta Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Pelanggan & Waktu Transaksi</h3>
                <a href="{{ route('sales.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                    <!-- Registered Customer Dropdown -->
                    <div class="form-group">
                        <label for="customer_id" class="form-label">Pilih Pelanggan / Pembeli</label>
                        <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror">
                            <option value="" data-name="Pelanggan Umum">-- Pelanggan Umum / Input Bebas --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" data-name="{{ $c->name }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone ?: 'Tanpa No. Telp' }})
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Customer Name on Invoice -->
                    <div class="form-group">
                        <label for="customer_name" class="form-label">Nama Pembeli pada Faktur</label>
                        <input 
                            type="text" 
                            name="customer_name" 
                            id="customer_name" 
                            class="form-control @error('customer_name') is-invalid @enderror" 
                            value="{{ old('customer_name', 'Pelanggan Umum') }}" 
                            placeholder="Pelanggan Umum / Nama Customer"
                        >
                        @error('customer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sold Date -->
                    <div class="form-group">
                        <label for="sold_at" class="form-label">Waktu Transaksi</label>
                        <input 
                            type="datetime-local" 
                            name="sold_at" 
                            id="sold_at" 
                            class="form-control @error('sold_at') is-invalid @enderror" 
                            value="{{ old('sold_at', now()->format('Y-m-d\TH:i')) }}"
                        >
                        @error('sold_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="notes" class="form-label">Catatan Transaksi (Opsional)</label>
                    <input 
                        type="text" 
                        name="notes" 
                        id="notes" 
                        class="form-control @error('notes') is-invalid @enderror" 
                        value="{{ old('notes') }}" 
                        placeholder="Contoh: Pembayaran tunai lunas"
                    >
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Line Items Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Keranjang Belanja / Produk Keluar</h3>
                <button type="button" class="btn btn-outline btn-sm" id="addItemRowBtn">
                    + Tambah Produk
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Pilih Produk</th>
                                <th style="width: 15%;">Stok Tersedia</th>
                                <th style="width: 15%;">Jumlah Beli (Qty)</th>
                                <th style="width: 15%;">Harga Satuan (Rp)</th>
                                <th style="width: 15%;">Subtotal (Rp)</th>
                                <th style="width: 5%; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Order Calculation Summary -->
                <div class="order-summary-box">
                    <div class="summary-row">
                        <span style="color: var(--text-secondary);">Total Macam Produk:</span>
                        <strong id="summaryTotalTypes">0 Jenis</strong>
                    </div>
                    <div class="summary-row">
                        <span style="color: var(--text-secondary);">Total Unit Barang Terjual:</span>
                        <strong id="summaryTotalQty">0 Unit</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Tagihan (Grand Total):</span>
                        <span id="summaryGrandTotal">Rp 0</span>
                    </div>
                </div>

                <div id="stockErrorMessage" class="alert alert-error" style="display: none; margin-top: 1rem;">
                    <strong>Perhatian:</strong> Jumlah pembelian melebihi stok yang tersedia pada salah satu baris item.
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Proses Penjualan & Kurangi Stok
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Product Options Template for JS -->
<template id="productOptionsTemplate">
    <option value="">-- Pilih Produk --</option>
    @foreach($products as $product)
        <option 
            value="{{ $product->id }}" 
            data-price="{{ $product->selling_price }}"
            data-stock="{{ $product->stock }}"
        >
            {{ $product->sku }} - {{ $product->name }} (Tersedia: {{ $product->stock }})
        </option>
    @endforeach
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('itemsTableBody');
        const addBtn = document.getElementById('addItemRowBtn');
        const submitBtn = document.getElementById('submitBtn');
        const stockErrorAlert = document.getElementById('stockErrorMessage');
        const templateOptions = document.getElementById('productOptionsTemplate').innerHTML;
        let rowIndex = 0;

        function addRow(productId = '', quantity = 1) {
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.dataset.index = rowIndex;

            tr.innerHTML = `
                <td>
                    <select name="items[${rowIndex}][product_id]" class="form-control product-select" required>
                        ${templateOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control stock-display" readonly value="-" style="background: #f8fafc; font-weight: 700;">
                </td>
                <td>
                    <input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity-input" min="1" value="${quantity}" required>
                    <small class="stock-warn" style="color: var(--danger); font-size: 0.72rem; display: none; font-weight: 600;">Stok tidak cukup!</small>
                </td>
                <td>
                    <input type="text" class="form-control price-display" readonly value="Rp 0" style="background: #f8fafc; text-align: right;">
                </td>
                <td>
                    <input type="text" class="form-control subtotal-display" readonly value="Rp 0" style="background: #f8fafc; font-weight: 700; text-align: right;">
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-danger-outline btn-sm remove-row-btn" title="Hapus baris">&times;</button>
                </td>
            `;

            tableBody.appendChild(tr);

            const select = tr.querySelector('.product-select');
            const qtyInput = tr.querySelector('.quantity-input');
            const stockDisplay = tr.querySelector('.stock-display');
            const priceDisplay = tr.querySelector('.price-display');
            const removeBtn = tr.querySelector('.remove-row-btn');

            if (productId) {
                select.value = productId;
            }

            select.addEventListener('change', function () {
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    const price = parseFloat(opt.dataset.price) || 0;
                    const stock = parseInt(opt.dataset.stock) || 0;
                    stockDisplay.value = stock + ' Unit';
                    priceDisplay.value = 'Rp ' + price.toLocaleString('id-ID');
                    qtyInput.max = stock;
                } else {
                    stockDisplay.value = '-';
                    priceDisplay.value = 'Rp 0';
                }
                calculateTotals();
            });

            qtyInput.addEventListener('input', calculateTotals);

            removeBtn.addEventListener('click', function () {
                if (document.querySelectorAll('.item-row').length > 1) {
                    tr.remove();
                    calculateTotals();
                } else {
                    alert('Minimal satu produk harus ada dalam transaksi.');
                }
            });

            rowIndex++;
            calculateTotals();
        }

        function calculateTotals() {
            let totalQty = 0;
            let grandTotal = 0;
            let typeCount = 0;
            let hasOversell = false;

            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const select = row.querySelector('.product-select');
                const qtyInput = row.querySelector('.quantity-input');
                const subtotalDisplay = row.querySelector('.subtotal-display');
                const stockWarn = row.querySelector('.stock-warn');

                const opt = select.options[select.selectedIndex];
                const price = (opt && opt.value) ? parseFloat(opt.dataset.price) || 0 : 0;
                const stock = (opt && opt.value) ? parseInt(opt.dataset.stock) || 0 : 0;
                const qty = parseInt(qtyInput.value) || 0;

                const subtotal = qty * price;
                subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID');

                if (select.value) {
                    typeCount++;
                    if (qty > stock) {
                        hasOversell = true;
                        stockWarn.style.display = 'block';
                        qtyInput.style.borderColor = 'var(--danger)';
                    } else {
                        stockWarn.style.display = 'none';
                        qtyInput.style.borderColor = '';
                    }
                }

                totalQty += qty;
                grandTotal += subtotal;
            });

            document.getElementById('summaryTotalTypes').innerText = typeCount + ' Jenis';
            document.getElementById('summaryTotalQty').innerText = totalQty.toLocaleString('id-ID') + ' Unit';
            document.getElementById('summaryGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

            if (hasOversell) {
                stockErrorAlert.style.display = 'flex';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
            } else {
                stockErrorAlert.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }
        }

        const customerSelect = document.getElementById('customer_id');
        const customerNameInput = document.getElementById('customer_name');
        if (customerSelect && customerNameInput) {
            customerSelect.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    customerNameInput.value = opt.dataset.name;
                } else {
                    customerNameInput.value = 'Pelanggan Umum';
                }
            });
        }

        addBtn.addEventListener('click', () => addRow());

        // Initialize with first row
        addRow();
    });
</script>
@endpush
@endsection
