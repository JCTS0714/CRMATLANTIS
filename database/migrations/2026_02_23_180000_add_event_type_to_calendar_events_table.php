<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('event_type', 50)->default('general')->after('id');
            $table->json('meta')->nullable()->after('related_id');

            $table->index(['assigned_to', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'event_type']);
            $table->dropColumn(['event_type', 'meta']);
        });
    }
};
