<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        try {
            DB::statement('ALTER TABLE leads DROP INDEX leads_migracion_index');
        } catch (\Throwable $e) {
            // ignore when index does not exist
        }

        if (Schema::hasColumn('leads', 'migracion')) {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE leads MODIFY migracion VARCHAR(255) NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE leads ALTER COLUMN migracion TYPE VARCHAR(255) USING migracion::VARCHAR');
                DB::statement('ALTER TABLE leads ALTER COLUMN migracion DROP NOT NULL');
            }
        }

        if (! Schema::hasColumn('leads', 'referencia')) {
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
                if (! empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (! Schema::hasColumn('leads', 'fecha_contacto_mes') || ! Schema::hasColumn('leads', 'fecha_contacto_anio')) {
            Schema::table('leads', function (Blueprint $table) {
                if (! Schema::hasColumn('leads', 'fecha_contacto_mes')) {
                    $table->unsignedTinyInteger('fecha_contacto_mes')->nullable()->after('company_address');
                }
                if (! Schema::hasColumn('leads', 'fecha_contacto_anio')) {
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
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement("UPDATE leads SET migracion = NULL WHERE migracion IS NOT NULL AND STR_TO_DATE(migracion, '%Y-%m-%d') IS NULL");
                DB::statement('ALTER TABLE leads MODIFY migracion DATE NULL');
                DB::statement('ALTER TABLE leads ADD INDEX leads_migracion_index (migracion)');
            } elseif ($driver === 'pgsql') {
                DB::statement("UPDATE leads SET migracion = NULL WHERE migracion IS NOT NULL AND migracion !~ '^\\d{4}-\\d{2}-\\d{2}$'");
                DB::statement('ALTER TABLE leads ALTER COLUMN migracion TYPE DATE USING NULLIF(migracion, \'\')::DATE');
                DB::statement('CREATE INDEX IF NOT EXISTS leads_migracion_index ON leads (migracion)');
            }
        }
    }
};
