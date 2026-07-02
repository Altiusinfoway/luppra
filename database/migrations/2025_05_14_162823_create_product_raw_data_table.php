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
        Schema::create('product_raw_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('inventory_plastic_id');
            $table->integer('plastic_weight')->default(0);
            $table->unsignedBigInteger('inventory_color_id');
            $table->integer('color_weight')->default(0);
            $table->decimal('color_weight_cal', 8, 2)->default(0.00);
            $table->unsignedBigInteger('inventory_matal_id');
            $table->integer('metal_weight')->default(0);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('inventory_plastic_id')->references('id')->on('inventory_plastics')->onDelete('cascade');
            $table->foreign('inventory_color_id')->references('id')->on('inventory_colors')->onDelete('cascade');
            $table->foreign('inventory_matal_id')->references('id')->on('inventory_metals')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_raw_data');
    }
};
