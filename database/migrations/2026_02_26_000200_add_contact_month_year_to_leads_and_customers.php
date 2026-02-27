<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedTinyInteger('fecha_contacto_mes')->nullable()->after('fecha_contacto');
            $table->unsignedSmallInteger('fecha_contacto_anio')->nullable()->after('fecha_contacto_mes');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedTinyInteger('fecha_contacto_mes')->nullable()->after('company_address');
            $table->unsignedSmallInteger('fecha_contacto_anio')->nullable()->after('fecha_contacto_mes');
        });

        DB::statement('UPDATE customers SET fecha_contacto_mes = MONTH(fecha_contacto), fecha_contacto_anio = YEAR(fecha_contacto) WHERE fecha_contacto IS NOT NULL AND (fecha_contacto_mes IS NULL OR fecha_contacto_anio IS NULL)');
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['fecha_contacto_mes', 'fecha_contacto_anio']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['fecha_contacto_mes', 'fecha_contacto_anio']);
        });
    }
};
