<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_signups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->string('name', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 30)->nullable();
            $table->string('company_name', 190)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 30)->default('pending')->index(); // pending, order_created, paid, failed
            $table->string('razorpay_order_id', 120)->nullable()->index();
            $table->string('razorpay_payment_id', 120)->nullable()->index();
            $table->string('razorpay_signature', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_signups');
    }
};

