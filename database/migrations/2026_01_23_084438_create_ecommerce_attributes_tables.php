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
        Schema::create('ecommerce_attributes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['select', 'text'])->default('select');

            $table->timestamps();
        });

        Schema::create('ecommerce_attribute_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attribute_id')
                ->constrained('ecommerce_attributes')
                ->cascadeOnDelete();

            $table->string('value');
            $table->string('slug');
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['attribute_id', 'slug']);
        });


        Schema::create('ecommerce_variant_attribute_value', function (Blueprint $table) {
            $table->foreignId('variant_id')
                ->constrained('ecommerce_product_variants')
                ->cascadeOnDelete();

            $table->foreignId('attribute_value_id')
                ->constrained('ecommerce_attribute_values')
                ->cascadeOnDelete();

            $table->primary(['variant_id', 'attribute_value_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_variant_attribute_value');
        Schema::dropIfExists('ecommerce_attribute_values');
        Schema::dropIfExists('ecommerce_attributes');
    }
};
