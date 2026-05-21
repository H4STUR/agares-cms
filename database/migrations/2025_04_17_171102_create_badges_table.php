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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
        
        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('awarded_at')->nullable();

            $table->unique(['user_id', 'badge_id'], 'badge_user_unique');
            $table->index(['user_id']);
            $table->index(['badge_id']);
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
    }
};


// CREATE TABLE `badges` (
//     `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     `name` VARCHAR(255) NOT NULL,
//     `description` TEXT NULL,
//     `icon` VARCHAR(255) NULL,
//     `created_at` TIMESTAMP NULL DEFAULT NULL,
//     `updated_at` TIMESTAMP NULL DEFAULT NULL
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


// CREATE TABLE `badge_user` (
//     `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     `user_id` BIGINT UNSIGNED NOT NULL,
//     `badge_id` BIGINT UNSIGNED NOT NULL,
//     `awarded_at` TIMESTAMP NULL DEFAULT NULL,
//     `created_at` TIMESTAMP NULL DEFAULT NULL,
//     `updated_at` TIMESTAMP NULL DEFAULT NULL,
//     FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
//     FOREIGN KEY (`badge_id`) REFERENCES `badges`(`id`) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
