<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();

            $table->boolean('all_day')->default(false);
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();

            // Reminder
            $table->unsignedInteger('reminder_minutes')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('reminded_at')->nullable();

            // Link to Lead/Customer/etc.
            $table->nullableMorphs('related');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['assigned_to', 'start_at']);
            $table->index(['assigned_to', 'reminder_at', 'reminded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
