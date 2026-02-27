<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('link', 512)->nullable()->after('company_address');
            $table->string('usuario', 150)->nullable()->after('link');
            $table->string('contrasena', 255)->nullable()->after('usuario');
            $table->string('servidor', 100)->nullable()->after('contrasena');
            $table->date('fecha_creacion')->nullable()->after('servidor');
            $table->date('fecha_contacto')->nullable()->after('fecha_creacion');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'link',
                'usuario',
                'contrasena',
                'servidor',
                'fecha_creacion',
                'fecha_contacto',
            ]);
        });
    }
};
