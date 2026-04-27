<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')
                ->nullable()
                ->constrained('menu_items')
                ->onDelete('cascade');
            $table->foreignId('menu_group_id')
                ->nullable()
                ->constrained('menu_groups')
                ->onDelete('cascade');
            $table->text('image_url');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('menu_item_id');
            $table->index('menu_group_id');
            $table->index('is_primary');
            $table->index(['menu_item_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_images');
    }
};
