<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('csv_numero')->nullable()->after('name');
            $table->decimal('precio', 12, 2)->nullable()->after('company_address');
            $table->string('rubro', 255)->nullable()->after('precio');
            $table->string('mes', 40)->nullable()->after('rubro');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['csv_numero', 'precio', 'rubro', 'mes']);
        });
    }
};
