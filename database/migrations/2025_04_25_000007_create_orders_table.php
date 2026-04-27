<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->string('order_code', 50)->unique();
            $table->string('customer_name');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('time_slot', 100)->nullable();
            $table->integer('guest_count')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('status', [
                'waiting_payment',
                'confirmed',
                'processing',
                'completed',
                'canceled',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('delivery_date');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['delivery_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
