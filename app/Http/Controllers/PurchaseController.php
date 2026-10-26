<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Display purchase transactions listing with filters and pagination
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('purchased_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchased_at', '<=', $endDate);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();

        return view('purchases.index', compact(
            'purchases',
            'search',
            'status',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show form to create a new purchase transaction
     */
    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created purchase transaction using DB::transaction
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'supplier_id.exists' => 'Pemasok tidak valid.',
            'items.required' => 'Minimal satu produk harus ditambahkan dalam pembelian.',
            'items.min' => 'Minimal satu produk harus ditambahkan.',
            'items.*.product_id.required' => 'Pilihan produk tidak boleh kosong.',
            'items.*.quantity.required' => 'Kuantitas produk wajib diisi.',
            'items.*.quantity.min' => 'Kuantitas produk minimal 1.',
            'items.*.unit_cost.required' => 'Harga beli satuan wajib diisi.',
            'items.*.unit_cost.min' => 'Harga beli tidak boleh negatif.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Generate unique purchase number (PO-YYYYMMDD-XXXX)
                $datePrefix = Carbon::now()->format('Ymd');
                $latestPurchase = Purchase::where('purchase_number', 'like', "PO-{$datePrefix}-%")
                    ->latest('id')
                    ->first();

                $sequence = 1;
                if ($latestPurchase) {
                    $parts = explode('-', $latestPurchase->purchase_number);
                    if (isset($parts[2])) {
                        $sequence = (int) $parts[2] + 1;
                    }
                }
                $purchaseNumber = sprintf("PO-%s-%04d", $datePrefix, $sequence);

                $purchase = Purchase::create([
                    'supplier_id' => $request->supplier_id,
                    'user_id' => Auth::id(),
                    'purchase_number' => $purchaseNumber,
                    'total' => 0,
                    'status' => 'completed',
                    'notes' => $request->notes,
                    'purchased_at' => $request->purchased_at ?: Carbon::now(),
                ]);

                $grandTotal = 0;

                foreach ($request->items as $itemData) {
                    $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                    $qty = (int) $itemData['quantity'];
                    $unitCost = (float) $itemData['unit_cost'];
                    $subtotal = $qty * $unitCost;

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                    ]);

                    // Increase product stock and record latest purchase cost
                    $product->increment('stock', $qty);
                    $product->update(['purchase_price' => $unitCost]);

                    $grandTotal += $subtotal;
                }

                $purchase->update(['total' => $grandTotal]);
            });

            return redirect()->route('purchases.index')
                ->with('success', 'Transaksi pembelian berhasil disimpan dan stok produk telah ditambahkan.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses transaksi pembelian: ' . $e->getMessage());
        }
    }

    /**
     * Show detail of a purchase transaction
     */
    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Cancel purchase and rollback stock (Admin only)
     */
    public function cancel(Purchase $purchase): RedirectResponse
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berwenang membatalkan transaksi.');
        }

        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Transaksi pembelian ini sudah dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                // Validate stock sufficiency for rollback to avoid negative stock
                foreach ($purchase->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Tidak dapat membatalkan: Stok produk '{$product->name}' tersisa {$product->stock}, tidak mencukupi untuk dikurangi {$item->quantity}.",
                        ]);
                    }
                }

                // Rollback stock
                foreach ($purchase->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    $product->decrement('stock', $item->quantity);
                }

                $purchase->update(['status' => 'cancelled']);
            });

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Transaksi pembelian berhasil dibatalkan dan penambahan stok telah di-rollback.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first('cancel'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan pembelian: ' . $e->getMessage());
        }
    }
}
