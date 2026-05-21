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
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('order_number')->unique();

            $table->enum('status', [
                'pending_payment',
                'processing',
                'on_hold',
                'completed',
                'cancelled',
                'refunded',
                'failed'
            ])->default('pending_payment')->index();

            $table->enum('payment_status', [
                'unpaid',
                'paid',
                'partially_refunded',
                'refunded'
            ])->default('unpaid')->index();

            $table->enum('fulfillment_status', [
                'unfulfilled',
                'partial',
                'fulfilled',
                'returned'
            ])->default('unfulfilled')->index();

            $table->string('currency', 3)->default('PLN');

            // SNAPSHOTS
            $table->json('billing_address');
            $table->json('shipping_address')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            $table->timestamp('placed_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_orders');
    }
};
