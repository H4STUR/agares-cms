<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->bigIncrements('id');

            // owner: Site | Category | Article
            $table->morphs('owner'); // owner_type, owner_id

            $table->string('name')->nullable();
            $table->string('variable')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'sort_order'], 'gallery_owner_sort_idx');
            $table->unique(['owner_type', 'owner_id', 'variable'], 'gallery_owner_variable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
