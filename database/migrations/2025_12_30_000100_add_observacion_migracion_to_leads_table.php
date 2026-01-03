<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->text('observacion')->nullable()->after('document_number');
            $table->date('migracion')->nullable()->after('observacion');
            $table->index(['migracion']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['migracion']);
            $table->dropColumn(['migracion', 'observacion']);
        });
    }
};
