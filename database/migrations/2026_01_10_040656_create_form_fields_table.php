<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('form_fields', function (Blueprint $table) {
      $table->id();
      $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();

      $table->string('key'); // machine name: email, message, consent
      $table->string('type'); // text|email|tel|textarea|checkbox|number|date|file
      $table->string('label')->nullable();
      $table->string('placeholder')->nullable();
      $table->boolean('required')->default(false);

      $table->json('options')->nullable(); // future: select/radio/groups
      $table->unsignedInteger('sort_order')->default(0);

      $table->timestamps();

      $table->index(['form_id', 'sort_order']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('form_fields');
  }
};
