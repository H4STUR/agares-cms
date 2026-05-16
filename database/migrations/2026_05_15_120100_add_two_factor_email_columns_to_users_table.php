<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_email_code')) {
                $table->string('two_factor_email_code', 100)->nullable()->after('two_factor_method');
            }
            if (!Schema::hasColumn('users', 'two_factor_email_code_sent_at')) {
                $table->timestamp('two_factor_email_code_sent_at')->nullable()->after('two_factor_email_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['two_factor_email_code_sent_at', 'two_factor_email_code'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
