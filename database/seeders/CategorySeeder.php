<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sembako & Pangan Pokok',
                'description' => 'Beras, minyak goreng, gula, tepung, dan kebutuhan pokok harian.',
            ],
            [
                'name' => 'Minuman & Kopi',
                'description' => 'Aneka minuman kemasan, kopi bubuk, teh, dan sirup.',
            ],
            [
                'name' => 'Perlengkapan Kebersihan',
                'description' => 'Sabun mandi, deterjen, cairan pencuci piring, dan pembersih lantai.',
            ],
            [
                'name' => 'Alat Tulis Kantor',
                'description' => 'Kertas HVS, pulpen, buku catatan, dan perlengkapan administrasi.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
