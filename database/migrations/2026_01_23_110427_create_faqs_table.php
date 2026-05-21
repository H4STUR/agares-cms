<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('faqs', function (Blueprint $table) {
      $table->id();
      $table->string('title')->nullable();
      $table->json('settings')->nullable(); // optional: e.g. heading text, etc.
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();

      $table->index('created_by');
      $table->index('updated_by');
    });
  }

  public function down(): void {
    Schema::dropIfExists('faqs');
  }
};
