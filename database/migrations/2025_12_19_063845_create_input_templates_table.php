<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('input_templates', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('scope'); // creates scope_type NULL + scope_id NULL

            $table->string('name');
            $table->string('applies_to'); // 'site' | 'category' | 'article'
            $table->timestamps();

            // Optional but useful: speed up lookups
            $table->index(['applies_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('input_templates');
    }
};
