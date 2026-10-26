<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin / Owner Account
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Owner Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Staff Account
        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Operasional',
                'password' => Hash::make('password'),
                'role' => 'staff',
            ]
        );

        // Pembeli / Customer Account
        User::firstOrCreate(
            ['email' => 'pembeli@example.com'],
            [
                'name' => 'Toko Berkah (Pembeli)',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );
    }
}
