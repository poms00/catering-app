<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');
            $table->enum('type', ['dp', 'settlement', 'full'])->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->enum('status', ['pending', 'paid', 'rejected'])->nullable();
            $table->enum('method', ['transfer', 'cash', 'manual'])->nullable();
            $table->enum('input_source', ['user', 'admin'])->nullable();
            $table->string('transaction_code', 100)->nullable();
            $table->text('proof_image')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('type');
            $table->index('method');
            $table->index('created_at');
            $table->index(['order_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
