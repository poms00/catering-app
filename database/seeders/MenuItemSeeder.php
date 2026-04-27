<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('menu_items')->insert([
            [
                'name' => 'Nasi Goreng Spesial',
                'base_price' => 25000,
                'description' => 'Nasi goreng dengan telur, ayam, dan sayuran segar',
                'is_active' => true,
                // 'menu_group_id' => 1,
                // 'menu_category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ayam Bakar Madu',
                'base_price' => 30000,
                'description' => 'Ayam bakar dengan bumbu madu manis gurih',
                'is_active' => true,
                // 'menu_group_id' => 1,
                // 'menu_category_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Goreng Jawa',
                'base_price' => 20000,
                'description' => 'Mie goreng khas Jawa dengan bumbu tradisional',
                'is_active' => true,
                // 'menu_group_id' => 2,
                // 'menu_category_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Teh Manis',
                'base_price' => 5000,
                'description' => 'Minuman segar es teh manis dingin',
                'is_active' => true,
                // 'menu_group_id' => null,
                // 'menu_category_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}