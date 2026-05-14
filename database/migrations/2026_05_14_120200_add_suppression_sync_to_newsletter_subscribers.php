<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('newsletter_subscribers', 'external_suppression_synced_at')) {
                $table->timestamp('external_suppression_synced_at')->nullable()->after('unsubscribed_at');
            }
            if (!Schema::hasColumn('newsletter_subscribers', 'external_suppression_sync_error')) {
                $table->string('external_suppression_sync_error', 500)->nullable()->after('external_suppression_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            foreach (['external_suppression_synced_at', 'external_suppression_sync_error'] as $col) {
                if (Schema::hasColumn('newsletter_subscribers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
