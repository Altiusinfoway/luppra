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
        Schema::create('customer_price_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_id')->comment('entity tbl');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount', 5, 2)->default(0.00)->comment('(%) val');

            $table->foreign('customer_id')->references('id')->on('entities')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_price_histories');
    }
};
