<?php
// database/migrations/2026_01_09_000003_create_cookie_consent_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cookie_consent_settings', function (Blueprint $table) {
            $table->id();

            // If your CMS is multi-site, use site_id or domain.
            // Use domain for now since you said later you’ll send current domain to scan.
            $table->string('domain')->unique(); // e.g. agares demo domain

            $table->boolean('enabled')->default(true);
            $table->boolean('block_until_choice')->default(true); // block analytics/marketing until consent
            $table->boolean('remember_consent')->default(true);

            // Banner text
            $table->string('title')->default('We use cookies');
            $table->text('message')->nullable();

            // Button labels
            $table->string('btn_accept_all')->default('Accept all');
            $table->string('btn_reject_all')->default('Reject');
            $table->string('btn_manage')->default('Manage');
            $table->string('btn_save')->default('Save');

            // Category toggles default state (for UI)
            $table->boolean('allow_essential')->default(true);   // usually always true & locked
            $table->boolean('allow_functional')->default(true);
            $table->boolean('allow_analytics')->default(false);
            $table->boolean('allow_marketing')->default(false);

            // Optional per-category descriptions (for your form)
            $table->text('desc_essential')->nullable();
            $table->text('desc_functional')->nullable();
            $table->text('desc_analytics')->nullable();
            $table->text('desc_marketing')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consent_settings');
    }
};
