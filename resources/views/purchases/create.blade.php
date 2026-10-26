@extends('layouts.app')

@section('title', 'Buat Transaksi Pembelian')
@section('page_title', 'Input Transaksi Pembelian Stok (PO)')

@section('content')
<div style="max-width: 960px;">
    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
        @csrf

        <!-- Header Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Pemasok & Transaksi</h3>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline btn-sm">&larr; Kembali</a>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Supplier -->
                    <div class="form-group">
                        <label for="supplier_id" class="form-label">Pemasok / Supplier <span style="color: var(--danger); font-weight: bold;">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pemasok --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} ({{ $supplier->phone ?? 'Tanpa No. Telp' }})
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Purchased Date -->
                    <div class="form-group">
                        <label for="purchased_at" class="form-label">Tanggal Transaksi</label>
                        <input 
                            type="datetime-local" 
                            name="purchased_at" 
                            id="purchased_at" 
                            class="form-control @error('purchased_at') is-invalid @enderror" 
                            value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}"
                        >
                        @error('purchased_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="notes" class="form-label">Catatan Tambahan (Opsional)</label>
                    <input 
                        type="text" 
                        name="notes" 
                        id="notes" 
                        class="form-control @error('notes') is-invalid @enderror" 
                        value="{{ old('notes') }}" 
                        placeholder="Contoh: Pengiriman via armada supplier gelombang 1"
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
                <h3 class="card-title">Daftar Produk Masuk (Items)</h3>
                <button type="button" class="btn btn-outline btn-sm" id="addItemRowBtn">
                    + Tambah Baris Produk
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="items-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 45%;">Pilih Produk</th>
                                <th style="width: 15%;">Jumlah (Qty)</th>
                                <th style="width: 20%;">Harga Beli Satuan (Rp)</th>
                                <th style="width: 15%;">Subtotal (Rp)</th>
                                <th style="width: 5%; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Rows dynamically populated by JS -->
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
                        <span style="color: var(--text-secondary);">Total Kuantitas Barang Masuk:</span>
                        <strong id="summaryTotalQty">0 Unit</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Pembelian (PO):</span>
                        <span id="summaryGrandTotal">Rp 0</span>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('purchases.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Konfirmasi & Tambah Stok
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Product Options Template Data for JS -->
<template id="productOptionsTemplate">
    <option value="">-- Pilih Produk --</option>
    @foreach($products as $product)
        <option 
            value="{{ $product->id }}" 
            data-price="{{ $product->purchase_price }}"
            data-stock="{{ $product->stock }}"
        >
            {{ $product->sku }} - {{ $product->name }} (Stok saat ini: {{ $product->stock }})
        </option>
    @endforeach
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('itemsTableBody');
        const addBtn = document.getElementById('addItemRowBtn');
        const templateOptions = document.getElementById('productOptionsTemplate').innerHTML;
        let rowIndex = 0;

        function addRow(productId = '', quantity = 1, unitCost = '') {
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
                    <input type="number" name="items[${rowIndex}][quantity]" class="form-control quantity-input" min="1" value="${quantity}" required>
                </td>
                <td>
                    <input type="number" name="items[${rowIndex}][unit_cost]" class="form-control price-input" step="0.01" min="0" value="${unitCost}" required placeholder="0">
                </td>
                <td>
                    <input type="text" class="form-control subtotal-display" readonly value="Rp 0" style="background: #f8fafc; font-weight: 700;">
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-danger-outline btn-sm remove-row-btn" title="Hapus baris">&times;</button>
                </td>
            `;

            tableBody.appendChild(tr);

            const select = tr.querySelector('.product-select');
            const qtyInput = tr.querySelector('.quantity-input');
            const priceInput = tr.querySelector('.price-input');
            const removeBtn = tr.querySelector('.remove-row-btn');

            if (productId) {
                select.value = productId;
            }

            select.addEventListener('change', function () {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.price) {
                    if (!priceInput.value || priceInput.value == '0') {
                        priceInput.value = selectedOption.dataset.price;
                    }
                }
                calculateTotals();
            });

            qtyInput.addEventListener('input', calculateTotals);
            priceInput.addEventListener('input', calculateTotals);

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

            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const select = row.querySelector('.product-select');
                const qtyInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.price-input');
                const subtotalDisplay = row.querySelector('.subtotal-display');

                const qty = parseInt(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = qty * price;

                subtotalDisplay.value = 'Rp ' + subtotal.toLocaleString('id-ID');

                if (select.value) {
                    typeCount++;
                }
                totalQty += qty;
                grandTotal += subtotal;
            });

            document.getElementById('summaryTotalTypes').innerText = typeCount + ' Jenis';
            document.getElementById('summaryTotalQty').innerText = totalQty.toLocaleString('id-ID') + ' Unit';
            document.getElementById('summaryGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        addBtn.addEventListener('click', () => addRow());

        // Initialize with first row
        addRow();
    });
</script>
@endpush
@endsection
