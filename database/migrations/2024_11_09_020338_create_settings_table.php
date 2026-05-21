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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->collation('utf8mb4_unicode_ci');
            $table->text('value')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('category', 100)->default('general')->collation('utf8mb4_unicode_ci');
            $table->enum('type', ['string', 'integer', 'boolean', 'json'])->default('string')->collation('utf8mb4_unicode_ci');
            $table->string('description', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
