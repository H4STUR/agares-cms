<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ecommerce_tags', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->timestamps();
        });

        Schema::create('ecommerce_product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('ecommerce_tags')->cascadeOnDelete();

            $table->primary(['product_id', 'tag_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_tag');
        Schema::dropIfExists('ecommerce_tags');
    }
};
