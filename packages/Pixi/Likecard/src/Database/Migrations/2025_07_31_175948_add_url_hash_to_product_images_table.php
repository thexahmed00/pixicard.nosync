<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method is executed when you run the 'php artisan migrate' command.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Add the 'url_hash' column.
            // It is 'nullable' so existing records without a hash don't cause errors.
            // An 'index' is added for faster lookups when checking if a hash exists.
            $table->string('url_hash')->nullable()->after('path')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is executed when you run 'php artisan migrate:rollback'.
     * It should undo the changes made in the 'up' method.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // To properly drop the column, we should drop the index first.
            $table->dropIndex(['url_hash']);
            $table->dropColumn('url_hash');
        });
    }
};