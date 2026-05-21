<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();

            // Friendly label: "React Frontend Server", "Mobile Proxy", etc.
            $table->string('name');

            // Store only a hash (never store plaintext)
            $table->string('key_hash', 255)->unique();

            // Optional: lock key to a tenant/site if you have it
            $table->unsignedBigInteger('site_id')->nullable()->index();

            // JSON scopes/abilities: ["sites:read", "articles:read"]
            $table->json('abilities')->nullable();

            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable()->index();

            $table->timestamps();

            // Optional FK constraints if you want (adjust table names)
            // $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            // $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
