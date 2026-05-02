<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('pago_estado', 40)->nullable()->after('estado');
            $table->unsignedTinyInteger('mes_pagado')->nullable()->after('pago_estado');
            $table->unsignedTinyInteger('mes_por_pagar')->nullable()->after('mes_pagado');

            $table->index(['pago_estado', 'mes_por_pagar']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['pago_estado', 'mes_por_pagar']);
            $table->dropColumn(['pago_estado', 'mes_pagado', 'mes_por_pagar']);
        });
    }
};
