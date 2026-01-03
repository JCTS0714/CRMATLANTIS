<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contador_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contador_id')->constrained('contadores')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamp('fecha_asignacion')->nullable();

            // En la práctica: un contador solo puede estar asignado a un cliente.
            $table->unique('contador_id');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contador_customer');
    }
};
