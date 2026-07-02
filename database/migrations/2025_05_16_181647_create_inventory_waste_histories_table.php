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
        Schema::create('inventory_waste_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_machinery_id');
            $table->integer('qty');
            $table->integer('price');
            $table->date('date');
            $table->unsignedBigInteger('user_id');

            $table->foreign('inventory_machinery_id')->references('id')->on('inventory_machineries')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_waste_histories');
    }
};
