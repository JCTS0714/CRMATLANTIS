<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contadores', function (Blueprint $table) {
            $table->id();
            $table->string('nro', 50)->nullable();
            $table->string('comercio', 255)->nullable();
            $table->string('nom_contador', 255)->nullable();
            $table->string('titular_tlf', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('telefono_actu', 50)->nullable();
            $table->string('link', 512)->nullable();
            $table->string('usuario', 150)->nullable();
            $table->string('contrasena', 255)->nullable();
            $table->string('servidor', 50)->nullable();
            $table->timestamps();

            $table->index(['nro']);
            $table->index(['comercio']);
            $table->index(['nom_contador']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contadores');
    }
};
