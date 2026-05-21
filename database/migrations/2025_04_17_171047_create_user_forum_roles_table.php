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
        Schema::create('user_forum_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable(); // For UI
            $table->string('icon')->nullable(); // Optional
            $table->timestamps();
        });
        
        Schema::create('user_forum_role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_forum_role_id')->constrained()->onDelete('cascade');

            $table->unique(['user_id', 'user_forum_role_id'], 'user_forum_role_user_unique');
            $table->index(['user_id']);
            $table->index(['user_forum_role_id']);
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_forum_role_user');
        Schema::dropIfExists('user_forum_roles');
    }
};


// CREATE TABLE `user_forum_roles` (
//     `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     `name` VARCHAR(255) NOT NULL,
//     `color` VARCHAR(50) NULL,
//     `icon` VARCHAR(255) NULL,
//     `created_at` TIMESTAMP NULL DEFAULT NULL,
//     `updated_at` TIMESTAMP NULL DEFAULT NULL
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


// CREATE TABLE `user_forum_role_user` (
//     `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//     `user_id` BIGINT UNSIGNED NOT NULL,
//     `user_forum_role_id` BIGINT UNSIGNED NOT NULL,
//     `created_at` TIMESTAMP NULL DEFAULT NULL,
//     `updated_at` TIMESTAMP NULL DEFAULT NULL,
//     FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
//     FOREIGN KEY (`user_forum_role_id`) REFERENCES `user_forum_roles`(`id`) ON DELETE CASCADE
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
