<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ecommerce_product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('ecommerce_products')
                ->cascadeOnDelete();

            // combination signature, e.g. "3-7"
            $table->string('signature', 255)->index();
            $table->unique(['product_id', 'signature'], 'product_variant_signature_unique');

            // variant image (uses your global media table)
            $table->unsignedBigInteger('image_media_id')->nullable()->index();
            // If your media table is named "media"
            $table->foreign('image_media_id')->references('id')->on('media')->nullOnDelete();

            $table->boolean('is_default')->default(false)->index();

            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->index();

            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->timestamp('sale_from')->nullable();
            $table->timestamp('sale_to')->nullable();

            $table->boolean('track_inventory')->default(true);
            $table->unsignedInteger('stock_qty')->nullable();
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'backorder'])
                ->default('in_stock')
                ->index();

            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->decimal('length', 10, 3)->nullable();

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_variants');
    }
};
