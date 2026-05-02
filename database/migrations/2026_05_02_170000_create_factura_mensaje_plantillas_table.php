<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_mensaje_plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->text('contenido');
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('is_default');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_mensaje_plantillas');
    }
};
