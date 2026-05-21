<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('input_instance_id')
                ->constrained('input_instances')
                ->cascadeOnDelete();

            $table->string('question');
            $table->longText('answer')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['input_instance_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_items');
    }
};