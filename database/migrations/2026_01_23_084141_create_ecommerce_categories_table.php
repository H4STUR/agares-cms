<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ecommerce_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('ecommerce_categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });


        Schema::create('ecommerce_product_category', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('ecommerce_categories')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);

            $table->primary(['product_id', 'category_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_category');
        Schema::dropIfExists('ecommerce_categories');
    }
};
