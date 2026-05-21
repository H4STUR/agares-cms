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
        Schema::create('ecommerce_payment_providers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('driver'); // stripe, payu, p24, paypal
            $table->boolean('enabled')->default(false);
            $table->json('config')->nullable();

            $table->timestamps();
        });

        Schema::create('ecommerce_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('ecommerce_orders')
                ->cascadeOnDelete();

            $table->foreignId('provider_id')
                ->constrained('ecommerce_payment_providers');

            $table->enum('status', [
                'pending',
                'authorized',
                'captured',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending')->index();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('provider_payment_id')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_payments');
        Schema::dropIfExists('ecommerce_payment_providers');
    }
};
