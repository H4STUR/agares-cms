<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('input_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('input_instances', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                // optional FK if you want:
                // $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('input_instances', function (Blueprint $table) {
            if (Schema::hasColumn('input_instances', 'updated_by')) {
                // optional drop FK first if you added it
                // $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
