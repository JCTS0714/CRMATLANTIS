<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stage_id')->constrained('lead_stages');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');

            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('PEN');

            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();

            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();

            $table->string('document_type', 10)->nullable();
            $table->string('document_number', 20)->nullable();

            $table->timestamp('won_at')->nullable();

            $table->timestamps();

            $table->index(['stage_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
