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
        Schema::create('ecommerce_tax_rules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id')->nullable()->index();

            $table->string('country', 2)->nullable();
            $table->string('region')->nullable();
            $table->decimal('rate', 5, 2);

            $table->boolean('prices_include_tax')->default(true);
            $table->boolean('enabled')->default(true);
            $table->integer('priority')->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_tax_rules');
    }
};
