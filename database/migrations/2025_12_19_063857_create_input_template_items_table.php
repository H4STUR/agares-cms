<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('input_template_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('input_template_id')
                ->constrained('input_templates')
                ->cascadeOnDelete();

            $table->foreignId('input_field_id')
                ->constrained('input_fields')
                ->cascadeOnDelete();

            // UI / identifier
            $table->string('label')->nullable();
            $table->string('variable'); // required for templates

            // default content
            $table->longText('default_value')->nullable();
            $table->text('description')->nullable();

            // ordering & rules
            $table->integer('sort_order')->default(0);
            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            $table->unique(['input_template_id', 'variable'], 'tpl_item_unique_variable');
            $table->index(['input_template_id', 'sort_order'], 'tpl_item_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('input_template_items');
    }
};
