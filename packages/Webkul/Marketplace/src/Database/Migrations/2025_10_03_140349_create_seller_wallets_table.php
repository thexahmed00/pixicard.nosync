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
        Schema::create('seller_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('seller_id');
            $table->unsignedInteger('vendor_id')->default(1);
            $table->decimal('amount', 12, 2)->default(0.00); // transaction amount (+/-)
            $table->unsignedInteger('order_id')->nullable(); // reference order
            $table->unsignedInteger('transaction_id')->nullable(); // optional note
            $table->timestamps();
            // Foreign keys
            $table->foreign('seller_id')->references('id')->on('marketplace_sellers')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_wallets');
    }
};
