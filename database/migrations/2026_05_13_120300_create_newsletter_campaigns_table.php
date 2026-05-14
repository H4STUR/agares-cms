<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('subject');
            $table->longText('body')->nullable();

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('newsletter_templates')
                ->nullOnDelete();

            // draft | ready | test_sent | delegated | cancelled
            $table->string('status', 20)->default('draft')->index();

            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();

            // Phase 3 placeholders: external Agares SaaS delegation
            $table->string('external_campaign_id')->nullable()->index();
            $table->string('external_status', 50)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('test_sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaigns');
    }
};
