<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catSembako = Category::where('name', 'Sembako & Pangan Pokok')->first();
        $catMinuman = Category::where('name', 'Minuman & Kopi')->first();
        $catBersih = Category::where('name', 'Perlengkapan Kebersihan')->first();
        $catATK = Category::where('name', 'Alat Tulis Kantor')->first();

        $products = [
            [
                'category_id' => $catSembako?->id ?? 1,
                'sku' => 'BR-RAMOS-05',
                'name' => 'Beras Premium Ramos 5kg',
                'purchase_price' => 65000,
                'selling_price' => 74000,
                'stock' => 45,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catSembako?->id ?? 1,
                'sku' => 'MG-SANIA-02',
                'name' => 'Minyak Goreng Sania 2L',
                'purchase_price' => 32000,
                'selling_price' => 37500,
                'stock' => 28,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catSembako?->id ?? 1,
                'sku' => 'GP-GULAKU-01',
                'name' => 'Gula Pasir Gulaku 1kg',
                'purchase_price' => 15500,
                'selling_price' => 18000,
                'stock' => 4, // Low stock demo
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catSembako?->id ?? 1,
                'sku' => 'TT-SEGITIGA-01',
                'name' => 'Tepung Terigu Segitiga Biru 1kg',
                'purchase_price' => 11000,
                'selling_price' => 13500,
                'stock' => 35,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catMinuman?->id ?? 2,
                'sku' => 'KP-KAPALAPI-165',
                'name' => 'Kopi Kapal Api Special 165g',
                'purchase_price' => 14000,
                'selling_price' => 17000,
                'stock' => 3, // Low stock demo
                'minimum_stock' => 8,
            ],
            [
                'category_id' => $catMinuman?->id ?? 2,
                'sku' => 'TH-SOSRO-30',
                'name' => 'Teh Celup Sosro Isi 30',
                'purchase_price' => 7500,
                'selling_price' => 9500,
                'stock' => 50,
                'minimum_stock' => 15,
            ],
            [
                'category_id' => $catMinuman?->id ?? 2,
                'sku' => 'SU-ULTRA-1000',
                'name' => 'Susu UHT Ultra Milk Full Cream 1L',
                'purchase_price' => 17500,
                'selling_price' => 21000,
                'stock' => 18,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catBersih?->id ?? 3,
                'sku' => 'SB-SUNLIGHT-750',
                'name' => 'Sunlight Jeruk Nipis 750ml Refill',
                'purchase_price' => 13500,
                'selling_price' => 16500,
                'stock' => 22,
                'minimum_stock' => 8,
            ],
            [
                'category_id' => $catBersih?->id ?? 3,
                'sku' => 'DT-RINSO-770',
                'name' => 'Deterjen Bubuk Rinso Anti Noda 770g',
                'purchase_price' => 22000,
                'selling_price' => 26500,
                'stock' => 2, // Low stock demo
                'minimum_stock' => 6,
            ],
            [
                'category_id' => $catATK?->id ?? 4,
                'sku' => 'ATK-PO-A480',
                'name' => 'Kertas HVS PaperOne A4 80gr 1 Rim',
                'purchase_price' => 46000,
                'selling_price' => 54000,
                'stock' => 30,
                'minimum_stock' => 5,
            ],
        ];

        foreach ($products as $item) {
            Product::firstOrCreate(['sku' => $item['sku']], $item);
        }
    }
}
