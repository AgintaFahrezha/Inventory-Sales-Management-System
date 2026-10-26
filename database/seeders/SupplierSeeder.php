<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Indofood Sukses Makmur',
                'email' => 'contact@indofood-distribusi.example.com',
                'phone' => '021-57958822',
                'address' => 'Sudirman Plaza, Jl. Jend. Sudirman Kav. 76-78, Jakarta Selatan',
            ],
            [
                'name' => 'PT Unilever Indonesia Distributor',
                'email' => 'sales@unilever-dist.example.com',
                'phone' => '021-80827000',
                'address' => 'BSD Green Office Park, Jl. BSD Grand Boulevard, Tangerang',
            ],
            [
                'name' => 'CV Sumber Makmur Pangan',
                'email' => 'info@sumbermakmur.example.com',
                'phone' => '031-7482910',
                'address' => 'Kawasan Industri Rungkut, Surabaya',
            ],
            [
                'name' => 'PT Sinar Graha Stationery',
                'email' => 'order@sinargraha.example.com',
                'phone' => '022-6019283',
                'address' => 'Jl. Soekarno Hatta No. 420, Bandung',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
