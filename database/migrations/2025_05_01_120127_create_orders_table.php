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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->string('customer_type');
            $table->unsignedBigInteger('customer_id');
            $table->date('date');
            $table->integer('status')->default(0)->comments('0=reject, 1=pending, 2=send, 3=final');
            $table->bigInteger('transport_id')->nullable();
            $table->double('gst')->nullable();
            $table->double('grand_total')->nullable();
            $table->tinyInteger('is_advance_payment')->default(0);
            $table->integer('payment_after_days')->nullable();
            $table->double('advance_payment')->nullable();
            $table->tinyInteger('is_final')->default(0);
            $table->text('notes')->nullable();
            $table->text('quote_invoice')->nullable();
            $table->bigInteger('created_by');
            $table->foreign('customer_id')->references('id')->on('entities')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
