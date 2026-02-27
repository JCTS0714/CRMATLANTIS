<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE leads DROP INDEX leads_migracion_index');
        } catch (\Throwable $e) {
            // ignore when index does not exist
        }

        if (Schema::hasColumn('leads', 'migracion')) {
            DB::statement('ALTER TABLE leads MODIFY migracion VARCHAR(255) NULL');
        }

        if (!Schema::hasColumn('leads', 'referencia')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('referencia', 50)->nullable()->after('migracion');
            });
        }

        if (Schema::hasColumn('leads', 'fecha_contacto_mes') || Schema::hasColumn('leads', 'fecha_contacto_anio')) {
            Schema::table('leads', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('leads', 'fecha_contacto_mes')) {
                    $drop[] = 'fecha_contacto_mes';
                }
                if (Schema::hasColumn('leads', 'fecha_contacto_anio')) {
                    $drop[] = 'fecha_contacto_anio';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('leads', 'fecha_contacto_mes') || !Schema::hasColumn('leads', 'fecha_contacto_anio')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'fecha_contacto_mes')) {
                    $table->unsignedTinyInteger('fecha_contacto_mes')->nullable()->after('company_address');
                }
                if (!Schema::hasColumn('leads', 'fecha_contacto_anio')) {
                    $table->unsignedSmallInteger('fecha_contacto_anio')->nullable()->after('fecha_contacto_mes');
                }
            });
        }

        if (Schema::hasColumn('leads', 'referencia')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('referencia');
            });
        }

        if (Schema::hasColumn('leads', 'migracion')) {
            DB::statement("UPDATE leads SET migracion = NULL WHERE migracion IS NOT NULL AND STR_TO_DATE(migracion, '%Y-%m-%d') IS NULL");
            DB::statement('ALTER TABLE leads MODIFY migracion DATE NULL');
            DB::statement('ALTER TABLE leads ADD INDEX leads_migracion_index (migracion)');
        }
    }
};
