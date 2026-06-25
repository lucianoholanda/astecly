<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cria a tabela de modalidades
        Schema::create('service_modalities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1); // Assumindo tenant 1 (luciano-tech)
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('requires_scheduling')->default(false);
            $table->timestamps();
        });

        // 2. Adiciona "Como Conheceu" em Customers
        Schema::table('customers', function (Blueprint $table) {
            $table->string('marketing_source')->nullable()->after('email');
        });

        // 3. Adiciona os campos de triagem e endereço na Service Orders
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('customer_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->foreignId('service_modality_id')->nullable()->constrained('service_modalities');
            $table->dateTime('scheduled_at')->nullable();
            
            $table->text('customer_report')->nullable();
            $table->text('initial_symptom')->nullable();
            $table->text('equipment_condition')->nullable();
            $table->text('general_observations')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['customer_address_id']);
            $table->dropForeign(['service_modality_id']);
            $table->dropColumn(['customer_address_id', 'service_modality_id', 'scheduled_at', 'customer_report', 'initial_symptom', 'equipment_condition', 'general_observations']);
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('marketing_source');
        });

        Schema::dropIfExists('service_modalities');
    }
};