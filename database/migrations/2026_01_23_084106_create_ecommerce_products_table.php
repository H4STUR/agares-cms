<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ecommerce_products', function (Blueprint $table) {
        $table->id();

        $table->enum('status', ['draft', 'published', 'archived'])
            ->default('draft')
            ->index();

        $table->enum('product_type', ['simple', 'variable', 'digital', 'service'])
            ->default('simple')
            ->index();

        $table->string('name');
        $table->string('slug')->unique();

        // Inventory / identifiers
        $table->string('sku', 100)->nullable()->index();
        $table->unsignedInteger('stock')->nullable();            // null = not tracked
        $table->boolean('manage_stock')->default(false)->index();
        $table->boolean('is_in_stock')->default(true)->index();

        // Content
        $table->text('short_description')->nullable();
        $table->longText('description')->nullable();

        // Pricing
        $table->decimal('base_price', 12, 2)->nullable();
        $table->decimal('sale_price', 12, 2)->nullable();
        $table->timestamp('sale_from')->nullable();
        $table->timestamp('sale_to')->nullable();

        // SEO
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        $table->string('meta_keywords')->nullable();
        $table->string('canonical_url')->nullable();

        $table->unsignedBigInteger('created_by')->nullable()->index();
        $table->unsignedBigInteger('updated_by')->nullable()->index();

        $table->timestamps();
        $table->softDeletes();
    });



    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_products');
    }
};
