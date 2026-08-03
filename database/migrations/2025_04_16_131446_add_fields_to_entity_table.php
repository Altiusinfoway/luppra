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
        if (!Schema::hasTable('entities')) {
            return;
        }

        Schema::table('entities', function (Blueprint $table) {
            if (!Schema::hasColumn('entities', 'company_name')) {
                $table->string('company_name')->nullable();
            }

            if (!Schema::hasColumn('entities', 'billing_address')) {
                $table->text('billing_address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('entities')) {
            return;
        }

        Schema::table('entities', function (Blueprint $table) {
            if (Schema::hasColumn('entities', 'billing_address')) {
                $table->dropColumn('billing_address');
            }

            if (Schema::hasColumn('entities', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
