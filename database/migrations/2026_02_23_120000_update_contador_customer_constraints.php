<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contador_customer', function (Blueprint $table) {
            $table->dropForeign(['contador_id']);
            $table->dropUnique(['contador_id']);
            $table->index('contador_id');
            $table->unique(['contador_id', 'customer_id']);
            $table->foreign('contador_id')->references('id')->on('contadores')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contador_customer', function (Blueprint $table) {
            $table->dropForeign(['contador_id']);
            $table->dropUnique(['contador_id', 'customer_id']);
            $table->dropIndex(['contador_id']);
            $table->unique('contador_id');
            $table->foreign('contador_id')->references('id')->on('contadores')->cascadeOnDelete();
        });
    }
};
