<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('file_name');
            $table->string('original_name')->nullable();

            // file_path can be longer than 255, better to increase
            $table->string('file_path', 1024);

            $table->string('mime_type');
            $table->bigInteger('size')->nullable();

            $table->enum('type', ['image', 'file']);

            // Meta for images
            $table->string('alternative')->nullable();
            $table->text('description')->nullable();

            // optional storage disk (you're using public_path uploads now, so can be null)
            $table->string('disk')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
