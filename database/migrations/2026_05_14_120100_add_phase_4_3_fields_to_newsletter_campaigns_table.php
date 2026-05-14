<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('newsletter_campaigns', 'external_accepted_count')) {
                $table->unsignedInteger('external_accepted_count')->nullable()->after('external_failed_count');
            }
            if (!Schema::hasColumn('newsletter_campaigns', 'external_skipped_count')) {
                $table->unsignedInteger('external_skipped_count')->nullable()->after('external_accepted_count');
            }
            if (!Schema::hasColumn('newsletter_campaigns', 'external_requested_count')) {
                $table->unsignedInteger('external_requested_count')->nullable()->after('external_skipped_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_campaigns', function (Blueprint $table) {
            foreach (['external_accepted_count', 'external_skipped_count', 'external_requested_count'] as $col) {
                if (Schema::hasColumn('newsletter_campaigns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
