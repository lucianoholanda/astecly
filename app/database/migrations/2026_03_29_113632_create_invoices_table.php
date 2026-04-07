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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            
            $table->string('invoice_number', 100);
            $table->string('series', 20)->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->decimal('total_value', 10, 2);
            $table->string('status', 50)->default('issued');
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};