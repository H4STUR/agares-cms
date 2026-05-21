<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->string('background_image')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['description', 'avatar', 'background_image']);
        });
    }
};


// ALTER TABLE `users`
// ADD COLUMN `description` TEXT NULL AFTER `phone`,
// ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `description`,
// ADD COLUMN `background_image` VARCHAR(255) NULL AFTER `avatar`,
// ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE AFTER `background_image`,
// ADD COLUMN `last_active_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_active`;
