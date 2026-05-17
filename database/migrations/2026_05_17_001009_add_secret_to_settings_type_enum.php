<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend `settings.type` ENUM with `secret` for write-once / masked values
     * (e.g. newsletter SaaS API key, webhook secret). Without this, the seeder
     * and NewsletterSettingsController fail with "Data truncated for column 'type'".
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `settings` MODIFY COLUMN `type` "
            . "ENUM('string','integer','boolean','json','secret') "
            . "NOT NULL DEFAULT 'string' "
            . "COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        // Revert any rows that used the new value before stepping back down to the old enum.
        DB::table('settings')->where('type', 'secret')->update(['type' => 'string']);

        DB::statement("ALTER TABLE `settings` MODIFY COLUMN `type` "
            . "ENUM('string','integer','boolean','json') "
            . "NOT NULL DEFAULT 'string' "
            . "COLLATE utf8mb4_unicode_ci");
    }
};
