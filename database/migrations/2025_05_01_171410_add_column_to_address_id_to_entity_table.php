<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable()->default(0)->after('gst_no');
            $table->unsignedBigInteger('billing_address_id')->nullable()->default(0)->after('address_id');
            $table->unsignedBigInteger('shipping_address_id')->nullable()->default(0)->after('billing_address_id');
        });

        // Clean invalid foreign keys before adding constraints
        DB::table('entities')
            ->whereNotNull('address_id')
            ->whereNotIn('address_id', function ($query) {
                $query->select('id')->from('addresses');
            })
            ->update(['address_id' => null]);

        DB::table('entities')
            ->whereNotNull('billing_address_id')
            ->whereNotIn('billing_address_id', function ($query) {
                $query->select('id')->from('addresses');
            })
            ->update(['billing_address_id' => null]);

        DB::table('entities')
            ->whereNotNull('shipping_address_id')
            ->whereNotIn('shipping_address_id', function ($query) {
                $query->select('id')->from('addresses');
            })
            ->update(['shipping_address_id' => null]);

        // Now add foreign keys
        Schema::table('entities', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('billing_address_id')->references('id')->on('addresses')->onDelete('cascade');
            $table->foreign('shipping_address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropForeign(['billing_address_id']);
            $table->dropForeign(['shipping_address_id']);

            $table->dropColumn(['address_id', 'billing_address_id', 'shipping_address_id']);
        });
    }
};
