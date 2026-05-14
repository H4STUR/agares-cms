<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();

            $table->string('email')->unique();
            $table->string('name')->nullable();

            // pending | active | unsubscribed | bounced | complained
            $table->string('status', 20)->default('pending')->index();

            // website_form | admin | import | api
            $table->string('source', 50)->nullable()->index();

            // GDPR consent snapshot
            $table->text('consent_text')->nullable();
            $table->string('consent_ip', 45)->nullable();
            $table->text('consent_user_agent')->nullable();

            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->string('verification_token', 64)->nullable()->index();
            $table->string('unsubscribe_token', 64)->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
