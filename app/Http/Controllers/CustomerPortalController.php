<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

class CustomerPortalController extends Controller
{
    /**
     * Helper to get or create Customer record for logged-in user
     */
    protected function getCustomerRecord(): Customer
    {
        $user = Auth::user();
        return Customer::firstOrCreate(
            ['email' => $user->email],
            [
                'name' => $user->name,
                'phone' => '-',
                'address' => 'Alamat Pelanggan',
            ]
        );
    }

    /**
     * Customer Dashboard
     */
    public function dashboard(): View
    {
        $customer = $this->getCustomerRecord();

        $ordersQuery = Sale::where(function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->orWhere('user_id', Auth::id())
              ->orWhere('customer_name', Auth::user()->name);
        });

        $totalOrders = (clone $ordersQuery)->count();
        $totalSpent = (clone $ordersQuery)->where('status', 'completed')->sum('total');
        $recentOrders = (clone $ordersQuery)->with('items.product')->latest('sold_at')->take(5)->get();

        // Featured products with stock
        $featuredProducts = Product::with('category')
            ->where('stock', '>', 0)
            ->latest()
            ->take(6)
            ->get();

        return view('customer.dashboard', compact(
            'customer',
            'totalOrders',
            'totalSpent',
            'recentOrders',
            'featuredProducts'
        ));
    }

    /**
     * Customer Product Catalog (Katalog Belanja)
     */
    public function catalog(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $query = Product::with('category')->where('stock', '>', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('customer.catalog', compact('products', 'categories', 'search', 'categoryId'));
    }

    /**
     * Order / Checkout Form
     */
    public function orderForm(Request $request): View
    {
        $selectedProductId = $request->query('product_id');
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $customer = $this->getCustomerRecord();

        return view('customer.order_form', compact('products', 'customer', 'selectedProductId'));
    }

    /**
     * Process Order Placement
     */
    public function placeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih minimal satu produk untuk dipesan.',
            'items.min' => 'Pilih minimal satu produk untuk dipesan.',
            'items.*.product_id.required' => 'Item produk wajib dipilih.',
            'items.*.quantity.required' => 'Jumlah pesanan wajib diisi.',
            'items.*.quantity.min' => 'Jumlah pesanan minimal 1.',
        ]);

        $customer = $this->getCustomerRecord();
        if ($request->filled('address')) {
            $customer->update(['address' => $request->address]);
        }

        try {
            $sale = DB::transaction(function () use ($request, $customer) {
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
                    'customer_id' => $customer->id,
                    'sale_number' => $saleNumber,
                    'customer_name' => $customer->name,
                    'total' => 0,
                    'status' => 'completed',
                    'notes' => $request->notes ?: 'Pesanan Online Pembeli',
                    'sold_at' => Carbon::now(),
                ]);

                $grandTotal = 0;

                // Group items by product_id
                $groupedItems = [];
                foreach ($request->items as $item) {
                    $pId = (int) $item['product_id'];
                    $qty = (int) $item['quantity'];
                    $groupedItems[$pId] = ($groupedItems[$pId] ?? 0) + $qty;
                }

                foreach ($groupedItems as $productId => $qty) {
                    $product = Product::lockForUpdate()->findOrFail($productId);

                    if ($qty < 1 || $product->stock < $qty) {
                        throw ValidationException::withMessages([
                            'items' => "Stok produk '{$product->name}' tidak mencukupi (tersedia: {$product->stock}, diminta: {$qty}).",
                        ]);
                    }

                    $unitPrice = (float) $product->selling_price;
                    $subtotal = $qty * $unitPrice;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $product->decrement('stock', $qty);
                    $grandTotal += $subtotal;
                }

                $sale->update(['total' => $grandTotal]);

                return $sale;
            });

            return redirect()->route('customer.orders.show', $sale)
                ->with('success', "Pesanan #{$sale->sale_number} berhasil dibuat! Stok produk telah diperbarui.");
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', $e->validator->errors()->first('items') ?? 'Validasi stok gagal.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Customer Order History
     */
    public function orders(Request $request): View
    {
        $customer = $this->getCustomerRecord();
        $search = $request->query('search');

        $query = Sale::with(['items.product'])
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                  ->orWhere('user_id', Auth::id())
                  ->orWhere('customer_name', Auth::user()->name);
            });

        if ($search) {
            $query->where('sale_number', 'like', "%{$search}%");
        }

        $orders = $query->latest('sold_at')->paginate(10)->withQueryString();

        return view('customer.orders', compact('orders', 'search'));
    }

    /**
     * Customer Order Invoice Detail
     */
    public function showOrder(Sale $sale): View
    {
        $customer = $this->getCustomerRecord();

        // Authorization check: only this customer or staff/admin can view
        if (! Auth::user()->isAdmin() && ! Auth::user()->isStaff()) {
            if ($sale->customer_id !== $customer->id && $sale->user_id !== Auth::id() && $sale->customer_name !== Auth::user()->name) {
                abort(403, 'Anda tidak memiliki hak untuk melihat faktur pesanan ini.');
            }
        }

        $sale->load(['items.product', 'customer', 'user']);

        return view('customer.order_detail', compact('sale'));
    }
}
