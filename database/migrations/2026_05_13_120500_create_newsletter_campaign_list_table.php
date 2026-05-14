<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('newsletter_campaign_list', function (Blueprint $table) {
            $table->foreignId('newsletter_campaign_id')
                ->constrained('newsletter_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('newsletter_list_id')
                ->constrained('newsletter_lists')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(
                ['newsletter_campaign_id', 'newsletter_list_id'],
                'newsletter_campaign_list_pk'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaign_list');
    }
};
