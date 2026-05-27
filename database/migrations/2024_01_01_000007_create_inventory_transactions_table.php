<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['Masuk', 'Keluar', 'Transfer']);
            $table->integer('quantity');
            $table->foreignId('source_warehouse_id')
                  ->nullable()
                  ->constrained('warehouses')
                  ->nullOnDelete();
            $table->foreignId('destination_warehouse_id')
                  ->nullable()
                  ->constrained('warehouses')
                  ->nullOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->string('reference_number')->nullable()->unique();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['type', 'created_at']);
            $table->index('operator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
