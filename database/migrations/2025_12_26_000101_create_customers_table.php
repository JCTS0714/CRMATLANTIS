<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();

            $table->string('document_type', 10)->nullable();
            $table->string('document_number', 20)->nullable();

            $table->timestamps();

            $table->unique(['document_type', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
