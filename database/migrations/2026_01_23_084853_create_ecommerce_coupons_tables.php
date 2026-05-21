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
        Schema::create('ecommerce_coupons', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id')->nullable()->index();

            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed', 'free_shipping']);
            $table->decimal('value', 12, 2)->nullable();

            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_customer')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('enabled')->default(true);

            $table->timestamps();
        });

        Schema::create('ecommerce_coupon_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coupon_id')
                ->constrained('ecommerce_coupons')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('ecommerce_orders')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->timestamp('redeemed_at')->useCurrent();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_coupon_redemptions');
        Schema::dropIfExists('ecommerce_coupons');
    }
};
