<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomCodesTable extends Migration
{
    public function up()
    {
        Schema::create('custom_codes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['script', 'style']); // Type (script or style)
            $table->longText('content'); // The actual script/style content
            $table->string('description')->nullable(); // Optional description
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_codes');
    }
}

/*
CREATE TABLE `custom_codes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('script', 'style') NOT NULL,
  `content` LONGTEXT NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
);
*/