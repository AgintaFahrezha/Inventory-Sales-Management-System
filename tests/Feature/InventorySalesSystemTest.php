<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventorySalesSystemTest extends TestCase
{
    use RefreshDatabase;
    protected User $admin;
    protected User $staff;
    protected Category $category;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Test', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Test', 'password' => Hash::make('password'), 'role' => 'staff']
        );

        $this->category = Category::firstOrCreate(
            ['name' => 'Kategori Pengujian'],
            ['description' => 'Untuk test suite']
        );

        $this->supplier = Supplier::firstOrCreate(
            ['name' => 'Supplier Pengujian'],
            ['phone' => '08123456789']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-SKU-001'],
            [
                'category_id' => $this->category->id,
                'name' => 'Produk Uji Coba',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'stock' => 20,
                'minimum_stock' => 5,
            ]
        );
    }

    /**
     * 1. Auth: Guest tidak dapat membuka dashboard; password salah ditolak.
     */
    public function test_auth_guest_cannot_access_dashboard_and_wrong_password_rejected(): void
    {
        // Guest access to dashboard should redirect to login
        $guestResponse = $this->get('/dashboard');
        $guestResponse->assertRedirect('/login');

        // Wrong password login attempt should be rejected
        $wrongLoginResponse = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword123',
        ]);
        $wrongLoginResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Correct password should authenticate
        $validLoginResponse = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $validLoginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    /**
     * 2. Product: SKU duplikat ditolak; harga negatif ditolak.
     */
    public function test_product_duplicate_sku_and_negative_price_rejected(): void
    {
        // Try creating product with duplicate SKU
        $duplicateResponse = $this->actingAs($this->admin)->post('/products', [
            'sku' => $this->product->sku,
            'name' => 'Produk Duplikat',
            'category_id' => $this->category->id,
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'minimum_stock' => 5,
        ]);
        $duplicateResponse->assertSessionHasErrors('sku');

        // Try creating product with negative price
        $negativePriceResponse = $this->actingAs($this->admin)->post('/products', [
            'sku' => 'UNIQUE-SKU-' . uniqid(),
            'name' => 'Produk Harga Negatif',
            'category_id' => $this->category->id,
            'purchase_price' => -5000,
            'selling_price' => -1000,
            'minimum_stock' => 5,
        ]);
        $negativePriceResponse->assertSessionHasErrors(['purchase_price', 'selling_price']);
    }

    /**
     * 3. Purchase: Pembelian sukses menambah stok sesuai kuantitas.
     */
    public function test_purchase_success_increases_stock_consistently(): void
    {
        $initialStock = $this->product->fresh()->stock;
        $purchaseQty = 15;

        $response = $this->actingAs($this->staff)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $purchaseQty,
                    'unit_cost' => 10500,
                ],
            ],
        ]);

        $response->assertRedirect('/purchases');
        $this->assertEquals($initialStock + $purchaseQty, $this->product->fresh()->stock);
    }

    /**
     * 4. Sale: Penjualan sukses mengurangi stok sesuai kuantitas.
     */
    public function test_sale_success_decreases_stock_consistently(): void
    {
        $this->product->update(['stock' => 25]);
        $initialStock = 25;
        $soldQty = 8;

        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'Budi Customer',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $soldQty,
                ],
            ],
        ]);

        $this->assertEquals($initialStock - $soldQty, $this->product->fresh()->stock);
    }

    /**
     * 5. Insufficient stock: Penjualan dengan stok tidak cukup ditolak tanpa perubahan data.
     */
    public function test_insufficient_stock_sale_is_rejected_without_stock_change(): void
    {
        $this->product->update(['stock' => 5]);
        $initialStock = 5;
        $excessiveQty = 10;

        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'Pembeli Borongan',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $excessiveQty,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        // Stock must remain strictly untouched
        $this->assertEquals($initialStock, $this->product->fresh()->stock);
    }

    /**
     * 6. Atomicity: Jika satu item transaksi gagal, seluruh transaksi rollback.
     */
    public function test_atomicity_transaction_rollback_when_one_item_fails(): void
    {
        $validProduct = Product::create([
            'sku' => 'PROD-A-' . uniqid(),
            'name' => 'Produk A Cukup Stok',
            'category_id' => $this->category->id,
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        $lowProduct = Product::create([
            'sku' => 'PROD-B-' . uniqid(),
            'name' => 'Produk B Sedikit Stok',
            'category_id' => $this->category->id,
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'stock' => 2, // only 2 available
            'minimum_stock' => 2,
        ]);

        $initialStockA = $validProduct->stock;
        $initialStockB = $lowProduct->stock;
        $salesCountBefore = Sale::count();

        // Multi-item sale: 1st item has enough stock (5 of 10), 2nd item fails (5 of 2)
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'Atomicity Test Customer',
            'items' => [
                ['product_id' => $validProduct->id, 'quantity' => 5],
                ['product_id' => $lowProduct->id, 'quantity' => 5], // Will fail!
            ],
        ]);

        $response->assertSessionHas('error');

        // Stock for Product A must NOT be decremented (rolled back)
        $this->assertEquals($initialStockA, $validProduct->fresh()->stock);
        // Stock for Product B unchanged
        $this->assertEquals($initialStockB, $lowProduct->fresh()->stock);
        // No sale header created
        $this->assertEquals($salesCountBefore, Sale::count());
    }

    /**
     * 7. Authorization: Staff tidak dapat mengakses fungsi Admin.
     */
    public function test_staff_cannot_access_admin_functions(): void
    {
        // Staff trying to view User Management -> 403 Forbidden
        $userMgmtResponse = $this->actingAs($this->staff)->get('/users');
        $userMgmtResponse->assertStatus(403);

        // Staff trying to delete a category -> 403 Forbidden
        $deleteCatResponse = $this->actingAs($this->staff)->delete("/categories/{$this->category->id}");
        $deleteCatResponse->assertStatus(403);

        // Staff trying to access product creation page -> 403 Forbidden
        $createProductResponse = $this->actingAs($this->staff)->get('/products/create');
        $createProductResponse->assertStatus(403);
    }

    /**
     * 8. Search: Filter dan pagination menghasilkan data yang sesuai.
     */
    public function test_search_and_filter_returns_expected_data(): void
    {
        // Search by SKU
        $response = $this->actingAs($this->staff)->get('/products?search=' . $this->product->sku);
        $response->assertStatus(200);
        $response->assertSee($this->product->name);

        // Filter low stock
        $this->product->update(['stock' => 2, 'minimum_stock' => 5]);
        $lowStockResponse = $this->actingAs($this->staff)->get('/products?filter=low_stock');
        $lowStockResponse->assertStatus(200);
        $lowStockResponse->assertSee($this->product->sku);
    }

    /**
     * 9. Customer: Pengelolaan data pembeli/pelanggan dan asosiasi penjualan.
     */
    public function test_customer_management_and_sale_association(): void
    {
        // Admin can create a customer
        $createCustomerResponse = $this->actingAs($this->admin)->post('/customers', [
            'name' => 'Toko Mitra Mandiri',
            'phone' => '081234567890',
            'email' => 'mitra@example.com',
            'address' => 'Jl. Pahlawan No. 45',
        ]);
        $createCustomerResponse->assertRedirect('/customers');

        $customer = \App\Models\Customer::where('name', 'Toko Mitra Mandiri')->firstOrFail();

        // Staff creates sale associated with customer
        $this->product->update(['stock' => 20]);
        $saleResponse = $this->actingAs($this->staff)->post('/sales', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $sale = Sale::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($sale);
        $this->assertEquals('Toko Mitra Mandiri', $sale->customer_name);
        $this->assertEquals($customer->id, $sale->customer->id);
    }

    /**
     * 10. Customer Portal: Akun pembeli dapat membuka dashboard pembeli, melihat katalog, dan checkout pesanan.
     */
    public function test_customer_portal_dashboard_and_order_flow(): void
    {
        $customerUser = User::create([
            'name' => 'Pak Budi Pembeli',
            'email' => 'pembeli.test@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // Accessing /dashboard as customer should redirect to /customer/dashboard
        $redirectResponse = $this->actingAs($customerUser)->get('/dashboard');
        $redirectResponse->assertRedirect('/customer/dashboard');

        // Customer dashboard renders successfully
        $dashboardResponse = $this->actingAs($customerUser)->get('/customer/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Saya');

        // Customer catalog renders with available products
        $catalogResponse = $this->actingAs($customerUser)->get('/customer/catalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee($this->product->name);

        // Customer places an order via portal
        $this->product->update(['stock' => 15]);
        $orderResponse = $this->actingAs($customerUser)->post('/customer/order', [
            'address' => 'Jl. Pengiriman Test No. 9',
            'notes' => 'Pesanan segera dikirim',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $order = Sale::where('customer_name', 'Pak Budi Pembeli')->latest()->first();
        $this->assertNotNull($order);
        $orderResponse->assertRedirect("/customer/orders/{$order->id}");

        // Stock decreased accordingly
        $this->assertEquals(13, $this->product->fresh()->stock);
    }

    /**
     * 11. Customer Registration: Calon pembeli dapat mendaftar akun baru sendiri dan langsung diarahkan ke portal belanja.
     */
    public function test_customer_registration_creates_account_and_logs_in(): void
    {
        $showRegisterResponse = $this->get('/register');
        $showRegisterResponse->assertStatus(200);
        $showRegisterResponse->assertSee('Daftar Akun Pembeli');

        $registerResponse = $this->post('/register', [
            'name' => 'Warung Barokah Ibu Nina',
            'email' => 'nina.barokah@example.com',
            'phone' => '081298765432',
            'address' => 'Jl. Mawar No. 12, Jakarta',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $registerResponse->assertRedirect('/customer/dashboard');
        $this->assertAuthenticated();

        $registeredUser = User::where('email', 'nina.barokah@example.com')->first();
        $this->assertNotNull($registeredUser);
        $this->assertEquals('customer', $registeredUser->role);
        $this->assertEquals('Warung Barokah Ibu Nina', $registeredUser->name);

        $customerRecord = \App\Models\Customer::where('email', 'nina.barokah@example.com')->first();
        $this->assertNotNull($customerRecord);
        $this->assertEquals('081298765432', $customerRecord->phone);
        $this->assertEquals('Jl. Mawar No. 12, Jakarta', $customerRecord->address);
    }

    /**
     * 12. Customer Invoice Authorization: Pembeli dapat melihat faktur miliknya, namun diblokir dari faktur pembeli lain.
     */
    public function test_customer_invoice_authorization(): void
    {
        $buyer1 = User::create([
            'name' => 'Pembeli Satu',
            'email' => 'buyer1@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $buyer2 = User::create([
            'name' => 'Pembeli Dua',
            'email' => 'buyer2@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $sale1 = Sale::create([
            'user_id' => $buyer1->id,
            'sale_number' => 'INV-TEST-0001',
            'customer_name' => 'Pembeli Satu',
            'total' => 50000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        // Buyer 1 can view their own invoice
        $response1 = $this->actingAs($buyer1)->get("/customer/orders/{$sale1->id}");
        $response1->assertStatus(200);
        $response1->assertSee('INV-TEST-0001');

        // Buyer 2 cannot view buyer 1's invoice (403 forbidden)
        $response2 = $this->actingAs($buyer2)->get("/customer/orders/{$sale1->id}");
        $response2->assertStatus(403);
    }
}
