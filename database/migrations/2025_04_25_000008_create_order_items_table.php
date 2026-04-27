<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');
            $table->foreignId('menu_item_id')
                ->nullable()
                ->constrained('menu_items')
                ->onDelete('set null');
            $table->string('name_snapshot')->nullable();
            $table->decimal('price_snapshot', 12, 2)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('subtotal', 12, 2)->nullable();

            $table->index('order_id');
            $table->index('menu_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
