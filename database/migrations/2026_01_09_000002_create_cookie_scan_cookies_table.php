<?php
// database/migrations/2026_01_09_000002_create_cookie_scan_cookies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cookie_scan_cookies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cookie_scan_id')->constrained('cookie_scans')->cascadeOnDelete();

            $table->string('name')->index();
            $table->text('value')->nullable(); // we'll store encrypted via model cast (optional)
            $table->string('domain')->index();
            $table->string('path')->default('/');

            $table->string('expires')->nullable();           // ISO string or "Session"
            $table->double('expires_timestamp')->nullable(); // your API returns float-ish

            $table->unsignedInteger('size')->default(0);

            $table->boolean('http_only')->default(false);
            $table->boolean('secure')->default(false);
            $table->string('same_site')->nullable();
            $table->boolean('session')->default(false);

            $table->string('type')->default('functional')->index(); // essential/functional/analytics/marketing
            $table->boolean('is_first_party')->default(true)->index();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['cookie_scan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_scan_cookies');
    }
};
