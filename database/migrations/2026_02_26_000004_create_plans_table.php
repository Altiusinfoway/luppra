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
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly', 'one_time'])->default('monthly');
            $table->unsignedInteger('trial_days')->default(0);
            $table->unsignedInteger('user_limit')->nullable();
            $table->unsignedInteger('whatsapp_limit')->nullable();
            $table->json('modules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'is_active']);
            $table->unique(['created_by', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
