<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->string('estado', 40)->default('factura_pendiente');
            $table->timestamps();

            $table->unique(['cliente_id', 'mes', 'anio']);
            $table->index(['estado', 'mes', 'anio']);
        });

        Schema::create('envios_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->unique()->constrained('pagos_mensuales')->cascadeOnDelete();
            $table->string('archivo_url', 2048)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('estado', 30)->default('preparado');
            $table->timestamp('fecha_preparado')->nullable();
            $table->timestamp('fecha_enviado')->nullable();
            $table->string('canal_envio', 30)->nullable();
            $table->string('message_id', 120)->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_enviado']);
        });

        Schema::create('auditoria_envios_factura', function (Blueprint $table) {
            $table->id();
            $table->string('accion', 120);
            $table->foreignId('pago_id')->nullable()->constrained('pagos_mensuales')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('detalles')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['accion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_envios_factura');
        Schema::dropIfExists('envios_factura');
        Schema::dropIfExists('pagos_mensuales');
    }
};
