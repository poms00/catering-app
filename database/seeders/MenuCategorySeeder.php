<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_categories')->insert([
            ['name' => 'Nasi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ayam', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Minuman', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
