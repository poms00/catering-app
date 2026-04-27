<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')
                ->constrained('packages')
                ->onDelete('cascade');
            $table->enum('type', ['fixed', 'selectable_group']);
            $table->foreignId('menu_item_id')
                ->nullable()
                ->constrained('menu_items')
                ->onDelete('cascade');
            $table->foreignId('menu_group_id')
                ->nullable()
                ->constrained('menu_groups')
                ->onDelete('cascade');
            $table->foreignId('default_menu_item_id')
                ->nullable()
                ->constrained('menu_items')
                ->onDelete('set null');
            $table->integer('qty')->default(1);
            $table->integer('min_select')->nullable();
            $table->integer('max_select')->nullable();

            $table->index('package_id');
            $table->index('menu_item_id');
            $table->index('menu_group_id');
            $table->index('default_menu_item_id');
            $table->index('type');
            $table->index(['package_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
    }
};
