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
        Schema::connection('landlord')->create('tenant_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('metric', 50);
            $table->string('period_key', 20);
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'metric', 'period_key']);
            $table->index(['tenant_id', 'metric']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('tenant_usages');
    }
};
