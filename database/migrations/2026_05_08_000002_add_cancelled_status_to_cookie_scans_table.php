<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cookie_scans MODIFY COLUMN status ENUM('pending','scanning','completed','failed','cancelled') NOT NULL DEFAULT 'completed'");
    }

    public function down(): void
    {
        // Move any cancelled rows to failed before shrinking the enum
        DB::statement("UPDATE cookie_scans SET status='failed' WHERE status='cancelled'");
        DB::statement("ALTER TABLE cookie_scans MODIFY COLUMN status ENUM('pending','scanning','completed','failed') NOT NULL DEFAULT 'completed'");
    }
};
