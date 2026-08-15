<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cria a tabela customizada de Origens de Marketing
        Schema::create('marketing_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->string('name');
            $table->timestamps();
        });

        // 2. Atualiza a tabela de Clientes
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('marketing_source'); // Remove o antigo campo de texto
            $table->foreignId('marketing_source_id')->nullable()->constrained('marketing_sources');
        });

        // 3. Adiciona "Acessórios" na OS
        Schema::table('service_orders', function (Blueprint $table) {
            $table->text('accessories')->nullable()->after('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) { $table->dropColumn('accessories'); });
        Schema::table('customers', function (Blueprint $table) { $table->dropForeign(['marketing_source_id']); $table->dropColumn('marketing_source_id'); });
        Schema::dropIfExists('marketing_sources');
    }
};