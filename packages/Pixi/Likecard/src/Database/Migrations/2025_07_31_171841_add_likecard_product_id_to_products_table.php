<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add the column. It should be nullable and unique for a reliable link.
            $table->unsignedBigInteger('likecard_product_id')->nullable()->unique()->after('id');
            $table->index('likecard_product_id'); // Add an index for faster lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['likecard_product_id']);
            $table->dropColumn('likecard_product_id');
        });
    }
};