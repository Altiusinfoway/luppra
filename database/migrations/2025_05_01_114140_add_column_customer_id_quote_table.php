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
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }

            if (Schema::hasColumn('quotes', 'where_from')) {
                $table->dropColumn('where_from');
            }
        });


        Schema::table('quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('lead_id');
            $table->string('where_from')->nullable()->comment('Lead,Customer')->after('customer_id');
            $table->foreign('customer_id')->references('id')->on('entities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropColumn('where_from');
        });
    }
};
