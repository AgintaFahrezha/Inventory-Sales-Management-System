<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Pelanggan Umum (Walk-in)',
                'phone' => '-',
                'email' => null,
                'address' => 'Pelanggan Langsung',
            ],
            [
                'name' => 'Toko Berkah (Pembeli)',
                'phone' => '0812-9876-5432',
                'email' => 'pembeli@example.com',
                'address' => 'Jl. Mawar No. 12, Pasar Minggu, Jakarta Selatan',
            ],
            [
                'name' => 'Warung Sembako Bu Siti',
                'phone' => '0857-1234-5678',
                'email' => 'busiti@example.com',
                'address' => 'Jl. Melati Blok C2 No. 5, Bekasi Barat',
            ],
            [
                'name' => 'Minimarket Sejahtera Jaya',
                'phone' => '0813-4567-8901',
                'email' => 'order@sejahterajaya.example.com',
                'address' => 'Jl. Diponegoro No. 88, Tangerang Kota',
            ],
            [
                'name' => 'Koperasi Karyawan Makmur Sentosa',
                'phone' => '021-5558901',
                'email' => 'kopkar@makmur.example.com',
                'address' => 'Kawasan Industri MM2100 Blok B, Cikarang',
            ],
        ];

        foreach ($customers as $cust) {
            Customer::firstOrCreate(['name' => $cust['name']], $cust);
        }
    }
}
