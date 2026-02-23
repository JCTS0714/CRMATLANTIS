<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_event_reminder_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->unsignedInteger('reminder_offset_minutes');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['calendar_event_id', 'reminder_offset_minutes'], 'calendar_event_offset_unique');
            $table->index(['sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_reminder_sends');
    }
};
