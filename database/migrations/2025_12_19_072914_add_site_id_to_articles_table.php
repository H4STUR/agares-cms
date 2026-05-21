<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('site_id')->after('id')->constrained('sites')->cascadeOnDelete();
            $table->index(['site_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropIndex(['site_id', 'title']);
            $table->dropColumn('site_id');
        });
    }

};
