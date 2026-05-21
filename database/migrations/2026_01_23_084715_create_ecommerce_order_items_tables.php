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
        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('ecommerce_orders')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();

            // SNAPSHOT
            $table->string('name');
            $table->string('sku')->nullable();

            $table->integer('qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->timestamps();
        });

        Schema::create('ecommerce_order_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('ecommerce_orders')
                ->cascadeOnDelete();

            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_status_history');
        Schema::dropIfExists('ecommerce_order_items');
    }
};
