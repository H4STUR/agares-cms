<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('input_instances', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Polymorphic owner: Site / Category / Article
            $table->morphs('owner'); // owner_type, owner_id

            // Input definition
            $table->foreignId('input_field_id')
                ->constrained('input_fields')
                ->cascadeOnDelete();

            // Value & meta
            $table->string('label')->nullable();
            $table->string('variable')->nullable();
            $table->longText('value')->nullable();
            $table->text('description')->nullable();

            // Media support
            $table->foreignId('media_id')->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->foreignId('gallery_id')
                ->nullable()
                ->constrained('galleries')
                ->nullOnDelete();


            // Ordering / control
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Constraints
            $table->unique(
                ['owner_type', 'owner_id', 'variable'],
                'input_instances_owner_variable_unique'
            );

            $table->index(
                ['owner_type', 'owner_id', 'sort_order'],
                'input_instances_owner_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('input_instances');
    }
};
