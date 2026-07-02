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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('start_date');
            $table->string('end_date');
            $table->string('total_days');
            $table->integer('leave_type')->comment('1=sick, 2=casual, 3= unpaid');
            $table->integer('hours_leave')->default(0);
            $table->text('reason')->nullable();
            $table->integer('status')->default(1)->comment('1=pending, 2=accept, 3= reject');
            $table->text('remark')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
