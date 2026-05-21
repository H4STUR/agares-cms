<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_media', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('gallery_id')
                ->constrained('galleries')
                ->cascadeOnDelete();

            $table->foreignId('media_id')
                ->constrained('media')
                ->cascadeOnDelete();

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['gallery_id', 'media_id'], 'gallery_media_unique');
            $table->index(['gallery_id', 'sort_order'], 'gallery_media_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
    }
};
