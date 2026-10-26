<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display product listing with search, category filter, and low-stock filter
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $filter = $request->query('filter');

        $query = Product::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($filter === 'low_stock') {
            $query->whereRaw('stock <= minimum_stock');
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $lowStockTotal = Product::whereRaw('stock <= minimum_stock')->count();

        return view('products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'filter',
            'lowStockTotal'
        ));
    }

    /**
     * Show form to create new product
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Store newly created product
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                'unique:products,sku',
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU ini sudah digunakan oleh produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk harus dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh bernilai negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh bernilai negatif.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
            'minimum_stock.integer' => 'Batas minimum stok harus bilangan bulat.',
            'minimum_stock.min' => 'Batas minimum stok minimal 0.',
        ]);

        $validated['stock'] = $validated['stock'] ?? 0;

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display detailed product information and activity
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'purchaseItems' => fn ($q) => $q->with('purchase')->latest()->take(5),
            'saleItems' => fn ($q) => $q->with('sale')->latest()->take(5),
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Show form to edit product
     */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update product details
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU ini sudah digunakan oleh produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori produk harus dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
            'minimum_stock.min' => 'Batas minimum stok minimal 0.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    /**
     * Soft delete product or check transaction constraints
     */
    public function destroy(Product $product): RedirectResponse
    {
        $hasTransactions = $product->purchaseItems()->exists() || $product->saleItems()->exists();

        // Soft delete preserves transaction history and prevents hard delete violations
        $product->delete();

        if ($hasTransactions) {
            return redirect()->route('products.index')
                ->with('success', "Produk '{$product->name}' telah dinonaktifkan (soft delete) untuk menjaga integritas data riwayat transaksi.");
        }

        return redirect()->route('products.index')
            ->with('success', "Produk '{$product->name}' berhasil dihapus.");
    }
}
