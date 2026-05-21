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
        Schema::create('ecommerce_settings', function (Blueprint $table) {
            $table->id();

            // optional scope (one shop per site/tenant)
            $table->unsignedBigInteger('site_id')->nullable()->index();

            $table->string('key', 100)->collation('utf8mb4_unicode_ci');
            $table->text('value')->nullable()->collation('utf8mb4_unicode_ci');

            $table->string('category', 100)->default('general')->collation('utf8mb4_unicode_ci');

            $table->enum('type', ['string', 'integer', 'boolean', 'json'])
                ->default('string')
                ->collation('utf8mb4_unicode_ci');

            $table->string('description', 255)->nullable()->collation('utf8mb4_unicode_ci');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // unique key per site scope (null site_id = "global shop defaults")
            $table->unique(['site_id', 'key'], 'ecommerce_settings_site_key_unique');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_settings');
    }
};
