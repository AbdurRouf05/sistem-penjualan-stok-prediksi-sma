<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperUserSeeder extends Seeder
{
    /**
     * Akun bawaan:
     *  - Pemilik toko (Super Admin, semua akses)
     *  - Kasir (penjualan, kasir POS, pesanan online, melihat produk & pelanggan)
     *
     * @return void
     */
    public function run()
    {
        $owner = User::create([
            'name' => 'Pemilik Toko',
            'email' => 'admin@duaputra.test',
            'password' => Hash::make(12345678),
            'is_active' => 1
        ]);

        $superAdmin = Role::create([
            'name' => 'Super Admin'
        ]);

        $owner->assignRole($superAdmin);

        $cashierRole = Role::create([
            'name' => 'Kasir'
        ]);
        $cashierRole->givePermissionTo([
            'edit_own_profile', 'show_notifications',
            'access_products', 'show_products',
            'access_customers', 'create_customers', 'show_customers', 'edit_customers',
            'access_sales', 'create_sales', 'show_sales', 'create_pos_sales', 'access_sale_payments',
            'access_orders',
        ]);

        $cashier = User::create([
            'name' => 'Kasir Toko',
            'email' => 'kasir@duaputra.test',
            'password' => Hash::make(12345678),
            'is_active' => 1
        ]);

        $cashier->assignRole($cashierRole);
    }
}
