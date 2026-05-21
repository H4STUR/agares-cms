<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }

            // You already have avatar, so only add if missing
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_id');
            }
        });

        // Add index safely (only if both columns exist)
        $this->addProviderIndexIfMissing();
    }

    private function addProviderIndexIfMissing(): void
    {
        // If either column is missing, skip
        if (!Schema::hasColumn('users', 'provider') || !Schema::hasColumn('users', 'provider_id')) {
            return;
        }

        // Try/catch because checking index existence is DB-specific
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['provider', 'provider_id'], 'users_provider_provider_id_index');
            });
        } catch (\Throwable $e) {
            // index likely already exists, ignore
        }
    }


    public function down(): void
    {
        // Drop index if present (ignore errors)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_provider_provider_id_index');
            });
        } catch (\Throwable $e) {}

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }

            // Only drop avatar if you *added* it via this migration.
            // Since you already had avatar, we should NOT drop it here.
            // So we skip dropping avatar entirely.
        });
    }

};
