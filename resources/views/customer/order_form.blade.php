@extends('layouts.app')

@section('title', 'Form Pemesanan Barang')
@section('page_title', 'Formulir Pemesanan Barang (Checkout)')

@section('content')
<div style="max-width: 960px;">
    <form action="{{ route('customer.order.store') }}" method="POST" id="customerOrderForm">
        @csrf

        <!-- Customer Destination Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Pengiriman & Pemesan</h3>
                <a href="{{ route('customer.catalog') }}" class="btn btn-outline btn-sm">&larr; Kembali ke Katalog</a>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Nama Pemesan / Toko</label>
                        <input type="text" class="form-control" value="{{ $customer->name }}" readonly style="background: #f8fafc; font-weight: 700;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Pemesan</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->email }}" readonly style="background: #f8fafc;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Alamat Pengiriman / Toko</label>
                    <textarea 
                        name="address" 
                        id="address" 
                        rows="2" 
                        class="form-control @error('address') is-invalid @enderror"
                        placeholder="Masukkan alamat pengantaran barang..."
                    >{{ old('address', $customer->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="notes" class="form-label">Catatan Pesanan (Opsional)</label>
                    <input 
                        type="text" 
                        name="notes" 
                        id="notes" 
                        class="form-control @error('notes') is-invalid @enderror" 
                        value="{{ old('notes') }}" 
                        placeholder="Contoh: Tolong kirim pagi hari"
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
                <h3 class="card-title">Daftar Barang yang Dipesan</h3>
                <button type="button" class="btn btn-outline btn-sm" id="addItemRowBtn">
                    + Tambah Produk Lain
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
                        <span style="color: var(--text-secondary);">Total Unit Barang:</span>
                        <strong id="summaryTotalQty">0 Unit</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Tagihan Pesanan:</span>
                        <span id="summaryGrandTotal">Rp 0</span>
                    </div>
                </div>

                <div id="stockErrorMessage" class="alert alert-error" style="display: none; margin-top: 1rem;">
                    <strong>Perhatian:</strong> Jumlah pesanan melebihi stok yang tersedia pada salah satu baris produk.
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <a href="{{ route('customer.catalog') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        Konfirmasi & Proses Pesanan
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
            {{ $product->name }} (Rp {{ number_format($product->selling_price, 0, ',', '.') }} | Sisa Stok: {{ $product->stock }})
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
        const initialSelectedProduct = "{{ $selectedProductId ?? '' }}";
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
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    stockDisplay.value = opt.dataset.stock + ' Unit';
                    priceDisplay.value = 'Rp ' + parseFloat(opt.dataset.price).toLocaleString('id-ID');
                }
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
                    alert('Minimal satu produk harus ada dalam pesanan.');
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

        addBtn.addEventListener('click', () => addRow());

        // Initialize with preselected product or empty row
        addRow(initialSelectedProduct);
    });
</script>
@endpush
@endsection
