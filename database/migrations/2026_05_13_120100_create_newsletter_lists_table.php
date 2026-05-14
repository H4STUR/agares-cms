<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_lists', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false)->index();

            $table->timestamps();
        });

        Schema::create('newsletter_list_subscriber', function (Blueprint $table) {
            $table->foreignId('newsletter_list_id')
                ->constrained('newsletter_lists')
                ->cascadeOnDelete();

            $table->foreignId('newsletter_subscriber_id')
                ->constrained('newsletter_subscribers')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['newsletter_list_id', 'newsletter_subscriber_id'], 'newsletter_list_subscriber_pk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_list_subscriber');
        Schema::dropIfExists('newsletter_lists');
    }
};
