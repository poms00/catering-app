<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_category_menu_item', function (Blueprint $table) {
            $table->foreignId('menu_category_id')
                ->constrained('menu_categories')
                ->cascadeOnDelete();
            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['menu_category_id', 'menu_item_id']);
            $table->index('menu_item_id');
        });

        DB::table('menu_items')
            ->whereNotNull('menu_category_id')
            ->orderBy('id')
            ->select(['id', 'menu_category_id'])
            ->get()
            ->each(function (object $item): void {
                DB::table('menu_category_menu_item')->insertOrIgnore([
                    'menu_category_id' => $item->menu_category_id,
                    'menu_item_id' => $item->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_category_menu_item');
    }
};
