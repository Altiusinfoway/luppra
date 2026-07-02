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
        Schema::create('employee_payment_histories', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('employee_salary_detail_id');
            $table->unsignedBigInteger('payment_id');

            $table->foreign('employee_salary_detail_id')->references('id')->on('employee_salary_details')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payment_histories');
    }
};
