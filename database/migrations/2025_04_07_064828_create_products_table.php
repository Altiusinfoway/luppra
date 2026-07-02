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
        Schema::create(
            'products', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('image');
            $table->string('sku_code');
            $table->float('price',8,2);
            $table->float('dealer_price',8,2);
            $table->integer('created_by');
            $table->tinyInteger('is_active')->default(1); //Active = 1 | In-Active = 0 
            $table->tinyInteger('delete_status')->default(0); // Deleted = 1
            $table->timestamps();
        }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
