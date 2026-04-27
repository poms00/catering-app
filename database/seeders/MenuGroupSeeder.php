<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_groups')->insert([
            ['name' => 'Makanan Utama', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Snack', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
