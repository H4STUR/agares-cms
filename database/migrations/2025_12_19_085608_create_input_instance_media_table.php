<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('input_instance_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('input_instance_id');
            $table->unsignedBigInteger('media_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('input_instance_id')->references('id')->on('input_instances')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');

            $table->unique(['input_instance_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('input_instance_media');
    }
};
