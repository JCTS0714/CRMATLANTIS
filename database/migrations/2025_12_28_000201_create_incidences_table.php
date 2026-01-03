<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidences', function (Blueprint $table) {
            $table->id();

            $table->string('correlative', 20)->nullable()->unique();

            $table->foreignId('stage_id')->constrained('incidence_stages');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->date('date')->nullable();
            $table->enum('priority', ['alta', 'media', 'baja'])->default('media');
            $table->text('notes')->nullable();

            $table->timestamp('archived_at')->nullable();

            $table->timestamps();

            $table->index(['stage_id', 'updated_at']);
            $table->index(['customer_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidences');
    }
};
