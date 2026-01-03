<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('ruc', 50)->nullable();
            $table->string('usuario', 255)->nullable();
            $table->string('clave', 255)->nullable();

            $table->date('fecha_creacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->enum('tipo', ['OSE', 'PSE'])->nullable();

            $table->text('observacion')->nullable();
            $table->dateTime('ultima_notificacion')->nullable();
            $table->string('imagen', 255)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['nombre']);
            $table->index(['ruc']);
            $table->index(['fecha_vencimiento']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
