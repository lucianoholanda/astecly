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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('device_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_order_status_id')->constrained();
            
            $table->text('reported_defect')->nullable();
            $table->text('found_defect')->nullable();
            $table->text('input_notes')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('solution')->nullable();
            $table->text('output_notes')->nullable();
            
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->decimal('final_value', 10, 2)->default(0);
            $table->dateTime('entry_date')->nullable();
            $table->dateTime('exit_date')->nullable();
            $table->json('photos')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
