<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->timestamp('delegated_at')->nullable()->after('test_sent_at');
            $table->timestamp('external_last_synced_at')->nullable()->after('external_status');
            $table->unsignedInteger('external_sent_count')->nullable()->after('external_last_synced_at');
            $table->unsignedInteger('external_failed_count')->nullable()->after('external_sent_count');
            $table->unsignedInteger('external_open_count')->nullable()->after('external_failed_count');
            $table->unsignedInteger('external_click_count')->nullable()->after('external_open_count');
            $table->text('external_last_error')->nullable()->after('external_click_count');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'delegated_at',
                'external_last_synced_at',
                'external_sent_count',
                'external_failed_count',
                'external_open_count',
                'external_click_count',
                'external_last_error',
            ]);
        });
    }
};
