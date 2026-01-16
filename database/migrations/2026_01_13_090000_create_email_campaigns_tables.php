<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('source', 20)->default('leads'); // leads|customers
            $table->string('subject_template', 255);
            $table->text('body_template');
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->string('contactable_type');
            $table->unsignedBigInteger('contactable_id');

            $table->string('display_name')->nullable();
            $table->string('email')->index();

            $table->string('rendered_subject', 255);
            $table->text('rendered_body');

            $table->string('status', 30)->default('pending');
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('error_message')->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['contactable_type', 'contactable_id']);
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('unsubscribed_at');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_unsubscribes');
    }
};
