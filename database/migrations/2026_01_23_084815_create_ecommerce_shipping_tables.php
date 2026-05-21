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
        Schema::create('ecommerce_shipping_methods', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id')->nullable()->index();

            $table->string('name');
            $table->enum('pricing_type', ['flat', 'weight', 'price'])->default('flat');
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('enabled')->default(true);

            $table->json('config')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_shipping_methods');
    }
};
