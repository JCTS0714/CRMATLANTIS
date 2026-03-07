<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scrum_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion');
            $table->foreignId('asignador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media');
            $table->dateTime('tiempo_ejecucion')->nullable();
            $table->text('observacion')->nullable();
            $table->enum('estado', ['pendiente', 'en_progreso', 'completada'])->default('pendiente');
            $table->timestamps();

            $table->index(['estado', 'prioridad']);
            $table->index('responsable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrum_tasks');
    }
};
