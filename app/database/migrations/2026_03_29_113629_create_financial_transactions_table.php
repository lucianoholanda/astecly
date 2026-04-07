<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->restrictOnDelete();
            
            $table->string('type', 20); // 'income' ou 'expense'
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('status', 50)->default('pending');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
