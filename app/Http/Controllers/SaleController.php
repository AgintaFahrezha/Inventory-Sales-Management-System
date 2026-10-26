<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * Display sales listing with filter and search
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Sale::with(['user', 'customer', 'items.product']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('sold_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('sold_at', '<=', $endDate);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();

        return view('sales.index', compact(
            'sales',
            'search',
            'status',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show form to create new sales transaction
     */
    public function create(): View
    {
        // Only products that have stock > 0 for selling
        $products = Product::where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();

        return view('sales.create', compact('products', 'customers'));
    }

    /**
     * Store new sale transaction with DB::transaction and lockForUpdate
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih minimal satu produk untuk transaksi penjualan.',
            'items.min' => 'Pilih minimal satu produk untuk transaksi penjualan.',
            'items.*.product_id.required' => 'Item produk wajib dipilih.',
            'items.*.quantity.required' => 'Jumlah pembelian wajib diisi.',
            'items.*.quantity.min' => 'Jumlah pembelian minimal 1.',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {
                // Determine customer name
                $customerName = 'Pelanggan Umum';
                if ($request->customer_id) {
                    $selectedCust = Customer::find($request->customer_id);
                    if ($selectedCust) {
                        $customerName = $selectedCust->name;
                    }
                } elseif ($request->filled('customer_name')) {
                    $customerName = $request->customer_name;
                }

                // Generate unique invoice number (INV-YYYYMMDD-XXXX)
                $datePrefix = Carbon::now()->format('Ymd');
                $latestSale = Sale::where('sale_number', 'like', "INV-{$datePrefix}-%")
                    ->latest('id')
                    ->first();

                $sequence = 1;
                if ($latestSale) {
                    $parts = explode('-', $latestSale->sale_number);
                    if (isset($parts[2])) {
                        $sequence = (int) $parts[2] + 1;
                    }
                }
                $saleNumber = sprintf("INV-%s-%04d", $datePrefix, $sequence);

                $sale = Sale::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $request->customer_id,
                    'sale_number' => $saleNumber,
                    'customer_name' => $customerName,
                    'total' => 0,
                    'status' => 'completed',
                    'notes' => $request->notes,
                    'sold_at' => $request->sold_at ?: Carbon::now(),
                ]);

                $grandTotal = 0;

                // Group quantities if same product was added multiple times
                $groupedItems = [];
                foreach ($request->items as $item) {
                    $pId = (int) $item['product_id'];
                    $qty = (int) $item['quantity'];
                    $groupedItems[$pId] = ($groupedItems[$pId] ?? 0) + $qty;
                }

                foreach ($groupedItems as $productId => $qty) {
                    // Lock for update to prevent overselling on concurrent requests
                    $product = Product::lockForUpdate()->findOrFail($productId);

                    if ($qty < 1 || $product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok produk '{$product->name}' tidak mencukupi (tersedia: {$product->stock}, diminta: {$qty}).",
                        ]);
                    }

                    // Price retrieved securely from DB, never trusted from client
                    $unitPrice = (float) $product->selling_price;
                    $subtotal = $qty * $unitPrice;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    // Atomically decrease product stock
                    $product->decrement('stock', $qty);

                    $grandTotal += $subtotal;
                }

                $sale->update(['total' => $grandTotal]);

                return $sale;
            });

            return redirect()->route('sales.show', $sale)
                ->with('success', "Transaksi penjualan #{$sale->sale_number} berhasil dicatat dan stok telah dikurangi.");
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->with('error', $e->validator->errors()->first('items') ?? 'Validasi stok gagal.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Display sale transaction receipt & detail
     */
    public function show(Sale $sale): View
    {
        $sale->load(['user', 'customer', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Cancel sale and restore product stock (Admin only)
     */
    public function cancel(Sale $sale): RedirectResponse
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Hanya Admin yang berwenang membatalkan transaksi.');
        }

        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Transaksi penjualan ini sudah dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($sale) {
                // Restore stock for each item in the sale
                foreach ($sale->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    $product->increment('stock', $item->quantity);
                }

                $sale->update(['status' => 'cancelled']);
            });

            return redirect()->route('sales.show', $sale)
                ->with('success', "Transaksi penjualan #{$sale->sale_number} berhasil dibatalkan dan stok produk telah dipulihkan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan transaksi penjualan: ' . $e->getMessage());
        }
    }
}
