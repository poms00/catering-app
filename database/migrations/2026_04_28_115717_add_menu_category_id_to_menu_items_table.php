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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('menu_category_id')
                ->nullable()
                ->after('menu_group_id')
                ->constrained('menu_categories')
                ->nullOnDelete();

            $table->index(['menu_category_id', 'is_active']);
        });

        DB::table('menu_items')
            ->whereNotNull('menu_group_id')
            ->whereNull('menu_category_id')
            ->update([
                'menu_category_id' => DB::raw('(select menu_groups.menu_category_id from menu_groups where menu_groups.id = menu_items.menu_group_id)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['menu_category_id', 'is_active']);
            $table->dropConstrainedForeignId('menu_category_id');
        });
    }
};
