<?php
// database/migrations/2026_01_09_000001_create_cookie_scans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cookie_scans', function (Blueprint $table) {
            $table->id();

            // If you have a sites table and want to bind scans to a Site record, uncomment:
            // $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();

            $table->string('domain')->index();          // e.g. www.xeox.pl
            $table->string('url');                     // scanned URL
            $table->timestamp('scanned_at')->index();

            // Stats snapshot
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('first_party')->default(0);
            $table->unsignedInteger('third_party')->default(0);
            $table->unsignedInteger('secure')->default(0);
            $table->unsignedInteger('http_only')->default(0);

            $table->unsignedInteger('essential')->default(0);
            $table->unsignedInteger('functional')->default(0);
            $table->unsignedInteger('analytics')->default(0);
            $table->unsignedInteger('marketing')->default(0);

            // Privacy analysis snapshot
            $table->unsignedTinyInteger('privacy_score')->nullable();
            $table->string('privacy_grade', 2)->nullable();

            // Diagnostics
            $table->json('requested_domains')->nullable();
            $table->json('third_party_domains')->nullable();
            $table->json('ga_detected')->nullable(); // if you added it on API response

            // Full raw response (optional but handy for debugging)
            $table->json('raw_payload')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_scans');
    }
};
