<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name');
            $table->string('slug')->unique();
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->integer('menu_order')->default(0);

            $table->string('title')->nullable();         // Metadata: Page title
            $table->text('description')->nullable();     // Metadata: Description
            $table->text('keywords')->nullable();        // Metadata: Keywords

            $table->string('template')->nullable();      // Template name or path

            // Redirect / Forward page
            $table->boolean('is_redirect')->default(false)->index();
            $table->string('redirect_url', 2048)->nullable();
            $table->unsignedSmallInteger('redirect_type')->default(302); // 301|302
            $table->boolean('redirect_new_tab')->default(false);

            $table->json('privileges')->nullable();      // Access privileges stored as JSON
            $table->string('status')->default('draft')->index();        // draft|published|scheduled|private
            $table->timestamp('published_at')->nullable()->index();     // used for published + scheduled

            $table->softDeletes(); // deleted_at
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('sites')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
