<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_group_id')
                ->nullable()
                ->constrained('menu_groups')
                ->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('base_price', 12, 2);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('menu_group_id');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('sort_order');
            $table->index(['menu_group_id', 'is_active']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
